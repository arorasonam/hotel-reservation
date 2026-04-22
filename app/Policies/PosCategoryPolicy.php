<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PosCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PosCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosCategory');
    }

    public function view(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('View:PosCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosCategory');
    }

    public function update(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('Update:PosCategory');
    }

    public function delete(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('Delete:PosCategory');
    }

    public function restore(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('Restore:PosCategory');
    }

    public function forceDelete(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('ForceDelete:PosCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosCategory');
    }

    public function replicate(AuthUser $authUser, PosCategory $posCategory): bool
    {
        return $authUser->can('Replicate:PosCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosCategory');
    }
}
