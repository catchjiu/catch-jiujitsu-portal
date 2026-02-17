<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivateClassBooking extends Model
{
    protected $fillable = [
        'coach_id',
        'member_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'price',
        'requested_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
            'duration_minutes' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}
