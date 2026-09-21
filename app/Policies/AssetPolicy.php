<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
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
     * Determine whether the user can view any assets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the asset.
     */
    public function view(User $user, Asset $asset): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create assets.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('inventory_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can update the asset.
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->hasRole('inventory_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can delete the asset.
     */
    public function delete(User $user, Asset $asset): bool
    {
        // Only administrator (handled in before) can permanently delete assets
        return false;
    }

    /**
     * Determine whether the user can retire / decommission the asset.
     */
    public function retire(User $user, Asset $asset): bool
    {
        return $user->hasRole('barangay_captain');
    }

    /**
     * Determine whether the user can upload documents to the asset dossier.
     */
    public function uploadDocument(User $user, Asset $asset): bool
    {
        return $user->hasRole('inventory_officer', 'maintenance_officer', 'bdrrmc_officer');
    }

    /**
     * Determine whether the user can delete an attached document.
     */
    public function deleteDocument(User $user, Asset $asset): bool
    {
        return false; // Handled by admin in before
    }
}

