<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $guarded = [];

    /**
     * Allowed moderation transitions. Rejected/hidden reviews can be
     * re-approved so a moderator can reverse an earlier decision.
     */
    public const MODERATION_TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'approved' => ['hidden', 'rejected'],
        'rejected' => ['hidden', 'approved'],
        'hidden' => ['approved', 'rejected'],
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::MODERATION_TRANSITIONS[$this->status] ?? [], true);
    }
}
