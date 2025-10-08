<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'id',
        'code',
        'name',
        'type',
        'category',
        'normal_balance',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public $incrementing = false; // Because you use fixed IDs
    protected $keyType = 'int';

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'account_id');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }
    // Accessor for balance
    public function getBalanceAttribute()
    {
        $debits = $this->journalLines()->sum('debit');
        $credits = $this->journalLines()->sum('credit');

        return $this->normal_balance === 'debit'
            ? $debits - $credits
            : $credits - $debits;
    }
    public function getTrialBalanceAttribute()
    {
        $balance = $this->balance;

        return [
            'debit'  => $this->normal_balance === 'debit' ? max($balance, 0) : 0,
            'credit' => $this->normal_balance === 'credit' ? max($balance, 0) : 0,
        ];
    }
}
