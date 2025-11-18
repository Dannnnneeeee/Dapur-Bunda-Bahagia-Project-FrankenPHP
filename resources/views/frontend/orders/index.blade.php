@extends('layouts.frontend')

@section('title', 'My Orders')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-slate-900 mb-8">Pesanan Saya</h1>

    @if($orders->count() > 0)
        <div class="space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900">{{ $order->order_number }}</h3>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'preparing' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $order->status === 'ready' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $order->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                <p class="text-sm text-slate-500 mt-1">
                                    <span class="font-medium">{{ $order->order_type === 'dine_in' ? 'Dine In' : 'Takeaway' }}</span>
                                    @if($order->table_number)
                                        • Meja {{ $order->table_number }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                                <span class="inline-block mt-1 px-2 py-1 text-xs font-medium rounded-full
                                    {{ $order->payment_status_text === 'Paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $order->payment_status_text === 'Partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $order->payment_status_text === 'Unpaid' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $order->payment_status_text }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="p-6 bg-slate-50">
                        <h4 class="text-sm font-semibold text-slate-900 mb-3">Detail Pesanan</h4>
                        <div class="space-y-2">
                            @foreach($order->items as $item)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-3">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name }}" class="w-10 h-10 rounded object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded bg-slate-200 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-slate-400" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-slate-900">{{ $item->product_name }}</p>
                                            <p class="text-slate-500">{{ $item->quantity }}x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                            @if($item->notes)
                                                <p class="text-xs text-slate-400 italic">{{ $item->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="font-medium text-slate-900">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pricing Breakdown -->
                        <div class="mt-4 pt-4 border-t border-slate-200 space-y-1 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Pajak (11%)</span>
                                <span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span>
                            </div>
                            @if($order->discount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Diskon</span>
                                    <span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        @if($order->notes)
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <p class="text-sm text-slate-600"><span class="font-medium">Catatan:</span> {{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="p-6 bg-white flex flex-wrap gap-2">
                        <a href="{{ route('orders.show', $order) }}" class="px-4 py-2 text-sm font-medium text-orange-600 hover:bg-orange-50 rounded-lg transition">
                            Lihat Detail
                        </a>
                        @if(in_array($order->status, ['pending', 'preparing']))
                            <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pesanan?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition">
                                    Batalkan Pesanan
                                </button>
                            </form>
                        @endif
                        @if($order->status === 'completed')
                            <button class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg">
                                Selesai
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <svg class="w-24 h-24 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <h2 class="text-2xl font-bold text-slate-900 mb-2">Belum Ada Pesanan</h2>
            <p class="text-slate-500 mb-6">Kamu belum pernah melakukan pesanan. Yuk mulai pesan menu favorit!</p>
            <a href="{{ route('products.index') }}" class="inline-block px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                Lihat Menu
            </a>
        </div>
    @endif
</div>
@endsection
