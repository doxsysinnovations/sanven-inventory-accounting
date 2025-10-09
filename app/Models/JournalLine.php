<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'memo',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

  
}
