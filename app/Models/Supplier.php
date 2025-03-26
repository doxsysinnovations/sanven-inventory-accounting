<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;
    protected $fillable = [
        'name',
        'address',
        'contact_number',
        'email',
        'is_active'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'address',
                'contact_number',
                'email',
                'is_active'
            ])->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
