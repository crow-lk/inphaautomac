<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueBatteryPacks extends Model
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
}
