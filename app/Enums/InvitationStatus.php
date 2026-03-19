<?php

namespace App\Enums;

/**
 * Estados para el campo `invitations.status`.
 */
enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Accepted => 'Aceptada',
            self::Rejected => 'Rechazada',
        };
    }
}

