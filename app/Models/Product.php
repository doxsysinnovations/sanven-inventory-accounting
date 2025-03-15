<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //this is for Medical materials like drugs etc
    protected $fillable = [
        'code',
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'brand_id',
        'unit_id',
        'is_active',
        'created_by',
        'updated_by',
    ];
}
