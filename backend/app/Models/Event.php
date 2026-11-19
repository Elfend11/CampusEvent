<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'title',
        'description',
        'location',
        'category',
        'registration_start',
        'registration_end',
        'event_start',
        'event_end',
        'capacity',
        'reserved_count',
        'sold_count',
        'ticket_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registration_start' => 'datetime',
            'registration_end' => 'datetime',
            'event_start' => 'datetime',
            'event_end' => 'datetime',
            'capacity' => 'integer',
            'reserved_count' => 'integer',
            'sold_count' => 'integer',
            'ticket_price' => 'decimal:2',
            'status' => EventStatus::class,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function officers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_officers')
            ->withTimestamps();
    }

    public function eventOfficers(): HasMany
    {
        return $this->hasMany(EventOfficer::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::PUBLISHED->value);
    }

    public function availableQuota(): int
    {
        return max(0, $this->capacity - $this->reserved_count - $this->sold_count);
    }

    public function isFree(): bool
    {
        return (float) $this->ticket_price === 0.0;
    }
}
