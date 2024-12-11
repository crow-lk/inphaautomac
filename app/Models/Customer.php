<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address', ];

    public function vehicles(){
        return $this->hasMany(Vehicle::class);
    }

    public function inpha_jobs(){
        return $this->hasMany(Inpha_Job::class);
    }
}
