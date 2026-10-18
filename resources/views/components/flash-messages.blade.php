{{--
    Single source of truth for flash + validation messages.
    Rendered ONCE by the layout — individual pages must NOT render
    session('success'|'error'|'warning'|'info'|'status') or $errors->any() themselves.
--}}
@php
    // Translate Breeze/Laravel machine status codes into human-friendly text.
    $statusMap = [
        'profile-updated'          => 'Your profile information has been updated.',
        'password-updated'         => 'Your password has been updated successfully.',
        'verification-link-sent'   => 'A new verification link has been sent to your email address.',
        'account-deleted'          => 'The account has been deactivated and archived.',
    ];
    $status = session('status');
    $statusText = $status ? ($statusMap[$status] ?? $status) : null;

    $messages = array_filter([
        'success' => session('success'),
        'error'   => session('error'),
        'warning' => session('warning'),
        'info'    => session('info') ?? $statusText,
    ]);
@endphp

@if($errors->any())
    <div class="mb-6">
        <x-alert type="error">
            <strong>Please correct the following:</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach(array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    </div>
@endif

@foreach($messages as $type => $message)
    <div class="mb-6">
        <x-alert :type="$type">{{ $message }}</x-alert>
    </div>
@endforeach
