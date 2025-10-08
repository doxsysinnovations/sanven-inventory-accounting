<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_no',
        'journal_date',
        'reference_type',
        'reference_id',
        'description',
        'status',
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }
    public function invoice()
{
    return $this->belongsTo(\App\Models\Invoice::class, 'reference_id');
}
}
