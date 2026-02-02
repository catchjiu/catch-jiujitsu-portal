<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Display the user's payments.
     */
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('payments', [
            'payments' => $payments,
        ]);
    }

    /**
     * Submit a new payment with proof.
     */
    public function submitPayment(Request $request)
    {
        $rules = [
            'proof_image' => 'required|image|max:2048', // Max 2MB
            'payment_method' => 'required|in:bank,linepay',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
        ];

        // Add account_last_5 validation only for bank transfers
        if ($request->input('payment_method') === 'bank') {
            $rules['account_last_5'] = 'required|string|size:5';
        }

        $request->validate($rules);

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('payment_proofs', 'public');

            Payment::create([
                'user_id' => Auth::id(),
                'amount' => $request->input('payment_amount'),
                'month' => now()->format('F Y'),
                'status' => 'Pending Verification',
                'proof_image_path' => $path,
                'submitted_at' => now(),
                'payment_method' => $request->input('payment_method'),
                'payment_date' => $request->input('payment_date'),
                'account_last_5' => $request->input('payment_method') === 'bank' ? $request->input('account_last_5') : null,
            ]);

            return back()->with('success', 'Payment submitted successfully. Waiting for admin approval.');
        }

        return back()->with('error', 'Failed to submit payment.');
    }

    /**
     * Upload proof of payment.
     */
    public function uploadProof(Request $request, $paymentId)
    {
        $rules = [
            'proof_image' => 'required|image|max:2048', // Max 2MB
            'payment_method' => 'required|in:bank,linepay',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
        ];

        // Add account_last_5 validation only for bank transfers
        if ($request->input('payment_method') === 'bank') {
            $rules['account_last_5'] = 'required|string|size:5';
        }

        $request->validate($rules);

        $payment = Payment::where('user_id', Auth::id())
            ->findOrFail($paymentId);

        if ($request->hasFile('proof_image')) {
            // Delete old proof if exists
            if ($payment->proof_image_path) {
                Storage::disk('public')->delete($payment->proof_image_path);
            }

            $path = $request->file('proof_image')->store('payment_proofs', 'public');

            $payment->update([
                'proof_image_path' => $path,
                'status' => 'Pending Verification',
                'submitted_at' => now(),
                'payment_method' => $request->input('payment_method'),
                'payment_date' => $request->input('payment_date'),
                'amount' => $request->input('payment_amount'),
                'account_last_5' => $request->input('payment_method') === 'bank' ? $request->input('account_last_5') : null,
            ]);

            return back()->with('success', 'Payment proof uploaded successfully. Waiting for admin approval.');
        }

        return back()->with('error', 'Failed to upload payment proof.');
    }
}
