<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Reservation = 'reservation';
    case FrontOffice = 'frontoffice';
    case Housekeeper = 'housekeeper';
    case User = 'user';

    public function isAdmin(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }
}
