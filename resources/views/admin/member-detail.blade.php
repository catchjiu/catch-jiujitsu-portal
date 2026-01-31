@extends('layouts.admin')

@section('title', $member->name)

@section('content')
<div class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.members') }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Member Profile</h1>
    </div>

    <!-- Profile Card -->
    <div class="glass rounded-2xl p-6 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <!-- Avatar (double-click to upload) -->
            <div class="relative w-24 h-24 mx-auto mb-4 group cursor-pointer" ondblclick="document.getElementById('avatarInput').click()">
                <div class="w-24 h-24 rounded-full overflow-hidden bg-slate-700 border-4 border-slate-600 transition-all group-hover:border-blue-500">
                    @if($member->avatar)
                        <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-3xl font-bold" style="font-family: 'Bebas Neue', sans-serif;">
                            {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <!-- Upload overlay on hover -->
                <div class="absolute inset-0 rounded-full bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="material-symbols-outlined text-white text-2xl">photo_camera</span>
                </div>
            </div>
            <p class="text-slate-500 text-xs mb-2">Double-click to upload photo</p>
            
            <!-- Hidden file input and form -->
            <form id="avatarForm" action="{{ route('admin.members.avatar', $member->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input type="file" id="avatarInput" name="avatar" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
            </form>
            
            <h2 class="text-2xl font-bold text-white mb-1">{{ $member->name }}</h2>
            <p class="text-slate-400 text-sm mb-4">{{ $member->email }}</p>

            <!-- Belt Display -->
            @php
                $beltColors = [
                    'White' => 'bg-gray-200',
                    'Grey' => 'bg-slate-400',
                    'Yellow' => 'bg-yellow-400',
                    'Orange' => 'bg-orange-500',
                    'Green' => 'bg-green-500',
                    'Blue' => 'bg-blue-600',
                    'Purple' => 'bg-purple-600',
                    'Brown' => 'bg-yellow-800',
                    'Black' => 'bg-black',
                ];
                $isBlackBelt = $member->rank === 'Black';
            @endphp
            <div class="flex justify-center mb-4">
                <div class="w-32 h-6 rounded {{ $beltColors[$member->rank] ?? 'bg-gray-200' }} relative flex items-center justify-end pr-2">
                    @if($isBlackBelt)
                        <!-- Red bar for black belt -->
                        <div class="h-full w-10 bg-red-600 flex items-center justify-around px-1">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @else
                        <!-- Black bar for stripes -->
                        <div class="h-full w-10 bg-black flex items-center justify-around px-1">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="text-center">
                    <span class="text-2xl font-bold text-blue-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $member->mat_hours }}</span>
                    <p class="text-xs text-slate-400 uppercase">Mat Hours</p>
                </div>
                <div class="text-center">
                    <span class="text-2xl font-bold text-amber-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $member->stripes }}</span>
                    <p class="text-xs text-slate-400 uppercase">Stripes</p>
                </div>
                <div class="text-center">
                    <span class="text-2xl font-bold text-emerald-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $bookings->count() }}</span>
                    <p class="text-xs text-slate-400 uppercase">Classes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">Edit Details</h3>
            
            <form action="{{ route('admin.members.update', $member->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', $member->first_name) }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', $member->last_name) }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', $member->email) }}" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Age Group</label>
                        <select name="age_group" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="Adults" {{ ($member->age_group ?? 'Adults') === 'Adults' ? 'selected' : '' }}>Adults</option>
                            <option value="Kids" {{ ($member->age_group ?? '') === 'Kids' ? 'selected' : '' }}>Kids</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mat Hours</label>
                        <input type="number" name="mat_hours" value="{{ old('mat_hours', $member->mat_hours) }}" required min="0"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Belt Rank</label>
                        <select name="rank" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @foreach(['White', 'Grey', 'Yellow', 'Orange', 'Green', 'Blue', 'Purple', 'Brown', 'Black'] as $rank)
                                <option value="{{ $rank }}" {{ $member->rank === $rank ? 'selected' : '' }}>{{ $rank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stripes</label>
                        <select name="stripes" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @for($i = 0; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ $member->stripes === $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <!-- Coach Toggle -->
                <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50 border border-slate-700/50">
                    <div>
                        <p class="text-white font-medium">Coach / Instructor</p>
                        <p class="text-slate-500 text-xs">Can be assigned to teach classes</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_coach" value="1" class="sr-only peer" {{ $member->is_coach ? 'checked' : '' }}>
                        <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                    </label>
                </div>

                <!-- Discount Options -->
                <div class="p-4 rounded-lg bg-slate-800/50 border border-slate-700/50">
                    <p class="text-white font-medium mb-3">Membership Discount</p>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="none" class="w-4 h-4 text-blue-500 bg-slate-700 border-slate-600 focus:ring-blue-500" {{ ($member->discount_type ?? 'none') === 'none' ? 'checked' : '' }}>
                            <div>
                                <span class="text-slate-300">None</span>
                                <p class="text-slate-500 text-xs">Regular membership pricing</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="half_price" class="w-4 h-4 text-amber-500 bg-slate-700 border-slate-600 focus:ring-amber-500" {{ ($member->discount_type ?? '') === 'half_price' ? 'checked' : '' }}>
                            <div>
                                <span class="text-amber-400">50% Off</span>
                                <p class="text-slate-500 text-xs">Half price on membership fees</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="gratis" class="w-4 h-4 text-emerald-500 bg-slate-700 border-slate-600 focus:ring-emerald-500" {{ ($member->discount_type ?? '') === 'gratis' ? 'checked' : '' }}>
                            <div>
                                <span class="text-emerald-400">Gratis (Free)</span>
                                <p class="text-slate-500 text-xs">Free membership, can always book classes</p>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Membership Management -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Membership</h3>
                @php
                    $statusColors = [
                        'active' => 'text-emerald-400 bg-emerald-400/10',
                        'pending' => 'text-amber-500 bg-amber-500/10',
                        'expired' => 'text-red-400 bg-red-400/10',
                        'none' => 'text-slate-400 bg-slate-400/10',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusColors[$member->membership_status] ?? $statusColors['none'] }}">
                    {{ $member->membership_status ?? 'None' }}
                </span>
            </div>

            @if($member->membershipPackage)
                <div class="mb-4 p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                    <p class="text-white font-medium">{{ $member->membershipPackage->name }}</p>
                    <div class="flex items-center gap-4 text-sm text-slate-400 mt-1">
                        @if($member->membership_expires_at)
                            <span>Expires: {{ $member->membership_expires_at->format('M d, Y') }}</span>
                        @endif
                        @if($member->membershipPackage->duration_type === 'classes' && $member->classes_remaining !== null)
                            <span>{{ $member->classes_remaining }} classes remaining</span>
                        @endif
                    </div>
                </div>
            @endif
            
            <form action="{{ route('admin.members.membership', $member->id) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Package</label>
                    <select name="membership_package_id"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="">No Package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ $member->membership_package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - ฿{{ number_format($package->price) }} ({{ $package->duration_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                        <select name="membership_status" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="none" {{ $member->membership_status === 'none' ? 'selected' : '' }}>None</option>
                            <option value="pending" {{ $member->membership_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $member->membership_status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $member->membership_status === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expires At</label>
                        <input type="date" name="membership_expires_at" value="{{ $member->membership_expires_at?->format('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Classes Remaining (for class packages)</label>
                    <input type="number" name="classes_remaining" value="{{ $member->classes_remaining }}" min="0"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                        placeholder="Leave empty for time-based packages">
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                    Update Membership
                </button>
            </form>
        </div>
    </div>

    <!-- Payment History -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">Payment History</h3>
            
            @if($payments->count() > 0)
                <div class="space-y-2">
                    @foreach($payments as $payment)
                        @php
                            $statusColors = [
                                'Paid' => 'text-emerald-400 bg-emerald-400/10',
                                'Pending Verification' => 'text-amber-500 bg-amber-500/10',
                                'Overdue' => 'text-red-400 bg-red-400/10',
                                'Rejected' => 'text-red-400 bg-red-400/10',
                            ];
                        @endphp
                        <div class="flex justify-between items-center py-2 border-b border-slate-700/50 last:border-0">
                            <div>
                                <p class="text-white text-sm">{{ $payment->month }}</p>
                                <p class="text-slate-500 text-xs">฿{{ number_format($payment->amount) }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $statusColors[$payment->status] ?? 'text-slate-400' }}">
                                {{ $payment->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-500 text-sm text-center py-4">No payment records.</p>
            @endif
        </div>
    </div>

    <!-- Delete Member -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden border border-red-500/20">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-red-500/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-red-400 mb-2" style="font-family: 'Bebas Neue', sans-serif;">Danger Zone</h3>
            <p class="text-slate-500 text-sm mb-4">Permanently delete this member and all their data (bookings, payments).</p>
            
            <form action="{{ route('admin.members.delete', $member->id) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete {{ $member->name }}? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full py-3 rounded-lg border border-red-500/50 text-red-400 font-bold uppercase text-sm tracking-wider hover:bg-red-500/10 transition-colors">
                    Delete Member
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
