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
        'sort',
    ];

    //when moduels are creating if battery_pack table vehicle_id is null, set is_inpha_auto_mac_owned to 1,if batterypack name starts with CINU, set not empty ir & capacitance from the latest module of the same serial number
    protected static function boot(){
        parent::boot();

        static::creating(function($module){
            if (isset($module->ir_value)) {
                $module->ir_value = $module->ir_value / 1000;
            }

            $batteryPack = $module->batteryPack;
            if(is_null($batteryPack->vehicle_id)){
                $module->is_inpha_auto_mac_owned = 1;
            }
            if(strpos($batteryPack->name, 'CINU') === 0){
                $latestModule = Module::where('serial_number', $module->serial_number)
                    ->whereNotNull('ir_value')
                    ->whereNotNull('capacitance')
                    ->latest()
                    ->first();
                if($latestModule){
                    $module->ir_value = $latestModule->ir_value;
                    $module->capacitance = $latestModule->capacitance;
                }
            }
        });

        static::updating(function($module){
            if (isset($module->ir_value)) {
                $module->ir_value = $module->ir_value / 1000;
            }
        });
    }




    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($module) {
    //         $batteryPack = $module->batteryPack;
    //         if (is_null($batteryPack->vehicle_id)) {
    //             $module->is_inpha_auto_mac_owned = 1;
    //         }
    //     });
    // }

    public function batteryPack()
    {
        return $this->belongsTo(BatteryPack::class);
    }

    // public function setIrValueAttribute($value)
    // {
    //     // Convert mΩ to Ω
    //     $this->attributes['ir_value'] = $value / 1000; // Convert from milliohms to ohms
    // }
}
