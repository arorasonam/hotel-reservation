<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class HotelAdminPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HotelAdmin');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:HotelAdmin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HotelAdmin');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:HotelAdmin');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:HotelAdmin');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:HotelAdmin');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:HotelAdmin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HotelAdmin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HotelAdmin');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:HotelAdmin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HotelAdmin');
    }

}