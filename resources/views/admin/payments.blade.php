@extends('layouts.admin')

@section('title', 'Payments & Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">Payments & Settings</h2>
        <p class="text-slate-400 text-sm">Verify payments and manage settings</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-3 gap-3">
        <div class="glass rounded-xl p-4 text-center">
            <span class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $stats['total_members'] }}</span>
            <p class="text-xs text-slate-400 mt-1">Members</p>
        </div>
        <div class="glass rounded-xl p-4 text-center">
            <span class="text-2xl font-bold text-amber-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $stats['pending_payments'] }}</span>
            <p class="text-xs text-slate-400 mt-1">Pending</p>
        </div>
        <div class="glass rounded-xl p-4 text-center">
            <span class="text-2xl font-bold text-emerald-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $stats['paid_this_month'] }}</span>
            <p class="text-xs text-slate-400 mt-1">Paid</p>
        </div>
    </div>

    <!-- Pending Payments Section -->
    <div>
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
            <span class="material-symbols-outlined text-amber-500">pending_actions</span>
            PENDING VERIFICATION
        </h3>

        @if($pendingPayments->count() === 0)
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">check_circle</span>
                <p>No pending payments.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($pendingPayments as $payment)
                    <div class="glass rounded-2xl p-5 border-l-4 border-l-amber-500 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase font-bold mb-1">{{ $payment->user->name }}</p>
                                    <p class="text-white font-bold">{{ $payment->month }}</p>
                                    <p class="text-xl text-amber-500 font-mono">฿{{ number_format($payment->amount) }}</p>
                                </div>
                                <div class="text-right text-xs text-slate-500">
                                    {{ $payment->submitted_at?->format('M j, Y') }}
                                </div>
                            </div>

                            <!-- Proof Image Preview -->
                            @if($payment->proof_image_path)
                                <div class="mb-4 bg-black rounded-lg overflow-hidden h-48 w-full relative group">
                                    <img src="{{ Storage::url($payment->proof_image_path) }}" alt="Proof" 
                                         class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ Storage::url($payment->proof_image_path) }}" target="_blank"
                                       class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/40">
                                        <span class="text-white text-xs font-bold uppercase border border-white px-3 py-1 rounded">View Full</span>
                                    </a>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-2 gap-3">
                                <form action="{{ route('admin.payments.reject', $payment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 rounded bg-red-500/10 text-red-500 font-bold uppercase text-xs hover:bg-red-500/20 transition-colors border border-red-500/20">
                                        Reject
                                    </button>
                                </form>
                                <form action="{{ route('admin.payments.approve', $payment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-3 rounded bg-emerald-500 text-white font-bold uppercase text-xs hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20">
                                        Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Recent Payments -->
    <div>
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
            <span class="material-symbols-outlined text-blue-500">history</span>
            RECENT PAYMENTS
        </h3>

        <div class="glass rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="text-left py-3 px-4 text-xs text-slate-400 uppercase tracking-wider font-bold">Member</th>
                            <th class="text-left py-3 px-4 text-xs text-slate-400 uppercase tracking-wider font-bold">Month</th>
                            <th class="text-right py-3 px-4 text-xs text-slate-400 uppercase tracking-wider font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($allPayments as $payment)
                            @php
                                $statusColors = [
                                    'Paid' => 'text-emerald-400',
                                    'Pending Verification' => 'text-amber-500',
                                    'Overdue' => 'text-red-400',
                                    'Rejected' => 'text-red-400',
                                ];
                            @endphp
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 text-white">{{ $payment->user->name }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $payment->month }}</td>
                                <td class="py-3 px-4 text-right">
                                    <span class="{{ $statusColors[$payment->status] ?? 'text-slate-400' }} text-xs font-bold">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
