@extends('layouts.app')

@section('meta-title', 'Packages | SBL Marketing')
@section('page-title', 'Packages')
@section('page-subtitle', 'SBL Membership & Dropshipping Packages')
@section('meta-description', 'View SBL Marketing Membership & Dropshipping packages, benefits, service details, and ownership terms in one place.')

@section('content')
<div class="space-y-6">
    <!-- SBL PACKAGES MASTER COMPONENT (Unified single source of truth, mobile-first, high-converting) -->
    @include('toolkit.partials.membership-package')
</div>
@endsection

