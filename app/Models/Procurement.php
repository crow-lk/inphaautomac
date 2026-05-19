<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Procurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unitcost',
        'qty',
        'totalcost',
        'item_id',
        'item_brand_id',
        'vehicle_model',
        'selling_price',
    ];

    // Automatically calculate total cost before saving
    protected static function boot()
    {
        parent::boot();


        static::creating(function ($procurement) {
            $item=Item::find($procurement->item_id);
            if ($item) {
                $item->increment('qty',$procurement->qty);
                $item->cost_price = $procurement->unitcost;
                $item->selling_price = $procurement->selling_price;
                $item->save();
            }
        });

        static::updating(function ($procurement) {
            $item=Item::find($procurement->item_id);
            if ($item) {
                $item->cost_price = $procurement->unitcost;
                $item->selling_price = $procurement->selling_price;
                $item->save();
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_model', 'model');
    }

    public function itemBrand()
    {
        return $this->belongsTo(ItemBrand::class, 'item_brand_id');
    }
}
