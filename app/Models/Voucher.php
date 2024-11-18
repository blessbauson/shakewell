<?php

namespace App\Models;

use App\Traits\HasPagination;
use App\Traits\HasTimeStamp;
use App\Traits\HasUserTracker;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Voucher extends Model
{
    use HasFactory, SoftDeletes, HasUserTracker, HasTimeStamp, HasPagination;

    public $table       = 'vouchers';
    public $timestamps  = true;

    protected $fillable = [
        'id',
        'code',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    protected $hidden = [
        'pivot'
    ];
    

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    protected static function getSearchableFields(): array
    {
        return ['code'];
    }


    public function scopeListConditions($query, Request $request)
    {
        return $query->when(($request->has('user_id') && !empty($request->user_id)), function ($query) use ($request) {
                    $query->where('user_id', $request->user_id);
                }) 
                ->when(($request->has('code') && !empty($request->code)), function ($query) use ($request) {
                    $query->where('code', 'LIKE', '%'.$request->code.'%');
                }) 
                ->whereNull('deleted_at');
    }
}
