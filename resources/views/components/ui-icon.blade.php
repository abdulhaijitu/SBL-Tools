@props(['name' => 'home'])
@php
    $paths = [
        'home' => 'M3 10l9-7 9 7v10a1 1 0 01-1 1h-5v-7H9v7H4a1 1 0 01-1-1z',
        'users' => 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m20 0v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'grid' => 'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
        'phone' => 'M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.12.96.35 1.9.69 2.79a2 2 0 01-.45 2.11L8.09 9.89a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0122 16.92z',
        'tree' => 'M12 3v6M5 15v-5h14v5M9 3h6v4H9zM2 16h6v5H2zM16 16h6v5h-6z',
        'shield' => 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11zM9 12l2 2 4-4',
        'plus' => 'M12 5v14M5 12h14',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'close' => 'M6 6l12 12M6 18L18 6',
        'logout' => 'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4m7 14 5-5-5-5m5 5H9',
        'check' => 'M20 6L9 17l-5-5',
        'clock' => 'M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];
@endphp
<svg {{ $attributes->class('ui-icon') }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['grid'] }}" /></svg>
