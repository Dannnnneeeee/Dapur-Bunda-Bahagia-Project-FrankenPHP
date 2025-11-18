@extends('layouts.frontend')

@section('title', 'Order Detail - ' . $order->order_number)

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Back Button -->
    <a href="{{ route('orders.index') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900 mb-6">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke My Orders
    </a>

    <!-- Order Header -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 mb-2">{{ $order->order_number }}</h1>
                <p class="text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="px-3 py-1.5 text-sm font-medium rounded-full
                {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $order->status === 'ready' ? 'bg-purple-100 text-purple-800' : '' }}
                {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                {{ ucfirst($order->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-500 mb-1">Customer</p>
                <p class="font-medium text-slate-900">{{ $order->customer_display_name }}</p>
                <p class="text-slate-600">{{ $order->customer_phone }}</p>
            </div>
            <div>
                <p class="text-slate-500 mb-1">Tipe Pesanan</p>
                <p class="font-medium text-slate-900">
                    {{ $order->order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                    @if($order->table_number)
                        - Meja {{ $order->table_number }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Detail Pesanan</h2>
        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex gap-4">
                    <div class="w-16 h-16 flex-shrink-0 rounded-lg bg-slate-100 overflow-hidden">
                        @if($item->product && $item->product->image)
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h3 class="font-medium text-slate-900">{{ $item->product_name }}</h3>
                        <p class="text-sm text-slate-500">{{ $item->quantity }}x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                        @if($item->notes)
                            <p class="text-sm text-slate-400 italic mt-1">Catatan: {{ $item->notes }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pricing -->
        <div class="mt-6 pt-6 border-t border-slate-200 space-y-2">
            <div class="flex justify-between text-sm text-slate-600">
                <span>Subtotal</span>
                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-600">
                <span>Pajak (11%)</span>
                <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
            </div>
            @if($order->discount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>Diskon</span>
                    <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between pt-2 border-t border-slate-200">
                <span class="font-semibold text-slate-900">Total</span>
                <span class="font-bold text-xl text-orange-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($order->notes)
            <div class="mt-4 pt-4 border-t border-slate-200">
                <p class="text-sm text-slate-600">
                    <span class="font-medium">Catatan Pesanan:</span><br>
                    {{ $order->notes }}
                </p>
            </div>
        @endif
    </div>

    <!-- Payment Info -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-900 mb-4">Informasi Pembayaran</h2>

        @if($order->payments->count() > 0)
            <div class="space-y-3">
                @foreach($order->payments as $payment)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                        <div>
                            <p class="font-medium text-slate-900">{{ ucfirst($payment->payment_method) }}</p>
                            <p class="text-sm text-slate-500">{{ $payment->created_at->format('d M Y, H:i') }}</p>
                            @if($payment->paid_at)
                                <p class="text-sm text-green-600">Dibayar: {{ $payment->paid_at->format('d M Y, H:i') }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-slate-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach

                <!-- Summary Payment -->
                <div class="pt-3 border-t border-slate-200">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-slate-600">Total Dibayar</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format($order->total_paid, 0, ',', '.') }}</span>
                    </div>
                    @if($order->remaining_amount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-red-600 font-medium">Kekurangan</span>
                            <span class="font-bold text-red-600">Rp {{ number_format($order->remaining_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-slate-500">Belum ada pembayaran</p>
            </div>
        @endif
    </div>

    <!-- Actions -->
    @if(in_array($order->status, ['pending', 'preparing']))
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="font-semibold text-slate-900 mb-4">Aksi</h3>
            <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                @csrf
                <button type="submit" class="w-full px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition">
                    Batalkan Pesanan
                </button>
            </form>
        </div>
    @endif

    @if($order->status === 'cancelled')
        <div class="bg-red-50 border border-red-200 rounded-xl p-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-red-900 mb-1">Pesanan Dibatalkan</h3>
                    @if($order->cancelled_reason)
                        <p class="text-sm text-red-800">Alasan: {{ $order->cancelled_reason }}</p>
                    @endif
                    <p class="text-sm text-red-700 mt-1">Dibatalkan pada: {{ $order->cancelled_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($order->status === 'completed')
        <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
            <svg class="w-16 h-16 text-green-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-bold text-green-900 mb-1">Pesanan Selesai</h3>
            <p class="text-sm text-green-700">Terima kasih telah memesan di {{ config('app.name') }}!</p>
            <p class="text-sm text-green-600 mt-2">Selesai pada: {{ $order->completed_at->format('d M Y, H:i') }}</p>
        </div>
    @endif
</div>
@if(in_array($order->status, ['pending', 'preparing', 'ready']))
    <script>
        // Auto refresh setiap 30 detik (ONLY jika status belum selesai)
        let refreshInterval = setInterval(() => {
            // Cek status via AJAX
            fetch('{{ route("orders.check-status", $order) }}')
                .then(res => res.json())
                .then(data => {
                    if (data.status !== '{{ $order->status }}') {
                        // Status berubah, reload page
                        window.location.reload();
                    }

                    // Stop polling kalau sudah completed/cancelled
                    if (['completed', 'cancelled'].includes(data.status)) {
                        clearInterval(refreshInterval);
                    }
                });
        }, 30000); // 30 detik (gak berat!)

        // Stop polling saat user leave page
        window.addEventListener('beforeunload', () => {
            clearInterval(refreshInterval);
        });
    </script>
@endif
@endsection
