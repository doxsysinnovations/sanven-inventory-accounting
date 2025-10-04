<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DatabaseBackupHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'disk',
        'file_size',
        'status',
        'error_message',
        'backup_date',
    ];

    protected $casts = [
        'backup_date' => 'datetime',
        'file_size' => 'decimal:2',
    ];
}
