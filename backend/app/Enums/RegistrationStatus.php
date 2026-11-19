<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case EXPIRED = 'EXPIRED';
    case CANCELLED = 'CANCELLED';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::CONFIRMED, self::EXPIRED, self::CANCELLED],
            self::CONFIRMED => [self::CANCELLED],
            self::EXPIRED, self::CANCELLED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
