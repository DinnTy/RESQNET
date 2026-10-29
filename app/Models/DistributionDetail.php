<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionDetail extends Model
{
    protected $fillable = [
        'distribution_id',
        'aid_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function aidItem(): BelongsTo
    {
        return $this->belongsTo(AidItem::class);
    }
}
