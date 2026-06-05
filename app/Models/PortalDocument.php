<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalDocument extends Model
{
    protected $table = 'documents';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'signed' => 'boolean',
        ];
    }
}
