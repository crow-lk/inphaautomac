<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id', 'service_id', 'item_id', 'quantity', 'warranty_available',
        'warranty_type', 'price', 'is_service', 'is_item'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoiceItems) {
            $item = Item::find($invoiceItems->item_id);

            // Check if the item is not a service before decrementing
            if (!$invoiceItems->is_service) {
                $item->decrement('qty', $invoiceItems->quantity);
            }
        });

        static::saving(function ($invoiceItems) {
            // Check if the item is a service
            if ($invoiceItems->is_service) {
                $invoiceItems->quantity = 1; // Set quantity to 1 if it's a service
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
