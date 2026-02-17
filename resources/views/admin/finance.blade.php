@extends('layouts.admin')

@section('title', 'Financial Dashboard')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Financial Dashboard</h1>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-400 text-sm">{{ now()->format('F Y') }}</span>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Estimated Monthly Revenue -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-500 text-lg">payments</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 uppercase">Est. Monthly Revenue</p>
                <p class="text-2xl font-bold text-emerald-400" style="font-family: 'Bebas Neue', sans-serif;">NT${{ number_format($estimatedRevenue) }}</p>
            </div>
        </div>
        
        <!-- Total Members -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-500 text-lg">groups</span>
                    </div>
                    @if($newMembersThisMonth > 0)
                        <span class="text-emerald-400 text-xs font-medium">+{{ $newMembersThisMonth }} new</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 uppercase">Total Members</p>
                <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $totalMembers }}</p>
            </div>
        </div>
        
        <!-- Active Memberships -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-cyan-500 text-lg">verified</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 uppercase">Active Members</p>
                <p class="text-2xl font-bold text-cyan-400" style="font-family: 'Bebas Neue', sans-serif;">{{ $activeMemberships }}</p>
            </div>
        </div>
        
        <!-- Expiring Soon -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden {{ $expiringSoon > 0 ? 'cursor-pointer hover:bg-slate-800/60' : '' }} transition-colors" 
             @if($expiringSoon > 0) onclick="toggleExpiringList()" @endif>
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-1">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-amber-500 text-lg">schedule</span>
                    </div>
                    @if($expiringSoon > 0)
                        <span class="material-symbols-outlined text-slate-500 text-lg transition-transform" id="expiringArrow">expand_more</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 uppercase">Expiring (7 days)</p>
                <p class="text-2xl font-bold {{ $expiringSoon > 0 ? 'text-amber-400' : 'text-white' }}" style="font-family: 'Bebas Neue', sans-serif;">{{ $expiringSoon }}</p>
            </div>
        </div>
    </div>
    
    <!-- Expiring Members List (Hidden by default) -->
    @if($expiringSoon > 0)
    <div id="expiringMembersList" class="hidden glass rounded-2xl p-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-amber-400 mb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">warning</span>
                Memberships Expiring Soon
            </h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($expiringMembersList as $member)
                    <a href="{{ route('admin.members.show', $member->id) }}" 
                       class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                                @if($member->avatar)
                                    <img src="{{ $member->avatar }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                        {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $member->name }}</p>
                                <p class="text-slate-500 text-xs">{{ $member->membershipPackage->name ?? 'Unknown Package' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @php
                                $daysLeft = (int) now()->startOfDay()->diffInDays($member->membership_expires_at->startOfDay(), false);
                            @endphp
                            <p class="text-amber-400 font-bold text-sm">{{ $member->membership_expires_at->format('M j') }}</p>
                            <p class="text-slate-500 text-xs">
                                @if($daysLeft <= 0)
                                    Today
                                @elseif($daysLeft == 1)
                                    Tomorrow
                                @else
                                    {{ $daysLeft }} days
                                @endif
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    
    <script>
        function toggleExpiringList() {
            const list = document.getElementById('expiringMembersList');
            const arrow = document.getElementById('expiringArrow');
            
            if (list.classList.contains('hidden')) {
                list.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                list.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    </script>
    @endif

    <!-- Member Growth Chart -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-400 text-lg">trending_up</span>
                Active Member Growth (Last 6 Months)
            </h3>
            <div class="h-48">
                <canvas id="memberGrowthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Membership Status Breakdown -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-purple-400 text-lg">pie_chart</span>
                Membership Status
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="h-40">
                    <canvas id="membershipStatusChart"></canvas>
                </div>
                <div class="flex flex-col justify-center space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                        <span class="text-slate-300 text-xs">Active: {{ $activeMemberships }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                        <span class="text-slate-300 text-xs">Pending: {{ $pendingMemberships }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-slate-300 text-xs">Expired: {{ $expiredMemberships }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-slate-500"></div>
                        <span class="text-slate-300 text-xs">None: {{ $noMembership }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Age Group & Package Breakdown -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Age Groups -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Age Groups</h3>
                <div class="h-32">
                    <canvas id="ageGroupChart"></canvas>
                </div>
                <div class="flex justify-center gap-4 mt-2 text-xs">
                    <span class="text-blue-400">Adults: {{ $adultMembers }}</span>
                    <span class="text-emerald-400">Kids: {{ $kidsMembers }}</span>
                </div>
            </div>
        </div>
        
        <!-- Discount Types -->
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Discounts</h3>
                <div class="space-y-3 mt-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-lg">volunteer_activism</span>
                            <span class="text-slate-300 text-sm">Gratis (Free)</span>
                        </div>
                        <div class="text-right">
                            <span class="text-emerald-400 font-bold">{{ $gratisMembers }}</span>
                            @if($gratisValue > 0)
                                <p class="text-slate-500 text-[10px]">-NT${{ number_format($gratisValue) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-400 text-lg">sell</span>
                            <span class="text-slate-300 text-sm">Discounted</span>
                        </div>
                        <div class="text-right">
                            <span class="text-amber-400 font-bold">{{ $discountedMembers }}</span>
                            @if($totalDiscountsGiven > 0)
                                <p class="text-slate-500 text-[10px]">-NT${{ number_format($totalDiscountsGiven) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @if($gratisValue > 0 || $totalDiscountsGiven > 0)
                <div class="mt-3 pt-3 border-t border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-xs">Revenue Adjustment</span>
                        <span class="text-red-400 font-bold text-sm">-NT${{ number_format($gratisValue + $totalDiscountsGiven) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Membership Packages Breakdown -->
    @if(count($membershipPlans) > 0)
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-400 text-lg">inventory_2</span>
                Package Distribution
            </h3>
            <div class="space-y-3">
                @foreach($membershipPlans as $plan)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1">
                            <span class="text-slate-300 text-sm truncate max-w-[120px]">{{ $plan['name'] }}</span>
                            <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $plan['color'] }} rounded-full transition-all" style="width: {{ $plan['percentage'] }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-3">
                            <span class="text-white font-bold text-sm">{{ $plan['count'] }}</span>
                            <span class="text-slate-500 text-xs">({{ $plan['percentage'] }}%)</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- This Month Stats -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-cyan-400 text-lg">calendar_month</span>
                This Month's Activity
            </h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $classesThisMonth }}</p>
                    <p class="text-xs text-slate-400">Classes Scheduled</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-blue-400" style="font-family: 'Bebas Neue', sans-serif;">{{ $totalBookingsThisMonth }}</p>
                    <p class="text-xs text-slate-400">Total Bookings</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-400" style="font-family: 'Bebas Neue', sans-serif;">{{ $newMembersThisMonth }}</p>
                    <p class="text-xs text-slate-400">New Signups</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    @if($recentPayments->count() > 0)
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-lg">receipt_long</span>
                Recent Transactions
            </h3>
            <a href="{{ route('admin.payments') }}" class="text-blue-400 text-xs hover:text-blue-300">View All</a>
        </div>

        <div class="space-y-2">
            @foreach($recentPayments->take(5) as $payment)
                @php
                    $statusColors = [
                        'Paid' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400', 'label' => 'PAID'],
                        'Pending Verification' => ['bg' => 'bg-amber-500/20', 'text' => 'text-amber-400', 'label' => 'PENDING'],
                        'Overdue' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'label' => 'OVERDUE'],
                        'Rejected' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'label' => 'FAILED'],
                    ];
                    $status = $statusColors[$payment->status] ?? $statusColors['Overdue'];
                @endphp
                
                <a href="{{ route('admin.members.show', $payment->user->id) }}" class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                        @if($payment->user->avatar)
                            <img src="{{ $payment->user->avatar }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ strtoupper(substr($payment->user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-white font-medium truncate text-sm">{{ $payment->user->name }}</h3>
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-bold {{ $status['bg'] }} {{ $status['text'] }}">
                                {{ $status['label'] }}
                            </span>
                        </div>
                        <p class="text-slate-500 text-xs">{{ $payment->month }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white font-semibold text-sm">NT${{ number_format($payment->amount) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('admin.payments') }}" class="glass rounded-xl p-4 flex items-center gap-3 hover:bg-slate-700/50 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-500">pending_actions</span>
            </div>
            <div>
                <p class="text-white font-medium text-sm">Pending</p>
                <p class="text-amber-400 text-xs font-bold">{{ $pendingCount }} to verify</p>
            </div>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="glass rounded-xl p-4 flex items-center gap-3 hover:bg-slate-700/50 transition-colors">
            <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                <span class="material-symbols-outlined text-blue-500">inventory_2</span>
            </div>
            <div>
                <p class="text-white font-medium text-sm">Packages</p>
                <p class="text-blue-400 text-xs font-bold">Manage plans</p>
            </div>
        </a>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Common chart options
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = 'Inter, sans-serif';
    
    // Member Growth Line Chart
    const memberGrowthCtx = document.getElementById('memberGrowthChart').getContext('2d');
    new Chart(memberGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! $monthLabels !!},
            datasets: [{
                label: 'Active Members',
                data: {!! $memberGrowth !!},
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    grid: { color: 'rgba(148, 163, 184, 0.1)' },
                    ticks: { precision: 0 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
    
    // Membership Status Doughnut Chart
    const membershipStatusCtx = document.getElementById('membershipStatusChart').getContext('2d');
    new Chart(membershipStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Expired', 'None'],
            datasets: [{
                data: [{{ $activeMemberships }}, {{ $pendingMemberships }}, {{ $expiredMemberships }}, {{ $noMembership }}],
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#64748B'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false }
            }
        }
    });
    
    // Age Group Doughnut Chart
    const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
    new Chart(ageGroupCtx, {
        type: 'doughnut',
        data: {
            labels: ['Adults', 'Kids'],
            datasets: [{
                data: [{{ $adultMembers }}, {{ $kidsMembers }}],
                backgroundColor: ['#3B82F6', '#10B981'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endsection
