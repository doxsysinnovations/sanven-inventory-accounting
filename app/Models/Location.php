<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class)
            ->using(AgentLocation::class)
            ->withTimestamps()
            ->withTrashed();
    }
}
