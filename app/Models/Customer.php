<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $fillable = ['name', 'email', 'phone', 'address', 'type'];
    // Remove this line:
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }
}
