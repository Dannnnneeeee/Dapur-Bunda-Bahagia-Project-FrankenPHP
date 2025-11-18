<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        // Gunakan full namespace untuk memastikan config ter-load
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token untuk payment
     */
    public function createTransaction($order)
    {
        // Re-set config untuk memastikan
        $this->setConfig();

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_price,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name ?? $order->user->name ?? 'Guest',
                'email' => $order->user->email ?? 'guest@example.com',
                'phone' => $order->customer_phone ?? $order->user->no_telp ?? '',
            ],
            'item_details' => $this->getItemDetails($order),
            'callbacks' => [
                'finish' => route('checkout.success', $order),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Midtrans transaction: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus($orderId)
    {
        // Re-set config untuk memastikan
        $this->setConfig();

        try {
            return Transaction::status($orderId);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get transaction status: ' . $e->getMessage());
        }
    }

    /**
     * Handle notification dari Midtrans
     */
    public function handleNotification($notification)
    {
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status ?? null;

        $status = 'pending';

        if ($transactionStatus == 'capture') {
            $status = ($fraudStatus == 'accept') ? 'paid' : 'failed';
        } elseif ($transactionStatus == 'settlement') {
            $status = 'paid';
        } elseif ($transactionStatus == 'pending') {
            $status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $status = 'failed';
        }

        return (object) [
            'order_id' => $notification->order_id,
            'transaction_id' => $notification->transaction_id,
            'transaction_status' => $transactionStatus,
            'payment_type' => $notification->payment_type ?? null,
            'status' => $status,
            'raw_response' => json_encode($notification),
        ];
    }

    /**
     * Format item details untuk Midtrans
     */
    private function getItemDetails($order)
    {
        $items = [];

        // Add order items
        foreach ($order->items as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product_name,
            ];
        }

        // Add tax
        if ($order->tax > 0) {
            $items[] = [
                'id' => 'tax',
                'price' => (int) $order->tax,
                'quantity' => 1,
                'name' => 'Pajak (11%)',
            ];
        }

        // Add discount (negative)
        if ($order->discount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -(int) $order->discount,
                'quantity' => 1,
                'name' => 'Diskon',
            ];
        }

        return $items;
    }

    /**
     * Helper method untuk set Midtrans config
     */
    private function setConfig()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }
}
