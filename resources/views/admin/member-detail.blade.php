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
            <!-- Avatar -->
            <div class="w-24 h-24 rounded-full mx-auto mb-4 overflow-hidden bg-slate-700 border-4 border-slate-600">
                @if($member->avatar_url)
                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-3xl font-bold" style="font-family: 'Bebas Neue', sans-serif;">
                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            
            <h2 class="text-2xl font-bold text-white mb-1">{{ $member->name }}</h2>
            <p class="text-slate-400 text-sm mb-4">{{ $member->email }}</p>

            <!-- Belt Display -->
            @php
                $beltColors = [
                    'White' => 'bg-gray-200',
                    'Blue' => 'bg-blue-600',
                    'Purple' => 'bg-purple-600',
                    'Brown' => 'bg-yellow-800',
                    'Black' => 'bg-slate-900 border border-red-600',
                ];
            @endphp
            <div class="flex justify-center mb-4">
                <div class="w-32 h-6 rounded {{ $beltColors[$member->rank] ?? 'bg-gray-200' }} relative flex items-center justify-end pr-2">
                    <div class="h-full w-10 bg-black flex items-center justify-around px-1">
                        @for ($i = 0; $i < $member->stripes; $i++)
                            <div class="w-1.5 h-full bg-white"></div>
                        @endfor
                    </div>
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
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Belt Rank</label>
                        <select name="rank" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @foreach(['White', 'Blue', 'Purple', 'Brown', 'Black'] as $rank)
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

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mat Hours</label>
                    <input type="number" name="mat_hours" value="{{ old('mat_hours', $member->mat_hours) }}" required min="0"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                    Save Changes
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
</div>
@endsection
