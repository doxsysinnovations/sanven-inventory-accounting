<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'product_id',
        'ordered_qty',
        'delivered_qty',
        'backorder_qty',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function batches()
    {
        return $this->hasMany(DeliveryNoteItemBatch::class);
    }
}

