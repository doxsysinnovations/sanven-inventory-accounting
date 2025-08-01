<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'invoice_id',
        'commission_amount',
        'status',
        'notes',
    ];

    /**
     * Relationship: Commission belongs to an Agent
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Relationship: Commission belongs to an Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
