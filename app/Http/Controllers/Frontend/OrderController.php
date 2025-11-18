<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
      protected function getAuthUser(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(401, 'Unauthenticated');
        }

        return $user;
    }

    public function index()
    {
        $orders = $this->getAuthUser()
            ->orders()
            ->with(['items.product', 'payments'])
            ->latest()
            ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Check authorization
        if ($order->user_id !== Auth::id()) { // ✅
            abort(403, 'Unauthorized');
        }

        $order->load(['items.product', 'payments']);

        return view('frontend.orders.show', compact('order'));
    }
        public function checkStatus(Order $order)
    {
        // Check authorization
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status_text,
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!in_array($order->status, ['pending', 'preparing'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan!');
        }

        DB::beginTransaction();

        try {
            // ✅ RESTORE STOCK untuk setiap item
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);

                    if ($product) {
                        // Kembalikan stock
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            // Cancel order
            $order->cancel('Dibatalkan oleh customer');

            DB::commit();

            return back()->with('success', 'Order berhasil dibatalkan dan stock dikembalikan!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membatalkan order: ' . $e->getMessage());
        }
    }
}
