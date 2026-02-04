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
        $user = User::currentFamilyMember();
        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('payments', [
            'payments' => $payments,
            'viewingUser' => $user,
        ]);
    }

    /**
     * Submit a new payment.
     */
    public function submitPayment(Request $request)
    {
        $rules = [
            'payment_method' => 'required|in:bank,linepay',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
        ];

        // Add account_last_5 validation only for bank transfers
        if ($request->input('payment_method') === 'bank') {
            $rules['account_last_5'] = 'required|string|size:5';
        }

        $request->validate($rules);

        $user = User::currentFamilyMember();

        Payment::create([
            'user_id' => $user->id,
            'amount' => $request->input('payment_amount'),
            'month' => now()->format('F Y'),
            'status' => 'Pending Verification',
            'submitted_at' => now(),
            'payment_method' => $request->input('payment_method'),
            'payment_date' => $request->input('payment_date'),
            'account_last_5' => $request->input('payment_method') === 'bank' ? $request->input('account_last_5') : null,
        ]);

        return back()->with('success', 'Payment submitted successfully. Waiting for admin approval.');
    }

    /**
     * Resubmit payment details (for rejected payments).
     */
    public function uploadProof(Request $request, $paymentId)
    {
        $rules = [
            'payment_method' => 'required|in:bank,linepay',
            'payment_date' => 'required|date',
            'payment_amount' => 'required|numeric|min:1',
        ];

        // Add account_last_5 validation only for bank transfers
        if ($request->input('payment_method') === 'bank') {
            $rules['account_last_5'] = 'required|string|size:5';
        }

        $request->validate($rules);

        $user = User::currentFamilyMember();
        $payment = Payment::where('user_id', $user->id)
            ->findOrFail($paymentId);

        $payment->update([
            'status' => 'Pending Verification',
            'submitted_at' => now(),
            'payment_method' => $request->input('payment_method'),
            'payment_date' => $request->input('payment_date'),
            'amount' => $request->input('payment_amount'),
            'account_last_5' => $request->input('payment_method') === 'bank' ? $request->input('account_last_5') : null,
        ]);

        return back()->with('success', 'Payment resubmitted successfully. Waiting for admin approval.');
    }
}
