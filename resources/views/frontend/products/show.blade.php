@extends('layouts.frontend')

@section('title', $product->name)

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-sm text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-orange-600">Home</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('products.index') }}" class="hover:text-orange-600">Menu</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:text-orange-600">{{ $product->category->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-900 font-medium">{{ $product->name }}</span>
    </nav>

    <!-- Product Detail -->
    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <!-- Image -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="aspect-square bg-slate-50 flex items-center justify-center">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-32 h-32 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                @endif
            </div>

            @if($product->images && count($product->images) > 0)
                <!-- Gallery (jika ada multiple images) -->
                <div class="grid grid-cols-4 gap-2 p-4">
                    @foreach($product->images as $img)
                        <button class="aspect-square rounded-lg border border-slate-200 overflow-hidden hover:border-orange-500 transition">
                            <img src="{{ asset('storage/' . $img) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Info -->
        <div class="flex flex-col">
            <!-- Category Badge -->
            <div class="mb-3">
                <span class="inline-block px-3 py-1 text-sm font-medium text-orange-600 bg-orange-50 rounded-full">
                    {{ $product->category->name }}
                </span>
            </div>

            <!-- Title -->
            <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-3">
                {{ $product->name }}
            </h1>

            <!-- Price -->
            <div class="mb-6">
                @if($product->discount_price)
                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-bold text-orange-600">
                            Rp {{ number_format($product->discount_price, 0, ',', '.') }}
                        </span>
                        <span class="text-xl text-slate-400 line-through">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                        <span class="px-2 py-1 text-sm font-bold text-white bg-red-500 rounded">
                            -{{ $product->discount_percentage }}%
                        </span>
                    </div>
                @else
                    <span class="text-3xl font-bold text-slate-900">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                @endif
            </div>

            <!-- Stock Status -->
            <div class="mb-6 flex items-center text-sm">
                @if($product->stock > 10)
                    <span class="flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Stok tersedia
                    </span>
                @elseif($product->stock > 0)
                    <span class="flex items-center text-yellow-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Tersisa {{ $product->stock }} item
                    </span>
                @else
                    <span class="flex items-center text-red-600">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        Stok habis
                    </span>
                @endif
            </div>

            <!-- Description -->
            @if($product->description)
                <div class="mb-6 pb-6 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-900 mb-2">Deskripsi</h3>
                    <p class="text-slate-600 leading-relaxed">{{ $product->description }}</p>
                </div>
            @endif

            <!-- Add to Cart Form -->
            @if($product->stock > 0)
                <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-auto">
                    @csrf

                    <!-- Quantity -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-slate-900 mb-3">Jumlah</label>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                                <button type="button" onclick="decrementQty()" class="px-4 py-3 hover:bg-slate-50 transition">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-16 text-center border-0 focus:ring-0 font-semibold text-slate-900" readonly>
                                <button type="button" onclick="incrementQty()" class="px-4 py-3 hover:bg-slate-50 transition">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>
                            <span class="text-sm text-slate-500">Stok: {{ $product->stock }}</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label for="notes" class="block text-sm font-semibold text-slate-900 mb-2">Catatan (opsional)</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Contoh: Less sugar, extra ice..." class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500 text-sm"></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition">
                            Tambah ke Keranjang
                        </button>
                        <a href="{{ route('products.index') }}" class="px-6 py-3 border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg transition">
                            Kembali
                        </a>
                    </div>
                </form>

                <script>
                    function incrementQty() {
                        const input = document.getElementById('quantity');
                        const max = parseInt(input.max);
                        if (parseInt(input.value) < max) {
                            input.value = parseInt(input.value) + 1;
                        }
                    }

                    function decrementQty() {
                        const input = document.getElementById('quantity');
                        if (parseInt(input.value) > 1) {
                            input.value = parseInt(input.value) - 1;
                        }
                    }
                </script>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <p class="text-red-800 font-medium">Produk ini sedang tidak tersedia</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-3 px-6 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg transition">
                        Lihat Menu Lainnya
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="border-t border-slate-200 pt-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Produk Terkait</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($relatedProducts as $p)
                    <div class="group bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-orange-200 hover:-translate-y-1 overflow-hidden transition-all duration-200">
                        <a href="{{ route('products.show', $p->slug) }}" class="block aspect-square bg-slate-50 overflow-hidden relative">
                            @if($p->image)
                                <img src="{{ asset('storage/' . $p->image) }}" alt="{{ $p->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                </div>
                            @endif
                        </a>

                        <div class="p-3">
                            <a href="{{ route('products.show', $p->slug) }}">
                                <h3 class="text-sm font-medium text-slate-900 group-hover:text-orange-600 transition line-clamp-2 min-h-[2.5rem]">
                                    {{ $p->name }}
                                </h3>
                            </a>

                            <div class="mt-2">
                                @if($p->discount_price)
                                    <p class="text-base font-bold text-orange-600">
                                        Rp {{ number_format($p->discount_price, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-slate-400 line-through">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </p>
                                @else
                                    <p class="text-base font-bold text-slate-900">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
