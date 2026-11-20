<?php

namespace App\Policies;

use App\Models\User;

/**
 * Only Admins manage Staff members, and nobody deletes them.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $staffMember): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $staffMember): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admins can deactivate anyone but themselves, so an install never locks out its last Admin by accident.
     */
    public function deactivate(User $user, User $staffMember): bool
    {
        return $user->isAdmin() && ! $user->is($staffMember);
    }

    public function delete(User $user, User $staffMember): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, User $staffMember): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $staffMember): bool
    {
        return false;
    }
}
