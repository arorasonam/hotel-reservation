<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Hotel;
use Illuminate\Auth\Access\HandlesAuthorization;

class HotelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Hotel');
    }

    public function view(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('View:Hotel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Hotel');
    }

    public function update(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('Update:Hotel');
    }

    public function delete(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('Delete:Hotel');
    }

    public function restore(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('Restore:Hotel');
    }

    public function forceDelete(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('ForceDelete:Hotel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Hotel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Hotel');
    }

    public function replicate(AuthUser $authUser, Hotel $hotel): bool
    {
        return $authUser->can('Replicate:Hotel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Hotel');
    }

}