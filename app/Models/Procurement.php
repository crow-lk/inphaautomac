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
    ];

    // Automatically calculate total cost before saving
    protected static function boot()
    {
        parent::boot();


        static::creating(function ($procurement) {
            $item=Item::find($procurement->name);
            $item->increment('qty',$procurement->qty);
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
