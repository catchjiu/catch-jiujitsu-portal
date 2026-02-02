@extends('layouts.admin')

@section('title', 'Payments & Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-1">
        <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div>
            <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">Payments & Settings</h2>
            <p class="text-slate-400 text-sm">Verify payments and manage settings</p>
        </div>
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
                                    <p class="text-xl text-amber-500 font-mono">NT${{ number_format($payment->amount) }}</p>
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
                                <button type="button" onclick="openApproveModal({{ $payment->id }}, {{ $payment->user->id }}, '{{ $payment->user->membership_package_id ?? '' }}', '{{ $payment->user->membership_status ?? 'none' }}', '{{ $payment->user->membership_expires_at?->format('Y-m-d') ?? '' }}', '{{ $payment->user->classes_remaining ?? '' }}')" 
                                    class="w-full py-3 rounded bg-emerald-500 text-white font-bold uppercase text-xs hover:bg-emerald-600 transition-colors shadow-lg shadow-emerald-500/20">
                                    Approve
                                </button>
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
                                data-duration-value="{{ $package->duration_value }}">
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
                            <option value="active">Active</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Expires At</label>
                        <input type="date" name="membership_expires_at" id="modal_expires_at"
                            class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:border-emerald-500 transition-colors">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Classes Remaining (for class packages)</label>
                    <input type="number" name="classes_remaining" id="modal_classes_remaining" min="0"
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
    function openApproveModal(paymentId, userId, packageId, status, expiresAt, classesRemaining) {
        const modal = document.getElementById('approveModal');
        const form = document.getElementById('approvePaymentForm');
        form.action = '{{ url("admin/payments") }}/' + paymentId + '/approve-with-membership';
        
        // Set current values
        document.getElementById('modal_package_select').value = packageId || '';
        document.getElementById('modal_status_select').value = status || 'active';
        document.getElementById('modal_expires_at').value = expiresAt || '';
        document.getElementById('modal_classes_remaining').value = classesRemaining || '';
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Auto-set expiry when modal opens if package is already selected
        if (packageId) {
            modalAutoSetExpiry();
        }
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
                let expiryDate = new Date(today);
                
                if (durationType === 'days') {
                    expiryDate.setDate(today.getDate() + durationValue);
                } else if (durationType === 'weeks') {
                    expiryDate.setDate(today.getDate() + (durationValue * 7));
                } else if (durationType === 'months') {
                    expiryDate.setMonth(today.getMonth() + durationValue);
                } else if (durationType === 'years') {
                    expiryDate.setFullYear(today.getFullYear() + durationValue);
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
