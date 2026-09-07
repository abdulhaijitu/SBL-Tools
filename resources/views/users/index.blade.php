@extends('layouts.app')

@section('page-title', 'User Management')
@section('page-subtitle', 'Manage team members, roles, and administrative access')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false,
    editModalOpen: false,
    editingUser: { id: null, name: '', email: '', phone: '', designation: '', role_id: '', status: 'active' }
}">

    <!-- Top Action Bar & Stat Counters -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg">
                👥
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Total Members</div>
                <div class="text-xl font-bold text-slate-900">{{ $users->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                🟢
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Active Status</div>
                <div class="text-xl font-bold text-emerald-600">{{ $users->where('status', 'active')->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                💼
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Sales Team</div>
                <div class="text-xl font-bold text-slate-900">{{ $users->filter(fn($u) => $u->hasRole(['sales-manager', 'sales-agent']))->count() }}</div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                🛡️
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Super Admins</div>
                <div class="text-xl font-bold text-purple-600">{{ $users->filter(fn($u) => $u->hasRole('super-admin'))->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filters & Action Header -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Search & Role Dropdown Filters -->
        <form aria-label="Filter users" method="GET" action="{{ route('users.index') }}" class="w-full md:w-auto flex-1 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-0 w-full">
                <input aria-label="Search by name, email, phone..." type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search by name, email, phone..." 
                       class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="role" onchange="this.form.submit()" class="text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->slug }}" {{ request('role') === $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>

            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="text-xs text-orange-600 hover:text-orange-700 font-semibold px-2 py-1">Reset</a>
            @endif
            <button type="submit" class="btn-secondary">Search</button>
        </form>

        <!-- Add Member Button -->
        <button @click="createModalOpen = true" class="w-full md:w-auto px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add Member</span>
        </button>
    </div>

    <!-- Desktop Table (md:block) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-400 uppercase text-[11px] font-semibold border-b border-slate-200/80 tracking-wider">
                <tr>
                    <th class="py-3.5 px-4">Member Name</th>
                    <th class="py-3.5 px-4">Role</th>
                    <th class="py-3.5 px-4">Designation</th>
                    <th class="py-3.5 px-4">Contact</th>
                    <th class="py-3.5 px-4 text-center">Workload</th>
                    <th class="py-3.5 px-4 text-center">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                <tr data-user-id="{{ $user->id }}" class="hover:bg-slate-50/60 transition-colors">
                    
                    <!-- Member Info -->
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span>{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 text-[10px] rounded font-bold uppercase">You</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Role Badge -->
                    <td class="py-3 px-4">
                        @php
                            $primaryRole = $user->primary_role;
                            $roleColors = [
                                'super-admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'sales-manager' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'sales-agent' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'marketing-officer' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'viewer' => 'bg-slate-100 text-slate-700 border-slate-200',
                            ];
                            $colorClass = $primaryRole ? ($roleColors[$primaryRole->slug] ?? 'bg-slate-100 text-slate-700 border-slate-200') : 'bg-slate-100 text-slate-700 border-slate-200';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $colorClass }}">
                            {{ $primaryRole ? $primaryRole->name : 'No Role' }}
                        </span>
                    </td>

                    <!-- Designation -->
                    <td class="py-3 px-4">
                        <span class="text-slate-700 font-medium">{{ $user->designation ?: 'Staff Member' }}</span>
                    </td>

                    <!-- Contact -->
                    <td class="py-3 px-4 text-xs">
                        @if($user->phone)
                            <div class="flex items-center gap-2 text-slate-700">
                                <span>📞 {{ $user->phone }}</span>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->phone) }}" target="_blank" class="text-emerald-600 hover:text-emerald-700" title="WhatsApp">💬</a>
                            </div>
                        @else
                            <span class="text-slate-400 italic">No phone set</span>
                        @endif
                    </td>

                    <!-- Workload (Leads & Tasks Count) -->
                    <td class="py-3 px-4 text-center">
                        <div class="inline-flex items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md" title="Assigned Leads">
                                👥 {{ $user->leads_count }}
                            </span>
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md" title="Assigned Tasks">
                                ✅ {{ $user->tasks_count }}
                            </span>
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="py-3 px-4 text-center">
                        @if($user->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <!-- Actions -->
                    <td class="py-3 px-4 text-right space-x-2">
                        <button @click="
                            editingUser = {
                                id: {{ $user->id }},
                                name: '{{ addslashes($user->name) }}',
                                email: '{{ addslashes($user->email) }}',
                                phone: '{{ addslashes($user->phone ?? '') }}',
                                designation: '{{ addslashes($user->designation ?? '') }}',
                                role_id: '{{ $primaryRole ? $primaryRole->id : '' }}',
                                status: '{{ $user->status }}'
                            };
                            editModalOpen = true;
                        " class="text-orange-600 hover:text-orange-800 font-semibold text-xs px-2 py-1 rounded hover:bg-orange-50 transition-colors">
                            Edit
                        </button>

                        @if($user->id !== auth()->id() && $user->email !== 'admin@sbl.test')
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete member {{ addslashes($user->name) }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 rounded hover:bg-rose-50 transition-colors">
                                Delete
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                        No team members found matching your search.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Responsive Cards (md:hidden) -->
    <div class="md:hidden space-y-3">
        @forelse($users as $user)
        <div data-user-id="{{ $user->id }}" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                            <span>{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <span class="px-1.5 py-0.2 bg-slate-100 text-slate-600 text-[10px] rounded font-bold uppercase">You</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-500">{{ $user->designation ?: 'Staff Member' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                    </div>
                </div>

                @php
                    $primaryRole = $user->primary_role;
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-100 text-orange-800">
                    {{ $primaryRole ? $primaryRole->name : 'Staff' }}
                </span>
            </div>

            <!-- Contact & Stats Bar -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <div>
                    @if($user->phone)
                        <span>📞 {{ $user->phone }}</span>
                    @else
                        <span class="text-slate-400 italic">No phone</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded">👥 {{ $user->leads_count }} Leads</span>
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded">✅ {{ $user->tasks_count }} Tasks</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button @click="
                    editingUser = {
                        id: {{ $user->id }},
                        name: '{{ addslashes($user->name) }}',
                        email: '{{ addslashes($user->email) }}',
                        phone: '{{ addslashes($user->phone ?? '') }}',
                        designation: '{{ addslashes($user->designation ?? '') }}',
                        role_id: '{{ $primaryRole ? $primaryRole->id : '' }}',
                        status: '{{ $user->status }}'
                    };
                    editModalOpen = true;
                " class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors">
                    Edit Details
                </button>

                @if($user->id !== auth()->id() && $user->email !== 'admin@sbl.test')
                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($user->name) }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 bg-rose-50 text-rose-600 text-xs font-semibold rounded-lg">Delete</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm">
            No team members found.
        </div>
        @endforelse
    </div>

    <!-- ADD MEMBER MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="createModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">👤</span>
                    <h3 class="text-base font-bold text-slate-900">Add New Team Member</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Mahfuzur Rahman" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="mahfuz@sbl.test" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" placeholder="017xxxxxxxx" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation</label>
                        <input type="text" name="designation" placeholder="e.g. Senior Sales Agent" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assign Role *</label>
                        <select name="role_id" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Initial Password *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Minimum 8 characters" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Create Member</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MEMBER MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900">Edit Member: <span class="text-orange-600" x-text="editingUser.name"></span></h3>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/users') }}/' + editingUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" x-model="editingUser.name" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address *</label>
                        <input type="email" name="email" x-model="editingUser.email" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                        <input type="text" name="phone" x-model="editingUser.phone" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation</label>
                        <input type="text" name="designation" x-model="editingUser.designation" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role *</label>
                        <select name="role_id" x-model="editingUser.role_id" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Account Status</label>
                        <select name="status" x-model="editingUser.status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option value="active">🟢 Active</option>
                            <option value="inactive">🔴 Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Change Password</label>
                        <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Update Member</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
