<?php

namespace App\Enums;

/**
 * The roles a User can hold. Stored as spatie/laravel-permission role names.
 */
enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';

    /**
     * Roles that may open the admin panel.
     *
     * @return list<self>
     */
    public static function panelRoles(): array
    {
        return [self::Admin, self::Manager];
    }
}
