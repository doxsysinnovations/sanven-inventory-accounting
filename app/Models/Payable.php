<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $fillable = [
        'payable_no',
        'type',
        'reference_no',
        'payee_name',
        'payee_id',
        'amount',
        'due_date',
        'status',
        'payment_method',
        'payment_date',
        'attachment',
        'remarks',
        'created_by',
    ];
}
