<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'unit',
        'qty',
        'comment',
        'selling_price',
        'cost_price',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function ($item) {
            if ($item->isDirty('selling_price')) {
                $item->procurement()->update(['selling_price' => $item->selling_price]);
            }
        });
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }


    public function brandItems()
    {
        return $this->hasMany(ItemBrand::class);
    }

    public function procurement()
    {
        return $this->hasMany(Procurement::class);
    }

    public function brand()
    {
        return $this->belongsTo(ItemBrand::class);
    }
}
