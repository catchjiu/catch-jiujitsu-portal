@extends('layouts.admin')

@section('title', 'Financial Management')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-slate-400">menu</span>
            <h1 class="text-lg font-bold text-white">Apex Financials</h1>
        </div>
        <div class="flex items-center gap-3">
            <button class="text-slate-400 hover:text-white transition-colors relative">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <div class="w-8 h-8 rounded-full bg-amber-500"></div>
        </div>
    </div>

    <!-- Month Selector -->
    <div class="flex items-center justify-between">
        <p class="text-slate-400 text-sm">{{ now()->format('F Y') }}</p>
        <button class="flex items-center gap-1 text-blue-500 text-sm font-medium hover:text-blue-400 transition-colors">
            EXPORT
            <span class="material-symbols-outlined text-lg">download</span>
        </button>
    </div>

    <!-- Overview Title -->
    <div class="flex items-center justify-between">
        <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Overview</p>
        <a href="#" class="text-blue-500 text-xs hover:text-blue-400">View Details</a>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-2 gap-3">
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-500 text-lg">trending_up</span>
                    </div>
                    <span class="text-emerald-400 text-xs font-medium">12%</span>
                </div>
                <p class="text-xs text-slate-400 uppercase">MRR</p>
                <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">฿{{ number_format($mrr) }}</p>
            </div>
        </div>
        
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-500 text-lg">groups</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 uppercase">Active Members</p>
                <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $activeMembers }}</p>
            </div>
        </div>
    </div>

    <!-- Membership Plans -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-xs text-slate-400 uppercase tracking-wider font-bold mb-4">Membership Plans</h3>
            
            <div class="space-y-4">
                @foreach($membershipPlans as $plan)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <span class="text-slate-300 text-sm">{{ $plan['name'] }}</span>
                            <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $plan['color'] }} rounded-full" style="width: {{ $plan['percentage'] }}%"></div>
                            </div>
                        </div>
                        <span class="text-slate-400 text-sm ml-3">{{ $plan['percentage'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Recent Transactions</p>
            <div class="flex items-center gap-2">
                <button class="text-slate-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-lg">search</span>
                </button>
                <button class="text-slate-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-lg">tune</span>
                </button>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex gap-2 mb-4">
            <button class="px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-500 text-white">All</button>
            <button class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-800/60 text-slate-400 hover:bg-slate-700/60">Failed</button>
            <button class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-800/60 text-slate-400 hover:bg-slate-700/60">Pending</button>
            <button class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-800/60 text-slate-400 hover:bg-slate-700/60">Refunds</button>
        </div>

        <!-- Transactions List -->
        <div class="space-y-2">
            @foreach($recentPayments as $payment)
                @php
                    $statusColors = [
                        'Paid' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400', 'label' => ''],
                        'Pending Verification' => ['bg' => 'bg-amber-500/20', 'text' => 'text-amber-400', 'label' => 'PENDING'],
                        'Overdue' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'label' => 'OVERDUE'],
                        'Rejected' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'label' => 'FAILED'],
                    ];
                    $status = $statusColors[$payment->status] ?? $statusColors['Overdue'];
                @endphp
                
                <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/30">
                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                        @if($payment->user->avatar_url)
                            <img src="{{ $payment->user->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ strtoupper(substr($payment->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-white font-medium truncate">{{ $payment->user->name }}</h3>
                            @if($status['label'])
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold {{ $status['bg'] }} {{ $status['text'] }}">
                                    {{ $status['label'] }}
                                </span>
                            @endif
                        </div>
                        <p class="text-slate-500 text-xs">{{ $payment->month }} • {{ $payment->updated_at->diffForHumans() }}</p>
                    </div>

                    <!-- Amount -->
                    <div class="text-right">
                        <p class="text-white font-semibold">฿{{ number_format($payment->amount) }}</p>
                        @if($payment->status === 'Rejected')
                            <button class="text-red-400 text-[10px] font-bold uppercase hover:text-red-300">RETRY</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Generate Report Button -->
    <button class="w-full py-4 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-colors flex items-center justify-center gap-2 shadow-lg shadow-blue-500/20">
        <span class="material-symbols-outlined">summarize</span>
        Generate Monthly Report
    </button>
</div>
@endsection
