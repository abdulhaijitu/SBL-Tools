@extends('layouts.app')
@section('page-title', 'Abbreviation')
@section('page-subtitle', 'Essential SBL Dropshipping, E-Commerce, Logistics & Marketing Glossary')
@section('content')
<div class="space-y-6" x-data="abbreviationManager" data-terms="{{ json_encode($abbreviations) }}" data-can-manage="{{ auth()->user()?->hasPermission('users.manage') ? '1' : '0' }}">
    <div class="section-heading">
        <div><h2>SBL Dropshipping Abbreviations</h2><p>A quick-reference guide for short forms, terminology, and operational concepts.</p></div>
        <div class="flex gap-3 items-center"><span class="text-sm" x-text="terms.length + ' Terms Listed'"></span><button type="button" class="btn-primary" x-show="canManage" @click="edit()">Add Abbreviation</button></div>
    </div>
    <p x-show="error" x-cloak class="app-notice app-notice-error" role="alert" x-text="error"></p>
    <div class="flex gap-2 overflow-x-auto pb-2">
        @foreach($categories as $cat)
        <button type="button" class="btn-secondary whitespace-nowrap" :class="{'ring-2 ring-inset ring-orange-600': category === '{{ $cat['slug'] }}'}" :aria-pressed="category === '{{ $cat['slug'] }}'" @click="category = '{{ $cat['slug'] }}'">{{ $cat['icon'] }} {{ $cat['name'] }} (<span x-text="count('{{ $cat['slug'] }}')"></span>)</button>
        @endforeach
    </div>
    <label class="block"><span class="text-sm font-semibold">Search abbreviations</span><input class="mt-1 w-full rounded-xl border-slate-300" type="search" x-model="search" placeholder="Search short form, name, or meaning"></label>
    <section x-show="editing" x-cloak class="bg-white border border-slate-200 rounded-2xl p-5" aria-label="Abbreviation editor">
        <h3 class="font-bold mb-4" x-text="form.id ? 'Edit Abbreviation' : 'Add Abbreviation'"></h3>
        <form x-ref="editor" @submit.prevent="save()" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach(['code' => ['Short Form', 50], 'name' => ['Full Name', 200], 'meaning_bn' => ['Meaning', 1000], 'description_bn' => ['Use Case', 3000], 'icon' => ['Icon (optional)', 20], 'tag' => ['Tag (optional)', 100]] as $field => [$label, $max])
                <label class="block text-sm font-semibold">{{ $label }}
                    @if(in_array($field, ['meaning_bn', 'description_bn']))
                    <textarea class="mt-1 w-full rounded-xl border-slate-300" x-model="form.{{ $field }}" maxlength="{{ $max }}" required rows="3"></textarea>
                    @else
                    <input class="mt-1 w-full rounded-xl border-slate-300" x-model="form.{{ $field }}" maxlength="{{ $max }}" {{ in_array($field, ['code', 'name']) ? 'required' : '' }}>
                    @endif
                </label>
                @endforeach
                <label class="block text-sm font-semibold">Category<select class="mt-1 w-full rounded-xl border-slate-300" x-model="form.category_slug" required>
                    @foreach($categories as $cat)
                    @if($cat['slug'] !== 'all')<option value="{{ $cat['slug'] }}">{{ $cat['name'] }}</option>@endif
                    @endforeach
                </select></label>
            </div>
            <div class="flex gap-3"><button class="btn-primary" :disabled="busy" x-text="busy ? 'Saving…' : 'Save Abbreviation'"></button><button type="button" class="btn-secondary" :disabled="busy" @click="editing = false; error = ''">Cancel</button></div>
        </form>
    </section>
    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full min-w-[1200px] text-left text-sm">
            <thead class="bg-slate-50"><tr>@foreach(['Short Form', 'Full Name', 'Category', 'Meaning', 'Use Case', 'Actions'] as $heading)<th class="p-4">{{ $heading }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="term in filtered" :key="term.id"><tr>
                    <td class="p-4 whitespace-nowrap"><button type="button" class="font-bold" @click="copy(term.code)" title="Copy short form"><span x-text="term.icon"></span> <span x-text="term.code"></span></button></td>
                    <td class="p-4"><strong x-text="term.name"></strong><p class="text-xs text-slate-500" x-text="term.tag"></p></td>
                    <td class="p-4 whitespace-nowrap" x-text="term.category"></td><td class="p-4" x-text="term.meaning_bn"></td><td class="p-4" x-text="term.description_bn"></td>
                    <td class="p-4"><div class="flex gap-2"><button type="button" class="btn-secondary" @click="copy(term.code + ' - ' + term.name + ': ' + term.meaning_bn)">Copy</button><button type="button" class="btn-secondary" x-show="canManage" :disabled="busy" @click="edit(term)">Edit</button><button type="button" class="btn-secondary text-red-700" x-show="canManage" :disabled="busy" @click="remove(term)">Delete</button></div></td>
                </tr></template>
                <tr x-show="!filtered.length"><td colspan="6" class="p-8 text-center text-slate-500">No abbreviations found.</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
