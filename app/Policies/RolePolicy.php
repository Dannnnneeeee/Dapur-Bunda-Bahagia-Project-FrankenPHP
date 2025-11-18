<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view_roles', 'admin');
    }

    public function view(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('view_roles', 'admin');
    }

    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('create_roles', 'admin');
    }

    public function update(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('edit_roles', 'admin');
    }

    public function delete(Admin $admin, Role $role): bool
    {
        // Tidak bisa delete role super-admin
        if ($role->name === 'super-admin') {
            return false;
        }

        return $admin->hasPermissionTo('delete_roles', 'admin');
    }

    public function restore(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('edit_roles', 'admin');
    }

    public function forceDelete(Admin $admin, Role $role): bool
    {
        return $admin->hasPermissionTo('delete_roles', 'admin');
    }
}
