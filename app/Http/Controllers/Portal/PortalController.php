<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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
                'outlet',
            ])
            ->findOrFail($id);

        return view('portal.order-detail', compact('order'));
    }
}
