<?php

namespace App\Enums;

/**
 * Where a Staff member is in their lifecycle. Staff members are never deleted.
 */
enum StaffStatus: string
{
    case Invited = 'invited';
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return __('staff.status.'.$this->value);
    }
}
