<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    use HandlesAuthorization;
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view_users', 'admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Admin $model): bool
    {
       return $admin->hasPermissionTo('view_users', 'admin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
         return $admin->hasPermissionTo('create_users', 'admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('edit_users', 'admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Admin $model): bool
    {
       if ($admin->id === $model->id) {
            return false;
        }

        return $admin->hasPermissionTo('delete_users', 'admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Admin $model): bool
    {
        return $admin->hasPermissionTo('edit_users', 'admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Admin $model): bool
    {
         return $admin->hasPermissionTo('delete_users', 'admin');
    }
}
