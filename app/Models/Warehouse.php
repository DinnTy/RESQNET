<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'manager_name',
        'latitude',
        'longitude',
        'district',
        'city',
        'province',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(AidStock::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }
}
