<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $table = 'modules';

    protected $fillable = [
        'serial_number',
        'ir_value',
        'capacitance',
        'battery_pack_id',
    ];

    //when moduels are creating if battery_pack table vehicle_id is null, set is_inpha_auto_mac_owned to 1
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            $batteryPack = $module->batteryPack;
            if (is_null($batteryPack->vehicle_id)) {
                $module->is_inpha_auto_mac_owned = 1;
            }
        });
    }

    public function batteryPack()
    {
        return $this->belongsTo(BatteryPack::class);
    }
}
