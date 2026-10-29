<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AidRequestItem extends Model
{
    protected $fillable = [
        'aid_request_id',
        'aid_item_id',
        'quantity',
        'unit',
        'priority',
        'notes',
    ];

    public function aidRequest(): BelongsTo
    {
        return $this->belongsTo(AidRequest::class);
    }

    public function aidItem(): BelongsTo
    {
        return $this->belongsTo(AidItem::class);
    }
}
