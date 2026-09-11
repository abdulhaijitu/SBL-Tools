@extends('layouts.app')

@section('meta-title', 'SBL Marketing Resource Center | Official Documents, Presentations & Assets')
@section('page-title', 'Resource Center')
@section('page-subtitle', 'Verified documents, presentations, marketing assets and training materials')
@section('meta-description', 'SBL Marketing-এর অফিসিয়াল লিফলেট, প্রেজেন্টেশন স্লাইড, ক্যাটালগ ও ব্র্যান্ড রিসোর্স এক জায়গায়।')

@section('content')
<div class="space-y-6">
    @include('toolkit.partials.resources-center')
</div>
@endsection
