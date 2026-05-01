<?php

namespace App\Policies;

use App\Models\HousekeepingTask;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HousekeepingTaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HousekeepingTask')
            || $authUser->can('View:HousekeepingTask')
            || $authUser->hasRole('housekeeping');
    }

    public function view(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('View:HousekeepingTask')
            || $authUser->hasRole('housekeeping');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HousekeepingTask');
    }

    public function update(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('Update:HousekeepingTask')
            || $authUser->hasRole('housekeeping');
    }

    public function delete(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('Delete:HousekeepingTask');
    }

    public function restore(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('Restore:HousekeepingTask');
    }

    public function forceDelete(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('ForceDelete:HousekeepingTask');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HousekeepingTask');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HousekeepingTask');
    }

    public function replicate(AuthUser $authUser, HousekeepingTask $housekeepingTask): bool
    {
        return $authUser->can('Replicate:HousekeepingTask');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HousekeepingTask');
    }
}
