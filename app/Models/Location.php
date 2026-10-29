<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'district',
        'city',
        'province',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(DisasterReport::class);
    }
}