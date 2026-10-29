<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AidRequest extends Model
{
    protected $fillable = [
        'disaster_report_id',
        'item_name',
        'quantity',
        'unit',
        'priority',
        'status',
        'notes',
    ];

    public function disasterReport(): BelongsTo
    {
        return $this->belongsTo(DisasterReport::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }
    public function items(): HasMany
{
    return $this->hasMany(AidRequestItem::class);
}
}
