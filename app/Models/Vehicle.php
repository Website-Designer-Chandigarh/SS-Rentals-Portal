<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'cof_expiry' => 'date:Y-m-d',
            'rego_expiry' => 'date:Y-m-d',
            'next_service' => 'date:Y-m-d',
            'insurance_expiry' => 'date:Y-m-d',
            'hire_start' => 'date:Y-m-d',
        ];
    }
}
