@extends('layouts.frontend')

@section('title', 'Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <h1 class="text-2xl font-bold text-slate-900 mb-4">Pembayaran</h1>

        <div class="mb-6 p-4 bg-orange-50 rounded-lg border border-orange-200">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="font-medium text-orange-900">Selesaikan Pembayaran</p>
                    <p class="text-sm text-orange-700 mt-1">
                        Klik tombol di bawah untuk melakukan pembayaran melalui Midtrans
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-3 mb-6">
            <div class="flex justify-between text-sm">
                <span class="text-slate-600">Nomor Pesanan</span>
                <span class="font-medium text-slate-900">{{ $order->order_number }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-600">Total Pembayaran</span>
                <span class="font-bold text-xl text-orange-600">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        <button id="pay-button" class="w-full px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
            Bayar Sekarang
        </button>

        <div class="mt-4 text-center">
            <a href="{{ route('checkout.success', $order) }}" class="text-sm text-slate-500 hover:text-slate-700">
                Saya akan bayar nanti
            </a>
        </div>
    </div>

    <!-- Powered by Midtrans -->
    <div class="text-center text-sm text-slate-400">
        <p>Powered by</p>
        <img src="https://midtrans.com/assets/images/midtrans-logo.svg" alt="Midtrans" class="h-6 mx-auto mt-2 opacity-50">
    </div>
</div>

<!-- Midtrans Snap JS -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    const payButton = document.getElementById('pay-button');

    payButton.addEventListener('click', function () {
        snap.pay('{{ $payment->midtrans_snap_token }}', {
            onSuccess: function(result) {
                console.log('success', result);
                window.location.href = "{{ route('checkout.success', $order) }}";
            },
            onPending: function(result) {
                console.log('pending', result);
                window.location.href = "{{ route('checkout.success', $order) }}";
            },
            onError: function(result) {
                console.log('error', result);
                alert('Pembayaran gagal! Silakan coba lagi.');
            },
            onClose: function() {
                console.log('customer closed the popup without finishing the payment');
            }
        });
    });
</script>
@endsection
