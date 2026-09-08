<x-guest-layout>
    <!-- Login Card Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-500 font-bold text-2xl mb-3 shadow-inner">
            🔐
        </div>
        <h2 class="text-xl font-bold text-slate-900 tracking-tight">সিস্টেমে লগইন করুন</h2>
        <p class="text-xs text-slate-500 mt-1">আপনার মোবাইল নম্বর ও পাসওয়ার্ড প্রদান করে সাইন ইন করুন</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Error Summary if any -->
    @if ($errors->any())
        <div class="mb-4 p-3.5 rounded-xl bg-red-50 border border-red-200/80 text-xs text-red-700 flex items-start gap-2">
            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
        @csrf

        <!-- Mobile Number / Login Field -->
        <div>
            <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                মোবাইল নম্বর অথবা ইমেইল (Mobile / Email)
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <input id="login" 
                       name="login" 
                       type="text" 
                       value="{{ old('login', old('email')) }}" 
                       required 
                       autofocus 
                       placeholder="01XXXXXXXXX"
                       class="block w-full pl-10 pr-3.5 py-2.5 text-sm font-medium text-slate-900 bg-slate-50/50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors shadow-2xs">
            </div>
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                পাসওয়ার্ড (Password)
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" 
                       name="password" 
                       :type="showPassword ? 'text' : 'password'" 
                       required 
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="block w-full pl-10 pr-10 py-2.5 text-sm font-medium text-slate-900 bg-slate-50/50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-colors shadow-2xs">
                <button type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                        aria-label="Toggle password visibility">
                    <span x-show="!showPassword" class="text-xs">👁️</span>
                    <span x-show="showPassword" class="text-xs" x-cloak>🙈</span>
                </button>
            </div>
        </div>

        <!-- Remember Me & Status Indicator -->
        <div class="flex items-center justify-between text-xs pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer select-none text-slate-600">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded text-orange-600 border-slate-300 focus:ring-orange-500">
                <span class="ms-2">লগইন মনে রাখুন</span>
            </label>

            <span class="text-[11px] text-slate-400 font-medium">SBL Secure Auth</span>
        </div>

        <!-- Login Button -->
        <div class="pt-2">
            <button type="submit" 
                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold rounded-xl text-sm shadow-md shadow-orange-500/20 transition-all transform active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                <span>লগইন করুন</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </div>
    </form>

    <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <p class="text-[11px] text-slate-400">
            অ্যাকাউন্ট না থাকলে বা কোনো সমস্যা হলে আপনার সিস্টেম অ্যাডমিনের সাথে যোগাযোগ করুন।
        </p>
    </div>
</x-guest-layout>
