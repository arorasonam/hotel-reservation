<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HotelGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class HotelGroupPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HotelGroup');
    }

    public function view(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('View:HotelGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HotelGroup');
    }

    public function update(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('Update:HotelGroup');
    }

    public function delete(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('Delete:HotelGroup');
    }

    public function restore(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('Restore:HotelGroup');
    }

    public function forceDelete(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('ForceDelete:HotelGroup');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HotelGroup');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HotelGroup');
    }

    public function replicate(AuthUser $authUser, HotelGroup $hotelGroup): bool
    {
        return $authUser->can('Replicate:HotelGroup');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HotelGroup');
    }

}