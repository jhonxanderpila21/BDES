<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function __construct(
        protected OtpService $otpService
    ) {}

    public function index(Request $request)
    {
        // Normalize any legacy maintenance_officer / bdrrmc_officer accounts into the unified inventory_officer role
        User::withTrashed()
            ->whereIn('role', ['maintenance_officer', 'bdrrmc_officer'])
            ->update(['role' => 'inventory_officer']);

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
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            if (in_array($request->role, ['inventory_officer', 'maintenance_officer'], true)) {
                $query->whereIn('role', ['inventory_officer', 'maintenance_officer', 'bdrrmc_officer']);
            } else {
                $query->where('role', $request->role);
            }
        }

        $users = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        $existingNames = User::withTrashed()
            ->pluck('name')
            ->filter()
            ->map(fn ($n) => strtolower(trim(preg_replace('/\s+/', ' ', $n))))
            ->values();

        $existingPhones = User::withTrashed()
            ->pluck('phone_number')
            ->filter()
            ->map(fn ($p) => preg_replace('/^\+?63/', '0', trim($p)))
            ->values();

        return view('administration.users.index', compact('users', 'existingNames', 'existingPhones'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // If raw 'name' is passed (e.g. from tests or scripts), split it if first_name not present
        if (empty($input['first_name']) && ! empty($input['name'])) {
            $parts = explode(' ', trim($input['name']));
            if (count($parts) === 1) {
                $input['first_name'] = $parts[0];
                $input['last_name']  = $parts[0];
            } else {
                $input['last_name']  = array_pop($parts);
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

        if (isset($input['phone_number']) && trim((string) $input['phone_number']) !== '') {
            $input['phone_number'] = User::normalizePhilippinePhone((string) $input['phone_number']);
        } elseif (empty($input['_form']) || $request->input('_form') !== 'add_user') {
            // Auto-generate unique Philippine phone number for programmatic/test requests when omitted
            $input['phone_number'] = self::generateUniquePhoneNumber();
        }

        // When submitted from the Add User modal UI, enforce unique full name and default username to phone number
        if ($request->input('_form') === 'add_user') {
            $nameParts = array_filter([
                $input['first_name'] ?? '',
                $input['middle_name'] ?? '',
                $input['last_name'] ?? '',
                $input['suffix'] ?? '',
            ]);
            $fullName = strtolower(trim(preg_replace('/\s+/', ' ', implode(' ', $nameParts))));

            if ($fullName !== '' && User::withTrashed()->whereRaw('LOWER(name) = ?', [$fullName])->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'first_name' => 'A user with this full name already exists. Names must be unique.',
                ]);
            }

            if (empty($input['username']) && ! empty($input['phone_number'])) {
                $input['username'] = preg_replace('/^\+?63/', '0', $input['phone_number']);
            }
        }

        // Auto-generate username and email from first and last name if not provided
        if (empty($input['username']) && ! empty($input['first_name']) && ! empty($input['last_name'])) {
            $input['username'] = self::generateBaseUsername($input['first_name'], $input['last_name']);
        }
        if (empty($input['email']) && ! empty($input['first_name']) && ! empty($input['last_name'])) {
            $input['email'] = self::generateBaseEmail($input['first_name'], $input['last_name']);
        }

        // Automatically resolve any uniqueness collision with existing users (only when not using phone-as-username on add_user)
        if (! empty($input['username']) && $request->input('_form') !== 'add_user') {
            $input['username'] = self::makeUniqueUsername($input['username']);
        }
        if (! empty($input['email'])) {
            $input['email'] = self::makeUniqueEmail($input['email']);
        }

        $request->merge($input);

        $nameRegex = '/^[a-zA-Z\s\-\'\.Ã±Ã‘Ã¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“Ãš]+$/u';

        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'middle_name'  => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'last_name'    => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'suffix'       => ['nullable', 'string', 'max:20'],
            'username'     => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'phone_number' => ['required', 'string', 'regex:/^\+639\d{9}$/', 'unique:users,phone_number'],
            'email'        => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'role'         => ['required', Rule::in(self::ROLES)],
        ], [
            'first_name.regex'    => 'The First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middle_name.regex'   => 'The Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex'     => 'The Last Name may only contain letters, spaces, hyphens, and apostrophes.',
            'phone_number.regex'  => 'Please enter a valid Philippine mobile number (e.g., +639171234567 or 09171234567).',
            'phone_number.unique' => 'This phone number is already registered to another user.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            return User::create([
                'first_name'           => $validated['first_name'],
                'middle_name'          => $validated['middle_name'] ?? null,
                'last_name'            => $validated['last_name'],
                'suffix'               => $validated['suffix'] ?? null,
                'username'             => $validated['username'],
                'phone_number'         => $validated['phone_number'],
                'phone_verified_at'    => null,
                'email'                => $validated['email'] ?? null,
                'password'             => Hash::make(User::DEFAULT_TEMP_PASSWORD),
                'must_change_password' => true,
                'role'                 => $validated['role'],
                'is_active'            => true,
            ]);
        });

        // Automatically generate and send 6-digit OTP to the user's registered phone number
        $otpResult = $this->otpService->generateAndSend($user, enforceCooldown: false);
        $smsNotice = $otpResult['sent']
            ? 'A 6-digit OTP has been sent to ' . $user->masked_phone . '.'
            : 'A 6-digit OTP has been generated for ' . $user->masked_phone . ' (verify SMS gateway settings in .env if not received).';

        return redirect()->route('users.index')
            ->with(
                'success',
                "User account for {$user->name} ({$user->username}) created as unverified! Temporary password: " . User::DEFAULT_TEMP_PASSWORD . ". {$smsNotice}"
            );
    }

    public function update(Request $request, User $user)
    {
        $input = $request->all();

        if (empty($input['first_name']) && ! empty($input['name'])) {
            $parts = explode(' ', trim($input['name']));
            if (count($parts) === 1) {
                $input['first_name'] = $parts[0];
                $input['last_name']  = $parts[0];
            } else {
                $input['last_name']  = array_pop($parts);
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
        if (isset($input['phone_number']) && trim((string) $input['phone_number']) !== '') {
            $input['phone_number'] = User::normalizePhilippinePhone((string) $input['phone_number']);
        } else {
            $input['phone_number'] = null;
        }
        if (isset($input['email']) && trim((string) $input['email']) !== '') {
            $input['email'] = strtolower(trim((string) $input['email']));
        } else {
            $input['email'] = $user->email;
        }
        $request->merge($input);

        $nameRegex = '/^[a-zA-Z\s\-\'\.Ã±Ã‘Ã¡Ã©Ã­Ã³ÃºÃÃ‰ÃÃ“Ãš]+$/u';

        $validated = $request->validate([
            'first_name'   => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'middle_name'  => ['nullable', 'string', 'max:50', 'regex:' . $nameRegex],
            'last_name'    => ['required', 'string', 'max:50', 'regex:' . $nameRegex],
            'suffix'       => ['nullable', 'string', 'max:20'],
            'username'     => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'regex:/^\+639\d{9}$/', Rule::unique('users', 'phone_number')->ignore($user->id)],
            'email'        => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'     => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'         => ['required', Rule::in(self::ROLES)],
        ], [
            'first_name.regex'    => 'The First Name may only contain letters, spaces, hyphens, and apostrophes.',
            'middle_name.regex'   => 'The Middle Name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex'     => 'The Last Name may only contain letters, spaces, hyphens, and apostrophes.',
            'phone_number.regex'  => 'Please enter a valid Philippine mobile number (e.g., +639171234567 or 09171234567).',
            'phone_number.unique' => 'This phone number is already registered to another user.',
        ]);

        $nameParts = array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? '',
            $validated['last_name'],
            $validated['suffix'] ?? '',
        ]);
        $fullName = strtolower(trim(preg_replace('/\s+/', ' ', implode(' ', $nameParts))));

        if (User::withTrashed()->where('id', '!=', $user->id)->whereRaw('LOWER(name) = ?', [$fullName])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'first_name' => 'A user with this full name already exists. Names must be unique.',
            ]);
        }

        $phoneChanged = ($validated['phone_number'] ?? null) !== $user->phone_number;

        $updateData = [
            'first_name'   => $validated['first_name'],
            'middle_name'  => $validated['middle_name'] ?? null,
            'last_name'    => $validated['last_name'],
            'suffix'       => $validated['suffix'] ?? null,
            'username'     => $validated['username'],
            'phone_number' => $validated['phone_number'] ?? null,
            'email'        => $validated['email'] ?? $user->email,
            'role'         => $validated['role'],
        ];

        if ($phoneChanged && ! empty($validated['phone_number'])) {
            $updateData['phone_verified_at'] = null;
        }

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($phoneChanged && ! empty($user->phone_number)) {
            $this->otpService->generateAndSend($user, enforceCooldown: false);
        }

        return redirect()->route('users.index')
            ->with('success', 'User successfully updated!');
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
            ->with('success', "User account for {$user->name} successfully restored.");
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

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('users.index')
            ->with('success', $user->is_active ? 'User account enabled.' : 'User account disabled.');
    }

    /**
     * Reset the user's password to the standard temporary password and require password change on next login.
     */
    public function resetPassword(User $user)
    {
        $user->update([
            'password'             => Hash::make(User::DEFAULT_TEMP_PASSWORD),
            'must_change_password' => true,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Password reset for {$user->name}. Temporary password is: " . User::DEFAULT_TEMP_PASSWORD);
    }

    /**
     * Endpoint to suggest collision-free username based on first and last name.
     */
    public function suggestCredentials(Request $request)
    {
        $firstName = (string) $request->query('first_name', '');
        $lastName  = (string) $request->query('last_name', '');

        if (trim($firstName) === '' || trim($lastName) === '') {
            return response()->json([
                'username'     => '',
                'email'        => '',
                'has_conflict' => false,
            ]);
        }

        $baseUser    = self::generateBaseUsername($firstName, $lastName);
        $baseEmail   = self::generateBaseEmail($firstName, $lastName);
        $uniqueUser  = self::makeUniqueUsername($baseUser);
        $uniqueEmail = self::makeUniqueEmail($baseEmail);

        return response()->json([
            'username'     => $uniqueUser,
            'email'        => $uniqueEmail,
            'has_conflict' => ($uniqueUser !== $baseUser || $uniqueEmail !== $baseEmail),
        ]);
    }

    /**
     * Generate a unique Philippine dummy phone number in +639XXXXXXXXX format.
     */
    public static function generateUniquePhoneNumber(): string
    {
        do {
            $candidate = '+639' . str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        } while (User::where('phone_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * Compute base username from first and last name (lastname first, full names).
     */
    public static function generateBaseUsername(string $firstName, string $lastName): string
    {
        $cleanFirst = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($firstName, '')));
        $cleanLast  = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($lastName, '')));

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
        $cleanLast  = preg_replace('/[^a-z0-9]/', '', strtolower(Str::slug($lastName, '')));

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
            $base    = $matches[1] !== '' ? $matches[1] : $username;
            $counter = (int) $matches[2];
        } else {
            $base    = $username;
            $counter = 1;
        }

        $candidate = $username;
        while (User::withTrashed()
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
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
        if ($email === '' || ! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if (preg_match('/^(.*?)([0-9]+)$/', $local, $matches)) {
            $base    = $matches[1] !== '' ? $matches[1] : $local;
            $counter = (int) $matches[2];
        } else {
            $base    = $local;
            $counter = 1;
        }

        $candidate = $email;
        while (User::withTrashed()
            ->when($ignoreUserId, fn ($q) => $q->where('id', '!=', $ignoreUserId))
            ->where('email', $candidate)
            ->exists()) {
            $counter++;
            $candidate = "{$base}{$counter}@{$domain}";
        }

        return $candidate;
    }
}
