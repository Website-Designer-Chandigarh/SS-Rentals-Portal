<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hire extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start' => 'date:Y-m-d',
            'end' => 'date:Y-m-d',
            'invoiced_to' => 'date:Y-m-d',
            'next_invoice' => 'date:Y-m-d',
            'bond_paid' => 'boolean',
            'signed' => 'boolean',
            'insurance_verified' => 'boolean',
            'checklist_done' => 'boolean',
        ];
    }
}
