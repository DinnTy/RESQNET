<?php

namespace App\Models;

use App\Models\DistributionActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distribution extends Model
{
    protected $fillable = [
        'aid_request_id',
        'warehouse_id',
        'assigned_by',
        'status',
        'tracking_code',
        'shipped_at',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function aidRequest(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DistributionDetail::class);
    }
    public function activities()
{
    return $this->hasMany(DistributionActivity::class)
        ->with('user')
        ->latest();
}
}
