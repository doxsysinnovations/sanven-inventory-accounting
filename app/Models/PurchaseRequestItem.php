<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_request_id',
        'product_name',
        'product_description',
        'quantity',
        'estimated_cost',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
