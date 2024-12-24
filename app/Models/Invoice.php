<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_name',
        'vehicle_number',
        'model',
        'mileage',
        'amount',
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
}