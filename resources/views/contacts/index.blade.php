@extends('layouts.app')

@section('page-title', 'Contact')
@section('page-subtitle', 'Official Support Hotlines, Department Numbers & Instant WhatsApp Channels')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '',
    createModalOpen: false,
    editModalOpen: false,
    editingContact: {
        id: null,
        department: '',
        contact_person: '',
        phone: '',
        whatsapp: '',
        email: '',
        available_hours: '10:00 AM - 08:00 PM',
        description: '',
        icon: '📞',
        badge: '',
        is_primary: false,
        sort_order: 0
    },
    copyToClipboard(text, title) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: title + ' copied to clipboard!', type: 'success' } }));
            });
        }
    },
    openEditModal(contact) {
        const copy = Object.assign({}, contact);
        copy.is_primary = Boolean(Number(contact.is_primary));
        this.editingContact = copy;
        this.editModalOpen = true;
    }
}">

    <div class="section-heading">
        <div>
            <h2>Contact directory</h2>
            <p>Support teams and business contacts, all in one place.</p>
        </div>
        @can('users.manage')
        <button type="button" @click="createModalOpen = true" class="btn-primary">
            <x-ui-icon name="plus" />Add contact
        </button>
        @endcan
    </div>

    <!-- Search & Summary Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-medium">Total <strong>{{ $contacts->count() }}</strong> active contact hotlines listed</span>
        </div>

        <div class="relative w-full sm:w-80">
            <input type="text" 
                   id="contacts-search-input"
                   x-model="searchQuery" 
                   placeholder="Search department, person, or phone..." 
                   class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Contacts Table View -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 border-collapse">
                <thead class="bg-slate-50/90 text-slate-500 uppercase text-[11px] font-bold border-b border-slate-200/80 tracking-wider select-none">
                    <tr>
                        <th class="py-3.5 px-4 min-w-[240px]">Department & Service</th>
                        <th class="py-3.5 px-4 min-w-[160px]">Contact Person</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Phone Hotline</th>
                        <th class="py-3.5 px-4 min-w-[180px]">WhatsApp</th>
                        <th class="py-3.5 px-4 min-w-[180px]">Email</th>
                        <th class="py-3.5 px-4 min-w-[160px]">Available Hours</th>
                        <th class="py-3.5 px-4 text-right min-w-[100px]">Actions</th>
                    </tr>
                </thead>
                <tbody id="contacts-table-body" class="divide-y divide-slate-100">
                    @forelse($contacts as $contact)
                    <tr data-contact-id="{{ $contact->id }}" 
                        data-search="{{ mb_strtolower($contact->department . ' ' . $contact->contact_person . ' ' . $contact->phone . ' ' . $contact->whatsapp . ' ' . $contact->email . ' ' . $contact->badge . ' ' . $contact->description) }}"
                        x-show="!searchQuery || {{ json_encode(mb_strtolower($contact->department . ' ' . $contact->contact_person . ' ' . $contact->phone . ' ' . $contact->whatsapp . ' ' . $contact->email . ' ' . $contact->badge . ' ' . $contact->description)) }}.includes(searchQuery.toLowerCase())"
                        class="hover:bg-slate-50/75 transition-colors group">
                        
                        <!-- 1. Department & Service -->
                        <td class="py-4 px-4 align-middle">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl {{ $contact->is_primary ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-slate-100 border border-slate-200/80 text-slate-700' }} flex items-center justify-center text-xl flex-shrink-0">
                                    {{ $contact->icon ?: '📞' }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">
                                            {{ $contact->department }}
                                        </span>
                                        @if($contact->badge)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $contact->is_primary ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                            {{ $contact->badge }}
                                        </span>
                                        @endif
                                    </div>
                                    @if($contact->description)
                                    <p class="text-xs text-slate-500 line-clamp-1 mt-0.5 max-w-xs" title="{{ $contact->description }}">
                                        {{ $contact->description }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- 2. Contact Person -->
                        <td class="py-4 px-4 align-middle whitespace-nowrap">
                            @if($contact->contact_person)
                            <div class="flex items-center gap-2 text-sm text-slate-800 font-medium">
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>{{ $contact->contact_person }}</span>
                            </div>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>

                        <!-- 3. Phone Hotline -->
                        <td class="py-4 px-4 align-middle whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm font-mono">{{ $contact->phone }}</span>
                                <button type="button" @click="copyToClipboard('{{ $contact->phone }}', 'Phone number')" title="Copy Phone" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                                <a href="tel:{{ $contact->clean_phone }}" title="Direct Call" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-900 hover:bg-black text-emerald-400 shadow-xs transition-colors active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </a>
                            </div>
                        </td>

                        <!-- 4. WhatsApp -->
                        <td class="py-4 px-4 align-middle whitespace-nowrap">
                            @if($contact->whatsapp)
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm font-mono">{{ $contact->whatsapp }}</span>
                                <button type="button" @click="copyToClipboard('{{ $contact->whatsapp }}', 'WhatsApp number')" title="Copy WhatsApp" class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                                <a href="https://wa.me/{{ $contact->clean_whatsapp }}?text={{ urlencode('Hello, I would like to connect with SBL Helpdesk.') }}" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-colors active:scale-95">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>
                                </a>
                            </div>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>

                        <!-- 5. Email -->
                        <td class="py-4 px-4 align-middle whitespace-nowrap">
                            @if($contact->email)
                            <a href="mailto:{{ $contact->email }}" class="text-xs text-slate-700 hover:text-emerald-600 font-medium truncate max-w-[160px] inline-flex items-center gap-1.5" title="{{ $contact->email }}">
                                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span>{{ $contact->email }}</span>
                            </a>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>

                        <!-- 6. Available Hours -->
                        <td class="py-4 px-4 align-middle whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200/80 text-slate-600 text-xs font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ $contact->available_hours }}</span>
                            </span>
                        </td>

                        <!-- 7. Actions -->
                        <td class="py-4 px-4 align-middle text-right whitespace-nowrap">
                            @if(Auth::user()->can('users.manage'))
                            <div class="inline-flex items-center gap-1.5">
                                <button type="button" @click="openEditModal({{ json_encode($contact) }})" class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Edit Contact">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <form action="{{ route('contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this contact?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete Contact">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500">
                            <div class="text-4xl mb-3">📞</div>
                            <h3 class="text-lg font-bold text-slate-800">No contacts found</h3>
                            <p class="text-sm mt-1">Add a new contact to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Contact Modal (Styled exactly as requested) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="createModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div @click.outside="createModalOpen = false" 
             class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                    Add / Edit Contact Information
                </h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl font-semibold leading-none">&times;</button>
            </div>

            <form action="{{ route('contacts.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Row 1: Department -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Department *</label>
                    <input type="text" name="department" required placeholder="e.g. Customer Support / Merchant Helpdesk" 
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Row 2: Phone Number & WhatsApp Number -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number*</label>
                        <input type="text" name="phone" required placeholder="e.g. 01700000000" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">WhatsApp Number</label>
                        <input type="text" name="whatsapp" placeholder="e.g. 01700000000" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Row 3: Contact Person & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Person</label>
                        <input type="text" name="contact_person" placeholder="e.g. Support Team Lead" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" placeholder="e.g. support@sbl.com.bd" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Row 4: Description -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of support services or helpline info..." 
                              class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all resize-none"></textarea>
                </div>

                <!-- Collapsible Additional Settings (Hours, Badge, Icon, Primary) -->
                <details class="group rounded-xl border border-slate-200/70 bg-slate-50/50 transition-all">
                    <summary class="flex cursor-pointer items-center justify-between px-3.5 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 select-none">
                        <span class="flex items-center gap-1.5 font-semibold">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Additional Settings (Hours, Badge, Icon, Primary)
                        </span>
                        <span class="text-[11px] text-slate-400">Optional</span>
                    </summary>
                    <div class="px-3.5 pb-3.5 pt-2 space-y-3 border-t border-slate-100 bg-white rounded-b-xl">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Available Hours</label>
                                <input type="text" name="available_hours" value="10:00 AM - 08:00 PM" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Badge</label>
                                <input type="text" name="badge" placeholder="e.g. 24/7 Helpline" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Icon (Emoji)</label>
                                <input type="text" name="icon" value="📞" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div class="pt-4">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="is_primary" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-xs font-semibold text-slate-700">Highlight as Primary</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </details>

                <!-- Footer Action Buttons: Cancel and Save -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" 
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                        Save
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Edit Contact Modal (Styled exactly as requested) -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-cloak>
        <div @click.outside="editModalOpen = false" 
             class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                    Add / Edit Contact Information
                </h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl font-semibold leading-none">&times;</button>
            </div>

            <form :action="'{{ url('/contacts') }}/' + editingContact.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <!-- Row 1: Department -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Department *</label>
                    <input type="text" name="department" x-model="editingContact.department" required placeholder="e.g. Customer Support / Merchant Helpdesk" 
                           class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Row 2: Phone Number & WhatsApp Number -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone Number*</label>
                        <input type="text" name="phone" x-model="editingContact.phone" required placeholder="e.g. 01700000000" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">WhatsApp Number</label>
                        <input type="text" name="whatsapp" x-model="editingContact.whatsapp" placeholder="e.g. 01700000000" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Row 3: Contact Person & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Person</label>
                        <input type="text" name="contact_person" x-model="editingContact.contact_person" placeholder="e.g. Support Team Lead" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" x-model="editingContact.email" placeholder="e.g. support@sbl.com.bd" 
                               class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all">
                    </div>
                </div>

                <!-- Row 4: Description -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" x-model="editingContact.description" rows="2" placeholder="Brief description of support services or helpline info..." 
                              class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all resize-none"></textarea>
                </div>

                <!-- Collapsible Additional Settings (Hours, Badge, Icon, Primary) -->
                <details class="group rounded-xl border border-slate-200/70 bg-slate-50/50 transition-all">
                    <summary class="flex cursor-pointer items-center justify-between px-3.5 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 select-none">
                        <span class="flex items-center gap-1.5 font-semibold">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Additional Settings (Hours, Badge, Icon, Primary)
                        </span>
                        <span class="text-[11px] text-slate-400">Optional</span>
                    </summary>
                    <div class="px-3.5 pb-3.5 pt-2 space-y-3 border-t border-slate-100 bg-white rounded-b-xl">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Available Hours</label>
                                <input type="text" name="available_hours" x-model="editingContact.available_hours" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Badge</label>
                                <input type="text" name="badge" x-model="editingContact.badge" placeholder="e.g. 24/7 Helpline" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Icon (Emoji)</label>
                                <input type="text" name="icon" x-model="editingContact.icon" 
                                       class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                            </div>
                            <div class="pt-4">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="is_primary" value="1" :checked="editingContact.is_primary" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-xs font-semibold text-slate-700">Highlight as Primary</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </details>

                <!-- Footer Action Buttons: Cancel and Save -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" 
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition-all">
                        Save
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
