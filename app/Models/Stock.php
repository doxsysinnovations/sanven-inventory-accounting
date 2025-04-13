<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'stock_number',
        'product_id',
        'supplier_id',
        'quantity',
        'price',
        'expiration_date',
        'batch_number',
        'location',
        'barcode',
        'remarks'
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'stock_number',
                'product_id',
                'supplier_id',
                'quantity',
                'price',
                'expiration_date',
                'batch_number',
                'location',
                'barcode',
                'remarks'
            ])->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
