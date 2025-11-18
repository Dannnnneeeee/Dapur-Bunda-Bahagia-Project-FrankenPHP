@extends('layouts.frontend')

@section('title', 'Home')

@section('content')
<!-- Hero -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900">Menu {{ config('app.name') }}</h1>
        <p class="mt-2 text-slate-500">Pilih kategori, cari menu, lalu tambahkan ke keranjang.</p>
    </div>
</div>

        <!-- Search + Categories -->
        <div class="mb-8 flex flex-col lg:flex-row lg:items-center gap-4">
            <!-- Search -->
        <div class="relative flex-1">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
            <input
                x-data
                x-ref="search"
                x-on:input="
                    document.querySelectorAll('[data-card]').forEach(el => {
                        const q = $refs.search.value.toLowerCase();
                        const hay = (el.dataset.name + ' ' + el.dataset.cat + ' ' + el.dataset.desc).toLowerCase();
                        el.style.display = hay.includes(q) ? '' : 'none';
                    });

                "
                type="search"
                placeholder="Cari menu..."
                class="w-full rounded-xl border-slate-300 pl-10 pr-4 py-2.5 text-sm focus:border-orange-500 focus:ring-orange-500"
            />
        </div>

    <!-- Category Pills -->
    @if($categories->count() > 0)
        <div class="flex flex-wrap items-center gap-2">
            @foreach($categories as $c)
                <a href="{{ route('products.index', ['category' => $c->id]) }}"
                   class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium hover:bg-orange-50 hover:border-orange-200 hover:text-orange-700 transition">
                    {{ $c->name }}
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Products by Category -->
<div class="space-y-12">
    @php
        $totalProducts = $categories->sum(fn($c) => $c->products->where('is_available', true)->where('stock', '>', 0)->count());
    @endphp

    @if($totalProducts === 0)
        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">
            Belum ada produk tersedia.
        </div>
    @endif

    @foreach($categories as $cat)
        @php
            $products = $cat->products->where('is_available', true)->where('stock', '>', 0);
        @endphp

        @if($products->count() > 0)
             <section data-category-section="{{ $cat->id }}">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-slate-900">{{ $cat->name }}</h2>
                    <a href="{{ route('products.index', ['category' => $cat->id]) }}" class="text-sm text-orange-600 hover:text-orange-700 font-medium">
                        Lihat semua →
                    </a>
                </div>

                <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
                    @foreach($products->take(6) as $p)
                        <div
                            data-card
                            data-name="{{ $p->name }}"
                            data-cat="{{ $cat->name }}"
                            data-desc="{{ $p->description ?? '' }}"
                            class="group bg-white rounded-xl border border-slate-200 shadow-[0_2px_8px_rgba(0,0,0,0.04)] hover:shadow-[0_4px_16px_rgba(0,0,0,0.08)] hover:border-orange-200 hover:-translate-y-1 overflow-hidden transition-all duration-200">

                            <!-- Image -->
                            <a href="{{ route('products.show', $p->slug) }}" class="block aspect-square bg-slate-50 overflow-hidden">
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
            </section>
        @endif
    @endforeach
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // Check empty sections after search
    function checkEmptySections() {
        document.querySelectorAll('[data-category-section]').forEach(section => {
            const visibleCards = section.querySelectorAll('[data-card]:not([style*="display: none"])');

            if (visibleCards.length === 0) {
                section.style.display = 'none';
            } else {
                section.style.display = '';
            }
        });
    }

    // Run after search input
    const searchInput = document.querySelector('[x-ref="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            setTimeout(checkEmptySections, 10); // Small delay after Alpine processes
        });
    }
});
</script>
@endpush
