<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
   public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong!');
        }

        $total = $this->calculateTotal($cart);
        $user = Auth::user();

        return view('frontend.checkout.index', compact('cart', 'total', 'user'));
    }

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong!');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'order_type' => 'required|in:dine_in,takeaway',
            'table_number' => 'required_if:order_type,dine_in|nullable|string|max:10',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash,midtrans',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $total = $this->calculateTotal($cart);

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => Auth::check() ? Auth::user()->name : $request->customer_name,
                'customer_phone' => Auth::check() ? Auth::user()->no_telp : $request->customer_phone,
                'order_type' => $request->order_type,
                'table_number' => $request->table_number,
                'notes' => $request->notes,
                'subtotal' => $total['subtotal'],
                'tax' => $total['tax'],
                'discount' => 0,
                'total_price' => $total['total'],
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($cart as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Decrease stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            // Create payment (cash = auto paid, midtrans = pending)
            $payment = $order->payments()->create([
                'amount' => $total['total'],
                'payment_method' => $request->payment_method,
                'status' => $request->payment_method === 'cash' ? 'paid' : 'pending',
                'paid_at' => $request->payment_method === 'cash' ? now() : null,
            ]);
             if ($request->payment_method === 'midtrans') {
                $midtrans = new MidtransService();
                $snapToken = $midtrans->createTransaction($order);

                $payment->update([
                    'midtrans_order_id' => $order->order_number,
                    'midtrans_snap_token' => $snapToken,
                ]);
            }

            // Update order status if paid
            if ($payment->status === 'paid') {
                $order->checkPaymentStatus();
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            if ($request->payment_method === 'midtrans') {
    return redirect()->route('checkout.payment', $order);
}

return redirect()->route('checkout.success', $order);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function success(Order $order)
    {
        return view('frontend.checkout.success', compact('order'));
    }

    private function calculateTotal($cart)
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = $subtotal * 0.11;
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
        public function midtransCallback(Request $request)
    {
        logger()->info('=== MIDTRANS CALLBACK START ===');
        logger()->info('Raw Input:', $request->all());

        try {
            // Set Midtrans Config
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');

            // Ambil data dari request
            $orderId = $request->input('order_id');
            $statusCode = $request->input('status_code');
            $grossAmount = $request->input('gross_amount');
            $signatureKey = $request->input('signature_key');

            // Verifikasi Signature (untuk keamanan)
            $serverKey = config('midtrans.server_key');
            $mySignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $mySignature) {
                logger()->warning('Invalid signature', [
                    'received' => $signatureKey,
                    'expected' => $mySignature
                ]);
                // Untuk development, lanjutkan saja (comment baris return ini di production)
                // return response()->json(['status' => 'error', 'message' => 'invalid signature'], 403);
            }

            $transactionStatus = $request->input('transaction_status');
            $transactionId = $request->input('transaction_id');
            $fraudStatus = $request->input('fraud_status');
            $paymentType = $request->input('payment_type');

            if (!$orderId) {
                logger()->error('order_id is missing');
                return response()->json(['status' => 'error', 'message' => 'order_id missing'], 400);
            }

            logger()->info('Processing order: ' . $orderId);

            // Map status ke internal status
            $status = 'pending';
            if ($transactionStatus == 'capture') {
                $status = ($fraudStatus == 'accept') ? 'paid' : 'failed';
            } elseif ($transactionStatus == 'settlement') {
                $status = 'paid';
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $status = 'failed';
            }

            logger()->info('Status mapped to: ' . $status);

            // Find order
            $order = Order::where('order_number', $orderId)->first();

            if (!$order) {
                logger()->error('Order not found: ' . $orderId);
                return response()->json(['status' => 'error', 'message' => 'order not found'], 404);
            }

            logger()->info('Order found:', ['id' => $order->id, 'total' => $order->total_price]);

            // Find payment
            $payment = $order->payments()
                ->where('payment_method', 'midtrans')
                ->first();

            if (!$payment) {
                logger()->error('Payment not found for order: ' . $orderId);
                return response()->json(['status' => 'error', 'message' => 'payment not found'], 404);
            }

            logger()->info('Payment found:', [
                'id' => $payment->id,
                'current_status' => $payment->status,
                'new_status' => $status
            ]);

            // Cek apakah sudah paid sebelumnya (prevent duplicate)
            if ($payment->status === 'paid' && $status === 'paid') {
                logger()->info('Payment already paid, skipping update');
                return response()->json(['status' => 'success', 'message' => 'already paid']);
            }

            // Update payment
            $payment->update([
                'midtrans_transaction_id' => $transactionId,
                'status' => $status,
                'paid_at' => $status === 'paid' ? now() : null,
            ]);

            logger()->info('Payment updated successfully');
            if ($status === 'paid' && $payment->status !== 'paid') {
                logger()->info('Reducing stock for paid order');
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $oldStock = $product->stock;
                            $product->decrement('stock', $item->quantity);
                            logger()->info("Stock reduced: {$item->product_name} from {$oldStock} to {$product->stock}");
                        }
                    }
                }
            }
            // Update order status
            $order->checkPaymentStatus();

            logger()->info('Order status updated to: ' . $order->status);
            logger()->info('=== CALLBACK SUCCESS ===');

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            logger()->error('=== CALLBACK ERROR ===');
            logger()->error('Message: ' . $e->getMessage());
            logger()->error('File: ' . $e->getFile() . ':' . $e->getLine());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function payment(Order $order)
{
    // Pastikan order milik user yang login (jika ada auth)
    if (Auth::check() && $order->user_id !== Auth::id()) {
        abort(403, 'Unauthorized');
    }

    // Cek apakah order sudah dibayar
    if ($order->payments()->where('status', 'paid')->exists()) {
        return redirect()->route('checkout.success', $order)
            ->with('info', 'Pesanan sudah dibayar');
    }

    // Ambil payment dengan snap token
    $payment = $order->payments()
        ->where('payment_method', 'midtrans')
        ->whereNotNull('midtrans_snap_token')
        ->latest()
        ->first();

    if (!$payment || !$payment->midtrans_snap_token) {
        return redirect()->route('checkout.success', $order)
            ->with('error', 'Token pembayaran tidak ditemukan');
    }

    return view('frontend.checkout.payment', compact('order', 'payment'));
}
}
