<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

trait HasPagination
{
    public function scopeList(Builder $query, Request $request, bool $return_as_list = false)
    {
        if (! $request->has('per_page') && ! $request->has('page')) {
            $list = $query->search($request)->get();

            if ($list->count()) {
                return $return_as_list ? $list : response()->success($list);
            }
        } else {
            $list =
                $query->search($request)
                    ->paginate(
                        $request->get('per_page', config('api.table_page_len'))
                        ,['*']
                        ,'page'
                        ,$request->page ?? 1
                    );

            $res = self::getPaginatorInfo($list);
            if ($res['data'] ?? false) {
                return $return_as_list ? $res : response()->detail($res);
            }
        }

        return $return_as_list ? [] : response()->failed('no_data');
    }


    public function scopeSearch(Builder $query, Request $request)
    {
        $query->when(method_exists(new (self::class), 'scopeListConditions'),
                fn (Builder $q) => $q->listConditions($request)
            )
            ->when($request->ids && is_array($request->ids),
                fn (Builder $q) => $q->whereIn('id', $request->ids)
            );


        if (! method_exists(self::class, 'getSearchableFields')) {
            $query->sort();
            return;
        }

        $searchable = self::getSearchableFields();
        $model = self::class;
        $table = (new $model())->getTable();
        $search = $request->search;

        $related = method_exists(self::class, 'getSearchableRelatedFields')
                    ? self::getSearchableRelatedFields()
                    : [];


        $query->when($search && strlen($search) >= config('api.search_keyword_len'),
                function ($q) use ($searchable, $related, $search, $table) {
                    return $q->where(function ($query) use ($searchable, $related, $search, $table) {
                        foreach ($searchable as $i => $field) {
                            $query = self::searchByKeyword($query, $table .'.'. $field, $search, $i);
                        }

                        // search by fullname
                        $query->when(in_array('first_name', $searchable) && in_array('last_name', $searchable),
                            fn ($q) =>
                                $q->orWhereRaw("concat(first_name, ' ', last_name) like ?", ['%'. $search .'%'])
                        );


                        foreach ($related as $relatedTable => $field) {
                            $i = 0;

                            $search_count = sizeof($searchable);
                            $query
                                ->when($search_count,
                                    fn ($q) =>
                                        $q->orWhereHas($relatedTable,
                                            fn ($q) => self::getRelatedCond($q, $searchable, $field, $search))
                                )
                                ->when(! $search_count,
                                    fn ($q) =>
                                        $q->whereHas($relatedTable,
                                            fn ($q) => self::getRelatedCond($q, $searchable, $field, $search))
                                );

                            $i++;
                        }

                        return $query;
                    });
                })
                ->sort();
    }


    public static function searchByKeyword(Builder $query, string $field, string $search, int $idx = 0)
    {
        return self::searchKeyword($query, $field, $search, $idx);
    }


    public static function searchKeyword(Builder $query, string $field, string $search, int $idx = 0)
    {
        $keyword = qualifyKeyword($search);

        return $query
            ->when($idx === 0,
                fn (Builder $q) => $q->where($field, 'LIKE', $keyword))
            ->when($idx,
                fn (Builder $q) => $q->orWhere($field, 'LIKE', $keyword))
            ->when(! filter_var(config('api.table_search_case_sensitive'), FILTER_VALIDATE_BOOLEAN),
                fn (Builder $q) =>
                    $q->orWhereRaw('lower('. $field.') like ?',[qualifyKeyword(strtolower($search))]));
    }


    protected static function getRelatedCond($q, $searchable, $field, $search)
    {
        if (! is_array($field)) {
            return self::searchByKeyword($q, $field, $search);
        }

        $i = 0;
        foreach ($field as $idx => $col) {
            if (is_string($idx)) {
                if (sizeof($searchable)) {
                    $q->orWhereHas($idx, fn ($q1) => self::getRelatedCond($q1, $searchable, $col, $search));
                } else {
                    $q->whereHas($idx, fn ($q1) => self::getRelatedCond($q1, $searchable, $col, $search));
                }
            } else {
                $q = self::searchByKeyword($q, $col, $search, $i);
            }

            $i++;
        }

        if ($i > 0 && is_array($field) && in_array('first_name', $field) && in_array('last_name', $field)) {
            $q->orWhereRaw('concat(first_name, " ", last_name) like ?', [qualifyKeyword($search)]);
        }

        return $q;
    }


    public function scopePage(Builder $query, Request $request): void
    {
        $query->skip($request->get('start', 0))
                ->take($request->get('length', config('api.table_page_len')));
    }
    
    
    public function scopeSort(
        Builder $query,
        string $order_by = null,
        string $sort_by = 'asc'
    ) {

        // validate
        if (request()->has('order')) {

            // change order
            $order = makeArray(request('order'));

            // validate order
            if (is_array($order)) {

                // prepare details
                $column = $order['column'] ?? '';
                $direction = $order['dir'] ?? 'asc';
                $concat = $order['concat'] ?? false;
                $concat_separator = $order['concat_separator'] ?? '" "';

                // get table columns
                $columns = Schema::getColumnListing($query->from);

                // check column
                if (in_array($column, $columns)) {

                    // general sort
                    $query->orderBy($column, $direction);
                } elseif($concat) {

                    // concat
                    $query->orderByRaw('CONCAT_WS(' . $concat_separator . ', ' . $column . ') ' . $direction);
                } elseif (str_contains($column, '.')
                    && count($column_data = explode('.', $column)) == 2
                ) {

                    // relationship sort
                    $query->relationshipSort($column_data[0], $column_data[1], $direction);
                }
            }
        } elseif(! is_null($order_by)) {

            // set ordering
            $query->orderBy($order_by, $sort_by);
        }
    }

    /**
     * Relationship Sort scope
     *
     * @param Builder $query
     * @param string $relationship
     * @param string $column
     * @param string $direction
     * @return void
     */
    public function scopeRelationshipSort(
        Builder $query,
        string $relationship,
        string $column,
        string $direction = 'asc'
    ) {

        // get parent models' data
        $model_class = get_class($query->getModel());
        $model_table = $query->getModel()->getTable();
        $model = new $model_class;


        // check if relation exists
        if (method_exists($model, $relationship)) {

            // foreign/relationship details
            $relationship_model = $model->{$relationship}()->getModel();
            $relationship_model_fields = $relationship_model->getFillable();
            if (empty($relationship_model_fields)) {
                $relationship_model_fields = Schema::getColumnListing($relationship_model->getTable());
            }

            // check if column is valid
            if (in_array($column, $relationship_model_fields)) {

                // prepare for the ordering
                $relationship_class = get_class($relationship_model);
                $foreign_key = $model->{$relationship}()->getForeignKeyName();

                // order
                $query->orderBy(
                    $relationship_class::select($column)
                        ->whereColumn('id', $model_table . '.' . $foreign_key),
                    $direction
                );
            }
        }
    }


    private static function getPaginatorInfo($list = null) {
        if ($list instanceof LengthAwarePaginator || $list instanceof Paginator) {
            return $list->toArray();
        }

        return [];
    }


    public static function deleteRecord(int $id, string $resource)
    {
        $record = self::find($id);
        if (! $record) {
            throw new ModelNotFoundException();
        }

        if (! $record->delete()) {
            return response()->failed();
        }

        return response()->message('record_deleted', ['record' => label($resource)]);
    }
}
