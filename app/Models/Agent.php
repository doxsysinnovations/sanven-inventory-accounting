<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Agent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)
            ->using(AgentLocation::class)
            ->withTimestamps()
            ->withTrashed();
    }
    public function commissions()
{
    return $this->hasMany(AgentCommission::class);
}
}
