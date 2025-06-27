<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'total_amount',
        'discount',
        'tax',
        'grand_total',
        'status',
        'payment_method',
        'due_date',
        'issued_date',
        'notes',
        'created_by',
        'updated_by',
        'agent_id',
    ];

    /**
     * Relationships
     */

    // An invoice belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // An invoice has many items
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // An invoice is created by a user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // An invoice is updated by a user
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the agent that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id', 'id');
    }
}
