<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;
    protected $fillable = [
        'product_code',
        'name',
        'slug',
        'description',
        'brand_id',
        'category_id',
        'subcategory_id',
        'capital_price',
        'selling_price',
        'discount',
        'discount_price',
        'quantity'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'product_code',
                'name',
                'slug',
                'description',
                'brand_id',
                'category_id',
                'subcategory_id',
                'capital_price',
                'selling_price',
                'discount',
                'discount_price',
                'quantity'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
