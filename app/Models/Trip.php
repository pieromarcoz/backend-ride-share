<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $fillable = [
        'user_id',
        'driver_id',
        'is_started',
        'is_completed',
        'origin',
        'destination',
        'destination_name',
        'drive_location',
    ];

    protected $casts = [
        'origin' => 'array',
        'destination' => 'array',
        'drive_location' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
