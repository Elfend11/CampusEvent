<?php

namespace App\Enums;

enum TicketStatus: string
{
    case ACTIVE = 'ACTIVE';
    case USED = 'USED';
    case VOID = 'VOID';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::ACTIVE => [self::USED, self::VOID],
            self::USED, self::VOID => [],
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
