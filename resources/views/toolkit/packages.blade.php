@extends('layouts.app')

@section('meta-title', 'Packages | SBL Marketing')
@section('page-title', 'SBL Packages')
@section('page-subtitle', 'Choose a package to view details and share information.')
@section('meta-description', 'Learn basic information about SBL Packages, present package details to prospects, and share package information easily.')

@section('content')
<div class="space-y-6">

    <!-- Minimal Header with Presentation View Trigger -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">SBL Packages</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-[#C2410C]">Official</span>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Choose a package to view details and share information.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] text-slate-400 font-medium">
                Verified for 2026 • Official SBL Terms
            </span>
        </div>
    </div>

    <!-- SBL 3 Primary Packages (Starter, National, International) with Details, Share, Presentation & Simple Comparison -->
    @include('toolkit.partials.membership-package')

</div>
@endsection
