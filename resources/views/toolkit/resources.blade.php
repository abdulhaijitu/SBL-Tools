@extends('layouts.app')

@section('meta-title', 'Resources | SBL Marketing')
@section('page-title', 'Resources')
@section('page-subtitle', 'Official Leaflets, Pitch Decks, Legal Documents & Brand Assets')
@section('meta-description', 'SBL Marketing-এর অফিসিয়াল লিফলেট, প্রেজেন্টেশন স্লাইড, ক্যাটালগ ও ব্র্যান্ড রিসোর্স এক জায়গায়।')

@section('content')
<div class="space-y-6"
     x-data="{
        createResourceModalOpen: false,
        editResourceModalOpen: false,
        editingResource: { id: null, title: '', category: 'Leaflets & Sheets', file_type: 'pdf', file_url: '', file_size: '', badge: '', description: '', sort_order: 0 },
        resourceFilter: 'all',
        resourceSearch: '',
        copiedUrl: null,
        copyToClipboard(url) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url);
                this.copiedUrl = url;
                setTimeout(() => { if (this.copiedUrl === url) this.copiedUrl = null; }, 2000);
            }
        },
        openEditResourceModal(res) {
            this.editingResource = Object.assign({}, res);
            this.editResourceModalOpen = true;
        }
     }">
    @include('toolkit.partials.resources')
</div>
@endsection
