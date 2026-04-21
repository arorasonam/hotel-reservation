<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PosOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PosOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PosOrder');
    }

    public function view(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('View:PosOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PosOrder');
    }

    public function update(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('Update:PosOrder');
    }

    public function delete(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('Delete:PosOrder');
    }

    public function restore(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('Restore:PosOrder');
    }

    public function forceDelete(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('ForceDelete:PosOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PosOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PosOrder');
    }

    public function replicate(AuthUser $authUser, PosOrder $posOrder): bool
    {
        return $authUser->can('Replicate:PosOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PosOrder');
    }
}
