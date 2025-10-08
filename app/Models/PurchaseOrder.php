<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'purchaser_id',
        'supplier_id',
        'order_type',
        'status',
        'remarks',
        'payment_terms'
    ];

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function approved_by_user()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
