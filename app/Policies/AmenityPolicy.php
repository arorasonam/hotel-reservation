<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Amenity;
use Illuminate\Auth\Access\HandlesAuthorization;

class AmenityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Amenity');
    }

    public function view(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('View:Amenity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Amenity');
    }

    public function update(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('Update:Amenity');
    }

    public function delete(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('Delete:Amenity');
    }

    public function restore(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('Restore:Amenity');
    }

    public function forceDelete(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('ForceDelete:Amenity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Amenity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Amenity');
    }

    public function replicate(AuthUser $authUser, Amenity $amenity): bool
    {
        return $authUser->can('Replicate:Amenity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Amenity');
    }

}