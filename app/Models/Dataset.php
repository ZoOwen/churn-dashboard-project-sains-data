<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
       protected $fillable = [
        'name',
        'original_filename',
        'stored_filename',
        'file_type',
        'file_size',
        'total_rows',
        'columns',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'processed_at' => 'datetime',
    ];
}
