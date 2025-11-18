@extends('layouts.frontend')

@section('title', 'Checkout')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Checkout</h1>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Left Column - Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Customer Info -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Informasi Customer</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-slate-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="customer_name"
                                id="customer_name"
                                value="{{ old('customer_name', auth()->user()->name ?? '') }}"
                                required
                                class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500"
                                placeholder="Masukkan nama lengkap">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-slate-700 mb-2">
                                No. Telepon <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="tel"
                                name="customer_phone"
                                id="customer_phone"
                                value="{{ old('customer_phone', auth()->user()->no_telp ?? '') }}"
                                required
                                class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500"
                                placeholder="08xx-xxxx-xxxx">
                            @error('customer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Order Type -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Tipe Pesanan</h2>

                    <div class="grid grid-cols-2 gap-4" x-data="{ orderType: 'dine_in' }">
                        <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition"
                               :class="orderType === 'dine_in' ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="order_type" value="dine_in" x-model="orderType" class="sr-only" checked>
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2" :class="orderType === 'dine_in' ? 'text-orange-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span class="font-medium" :class="orderType === 'dine_in' ? 'text-orange-600' : 'text-slate-700'">Dine In</span>
                            </div>
                        </label>

                        <label class="relative flex items-center justify-center p-4 border-2 rounded-lg cursor-pointer transition"
                               :class="orderType === 'takeaway' ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="order_type" value="takeaway" x-model="orderType" class="sr-only">
                            <div class="text-center">
                                <svg class="w-8 h-8 mx-auto mb-2" :class="orderType === 'takeaway' ? 'text-orange-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span class="font-medium" :class="orderType === 'takeaway' ? 'text-orange-600' : 'text-slate-700'">Takeaway</span>
                            </div>
                        </label>

                        <!-- Table Number (hanya untuk dine-in) -->
                        <div x-show="orderType === 'dine_in'" x-transition class="col-span-2">
                            <label for="table_number" class="block text-sm font-medium text-slate-700 mb-2">
                                Nomor Meja <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="table_number"
                                id="table_number"
                                value="{{ old('table_number') }}"
                                :required="orderType === 'dine_in'"
                                class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500"
                                placeholder="Contoh: A1, B2, C3">
                            @error('table_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Catatan Pesanan</h2>
                    <textarea
                        name="notes"
                        id="notes"
                        rows="3"
                        class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500"
                        placeholder="Tambahkan catatan untuk pesanan Anda (opsional)">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Metode Pembayaran</h2>

                    <div class="space-y-3" x-data="{ payment: 'cash' }">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                               :class="payment === 'cash' ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="payment_method" value="cash" x-model="payment" class="w-4 h-4 text-orange-600 focus:ring-orange-500" checked>
                            <div class="ml-3 flex-1 flex items-center justify-between">
                                <div>
                                    <span class="block font-medium" :class="payment === 'cash' ? 'text-orange-600' : 'text-slate-900'">Cash</span>
                                    <span class="block text-sm text-slate-500">Bayar langsung di kasir</span>
                                </div>
                                <svg class="w-6 h-6" :class="payment === 'cash' ? 'text-orange-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </label>

                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition"
                               :class="payment === 'midtrans' ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="payment_method" value="midtrans" x-model="payment" class="w-4 h-4 text-orange-600 focus:ring-orange-500">
                            <div class="ml-3 flex-1 flex items-center justify-between">
                                <div>
                                    <span class="block font-medium" :class="payment === 'midtrans' ? 'text-orange-600' : 'text-slate-900'">Cashless (Midtrans)</span>
                                    <span class="block text-sm text-slate-500">Transfer, E-wallet, Kartu Kredit</span>
                                </div>
                                <svg class="w-6 h-6" :class="payment === 'midtrans' ? 'text-orange-600' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Right Column - Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sticky top-20">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Ringkasan Pesanan</h2>

                    <!-- Cart Items -->
                    <div class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                        @foreach($cart as $item)
                            <div class="flex gap-3 text-sm">
                                <div class="w-12 h-12 flex-shrink-0 rounded bg-slate-100 overflow-hidden">
                                    @if($item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-900 truncate">{{ $item['name'] }}</p>
                                    <p class="text-slate-500">{{ $item['quantity'] }}x Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                <p class="font-medium text-slate-900">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pricing -->
                    <div class="border-t border-slate-200 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal</span>
                            <span class="font-medium text-slate-900">Rp {{ number_format($total['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Pajak (11%)</span>
                            <span class="font-medium text-slate-900">Rp {{ number_format($total['tax'], 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-2 flex justify-between">
                            <span class="font-semibold text-slate-900">Total</span>
                            <span class="font-bold text-xl text-orange-600">Rp {{ number_format($total['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full mt-6 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                        Buat Pesanan
                    </button>

                    <a href="{{ route('cart.index') }}" class="block w-full mt-3 px-6 py-3 border border-slate-300 hover:bg-slate-50 text-slate-700 text-center font-medium rounded-lg transition">
                        Kembali ke Keranjang
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
