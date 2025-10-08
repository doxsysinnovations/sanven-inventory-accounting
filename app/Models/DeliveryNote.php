<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_note_number',
        'sales_order_id',
        'delivery_date',
        'status',
        'remarks',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        // Through the sales order
        return $this->hasOneThrough(Customer::class, SalesOrder::class, 'id', 'id', 'sales_order_id', 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}
