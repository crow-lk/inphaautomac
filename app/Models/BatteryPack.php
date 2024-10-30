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
    ];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
