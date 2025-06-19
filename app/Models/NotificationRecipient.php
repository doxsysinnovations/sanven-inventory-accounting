<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationRecipient extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'email', 
        'is_active'
    ];
}
