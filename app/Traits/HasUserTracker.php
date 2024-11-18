<?php

namespace App\Traits;

trait HasUserTracker
{
    public static function bootHasUserTracker()
    {
        static::creating(function ($model) {
            if (! $model->created_by) {
                $user_id = request()->get('user_id');
                $model->created_by = $user_id;
                $model->updated_by = $user_id;
            }
        });

        static::updating(function ($model) {
            $model->updated_by = request()->get('user_id');
        });

        static::deleting(function ($model) {
            if (! array_key_exists('deleted_by', $model->attributesToArray())) {
                return;
            }

            if (method_exists($model, 'trashed') && $model->email) {
                $model->email = 'deleted_'. time() .'_'. $model->email;
            }

            $model->deleted_by = request()->get('user_id');
            $model->save();
        });
    }
}
