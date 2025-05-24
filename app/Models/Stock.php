<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Stock extends Model implements HasMedia
{
    use SoftDeletes, LogsActivity, InteractsWithMedia;

    protected $fillable = [
        'stock_number',
        'product_id',
        'product_name', // Add product name
        'supplier_id',
        'quantity',
        'unit_id', // Add unit ID
        'price',
        'capital_price', // Add capital price
        'selling_price', // Add selling price
        'expiration_date',
        'manufactured_date', // Add manufactured date
        'batch_number',
        'location',
        'stock_location', // Add stock location
        'invoice_number', // Add invoice number
        'batch_notes', // Add batch notes
        'barcode',
        'remarks'
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'price' => 'decimal:2'
    ];

    public function getFormattedManufacturedDateAttribute()
    {
        return Carbon::parse($this->manufactured_date)->format('F j, Y');
    }

    public function getFormattedExpirationDateAttribute()
    {
        return Carbon::parse($this->expiration_date)->format('F j, Y');
    }
    
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
