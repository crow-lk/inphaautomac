<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'vehicle_number',
        'model',
        'mileage',
        'amount',
        'is_km',
        'is_miles',
        'is_invoice',
        'is_quatation',
        'credit_balance',
        'payment_status',
        'invoice_date'
    ];



    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

