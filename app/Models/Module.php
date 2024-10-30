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

    public function batteryPack()
    {
        return $this->belongsTo(BatteryPack::class);
    }
}
