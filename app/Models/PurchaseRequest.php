<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pr_number', 
        'requestor_id', 
        'request_type', 
        'status', 
        'remarks'

    ];

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }
}
