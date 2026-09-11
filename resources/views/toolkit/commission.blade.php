@extends('layouts.app')

@section('meta-title', 'Commission Calculator & Multi-tier Simulator | SBL Marketing')
@section('page-title', 'Commission Calculator')
@section('page-subtitle', 'Real-time Weekly ROI, Direct Spot Commission, Pair Reward & 10-Generation Affiliate Simulator')
@section('meta-description', 'Calculate SBL plan returns, spot referral commissions, binary pair rewards, and 10-tier UDR generation simulations with transparent formulas.')

@section('content')
    @include('toolkit.partials.commission-system')
@endsection
