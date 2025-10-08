<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'status',
        'user_id',
        'reference',
        'notes',
        'balance_after',
    ];

    /**
     * Relation to Invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relation to uploaded proof files (if any).
     */
    public function proofs()
    {
        return $this->hasMany(PaymentProof::class);
    }
}
