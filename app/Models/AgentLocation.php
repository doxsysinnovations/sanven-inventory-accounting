<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentLocation extends Pivot
{
    use SoftDeletes;

    protected $table = 'agent_location';
    protected $guarded = [];
}
