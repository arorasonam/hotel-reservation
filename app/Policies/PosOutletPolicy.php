<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PosOutlet;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PosOutletPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosOutlet');
    }

    public function view(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('View:PosOutlet');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosOutlet');
    }

    public function update(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('Update:PosOutlet');
    }

    public function delete(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('Delete:PosOutlet');
    }

    public function restore(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('Restore:PosOutlet');
    }

    public function forceDelete(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('ForceDelete:PosOutlet');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosOutlet');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosOutlet');
    }

    public function replicate(AuthUser $authUser, PosOutlet $posOutlet): bool
    {
        return $authUser->can('Replicate:PosOutlet');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosOutlet');
    }
}
