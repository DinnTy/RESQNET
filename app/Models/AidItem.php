<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AidItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'unit',
        'description',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(AidStock::class);
    }

    public function distributionDetails(): HasMany
    {
        return $this->hasMany(DistributionDetail::class);
    }
}
