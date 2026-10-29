<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisasterReport extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'location_id',
        'title',
        'description',
        'severity',
        'status',
        'evidence',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DisasterCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function aidRequests(): HasMany
    {
        return $this->hasMany(AidRequest::class);
    }
}
