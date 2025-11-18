@extends('layouts.frontend')

@section('title', 'Menu')

@section('content')
<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">Semua Menu</h1>
        <p class="mt-1 text-sm text-slate-500">
            Menampilkan {{ $products->total() }} produk
        </p>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="mb-6 flex flex-col sm:flex-row gap-3">
    <!-- Search -->
    <div class="relative flex-1">
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
        </svg>
        <form action="{{ route('products.index') }}" method="GET">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari menu..."
                class="w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
            />
        </form>
    </div>

    <!-- Category Filter -->
    <div class="sm:w-48">
        <form action="{{ route('products.index') }}" method="GET">
            <select
                name="category"
                onchange="this.form.submit()"
                class="w-full rounded-xl border-slate-300 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Sort -->
    <div class="sm:w-48">
        <form action="{{ route('products.index') }}" method="GET">
            <input type="hidden" name="category" value="{{ request('category') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <select
                name="sort"
                onchange="this.form.submit()"
                class="w-full rounded-xl border-slate-300 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500">
                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
            </select>
        </form>
    </div>
</div>

<!-- Products Grid -->
@if($products->count() > 0)
    <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        @foreach($products as $p)
            <div class="group bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md hover:border-orange-200 hover:-translate-y-1 overflow-hidden transition-all duration-200">

                <!-- Image -->
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

                    @if($p->discount_price)
                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            -{{ $p->discount_percentage }}%
                        </div>
                    @endif
                </a>

                <!-- Info -->
                <div class="p-3">
                    <a href="{{ route('products.show', $p->slug) }}">
                        <h3 class="text-sm font-medium text-slate-900 group-hover:text-orange-600 transition line-clamp-2 min-h-[2.5rem]">
                            {{ $p->name }}
                        </h3>
                    </a>

                    <!-- Description (1 line, optional) -->
                    @if($p->description)
                        <p class="mt-1 text-xs text-slate-500 line-clamp-1">
                            {{ $p->description }}
                        </p>
                    @endif

                    <!-- Price -->
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

                    <!-- Stock Indicator -->
                    <div class="mt-1.5 flex items-center text-xs">
                        @if($p->stock > 10)
                            <span class="flex items-center text-green-600">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Stok tersedia
                            </span>
                        @elseif($p->stock > 0)
                            <span class="flex items-center text-yellow-600">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Tersisa {{ $p->stock }}
                            </span>
                        @else
                            <span class="flex items-center text-red-600">
                                <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Habis
                            </span>
                        @endif
                    </div>

                    <!-- Button Tambah -->
                    @if($p->stock > 0)
                        <form action="{{ route('cart.add', $p) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="w-full mt-2 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg transition">
                                Tambah
                            </button>
                        </form>
                    @else
                        <button disabled class="w-full mt-2 px-3 py-1.5 bg-slate-200 text-slate-500 text-sm font-medium rounded-lg cursor-not-allowed">
                            Habis
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@else
    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-slate-900 mb-1">Produk tidak ditemukan</h3>
        <p class="text-slate-500 mb-4">Coba ubah filter atau kata kunci pencarian</p>
        <a href="{{ route('products.index') }}" class="inline-block px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
            Reset Filter
        </a>
    </div>
@endif
@endsection
