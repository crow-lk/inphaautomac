<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        'number',
        'brand',
        'model',
        'milage',
        'is_km',
        'is_miles',
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function batteryPack()
    {
        return $this->hasOne(BatteryPack::class);
    }

    public function inpha_jobs()
    {
        return $this->hasMany(Inpha_Job::class);
    }
}
