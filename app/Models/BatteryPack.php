<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatteryPack extends Model
{
    use SoftDeletes;

    protected $table = 'battery_packs';

    protected $fillable = [
        'name',
        'no_of_modules',
        'vehicle_id',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    //boot method to set name for new battery pack
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batteryPack) {
            //naming convension is NU-0000001, NU-0000002, etc
            $nextId = self::max('id') + 1;
            $batteryPack->name = 'NU-' . str_pad($nextId, 7, '0', STR_PAD_LEFT);
        });
    }
}
