<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentProofController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $customer = auth('customer')->user();

        $order = Order::where('customer_id', $customer->id)->findOrFail($orderId);

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $path = $request->file('payment_proof')->store(
            'payment-proofs/' . $order->order_number,
            'public'
        );

        $order->payment_proofs()->create([
            'file_path' => $path,
            'original_name' => $request->file('payment_proof')->getClientOriginalName(),
            'file_size' => $request->file('payment_proof')->getSize(),
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Admin akan segera memverifikasi.');
    }
}
