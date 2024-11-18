<?php

namespace App\Traits;

trait HasTimeStamp
{
    public function serializeDate($date): ?string
    {
        return $date ?  $date->format('Y-m-d H:i:s') : null;
    }
}
