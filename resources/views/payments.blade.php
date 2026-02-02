@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">Payments</h2>
        <p class="text-slate-400 text-sm">Manage your monthly membership</p>
    </div>

    <!-- Payment Instructions Card -->
    <div class="glass rounded-2xl p-5 border-blue-500/30 bg-slate-800/80 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-white mb-4">Payment Methods</h3>
            
            <!-- Payment Method Tabs -->
            <div class="flex space-x-2 mb-6 p-1 bg-slate-900 rounded-lg" x-data="{ method: 'bank' }">
                <button @click="method = 'bank'" :class="method === 'bank' ? 'bg-slate-700 text-white' : 'text-slate-500'"
                    class="flex-1 py-2 text-sm font-bold rounded transition-colors">
                    Bank Transfer
                </button>
                <button @click="method = 'line'" :class="method === 'line' ? 'bg-slate-700 text-white' : 'text-slate-500'"
                    class="flex-1 py-2 text-sm font-bold rounded transition-colors">
                    LinePay
                </button>
            </div>

            <!-- Bank Transfer Details -->
            <div id="bank-details" class="space-y-4">
                <div class="text-center p-4 bg-slate-900 rounded border border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-slate-400 mb-2">account_balance</span>
                    <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">CTBC Bank</p>
                    <p class="text-xl font-mono text-amber-500 select-all cursor-pointer">822 037540606649</p>
                    <p class="text-slate-500 text-xs mt-1">Account Name: Catch Jiu Jitsu</p>
                </div>
                <div class="text-center p-4 bg-slate-900 rounded border border-slate-700">
                    <span class="material-symbols-outlined text-4xl text-green-500 mb-2">qr_code_2</span>
                    <p class="text-slate-400 text-xs uppercase tracking-widest mb-2">LinePay QR Code</p>
                    <div class="w-32 h-32 bg-white mx-auto rounded p-2 flex items-center justify-center">
                        <div class="w-full h-full bg-slate-200 flex items-center justify-center text-slate-400 text-xs">
                            QR Code
                        </div>
                    </div>
                    <p class="text-slate-500 text-xs mt-2">Scan via Line App</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-white px-2" style="font-family: 'Bebas Neue', sans-serif;">History & Status</h3>
        
        @forelse($payments as $payment)
            @php
                $statusColors = [
                    'Paid' => 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20',
                    'Pending Verification' => 'text-amber-500 bg-amber-500/10 border-amber-500/20',
                    'Overdue' => 'text-red-400 bg-red-400/10 border-red-400/20',
                    'Rejected' => 'text-red-400 bg-red-400/10 border-red-400/20',
                ];
                $statusColor = $statusColors[$payment->status] ?? 'text-slate-400';
            @endphp

            <div class="glass rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 flex flex-col gap-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-slate-400 uppercase font-bold">{{ $payment->month }}</p>
                            <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">฿{{ number_format($payment->amount) }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusColor }}">
                            {{ $payment->status }}
                        </span>
                    </div>

                    @if($payment->status !== 'Paid' && $payment->status !== 'Pending Verification')
                        <div class="mt-2 pt-4 border-t border-white/5">
                            <form action="{{ route('payments.upload', $payment->id) }}" method="POST" enctype="multipart/form-data" 
                                  x-data="{ 
                                      fileName: '', 
                                      paymentMethod: 'bank',
                                      paymentDate: '{{ now()->format('Y-m-d') }}',
                                      paymentAmount: '',
                                      accountLast5: ''
                                  }">
                                @csrf
                                
                                <!-- Payment Method Selection -->
                                <div class="mb-4">
                                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Payment Method</label>
                                    <div class="flex space-x-2">
                                        <button type="button" @click="paymentMethod = 'bank'" 
                                                :class="paymentMethod === 'bank' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-800 text-slate-400 border-slate-600'"
                                                class="flex-1 py-2 px-3 text-sm font-bold rounded border transition-colors flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-base">account_balance</span>
                                            Bank Transfer
                                        </button>
                                        <button type="button" @click="paymentMethod = 'linepay'" 
                                                :class="paymentMethod === 'linepay' ? 'bg-green-600 text-white border-green-600' : 'bg-slate-800 text-slate-400 border-slate-600'"
                                                class="flex-1 py-2 px-3 text-sm font-bold rounded border transition-colors flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-base">qr_code_2</span>
                                            LinePay
                                        </button>
                                    </div>
                                    <input type="hidden" name="payment_method" :value="paymentMethod">
                                </div>

                                <!-- Payment Date -->
                                <div class="mb-4">
                                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Payment Date</label>
                                    <input type="date" name="payment_date" x-model="paymentDate" required
                                           class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                                </div>

                                <!-- Payment Amount -->
                                <div class="mb-4">
                                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Amount Paid (NT$)</label>
                                    <input type="number" name="payment_amount" x-model="paymentAmount" required min="1"
                                           placeholder="Enter amount"
                                           class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500">
                                </div>

                                <!-- Last 5 Digits (Bank Transfer Only) -->
                                <div class="mb-4" x-show="paymentMethod === 'bank'" x-transition>
                                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Last 5 Digits of Your Account</label>
                                    <input type="text" name="account_last_5" x-model="accountLast5" maxlength="5" 
                                           :required="paymentMethod === 'bank'"
                                           placeholder="e.g. 12345"
                                           class="w-full bg-slate-800 border border-slate-600 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-blue-500 font-mono tracking-widest text-center">
                                    <p class="text-xs text-slate-500 mt-1">Enter the last 5 digits of your bank account for verification</p>
                                </div>

                                <!-- Upload Proof Image -->
                                <div class="mb-4">
                                    <label class="block text-xs text-slate-400 uppercase tracking-wider mb-2">Payment Proof</label>
                                    <label class="block w-full cursor-pointer">
                                        <input type="file" name="proof_image" accept="image/*" class="hidden" required
                                               @change="fileName = $event.target.files[0]?.name || ''"
                                               x-ref="fileInput">
                                        <div @click="$refs.fileInput.click()" 
                                             class="w-full h-20 border-2 border-dashed border-blue-500 rounded-lg flex flex-col items-center justify-center bg-blue-500/5 hover:bg-blue-500/10 transition-colors">
                                            <span class="material-symbols-outlined text-blue-500">cloud_upload</span>
                                            <span class="text-xs text-blue-500 mt-1 font-bold" x-text="fileName || 'Tap to Upload Screenshot/Slip'"></span>
                                        </div>
                                    </label>
                                </div>

                                <button type="submit" 
                                        class="w-full py-3 bg-slate-100 text-slate-900 font-bold uppercase text-xs tracking-wider rounded hover:bg-white transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!fileName || !paymentDate || !paymentAmount || (paymentMethod === 'bank' && accountLast5.length !== 5)">
                                    <span class="material-symbols-outlined text-sm">upload_file</span>
                                    Submit Payment Proof
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($payment->status === 'Pending Verification')
                        <p class="text-xs text-slate-500 text-center italic">Slip uploaded. Waiting for admin approval.</p>
                    @endif

                    @if($payment->status === 'Rejected')
                        <p class="text-xs text-red-400 text-center">Payment was rejected. Please upload a new proof.</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">receipt_long</span>
                <p>No payment records found.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Alpine.js for interactivity -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
