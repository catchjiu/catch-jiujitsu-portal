@extends('layouts.admin')

@section('title', $member->name)

@section('content')
<div class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center gap-4">
        <button onclick="goBackToMembers()" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </button>
        <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Member Profile</h1>
    </div>
    
    <script>
        function goBackToMembers() {
            // Try to go back in history first (preserves filters via sessionStorage)
            if (document.referrer.includes('/admin/members')) {
                window.history.back();
            } else {
                // Fallback to members page
                window.location.href = '{{ route('admin.members') }}';
            }
        }
        
        function toggleBeltVariation() {
            const rankSelect = document.getElementById('rankSelect');
            const container = document.getElementById('beltVariationContainer');
            const kidsBelts = ['Grey', 'Yellow', 'Orange', 'Green'];
            
            if (kidsBelts.includes(rankSelect.value)) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
        
        // Run on page load
        document.addEventListener('DOMContentLoaded', toggleBeltVariation);
    </script>

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
            <div class="flex justify-center mb-4">
                @if($member->rank === 'Black')
                    <div class="w-32 h-6 rounded bg-black relative flex items-center">
                        <div class="h-full w-10 bg-red-600 flex items-center justify-start gap-1 px-1 rounded-l">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    </div>
                @elseif($member->rank === 'Brown')
                    <div class="w-32 h-6 rounded bg-yellow-900 relative flex items-center">
                        <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    </div>
                @elseif($member->rank === 'Purple')
                    <div class="w-32 h-6 rounded bg-purple-600 relative flex items-center">
                        <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    </div>
                @elseif($member->rank === 'Blue')
                    <div class="w-32 h-6 rounded bg-blue-600 relative flex items-center">
                        <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    </div>
                @elseif($member->rank === 'Green')
                    @if($member->belt_variation === 'solid')
                        <div class="w-32 h-6 rounded bg-green-500 relative flex items-center justify-start gap-1 px-2">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @elseif($member->belt_variation === 'white')
                        <div class="w-32 h-6 rounded bg-green-500 relative flex items-center">
                            <div class="h-full w-10 bg-white flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-green-500"></div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="w-32 h-6 rounded bg-green-500 relative flex items-center">
                            <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-white"></div>
                                @endfor
                            </div>
                        </div>
                    @endif
                @elseif($member->rank === 'Orange')
                    @if($member->belt_variation === 'solid')
                        <div class="w-32 h-6 rounded bg-orange-500 relative flex items-center justify-start gap-1 px-2">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @elseif($member->belt_variation === 'white')
                        <div class="w-32 h-6 rounded bg-orange-500 relative flex items-center">
                            <div class="h-full w-10 bg-white flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-orange-500"></div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="w-32 h-6 rounded bg-orange-500 relative flex items-center">
                            <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-white"></div>
                                @endfor
                            </div>
                        </div>
                    @endif
                @elseif($member->rank === 'Yellow')
                    @if($member->belt_variation === 'solid')
                        <div class="w-32 h-6 rounded bg-yellow-400 relative flex items-center justify-start gap-1 px-2">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @elseif($member->belt_variation === 'white')
                        <div class="w-32 h-6 rounded bg-yellow-400 relative flex items-center">
                            <div class="h-full w-10 bg-white flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-yellow-400"></div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="w-32 h-6 rounded bg-yellow-400 relative flex items-center">
                            <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-white"></div>
                                @endfor
                            </div>
                        </div>
                    @endif
                @elseif($member->rank === 'Grey')
                    @if($member->belt_variation === 'solid')
                        <div class="w-32 h-6 rounded bg-slate-300 relative flex items-center justify-start gap-1 px-2">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    @elseif($member->belt_variation === 'white')
                        <div class="w-32 h-6 rounded bg-slate-300 relative flex items-center">
                            <div class="h-full w-10 bg-white flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-slate-300"></div>
                                @endfor
                            </div>
                        </div>
                    @else
                        <div class="w-32 h-6 rounded bg-slate-300 relative flex items-center">
                            <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                                @for ($i = 0; $i < $member->stripes; $i++)
                                    <div class="w-1.5 h-full bg-white"></div>
                                @endfor
                            </div>
                        </div>
                    @endif
                @else
                    <!-- White Belt (default) -->
                    <div class="w-32 h-6 rounded bg-gray-100 relative flex items-center">
                        <div class="h-full w-10 bg-black flex items-center justify-start gap-1 px-1 rounded-l">
                            @for ($i = 0; $i < $member->stripes; $i++)
                                <div class="w-1.5 h-full bg-white"></div>
                            @endfor
                        </div>
                    </div>
                @endif
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
                            <option value="Adults" {{ old('age_group', $member->age_group) === 'Adults' ? 'selected' : '' }}>Adults</option>
                            <option value="Kids" {{ old('age_group', $member->age_group) === 'Kids' ? 'selected' : '' }}>Kids</option>
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
                        <select name="rank" id="rankSelect" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            onchange="toggleBeltVariation()">
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

                <!-- Belt Variation (for kids belts) -->
                <div id="beltVariationContainer" class="hidden">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Belt Variation</label>
                    <select name="belt_variation" id="beltVariationSelect"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <option value="white" {{ $member->belt_variation === 'white' ? 'selected' : '' }}>White Bar (e.g., Grey-White)</option>
                        <option value="solid" {{ $member->belt_variation === 'solid' ? 'selected' : '' }}>Solid (No Bar)</option>
                        <option value="black" {{ $member->belt_variation === 'black' ? 'selected' : '' }}>Black Bar (e.g., Grey-Black)</option>
                    </select>
                    <p class="text-slate-500 text-xs mt-1">Kids belts have 3 progressions: White bar → Solid → Black bar</p>
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
                            <input type="radio" name="discount_type" value="none" id="discount_none" class="w-4 h-4 text-blue-500 bg-slate-700 border-slate-600 focus:ring-blue-500" {{ ($member->discount_type ?? 'none') === 'none' ? 'checked' : '' }} onchange="toggleDiscountInput()">
                            <div>
                                <span class="text-slate-300">None</span>
                                <p class="text-slate-500 text-xs">Regular membership pricing</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="fixed" id="discount_fixed" class="w-4 h-4 text-amber-500 bg-slate-700 border-slate-600 focus:ring-amber-500" {{ in_array($member->discount_type ?? '', ['fixed', 'percentage', 'half_price']) ? 'checked' : '' }} onchange="toggleDiscountInput()">
                            <div class="flex-1">
                                <span class="text-amber-400">Custom Discount</span>
                                <p class="text-slate-500 text-xs">Set a specific discount amount</p>
                            </div>
                        </label>
                        <!-- Discount amount input -->
                        <div id="discount_amount_container" class="ml-7 {{ !in_array($member->discount_type ?? 'none', ['fixed', 'percentage', 'half_price']) ? 'hidden' : '' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-amber-400 font-bold">NT$</span>
                                <input type="number" name="discount_amount" id="discount_amount_input" 
                                    value="{{ $member->discount_amount ?? 0 }}" 
                                    min="0" max="100000" 
                                    class="w-28 px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white text-center focus:outline-none focus:border-amber-500 transition-colors"
                                    placeholder="500">
                                <span class="text-slate-400 text-sm">Off</span>
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="discount_type" value="gratis" id="discount_gratis" class="w-4 h-4 text-emerald-500 bg-slate-700 border-slate-600 focus:ring-emerald-500" {{ ($member->discount_type ?? '') === 'gratis' ? 'checked' : '' }} onchange="toggleDiscountInput()">
                            <div>
                                <span class="text-emerald-400">Gratis (Free)</span>
                                <p class="text-slate-500 text-xs">Free membership, can always book classes</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                <script>
                    function toggleDiscountInput() {
                        const container = document.getElementById('discount_amount_container');
                        const fixedRadio = document.getElementById('discount_fixed');
                        if (fixedRadio.checked) {
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    }
                </script>

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
                    <select name="membership_package_id" id="membership_package_select"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                        onchange="autoSetActiveAndExpiry()">
                        <option value="" data-duration-type="" data-duration-value="">No Package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" 
                                data-duration-type="{{ $package->duration_type }}" 
                                data-duration-value="{{ $package->duration_value }}"
                                {{ $member->membership_package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - NT${{ number_format($package->price) }} ({{ $package->duration_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                        <select name="membership_status" id="membership_status_select" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            <option value="none" {{ $member->membership_status === 'none' ? 'selected' : '' }}>None</option>
                            <option value="pending" {{ $member->membership_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ $member->membership_status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ $member->membership_status === 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        
                        <script>
                            function autoSetActiveAndExpiry() {
                                const packageSelect = document.getElementById('membership_package_select');
                                const statusSelect = document.getElementById('membership_status_select');
                                const expiryInput = document.getElementById('membership_expires_at');
                                
                                // Member's current expiry date
                                const currentExpiresAt = '{{ $member->membership_expires_at?->format("Y-m-d") ?? "" }}';
                                
                                // If a package is selected
                                if (packageSelect.value) {
                                    // Auto-set status to active
                                    statusSelect.value = 'active';
                                    
                                    // Get selected option's duration data
                                    const selectedOption = packageSelect.options[packageSelect.selectedIndex];
                                    const durationType = selectedOption.dataset.durationType;
                                    const durationValue = parseInt(selectedOption.dataset.durationValue) || 0;
                                    
                                    // Calculate expiry date
                                    if (durationType && durationValue > 0) {
                                        const today = new Date();
                                        today.setHours(0, 0, 0, 0);
                                        
                                        // Start from member's previous expiry date if it exists and is in the future
                                        let startDate = new Date(today);
                                        if (currentExpiresAt) {
                                            const prevExpiry = new Date(currentExpiresAt);
                                            prevExpiry.setHours(0, 0, 0, 0);
                                            if (prevExpiry > today) {
                                                startDate = prevExpiry;
                                            }
                                        }
                                        
                                        let expiryDate = new Date(startDate);
                                        
                                        if (durationType === 'days') {
                                            expiryDate.setDate(startDate.getDate() + durationValue);
                                        } else if (durationType === 'weeks') {
                                            expiryDate.setDate(startDate.getDate() + (durationValue * 7));
                                        } else if (durationType === 'months') {
                                            expiryDate.setMonth(startDate.getMonth() + durationValue);
                                        } else if (durationType === 'years') {
                                            expiryDate.setFullYear(startDate.getFullYear() + durationValue);
                                        }
                                        
                                        // Format as YYYY-MM-DD
                                        const year = expiryDate.getFullYear();
                                        const month = String(expiryDate.getMonth() + 1).padStart(2, '0');
                                        const day = String(expiryDate.getDate()).padStart(2, '0');
                                        expiryInput.value = `${year}-${month}-${day}`;
                                    }
                                }
                            }
                        </script>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expires At</label>
                        <input type="date" name="membership_expires_at" id="membership_expires_at" value="{{ $member->membership_expires_at?->format('Y-m-d') }}"
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
                <div class="space-y-3">
                    @foreach($payments as $payment)
                        @php
                            $statusColors = [
                                'Paid' => 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20',
                                'Pending Verification' => 'text-amber-500 bg-amber-500/10 border-amber-500/20',
                                'Overdue' => 'text-red-400 bg-red-400/10 border-red-400/20',
                                'Rejected' => 'text-red-400 bg-red-400/10 border-red-400/20',
                            ];
                        @endphp
                        <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-white font-medium">{{ $payment->month }}</p>
                                    <p class="text-lg font-bold text-amber-500">NT${{ number_format($payment->amount) }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-[10px] font-bold uppercase border {{ $statusColors[$payment->status] ?? 'text-slate-400' }}">
                                    {{ $payment->status }}
                                </span>
                            </div>
                            
                            @if($payment->payment_method || $payment->payment_date || $payment->account_last_5)
                                <div class="text-xs text-slate-400 space-y-1 mb-2 pt-2 border-t border-slate-700/50">
                                    @if($payment->payment_method)
                                        <p><span class="text-slate-500">Method:</span> {{ $payment->payment_method === 'bank' ? 'Bank Transfer' : 'LinePay' }}</p>
                                    @endif
                                    @if($payment->payment_date)
                                        <p><span class="text-slate-500">Paid on:</span> {{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</p>
                                    @endif
                                    @if($payment->account_last_5)
                                        <p><span class="text-slate-500">Account (last 5):</span> {{ $payment->account_last_5 }}</p>
                                    @endif
                                </div>
                            @endif
                            
                            @if($payment->submitted_at)
                                <p class="text-xs text-slate-500">Submitted {{ $payment->submitted_at->diffForHumans() }}</p>
                            @endif
                            
                            @if($payment->status === 'Pending Verification')
                                <div class="flex gap-2 mt-3 pt-3 border-t border-slate-700/50">
                                    <button type="button" onclick="openApproveModal({{ $payment->id }})" class="flex-1 py-2 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold uppercase transition-colors flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-sm">check</span>
                                        Confirm
                                    </button>
                                    <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-2 rounded border border-red-500/50 text-red-400 hover:bg-red-500/10 text-xs font-bold uppercase transition-colors flex items-center justify-center gap-1">
                                            <span class="material-symbols-outlined text-sm">close</span>
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif
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

<!-- Approve Payment Modal -->
<div id="approveModal" class="fixed inset-0 z-50 hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/70" onclick="closeApproveModal()"></div>
    
    <!-- Modal Content -->
    <div class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-md bg-slate-800 rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Confirm Payment & Update Membership</h3>
            <button onclick="closeApproveModal()" class="text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <div class="p-4 overflow-y-auto flex-1">
            <form id="approvePaymentForm" method="POST" class="space-y-4">
                @csrf
                
                <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-center mb-4">
                    <span class="material-symbols-outlined text-emerald-400 text-2xl">check_circle</span>
                    <p class="text-emerald-400 text-sm font-medium mt-1">Payment will be marked as PAID</p>
                </div>
                
                <p class="text-slate-400 text-sm mb-4">Update membership information for this member:</p>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Package</label>
                    <select name="membership_package_id" id="modal_package_select"
                        class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:border-emerald-500 transition-colors"
                        onchange="modalAutoSetExpiry()">
                        <option value="" data-duration-type="" data-duration-value="">No Package</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" 
                                data-duration-type="{{ $package->duration_type }}" 
                                data-duration-value="{{ $package->duration_value }}"
                                {{ $member->membership_package_id == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - NT${{ number_format($package->price) }} ({{ $package->duration_label }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                        <select name="membership_status" id="modal_status_select"
                            class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:border-emerald-500 transition-colors">
                            <option value="none">None</option>
                            <option value="pending">Pending</option>
                            <option value="active" selected>Active</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expires At</label>
                        <input type="date" name="membership_expires_at" id="modal_expires_at" value="{{ $member->membership_expires_at?->format('Y-m-d') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Classes Remaining (for class packages)</label>
                    <input type="number" name="classes_remaining" value="{{ $member->classes_remaining }}" min="0"
                        class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:border-emerald-500 transition-colors"
                        placeholder="Leave empty for time-based packages">
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeApproveModal()" 
                        class="flex-1 py-3 rounded-lg border border-slate-600 text-slate-400 font-bold uppercase text-sm tracking-wider hover:bg-slate-700 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 py-3 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold uppercase text-sm tracking-wider transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">check</span>
                        Confirm & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Member's current expiry date for calculation
    const memberCurrentExpiresAt = '{{ $member->membership_expires_at?->format("Y-m-d") ?? "" }}';
    
    function openApproveModal(paymentId) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approvePaymentForm');
        form.action = '{{ url("admin/payments") }}/' + paymentId + '/approve-with-membership';
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Auto-set expiry when modal opens if package is already selected
        modalAutoSetExpiry();
    }
    
    function closeApproveModal() {
        const modal = document.getElementById('approveModal');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    function modalAutoSetExpiry() {
        const packageSelect = document.getElementById('modal_package_select');
        const statusSelect = document.getElementById('modal_status_select');
        const expiryInput = document.getElementById('modal_expires_at');
        
        if (packageSelect.value) {
            statusSelect.value = 'active';
            
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            const durationType = selectedOption.dataset.durationType;
            const durationValue = parseInt(selectedOption.dataset.durationValue) || 0;
            
            if (durationType && durationValue > 0) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                // Start from member's previous expiry date if it exists and is in the future
                let startDate = new Date(today);
                if (memberCurrentExpiresAt) {
                    const prevExpiry = new Date(memberCurrentExpiresAt);
                    prevExpiry.setHours(0, 0, 0, 0);
                    if (prevExpiry > today) {
                        startDate = prevExpiry;
                    }
                }
                
                let expiryDate = new Date(startDate);
                
                if (durationType === 'days') {
                    expiryDate.setDate(startDate.getDate() + durationValue);
                } else if (durationType === 'weeks') {
                    expiryDate.setDate(startDate.getDate() + (durationValue * 7));
                } else if (durationType === 'months') {
                    expiryDate.setMonth(startDate.getMonth() + durationValue);
                } else if (durationType === 'years') {
                    expiryDate.setFullYear(startDate.getFullYear() + durationValue);
                }
                
                const year = expiryDate.getFullYear();
                const month = String(expiryDate.getMonth() + 1).padStart(2, '0');
                const day = String(expiryDate.getDate()).padStart(2, '0');
                expiryInput.value = `${year}-${month}-${day}`;
            }
        }
    }
</script>
@endsection
