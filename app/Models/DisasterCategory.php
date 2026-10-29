<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(DisasterReport::class, 'category_id');
    }
}
