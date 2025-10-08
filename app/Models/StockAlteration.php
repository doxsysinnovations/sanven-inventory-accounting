<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlteration extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'type',
        'quantity',
        'reason',
        'user_id',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
