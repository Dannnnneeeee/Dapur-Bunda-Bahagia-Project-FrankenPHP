<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
   use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view_products');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, Category $category): bool
    {
        return $admin->hasPermissionTo('view_products');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('create_products');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, Category $category): bool
    {
        return $admin->hasPermissionTo('edit_products');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, Category $category): bool
    {
        return $admin->hasPermissionTo('delete_products');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, Category $category): bool
    {
        return $admin->hasPermissionTo('delete_products');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Category $category): bool
    {
        return $admin->hasPermissionTo('delete_products');
    }
}
