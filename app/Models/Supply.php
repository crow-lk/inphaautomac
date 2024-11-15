<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supply extends Model
{
    use SoftDeletes;

    protected $table = 'supplies';

    protected $fillable = [
        'item_name',
        'qty',
        'unit_price',
        'total',
    ];
}
