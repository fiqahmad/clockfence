<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

/**
 * Admins manage Sites; Managers can see them; nobody deletes them.
 */
class SitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Site $site): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Site $site): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Site $site): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Site $site): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Site $site): bool
    {
        return false;
    }

    public function forceDelete(User $user, Site $site): bool
    {
        return false;
    }
}
