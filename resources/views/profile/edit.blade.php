@extends('layouts.app')
@section('page-title', 'Profile')
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="section-heading"><div><h2>Your account</h2><p>Manage your personal details and sign-in security.</p></div></div>
    <section class="app-panel">@include('profile.partials.update-profile-information-form')</section>
    <section class="app-panel">@include('profile.partials.update-password-form')</section>
    <section class="app-panel">@include('profile.partials.delete-user-form')</section>
</div>
@endsection
