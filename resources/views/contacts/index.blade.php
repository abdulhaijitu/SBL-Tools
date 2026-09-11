@extends('layouts.app')

@section('page-title', 'Contact & Support')
@section('page-subtitle', 'Find the right SBL support contact and connect instantly via Phone or WhatsApp.')

@section('content')
<div id="contacts-container"
     class="max-w-7xl mx-auto space-y-6"
     x-data="contactsManager"
     data-contacts="{{ json_encode($contacts) }}"
     data-can-manage="{{ (Auth::user()->can('users.manage') || Auth::user()->isSuperAdmin()) ? '1' : '0' }}">

    <!-- ==================== 1. PAGE HEADER ==================== -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight"
                    :style="isBn ? 'letter-spacing: normal; word-break: normal;' : ''"
                    data-en="Contact & Support"
                    data-bn="কন্টাক্ট ও সাপোর্ট">
                    Contact & Support
                </h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="contacts.length + (isBn ? ' টি সক্রিয় কন্টাক্ট' : ' Active Contacts')">
                        {{ $contacts->count() }} Active Contacts
                    </span>
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed"
               :style="isBn ? 'letter-spacing: normal; word-break: normal; line-height: 1.6;' : ''"
               data-en="Find the right SBL support contact and connect instantly via Phone or WhatsApp."
               data-bn="প্রয়োজনীয় এসবিএল কন্টাক্ট নম্বর খুঁজুন এবং সরাসরি ফোন বা হোয়াটসঅ্যাপে যোগাযোগ করুন।">
                Find the right SBL support contact and connect instantly via Phone or WhatsApp.
            </p>
        </div>

        @if(Auth::user()->can('users.manage') || Auth::user()->isSuperAdmin())
        <div class="flex items-center gap-2 flex-shrink-0">
            <button type="button" 
                    @click="createModalOpen = true" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-xs hover:shadow transition-all active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span data-en="+ Add Contact" data-bn="+ নতুন কন্টাক্ট">+ Add Contact</span>
            </button>
        </div>
        @endif
    </div>

    <!-- ==================== 2. QUICK SUPPORT CARDS (PRIORITY HOTLINES) ==================== -->
    <div class="space-y-3" x-show="featuredContacts.length > 0">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5"
                    :style="isBn ? 'letter-spacing: normal;' : ''">
                    <span>⚡</span>
                    <span data-en="Quick Support" data-bn="কুইক সাপোর্ট">Quick Support</span>
                </h2>
                <p class="text-[11px] text-slate-500 mt-0.5" data-en="Priority contacts for fast assistance" data-bn="দ্রুত সহায়তার জন্য জরুরি সাপোর্ট চ্যানেল">
                    Priority contacts for fast assistance
                </p>
            </div>
            <span class="text-[11px] px-2.5 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"
                  data-en="1-Tap Connect" data-bn="১-ট্যাপে সরাসরি যোগাযোগ">
                1-Tap Connect
            </span>
        </div>

        <!-- Proper CSS Grid: 1 col on mobile, 2 cols on tablet, 3 cols on desktop - NO OVERLAPPING -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="item in featuredContacts" :key="'featured-' + item.id">
                <div class="bg-gradient-to-br from-emerald-50/60 via-white to-slate-50/60 rounded-2xl p-4 sm:p-5 border border-emerald-200/80 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between gap-4">
                    <!-- Top Info: Icon, Department, Badge, Phone -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-emerald-200 text-emerald-700 flex items-center justify-center text-2xl flex-shrink-0 shadow-2xs"
                                 x-text="item.icon || '📞'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="font-bold text-slate-900 text-sm truncate"
                                        :style="isBn ? 'letter-spacing: normal;' : ''"
                                        x-text="getDeptName(item)"></h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap"
                                          x-show="getServiceLabel(item)"
                                          x-text="getServiceLabel(item)"></span>
                                </div>
                                <p class="text-xs text-slate-700 mt-1 font-mono font-semibold" x-text="item.phone"></p>
                            </div>
                        </div>

                        <!-- Status Badge (Open / Closed) -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border flex-shrink-0 whitespace-nowrap"
                              :class="getStatus(item).badgeClass">
                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatus(item).dotClass"></span>
                            <span x-text="getStatus(item).label"></span>
                        </span>
                    </div>

                    <!-- 1-Tap Call & WhatsApp (44px min-height, 1 line, no wrap) -->
                    <div class="grid grid-cols-2 gap-2 pt-1 border-t border-emerald-100/80">
                        <a :href="'tel:' + getCleanPhone(item)"
                           class="min-h-[44px] flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-emerald-400 text-xs font-bold shadow-xs active:scale-95 transition-all whitespace-nowrap text-center cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="whitespace-nowrap" x-text="isBn ? 'সরাসরি কল' : 'Call'">Call</span>
                        </a>

                        <a :href="'https://wa.me/' + getCleanWa(item) + '?text=' + encodeURIComponent(isBn ? 'আসসালামু আলাইকুম, এসবিএল সাপোর্ট সংক্রান্ত বিষয়ে যোগাযোগ করতে চাচ্ছি।' : 'Hello, I would like to get assistance regarding SBL services.')"
                           target="_blank" rel="noopener noreferrer"
                           class="min-h-[44px] flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all whitespace-nowrap text-center cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>
                            <span class="whitespace-nowrap" x-text="isBn ? 'হোয়াটসঅ্যাপ' : 'WhatsApp'">WhatsApp</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ==================== 3. SEARCH & CATEGORY FILTERS ==================== -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Single Live Search Field -->
            <div class="relative flex-1">
                <input type="text" 
                       id="contacts-search-input"
                       x-model="searchQuery" 
                       :placeholder="isBn ? 'ডিপার্টমেন্ট, ব্যক্তি, ফোন নম্বর বা সেবা লিখে খুঁজুন...' : 'Search department, person, phone or service...'" 
                       class="w-full pl-10 pr-9 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-hidden transition-all placeholder:text-slate-400">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <button type="button" 
                        x-show="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-sm font-bold p-0.5 cursor-pointer"
                        aria-label="Clear search">✕</button>
            </div>

            <!-- Matched Count & Reset -->
            <div class="flex items-center justify-between sm:justify-end gap-2 text-xs text-slate-500 flex-shrink-0 px-1">
                <span class="font-medium">
                    <span class="font-bold text-slate-900" x-text="filteredContacts.length"></span>
                    <span x-text="isBn ? ' টি চ্যানেল পাওয়া গেছে' : ' contacts found'"></span>
                </span>
                <button type="button" 
                        x-show="selectedCategory !== 'all' || searchQuery"
                        @click="selectedCategory = 'all'; searchQuery = ''"
                        class="text-emerald-600 hover:text-emerald-700 font-semibold underline underline-offset-2 ml-1 cursor-pointer"
                        x-text="isBn ? 'রিসেট' : 'Reset'">Reset</button>
            </div>
        </div>

        <!-- Category Filter Chips (Wrapping on desktop, scrollable on mobile) -->
        <div class="flex items-center gap-1.5 overflow-x-auto whitespace-nowrap py-1 text-xs">
            <template x-for="cat in categories" :key="cat.id">
                <button type="button"
                        @click="selectedCategory = cat.id"
                        :class="selectedCategory === cat.id ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-medium'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl whitespace-nowrap transition-all flex-shrink-0 cursor-pointer">
                    <span x-text="cat.icon"></span>
                    <span :style="isBn ? 'letter-spacing: normal;' : ''" x-text="isBn ? cat.name_bn : cat.name_en"></span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full"
                          :class="selectedCategory === cat.id ? 'bg-emerald-700 text-emerald-100' : 'bg-white text-slate-600'"
                          x-text="cat.count"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- ==================== 4. ALL CONTACTS LIST (ACTION-ORIENTED CARDS) ==================== -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-bold text-slate-900"
                :style="isBn ? 'letter-spacing: normal;' : ''"
                data-en="All Contacts" data-bn="সকল কন্টাক্ট ডিরেক্টরি">
                All Contacts
            </h2>
            <span class="text-xs text-slate-500" x-text="filteredContacts.length + (isBn ? ' টি কন্টাক্ট' : ' contacts')"></span>
        </div>

        <!-- Contacts Grid: 1 col on mobile, 2 cols on tablet, 3 cols on desktop -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="filteredContacts.length > 0">
            <template x-for="contact in filteredContacts" :key="'contact-' + contact.id">
                <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:shadow-sm transition-all flex flex-col justify-between gap-3.5">
                    
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl flex-shrink-0 shadow-2xs"
                                 :class="contact.is_primary ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-700'"
                                 x-text="contact.icon || '📞'">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h3 class="font-bold text-slate-900 text-sm truncate"
                                        :style="isBn ? 'letter-spacing: normal; word-break: normal;' : ''"
                                        x-text="getDeptName(contact)"></h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap"
                                          :class="contact.is_primary ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200'"
                                          x-show="getServiceLabel(contact)"
                                          x-text="getServiceLabel(contact)"></span>
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5 flex items-center gap-1 truncate" x-show="contact.contact_person">
                                    <span>👤</span>
                                    <span x-text="contact.contact_person"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Operating Status Pill -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border flex-shrink-0 whitespace-nowrap"
                              :class="getStatus(contact).badgeClass">
                            <span class="w-1.5 h-1.5 rounded-full" :class="getStatus(contact).dotClass"></span>
                            <span x-text="getStatus(contact).label"></span>
                        </span>
                    </div>

                    <!-- Description (if any) -->
                    <p class="text-xs text-slate-600 bg-slate-50/80 p-2.5 rounded-xl border border-slate-100 leading-relaxed line-clamp-2"
                       :style="isBn ? 'letter-spacing: normal; line-height: 1.6;' : ''"
                       x-show="getDescription(contact)"
                       x-text="getDescription(contact)"></p>

                    <!-- Phone Number & Hours Meta -->
                    <div class="flex items-center justify-between text-xs text-slate-500 pt-1 border-t border-slate-100 flex-wrap gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-900 font-mono text-sm" x-text="contact.phone"></span>
                            <button type="button" 
                                    @click="copyToClipboard(contact.phone, isBn ? 'ফোন নম্বর' : 'Phone number')" 
                                    :title="isBn ? 'ফোন নম্বর কপি করুন' : 'Copy Phone'" 
                                    class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>

                        <span class="text-[11px] bg-slate-100 px-2 py-0.5 rounded-md text-slate-600 font-medium flex items-center gap-1 whitespace-nowrap">
                            <span>🕒</span>
                            <span x-text="getHours(contact)"></span>
                        </span>
                    </div>

                    <!-- Action Buttons: Call & WhatsApp (44px min-height, 1 line) -->
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a :href="'tel:' + getCleanPhone(contact)"
                           class="min-h-[44px] flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-black text-emerald-400 text-xs font-bold shadow-xs active:scale-95 transition-all text-center whitespace-nowrap cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="whitespace-nowrap" x-text="isBn ? 'সরাসরি কল' : 'Call'">Call</span>
                        </a>

                        <a :href="'https://wa.me/' + getCleanWa(contact) + '?text=' + encodeURIComponent(isBn ? 'আসসালামু আলাইকুম, এসবিএল সাপোর্ট সংক্রান্ত বিষয়ে যোগাযোগ করতে চাচ্ছি।' : 'Hello, I would like to get assistance regarding SBL services.')"
                           target="_blank" rel="noopener noreferrer"
                           class="min-h-[44px] flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs active:scale-95 transition-all text-center whitespace-nowrap cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.699c.971.53 1.769.815 2.796.815 3.182 0 5.768-2.587 5.768-5.766 0-3.18-2.586-5.767-5.768-5.767zm3.385 8.163c-.143.402-.832.744-1.144.789-.312.046-.713.064-2.032-.477-.735-.302-1.396-.757-1.93-1.288-.535-.53-.992-1.19-1.295-1.924-.543-1.319-.525-1.72-.479-2.032.045-.312.387-1.001.789-1.144.135-.048.277-.024.38.064l.872 1.071c.092.113.109.269.043.4l-.391.783c-.066.131-.038.29.068.396.406.407.886.732 1.413.957.147.063.315.029.426-.083l.635-.634c.121-.122.302-.152.455-.075l1.28.639c.143.072.224.223.199.381l-.105.794z"/></svg>
                            <span class="whitespace-nowrap" x-text="isBn ? 'হোয়াটসঅ্যাপ' : 'WhatsApp'">WhatsApp</span>
                        </a>
                    </div>

                    <!-- Bottom Bar: Details Drawer & Admin Controls -->
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-xs">
                        <button type="button" 
                                @click="openDrawer(contact)" 
                                class="text-emerald-700 hover:text-emerald-800 font-semibold inline-flex items-center gap-1 py-1 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="isBn ? 'বিস্তারিত দেখুন' : 'View Details'">View Details</span>
                        </button>

                        <div class="flex items-center gap-1">
                            <button type="button" 
                                    @click="shareContact(contact)" 
                                    :title="isBn ? 'কন্টাক্ট শেয়ার করুন' : 'Share Contact'" 
                                    class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>

                            <template x-if="canManage">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" 
                                            @click="openEditModal(contact)" 
                                            class="p-1.5 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer" 
                                            :title="isBn ? 'সম্পাদনা' : 'Edit'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form :action="'{{ url('/contacts') }}/' + contact.id" method="POST" onsubmit="return confirm('Are you sure you want to delete this contact?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" :title="isBn ? 'মুছে ফেলুন' : 'Delete'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredContacts.length === 0" class="bg-white rounded-2xl p-8 border border-slate-200 text-center text-slate-500">
            <div class="text-4xl mb-3">🔍</div>
            <h3 class="text-base font-bold text-slate-800" x-text="isBn ? 'কোনো কন্টাক্ট পাওয়া যায়নি' : 'No contacts found'">No contacts found</h3>
            <p class="text-xs mt-1 text-slate-500" x-text="isBn ? 'অন্য কোনো কীওয়ার্ড লিখে খুঁজুন অথবা ফিল্টার রিসেট করুন।' : 'Try another search keyword or reset filters.'"></p>
        </div>
    </div>

    <!-- ==================== 5. CONTACT DETAILS DRAWER & BOTTOM SHEET ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="drawerOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-2xs transition-opacity"
             x-show="drawerOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeDrawer()"></div>

        <div class="fixed inset-0 flex justify-end pointer-events-none">
            <!-- Sheet Container -->
            <div class="pointer-events-auto w-full md:max-w-md bg-white shadow-2xl h-full flex flex-col justify-between overflow-y-auto"
                 x-show="drawerOpen"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-y-full md:translate-y-0 md:translate-x-full"
                 x-transition:enter-end="translate-y-0 md:translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-y-0 md:translate-x-0"
                 x-transition:leave-end="translate-y-full md:translate-y-0 md:translate-x-full">

                <!-- Drawer Header -->
                <div class="p-5 border-b border-slate-100 flex items-start justify-between gap-4 bg-slate-50/60">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-800 flex items-center justify-center text-3xl flex-shrink-0 shadow-xs"
                             x-text="selectedContact?.icon || '📞'"></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <h2 class="text-base font-bold text-slate-900 truncate"
                                    :style="isBn ? 'letter-spacing: normal;' : ''"
                                    x-text="getDeptName(selectedContact)"></h2>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap"
                                      x-show="getServiceLabel(selectedContact)"
                                      x-text="getServiceLabel(selectedContact)"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 flex items-center gap-1" x-show="selectedContact?.contact_person">
                                <span>👤</span>
                                <span x-text="selectedContact?.contact_person"></span>
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeDrawer()" class="text-slate-400 hover:text-slate-700 text-2xl font-bold leading-none p-1 cursor-pointer">&times;</button>
                </div>

                <!-- Drawer Body -->
                <div class="p-5 space-y-4 flex-1 overflow-y-auto">
                    <!-- Status & Verification Badges -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 rounded-xl border flex flex-col justify-between gap-1"
                             :class="getStatus(selectedContact).badgeClass">
                            <span class="text-[10px] font-bold uppercase opacity-80" x-text="isBn ? 'অপারেটিং স্ট্যাটাস' : 'Operating Status'"></span>
                            <div class="flex items-center gap-1.5 text-xs font-bold">
                                <span class="w-2 h-2 rounded-full" :class="getStatus(selectedContact).dotClass"></span>
                                <span x-text="getStatus(selectedContact).label"></span>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl border flex flex-col justify-between gap-1"
                             :class="getVerification(selectedContact).badgeClass">
                            <span class="text-[10px] font-bold uppercase opacity-80" x-text="isBn ? 'ভেরিফিকেশন' : 'Verification'"></span>
                            <div class="flex items-center gap-1 text-xs font-bold">
                                <span x-text="getVerification(selectedContact).label"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Direct Contact Channels -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider" x-text="isBn ? 'যোগাযোগের মাধ্যম' : 'Contact Channels'"></h4>
                        
                        <!-- Phone Hotline Card -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase" x-text="isBn ? 'ফোন নম্বর' : 'Phone Number'"></span>
                                <p class="text-sm font-bold text-slate-900 font-mono mt-0.5" x-text="selectedContact?.phone"></p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="copyToClipboard(selectedContact?.phone, isBn ? 'ফোন নম্বর' : 'Phone number')" 
                                        class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-200/80 rounded-lg transition-colors cursor-pointer" 
                                        :title="isBn ? 'কপি করুন' : 'Copy'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <a :href="'tel:' + getCleanPhone(selectedContact)" 
                                   class="px-3 py-1.5 bg-slate-900 hover:bg-black text-emerald-400 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                                    <span x-text="isBn ? 'কল' : 'Call'"></span>
                                </a>
                            </div>
                        </div>

                        <!-- WhatsApp Service Card -->
                        <div class="p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-200/80 flex items-center justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold text-emerald-800 uppercase" x-text="isBn ? 'হোয়াটসঅ্যাপ সাপোর্ট' : 'WhatsApp Support'"></span>
                                <p class="text-sm font-bold text-slate-900 font-mono mt-0.5" x-text="selectedContact?.whatsapp || selectedContact?.phone"></p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="copyToClipboard(selectedContact?.whatsapp || selectedContact?.phone, isBn ? 'হোয়াটসঅ্যাপ' : 'WhatsApp')" 
                                        class="p-2 text-slate-500 hover:text-slate-800 hover:bg-emerald-100 rounded-lg transition-colors cursor-pointer" 
                                        :title="isBn ? 'কপি করুন' : 'Copy'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <a :href="'https://wa.me/' + getCleanWa(selectedContact)" 
                                   target="_blank" rel="noopener noreferrer" 
                                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer">
                                    <span x-text="isBn ? 'মেসেজ' : 'Message'"></span>
                                </a>
                            </div>
                        </div>

                        <!-- Email (if available) -->
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between gap-3" x-show="selectedContact?.email">
                            <div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase">Email</span>
                                <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate" x-text="selectedContact?.email"></p>
                            </div>
                            <a :href="'mailto:' + selectedContact?.email" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                                Send
                            </a>
                        </div>
                    </div>

                    <!-- Hours & Description -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="font-semibold" x-text="isBn ? 'সেবা পাওয়ার সময়সূচি:' : 'Operating Hours:'"></span>
                            <span class="font-bold text-slate-900" x-text="getHours(selectedContact)"></span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100"
                           :style="isBn ? 'letter-spacing: normal; line-height: 1.6;' : ''"
                           x-show="getDescription(selectedContact)"
                           x-text="getDescription(selectedContact)"></p>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-2">
                    <button type="button" 
                            @click="shareContact(selectedContact)" 
                            class="flex-1 py-2.5 px-3 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span x-text="isBn ? 'শেয়ার' : 'Share'"></span>
                    </button>
                    <button type="button" 
                            @click="closeDrawer()" 
                            class="py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition-all cursor-pointer">
                        <span x-text="isBn ? 'বন্ধ করুন' : 'Close'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ==================== 6. CREATE CONTACT MODAL (Admin Only) ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="createModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4">
        <div @click.outside="createModalOpen = false" 
             class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900" x-text="isBn ? 'নতুন কন্টাক্ট নম্বর জমা রাখুন' : 'Add New Support Contact'">Add New Support Contact</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="isBn ? 'এসবিএল হেল্পলাইন ও অফিসিয়াল যোগাযোগের তথ্য' : 'SBL Support Hotline and Channel Information'"></p>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl font-semibold leading-none cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('contacts.store') }}" method="POST" class="space-y-3.5">
                @csrf
                
                <!-- Row 1: Department (EN / BN) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department (English) *</label>
                        <input type="text" name="department_en" required placeholder="e.g. Customer Care & Support Cell" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ডিপার্টমেন্ট (বাংলা) *</label>
                        <input type="text" name="department_bn" required placeholder="যেমন: কাস্টমার কেয়ার ও সাপোর্ট সেল" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 2: Phone & WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Hotline *</label>
                        <input type="text" name="phone" required placeholder="01700000000" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp" placeholder="01700000000" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 3: Contact Person & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" placeholder="e.g. SBL Helpdesk Lead" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Official Email</label>
                        <input type="email" name="email" placeholder="support@sbl.com.bd" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 4: Category & Verification -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                        <select name="category" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                            <option value="customer_care">Customer Care (কাস্টমার কেয়ার)</option>
                            <option value="dropshipping">Dropshipping (ড্রপশিপিং)</option>
                            <option value="business">Business & Investor (বিজনেস ও ইনভেস্টর)</option>
                            <option value="technical">Technical Support (টেকনিক্যাল সাপোর্ট)</option>
                            <option value="training">Training & Counseling (ট্রেনিং ও কাউন্সেলিং)</option>
                            <option value="accounts">Accounts & Payout (অ্যাকাউন্টস ও পে-আউট)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Verification Status</label>
                        <select name="verification_status" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                            <option value="needs_review">Needs Review (রিভিউ প্রয়োজন - ডিফল্ট)</option>
                            <option value="verified">Verified (অফিসিয়াল নিশ্চিত চ্যানেল)</option>
                            <option value="unverified">Unverified (যাচাইবিহীন)</option>
                            <option value="inactive">Inactive (সাময়িকভাবে নিষ্ক্রিয়)</option>
                        </select>
                    </div>
                </div>

                <!-- Row 5: Descriptions (EN / BN) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Description (EN)</label>
                        <textarea name="description_en" rows="2" placeholder="Brief service details..." 
                                  class="w-full px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">বিবরণী (বাংলা)</label>
                        <textarea name="description_bn" rows="2" placeholder="সার্ভিস সংক্রান্ত বিবরণ..." 
                                  class="w-full px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                    </div>
                </div>

                <!-- Collapsible Settings: Hours, Badge, Icon, Primary -->
                <details class="group rounded-xl border border-slate-200/70 bg-slate-50/50">
                    <summary class="flex cursor-pointer items-center justify-between px-3.5 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 select-none">
                        <span class="font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Operating Hours, Service Badges & Highlight
                        </span>
                        <span class="text-[10px] text-slate-400">Expand</span>
                    </summary>
                    <div class="px-3.5 pb-3 pt-2 space-y-3 border-t border-slate-100 bg-white rounded-b-xl text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Hours (EN)</label>
                                <input type="text" name="hours_en" value="10:00 AM - 08:00 PM" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">সময় (বাংলা)</label>
                                <input type="text" name="hours_bn" value="সকাল ১০:০০ - রাত ০৮:০০" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 items-center">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Icon Emoji</label>
                                <input type="text" name="icon" value="📞" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-center text-base">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Service Badge</label>
                                <input type="text" name="service_label_en" placeholder="e.g. 24/7 HELPLINE" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div class="pt-4 space-y-1">
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_primary" value="1" class="rounded text-emerald-600">
                                    <span class="font-semibold text-slate-700">Quick Support</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_24_hours" value="1" class="rounded text-emerald-600">
                                    <span class="font-semibold text-slate-700">24/7 Open</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </details>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer">
                        Save Contact
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- ==================== 7. EDIT CONTACT MODAL (Admin Only) ==================== -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="editModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4">
        <div @click.outside="editModalOpen = false" 
             class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900" x-text="isBn ? 'কন্টাক্ট সম্পাদনা করুন' : 'Edit Support Contact'">Edit Support Contact</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="isBn ? 'তথ্য পরিবর্তন ও আপডেট করুন' : 'Modify contact details and hours'"></p>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl font-semibold leading-none cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/contacts') }}/' + editingContact.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')
                
                <!-- Row 1: Department (EN / BN) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Department (English) *</label>
                        <input type="text" name="department_en" x-model="editingContact.department_en" required 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">ডিপার্টমেন্ট (বাংলা) *</label>
                        <input type="text" name="department_bn" x-model="editingContact.department_bn" required 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 2: Phone & WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Hotline *</label>
                        <input type="text" name="phone" x-model="editingContact.phone" required 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                        <input type="text" name="whatsapp" x-model="editingContact.whatsapp" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 3: Contact Person & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" x-model="editingContact.contact_person" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Official Email</label>
                        <input type="email" name="email" x-model="editingContact.email" 
                               class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                    </div>
                </div>

                <!-- Row 4: Category & Verification -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
                        <select name="category" x-model="editingContact.category" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                            <option value="customer_care">Customer Care (কাস্টমার কেয়ার)</option>
                            <option value="dropshipping">Dropshipping (ড্রপশিপিং)</option>
                            <option value="business">Business & Investor (বিজনেস ও ইনভেস্টর)</option>
                            <option value="technical">Technical Support (টেকনিক্যাল সাপোর্ট)</option>
                            <option value="training">Training & Counseling (ট্রেনিং ও কাউন্সেলিং)</option>
                            <option value="accounts">Accounts & Payout (অ্যাকাউন্টস ও পে-আউট)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Verification Status</label>
                        <select name="verification_status" x-model="editingContact.verification_status" class="w-full px-3.5 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden">
                            <option value="needs_review">Needs Review (রিভিউ প্রয়োজন)</option>
                            <option value="verified">Verified (অফিসিয়াল নিশ্চিত চ্যানেল)</option>
                            <option value="unverified">Unverified (যাচাইবিহীন)</option>
                            <option value="inactive">Inactive (সাময়িকভাবে নিষ্ক্রিয়)</option>
                        </select>
                    </div>
                </div>

                <!-- Row 5: Descriptions (EN / BN) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Description (EN)</label>
                        <textarea name="description_en" x-model="editingContact.description_en" rows="2" 
                                  class="w-full px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">বিবরণী (বাংলা)</label>
                        <textarea name="description_bn" x-model="editingContact.description_bn" rows="2" 
                                  class="w-full px-3.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-hidden resize-none"></textarea>
                    </div>
                </div>

                <!-- Collapsible Settings: Hours, Badge, Icon, Primary -->
                <details class="group rounded-xl border border-slate-200/70 bg-slate-50/50">
                    <summary class="flex cursor-pointer items-center justify-between px-3.5 py-2 text-xs font-medium text-slate-500 hover:text-slate-800 select-none">
                        <span class="font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Operating Hours, Service Badges & Highlight
                        </span>
                        <span class="text-[10px] text-slate-400">Expand</span>
                    </summary>
                    <div class="px-3.5 pb-3 pt-2 space-y-3 border-t border-slate-100 bg-white rounded-b-xl text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Hours (EN)</label>
                                <input type="text" name="hours_en" x-model="editingContact.hours_en" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">সময় (বাংলা)</label>
                                <input type="text" name="hours_bn" x-model="editingContact.hours_bn" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 items-center">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Icon Emoji</label>
                                <input type="text" name="icon" x-model="editingContact.icon" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-center text-base">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Service Badge</label>
                                <input type="text" name="service_label_en" x-model="editingContact.service_label_en" class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            </div>
                            <div class="pt-4 space-y-1">
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_primary" value="1" :checked="editingContact.is_primary" class="rounded text-emerald-600">
                                    <span class="font-semibold text-slate-700">Quick Support</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                    <input type="checkbox" name="is_24_hours" value="1" :checked="editingContact.is_24_hours" class="rounded text-emerald-600">
                                    <span class="font-semibold text-slate-700">24/7 Open</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </details>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 rounded-xl transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
