<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'file_path',
        'file_type',
        'original_name',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
