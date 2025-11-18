<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return $admin->hasPermissionTo('view_orders');
    }

    /**
     * Determine whether the admin can view the model.
     */
    public function view(Admin $admin, Payment $payment): bool
    {
        return $admin->hasPermissionTo('view_orders');
    }

    /**
     * Determine whether the admin can create models.
     */
    public function create(Admin $admin): bool
    {
        return $admin->hasPermissionTo('create_orders');
    }

    /**
     * Determine whether the admin can update the model.
     */
    public function update(Admin $admin, Payment $payment): bool
    {
        return $admin->hasPermissionTo('edit_orders');
    }

    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $admin, Payment $payment): bool
    {
        return $admin->hasPermissionTo('delete_orders');
    }

    /**
     * Determine whether the admin can restore the model.
     */
    public function restore(Admin $admin, Payment $payment): bool
    {
        return $admin->hasPermissionTo('delete_orders');
    }

    /**
     * Determine whether the admin can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Payment $payment): bool
    {
        return $admin->hasPermissionTo('delete_orders');
    }
}
