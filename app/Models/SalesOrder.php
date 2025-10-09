<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'requested_delivery_date',
        'status',
        'payment_terms',
        'payment_method',
        'discount',
        'tax',
        'tax_rate',
        'subtotal',
        'grand_total',
        'notes',
        'terms_conditions',
        'agent_id',
    ];
    protected $casts = [
        'order_date' => 'date',
        'requested_delivery_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
