@props([
    'phoneRef' => null,
    'nameRef' => null,
    'phoneSelector' => null,
    'nameSelector' => null,
    'size' => 'sm'
])

@php
    $sizeClasses = $size === 'xs' ? 'p-1 text-xs' : 'p-1.5 text-xs';
@endphp

<button type="button" 
        @click="window.pickMobileContact ? window.pickMobileContact({{ $phoneRef ? '$refs.' . $phoneRef : ($phoneSelector ? '\'' . $phoneSelector . '\'' : '$el.closest(\'.relative\').querySelector(\'input[type=text], input[type=tel]\')') }}, {{ $nameRef ? '$refs.' . $nameRef : ($nameSelector ? '\'' . $nameSelector . '\'' : 'null') }}) : null"
        title="মোবাইল কন্টাক্ট থেকে নম্বর আনুন"
        aria-label="মোবাইল কন্টাক্ট থেকে নম্বর আনুন"
        class="inline-flex items-center justify-center {{ $sizeClasses }} text-slate-400 hover:text-orange-600 hover:bg-orange-50 active:scale-95 rounded-lg transition-all cursor-pointer border border-transparent hover:border-orange-200">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
    </svg>
</button>

