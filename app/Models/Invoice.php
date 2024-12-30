<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'customer_name',
        'vehicle_number',
        'model',
        'mileage',
        'amount',
        'is_km',
        'is_miles',
        'invoice_date'
    ];

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function calculateTotalAmount()
    {
        return $this->invoiceItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
