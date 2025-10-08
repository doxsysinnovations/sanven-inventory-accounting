<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItemBatch extends Model
{
    protected $fillable = [
        'delivery_note_item_id',
        'stock_id',
        'allocated_qty',
    ];

    public function deliveryNoteItem()
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
