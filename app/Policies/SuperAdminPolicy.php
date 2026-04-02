<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuperAdminPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SuperAdmin');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:SuperAdmin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SuperAdmin');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:SuperAdmin');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:SuperAdmin');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:SuperAdmin');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:SuperAdmin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SuperAdmin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SuperAdmin');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:SuperAdmin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SuperAdmin');
    }

}