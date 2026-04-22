<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PosItem;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PosItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosItem');
    }

    public function view(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('View:PosItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosItem');
    }

    public function update(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('Update:PosItem');
    }

    public function delete(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('Delete:PosItem');
    }

    public function restore(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('Restore:PosItem');
    }

    public function forceDelete(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('ForceDelete:PosItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosItem');
    }

    public function replicate(AuthUser $authUser, PosItem $posItem): bool
    {
        return $authUser->can('Replicate:PosItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosItem');
    }
}
