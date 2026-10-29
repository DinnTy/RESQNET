<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'user_id',
        'activity',
        'old_status',
        'new_status',
        'description',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}