<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentProof;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    public function index()
    {
        $customer = auth('customer')->user();

        $customer->loadCount('orders');
        $customer->loadSum('orders', 'total_amount');

        $recentOrders = Order::where('customer_id', $customer->id)
            ->with(['orderItems.product', 'payments'])
            ->latest()
            ->take(10)
            ->get();

        return view('portal.index', compact('customer', 'recentOrders'));
    }

    public function lookup(Request $request)
    {
        $customer = auth('customer')->user();

        $request->validate([
            'order_number' => 'nullable|string',
        ]);

        $query = Order::where('customer_id', $customer->id)
            ->with(['orderItems.product', 'payments'])
            ->latest();

        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', "%{$request->order_number}%");
        }

        $orders = $query->take(20)->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Tidak ada pesanan ditemukan.')->withInput();
        }

        return view('portal.orders', compact('customer', 'orders'));
    }

    public function orderDetail($id)
    {
        $customer = auth('customer')->user();

        $order = Order::where('customer_id', $customer->id)
            ->with([
                'orderItems.product',
                'orderItems.productVariant',
                'payments.paymentMethod',
                'paymentProofs',
                'outlet',
            ])
            ->findOrFail($id);

        return view('portal.order-detail', compact('order'));
    }

    public function downloadInvoice($id)
    {
        $customer = auth('customer')->user();

        $order = Order::where('customer_id', $customer->id)
            ->with(['orderItems.product', 'payments.paymentMethod', 'outlet', 'customer'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4');

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }

    public function uploadProof(Request $request, $id)
    {
        $customer = auth('customer')->user();

        $order = Order::where('customer_id', $customer->id)->findOrFail($id);

        $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $file = $request->file('proof_file');
        $path = $file->store('payment-proofs/' . $customer->id, 'public');

        PaymentProof::create([
            'order_id' => $order->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'amount' => $request->amount,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Admin akan memverifikasi dalam 1x24 jam.');
    }
}
