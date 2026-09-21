<?php

namespace App\Policies;

use App\Models\Deployment;
use App\Models\User;

class DeploymentPolicy
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
     * Determine whether the user can view any deployments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the deployment.
     */
    public function view(User $user, Deployment $deployment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create deployments.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('inventory_officer', 'bdrrmc_officer', 'barangay_captain');
    }

    /**
     * Determine whether the user can process returns.
     */
    public function return(User $user, Deployment $deployment): bool
    {
        return $user->hasRole('inventory_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can delete the deployment record.
     */
    public function delete(User $user, Deployment $deployment): bool
    {
        return false; // Handled by admin in before
    }
}

