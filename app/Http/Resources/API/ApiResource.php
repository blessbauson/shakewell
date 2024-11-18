<?php
namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
