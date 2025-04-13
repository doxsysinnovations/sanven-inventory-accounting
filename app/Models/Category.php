<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;
    protected $fillable = ['name', 'slug', 'is_active', 'is_parent', 'parent_id'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'is_active', 'is_parent', 'parent_id'])->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get all of the products for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /**
     * Get all of the types for the Category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function types(): HasMany
    {
        return $this->hasMany(ProductType::class, 'category_id', 'id');
    }
}
