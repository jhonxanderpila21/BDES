<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public const ROLES = [
        'administrator',
        'inventory_officer',
        'maintenance_officer',
        'viewer',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = User::query();

        if ($status === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest('id')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // If raw 'name' is passed (e.g. from tests or scripts), split it if first_name not present
        if (empty($input['first_name']) && !empty($input['name'])) {
            $parts = explode(' ', trim($input['name']));
            if (count($parts) === 1) {
                $input['first_name'] = $parts[0];
                $input['last_name'] = $parts[0];
            } else {
                $input['last_name'] = array_pop($parts);
                $input['first_name'] = implode(' ', $parts);
            }
        }

        foreach (['first_name', 'middle_name', 'last_name', 'suffix'] as $field) {
            if (isset($input[$field])) {
                $input[$field] = strip_tags(trim($input[$field]));
            }
        }

        if (isset($input['username'])) {
            $input['username'] = strtolower(trim(strip_tags($input['username'])));
        }
        if (isset($input['email'])) {
            $input['email'] = strtolower(trim($input['email']));
        }

        // Auto-generate username and email from first and last name if not provided
        if (empty($input['username']) && !empty($input['first_name']) && !empty($input['last_name'])) {
            $input['username'] = self::generateBaseUsername($input['first_name'], $input['last_name']);
        }
        if (empty($input['email']) && !empty($input['first_name']) && !empty($input['last_name'])) {
            $input['email'] = self::generateBaseEmail($input['first_name'], $input['last_name']);
        }

        // Automatically resolve any uniqueness collision with existing users (including soft-deleted)
        if (!empty($input['username'])) {
            $input['username'] = self::makeUniqueUsername($input['username']);
        }
        if (!empty($input['email'])) {
            $input['email'] = self::makeUniqueEmail($input['email']);
        }

        $request->merge($input);

        $nameRegex = '/^[a-zA-Z\s\-\'\.ñÑáéíóúÁÉÍÓÚ]+$/u';

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'middle_name' => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'last_name' => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'suffix' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(self::ROLES)],
        ], [
            'first_name.regex' => 'The First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middle_name.regex' => 'The Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'The Last Name may only contain letters, spaces, hyphens, and apostrophes.',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make(User::DEFAULT_TEMP_PASSWORD),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} created successfully! Temporary password set to: " . User::DEFAULT_TEMP_PASSWORD);
    }

    public function update(Request $request, User $user)
    {
        $input = $request->all();

        if (empty($input['first_name']) && !empty($input['name'])) {
            $parts = explode(' ', trim($input['name']));
            if (count($parts) === 1) {
                $input['first_name'] = $parts[0];
                $input['last_name'] = $parts[0];
            } else {
                $input['last_name'] = array_pop($parts);
                $input['first_name'] = implode(' ', $parts);
            }
        }

        foreach (['first_name', 'middle_name', 'last_name', 'suffix'] as $field) {
            if (isset($input[$field])) {
                $input[$field] = strip_tags(trim($input[$field]));
            }
        }
        if (isset($input['username'])) {
            $input['username'] = strtolower(trim(strip_tags($input['username'])));
        }
        if (isset($input['email'])) {
            $input['email'] = strtolower(trim($input['email']));
        }
        $request->merge($input);

        $nameRegex = '/^[a-zA-Z\s\-\'\.ñÑáéíóúÁÉÍÓÚ]+$/u';

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'middle_name' => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'last_name' => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'suffix' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ROLES)],
        ], [
            'first_name.regex' => 'The First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middle_name.regex' => 'The Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'The Last Name may only contain letters, spaces, hyphens, and apostrophes.',
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        if ($user->isAdmin()) {
            $activeAdminCount = User::whereIn('role', ['administrator', 'barangay_captain'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where('id', '!=', $user->id)
                ->count();

            if ($activeAdminCount < 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot delete or archive the only remaining active administrator account!');
            }
        }

        $user->update(['is_active' => false]);
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User account for {$user->name} has been archived (soft-deleted).");
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index', ['status' => 'archived'])
            ->with('success', "User account for {$user->name} restored successfully.");
    }

    /**
     * Toggle whether the user's account is active (disable/enable).
     */
    public function toggleActive(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot disable your own account!');
        }

        if ($user->isAdmin() && $user->is_active) {
            $otherActiveAdmins = User::whereIn('role', ['administrator', 'barangay_captain'])
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins < 1) {
                return redirect()->route('users.index')
                    ->with('error', 'Cannot disable the only remaining active administrator account!');
            }
        }

        $user->update(['is_active' => !$user->is_active]);

        return redirect()->route('users.index')
            ->with('success', $user->is_active ? 'User account enabled.' : 'User account disabled.');
    }

    /**
     * Reset the user's password to the standard temporary password.
     */
    public function resetPassword(User $user)
    {
        $user->update(['password' => Hash::make(User::DEFAULT_TEMP_PASSWORD)]);

        return redirect()->route('users.index')
            ->with('success', "Password reset for {$user->name}. Temporary password is: " . User::DEFAULT_TEMP_PASSWORD);
    }

    /**
     * Endpoint to suggest collision-free username and email based on first and last name.
     */
    public function suggestCredentials(Request $request)
    {
        $firstName = (string) $request->query('first_name', '');
        $lastName = (string) $request->query('last_name', '');

        if (trim($firstName) === '' || trim($lastName) === '') {
            return response()->json([
                'username' => '',
                'email' => '',
                'has_conflict' => false,
            ]);
        }

        $baseUser = self::generateBaseUsername($firstName, $lastName);
        $baseEmail = self::generateBaseEmail($firstName, $lastName);

        $uniqueUser = self::makeUniqueUsername($baseUser);
        $uniqueEmail = self::makeUniqueEmail($baseEmail);

        return response()->json([
            'username' => $uniqueUser,
            'email' => $uniqueEmail,
            'has_conflict' => ($uniqueUser !== $baseUser || $uniqueEmail !== $baseEmail),
        ]);
    }

    /**
     * Compute base username from first and last name (lastname first, full names).
     */
    public static function generateBaseUsername(string $firstName, string $lastName): string
    {
        $cleanFirst = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($firstName, '')));
        $cleanLast = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($lastName, '')));

        if ($cleanLast !== '' && $cleanFirst !== '') {
            $base = "{$cleanLast}{$cleanFirst}";
        } else {
            $base = ($cleanLast ?: $cleanFirst) ?: 'user';
        }

        return $base;
    }

    /**
     * Compute base email from first and last name (lastname first, full names, dot-separated).
     */
    public static function generateBaseEmail(string $firstName, string $lastName, string $domain = 'bdes.gov'): string
    {
        $cleanFirst = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($firstName, '')));
        $cleanLast = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($lastName, '')));

        if ($cleanLast !== '' && $cleanFirst !== '') {
            $prefix = "{$cleanLast}.{$cleanFirst}";
        } else {
            $prefix = ($cleanLast ?: $cleanFirst) ?: 'user';
        }

        return "{$prefix}@{$domain}";
    }

    /**
     * Auto-increment username counter if it collides with an existing or archived user.
     */
    public static function makeUniqueUsername(string $username, ?int $ignoreUserId = null): string
    {
        $username = strtolower(trim($username));
        if ($username === '') {
            return $username;
        }

        if (preg_match('/^(.*?)([0-9]+)$/', $username, $matches)) {
            $base = $matches[1] !== '' ? $matches[1] : $username;
            $counter = (int) $matches[2];
        } else {
            $base = $username;
            $counter = 1;
        }

        $candidate = $username;
        while (User::withTrashed()
            ->when($ignoreUserId, fn($q) => $q->where('id', '!=', $ignoreUserId))
            ->where('username', $candidate)
            ->exists()) {
            $counter++;
            $candidate = "{$base}{$counter}";
        }

        return $candidate;
    }

    /**
     * Auto-increment email counter if it collides with an existing or archived user.
     */
    public static function makeUniqueEmail(string $email, ?int $ignoreUserId = null): string
    {
        $email = strtolower(trim($email));
        if ($email === '' || !str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if (preg_match('/^(.*?)([0-9]+)$/', $local, $matches)) {
            $base = $matches[1] !== '' ? $matches[1] : $local;
            $counter = (int) $matches[2];
        } else {
            $base = $local;
            $counter = 1;
        }

        $candidate = $email;
        while (User::withTrashed()
            ->when($ignoreUserId, fn($q) => $q->where('id', '!=', $ignoreUserId))
            ->where('email', $candidate)
            ->exists()) {
            $counter++;
            $candidate = "{$base}{$counter}@{$domain}";
        }

        return $candidate;
    }
}
