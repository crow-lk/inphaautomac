<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inpha_Job extends Model
{
    //define table
    protected $table = 'inpha_jobs';
    use SoftDeletes;

    protected $fillable = [
        'job_id',
        'customer_id',
        'vehicle_id',
        'service_ids',
    ];

    protected $casts = [
        'service_ids' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
    
}
