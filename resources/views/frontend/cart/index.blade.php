@extends('layouts.frontend')

@section('title', 'Keranjang')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Keranjang Belanja</h1>
        @if(count($cart) > 0)
            <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Kosongkan keranjang?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                    Kosongkan Keranjang
                </button>
            </form>
        @endif
    </div>

    @if(count($cart) > 0)
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cart as $id => $item)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                        <div class="flex gap-4">
                            <!-- Image -->
                            <div class="w-20 h-20 flex-shrink-0 rounded-lg bg-slate-100 overflow-hidden">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-slate-900">{{ $item['name'] }}</h3>
                                <p class="text-sm text-slate-500 mt-1">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                @if($item['notes'])
                                    <p class="text-xs text-slate-400 mt-1">Catatan: {{ $item['notes'] }}</p>
                                @endif

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-3 mt-3">
                                    <form action="{{ route('cart.update', $id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-300 hover:bg-slate-50 transition">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <span class="text-sm font-semibold text-slate-900 w-8 text-center">{{ $item['quantity'] }}</span>

                                    <form action="{{ route('cart.update', $id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $item['quantity'] + 1 }}">
                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-slate-300 hover:bg-slate-50 transition">
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <form action="{{ route('cart.remove', $id) }}" method="POST" class="ml-auto">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div class="text-right">
                                <p class="font-bold text-slate-900">
                                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 sticky top-20">
                    <h2 class="text-lg font-bold text-slate-900 mb-4">Ringkasan Belanja</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Subtotal ({{ count($cart) }} item)</span>
                            <span class="font-medium text-slate-900">Rp {{ number_format($total['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Pajak (11%)</span>
                            <span class="font-medium text-slate-900">Rp {{ number_format($total['tax'], 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-200 pt-3 flex justify-between">
                            <span class="font-semibold text-slate-900">Total</span>
                            <span class="font-bold text-xl text-orange-600">Rp {{ number_format($total['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="block w-full mt-6 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white text-center font-semibold rounded-lg transition">
                        Checkout
                    </a>

                    <a href="{{ route('products.index') }}" class="block w-full mt-3 px-6 py-3 border border-slate-300 hover:bg-slate-50 text-slate-700 text-center font-medium rounded-lg transition">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <svg class="w-24 h-24 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Keranjang Kosong</h2>
            <p class="text-slate-500 mb-6">Belum ada produk di keranjang. Yuk mulai belanja!</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                Lihat Menu
            </a>
        </div>
    @endif
</div>
@endsection
