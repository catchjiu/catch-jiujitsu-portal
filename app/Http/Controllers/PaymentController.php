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
     * Upload proof of payment.
     */
    public function uploadProof(Request $request, $paymentId)
    {
        $request->validate([
            'proof_image' => 'required|image|max:2048', // Max 2MB
        ]);

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
            ]);

            return back()->with('success', 'Payment proof uploaded successfully. Waiting for admin approval.');
        }

        return back()->with('error', 'Failed to upload payment proof.');
    }
}
