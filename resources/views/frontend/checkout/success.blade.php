@extends('layouts.frontend')

@section('title', 'Pesanan Berhasil')

@section('content')
<div class="max-w-2xl mx-auto text-center">
    <!-- Success Icon -->
    <div class="mb-8">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 mb-2">Pesanan Berhasil Dibuat!</h1>
        <p class="text-slate-600">Terima kasih telah memesan di {{ config('app.name') }}</p>
    </div>

    <!-- Order Info Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="mb-6">
            <p class="text-sm text-slate-500 mb-1">Nomor Pesanan</p>
            <p class="text-2xl font-bold text-slate-900">{{ $order->order_number }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4 py-4 border-y border-slate-200 text-sm">
            <div>
                <p class="text-slate-500 mb-1">Tipe Pesanan</p>
                <p class="font-medium text-slate-900">
                    {{ $order->order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}
                    @if($order->table_number)
                        - Meja {{ $order->table_number }}
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-500 mb-1">Total Pembayaran</p>
                <p class="text-xl font-bold text-orange-600">
                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="mt-6">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-800 rounded-lg text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Status: <strong>{{ ucfirst($order->status) }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Payment Info -->
    @if($order->payments->count() > 0)
        @php $payment = $order->payments->first(); @endphp

        <div class="bg-slate-50 rounded-xl border border-slate-200 p-6 mb-6">
            <h3 class="font-semibold text-slate-900 mb-3">Informasi Pembayaran</h3>
            <div class="text-sm space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-600">Metode</span>
                    <span class="font-medium text-slate-900">{{ ucfirst($payment->payment_method) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Status</span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                        {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>

            @if($payment->payment_method === 'cash')
                <div class="mt-4 p-3 bg-white rounded-lg border border-slate-200">
                    <p class="text-sm text-slate-700">
                        💰 Silakan lakukan pembayaran di kasir dengan menunjukkan nomor pesanan di atas.
                    </p>
                </div>
            @endif

           @if($payment->payment_method === 'midtrans' && $payment->status === 'pending')
    <div class="mt-4 p-3 bg-orange-50 rounded-lg border border-orange-200">
        <div class="flex items-start gap-3 mb-3">
            <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-orange-900">Menunggu Pembayaran</p>
                <p class="text-sm text-orange-700 mt-1">
                    Pesanan Anda berhasil dibuat. Silakan selesaikan pembayaran untuk melanjutkan proses pesanan.
                </p>
            </div>
        </div>
        <a href="{{ route('checkout.payment', $order) }}" class="block w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-center rounded-lg transition">
            Lanjutkan Pembayaran
        </a>
        <p class="text-xs text-orange-600 text-center mt-2">
            Token pembayaran berlaku selama 24 jam
        </p>
    </div>
@endif
        </div>
    @endif

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-3">
        @auth
            <a href="{{ route('orders.show', $order) }}" class="flex-1 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                Lihat Detail Pesanan
            </a>
        @endauth
        <a href="{{ route('products.index') }}" class="flex-1 px-6 py-3 border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg transition">
            Pesan Lagi
        </a>
    </div>

    @guest
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800">
                💡 <strong>Tips:</strong> Daftar akun untuk melacak pesanan dan mendapatkan promo spesial!
            </p>
            <a href="{{ route('register') }}" class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                Daftar Sekarang →
            </a>
        </div>
    @endguest
</div>
@endsection
