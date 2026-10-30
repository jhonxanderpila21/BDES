<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;

class MaintenancePolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any maintenance records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the maintenance record.
     */
    public function view(User $user, Maintenance $maintenance): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create maintenance work orders.
     */
    public function create(User $user): bool
    {
        if ($user->isViewer() || $user->hasRole('viewer', 'staff')) {
            return false;
        }

        return $user->hasRole('maintenance_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can update the maintenance work order.
     */
    public function update(User $user, Maintenance $maintenance): bool
    {
        if ($user->isViewer() || $user->hasRole('viewer', 'staff')) {
            return false;
        }

        return $user->hasRole('maintenance_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can complete the maintenance work order.
     */
    public function complete(User $user, Maintenance $maintenance): bool
    {
        if ($user->isViewer() || $user->hasRole('viewer', 'staff')) {
            return false;
        }

        return $user->hasRole('maintenance_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can delete the maintenance record.
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        if ($user->isViewer() || $user->hasRole('viewer', 'staff')) {
            return false;
        }

        return false; // Handled by admin in before
    }
}

