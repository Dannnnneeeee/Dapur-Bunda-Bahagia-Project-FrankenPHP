<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;
 /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view_users');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo('view_users');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('create_users');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo('edit_users');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo('delete_users');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo('delete_users');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, User $user): bool
    {
        return $admin->hasPermissionTo('delete_users');
    }
}
