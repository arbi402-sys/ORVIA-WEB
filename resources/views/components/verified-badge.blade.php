@props(['user' => null, 'size' => 'sm'])

@if($user && method_exists($user, 'isVerifiedInstitution') && $user->isVerifiedInstitution())
    @php
        $dim = match($size) {
            'xs' => 'w-3.5 h-3.5',
            'md' => 'w-5 h-5',
            'lg' => 'w-6 h-6',
            default => 'w-4 h-4',
        };
    @endphp
    <span
        class="inline-flex items-center justify-center shrink-0 align-middle"
        title="Lembaga Terverifikasi ORVIA"
        aria-label="Lembaga Terverifikasi"
        data-verified-badge="yellow"
    >
        <svg class="{{ $dim }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L14.85 4.18L18.43 3.97L19.6 7.38L22.58 9.42L21.65 12.88L22.58 16.34L19.6 18.38L18.43 21.79L14.85 21.58L12 23.76L9.15 21.58L5.57 21.79L4.4 18.38L1.42 16.34L2.35 12.88L1.42 9.42L4.4 7.38L5.57 3.97L9.15 4.18L12 2Z" fill="#FACC15"/>
            <path d="M8.5 12.5L10.8 14.8L15.8 9.8" stroke="#0B0B0B" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </span>
@endif
