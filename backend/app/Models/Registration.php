<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    use HasFactory;

    public const RESERVATION_MINUTES = 10;

    protected $fillable = [
        'user_id',
        'event_id',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RegistrationStatus::PENDING->value);
    }

    public function scopeExpiredReservations(Builder $query): Builder
    {
        return $query
            ->where('status', RegistrationStatus::PENDING->value)
            ->where('expires_at', '<=', now());
    }

    public function isPending(): bool
    {
        return $this->status === RegistrationStatus::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === RegistrationStatus::CONFIRMED;
    }

    public function isReservationExpired(): bool
    {
        return $this->isPending()
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public function hasActiveReservation(): bool
    {
        return $this->isPending() && ! $this->isReservationExpired();
    }
}
