@extends('layouts.app')

@section('header_title', 'Daftar Produk')

@section('content')
<div
    x-data="{ searchTerm: '', activeTab: '{{ request('type', '') }}' }"
    class="p-4 md:p-6 space-y-5"
>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--cc-text)">Produk</h1>
            <p class="text-sm mt-0.5" style="color:var(--cc-text-muted)">Katalog produk dan layanan Golden Bird</p>
        </div>
        @if(auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isDirector())
        <a href="{{ route('products.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 cursor-pointer shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
        </a>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->has('delete'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        {{ $errors->first('delete') }}
    </div>
    @endif

    {{-- Search + Filter Bar --}}
    <div class="glass-panel cc-card rounded-2xl shadow-sm p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">

            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama produk atau SKU..."
                    class="w-full pl-9 pr-4 py-2 text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-black/20 border border-white/10 text-white placeholder-slate-400"
                >
            </div>

            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 cursor-pointer transition-colors">
                Cari
            </button>
            @if(request('q') || request('type'))
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 bg-slate-700/50 border border-white/10 text-white text-sm rounded-lg hover:bg-slate-700 cursor-pointer transition-colors text-center">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Category Filter Tabs --}}
    <div class="flex gap-1 overflow-x-auto pb-1">
        @php
        $tabs = [
            ''            => 'Semua',
            'short_term'  => 'Short Term',
            'long_term'   => 'Long Term',
            'evoucher'    => 'E-Voucher',
        ];
        @endphp
        @foreach($tabs as $val => $label)
        <a href="{{ route('products.index', array_merge(request()->except('type','page'), $val ? ['type' => $val] : [])) }}"
           class="flex-shrink-0 px-4 py-1.5 text-sm font-medium rounded-full transition-colors cursor-pointer {{ request('type', '') === $val ? 'bg-blue-600 text-white shadow-sm' : 'cc-card border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="glass-panel cc-card rounded-2xl shadow-sm overflow-hidden border border-white/10">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-black/20 border-b border-white/10">
                        <th class="text-left px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:var(--cc-text-muted)">SKU</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:var(--cc-text-muted)">Nama Produk</th>
                        <th class="text-left px-5 py-3.5 text-xs font-semibold uppercase tracking-wide hidden md:table-cell" style="color:var(--cc-text-muted)">Kategori</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:var(--cc-text-muted)">Harga Dasar</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold uppercase tracking-wide hidden sm:table-cell" style="color:var(--cc-text-muted)">Unit</th>
                        <th class="text-center px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:var(--cc-text-muted)">Status</th>
                        <th class="text-right px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:var(--cc-text-muted)">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($products as $product)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-xs px-2 py-0.5 rounded" style="background:var(--cc-sidebar);color:var(--cc-text)">
                                {{ $product->sku }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div>
                                <a href="{{ route('products.show', $product->id) }}"
                                   class="font-semibold hover:text-blue-400 cursor-pointer transition-colors" style="color:var(--cc-text)">
                                    {{ $product->name }}
                                </a>
                                @if($product->description)
                                <p class="text-xs mt-0.5 line-clamp-1" style="color:var(--cc-text-muted)">{{ $product->description }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            @if($product->category)
                            @php
                            $typeBadge = [
                                'short_term' => 'bg-blue-100 text-blue-700',
                                'long_term'  => 'bg-purple-100 text-purple-700',
                                'evoucher'   => 'bg-emerald-100 text-emerald-700',
                            ];
                            $typeLabel = [
                                'short_term' => 'Short Term',
                                'long_term'  => 'Long Term',
                                'evoucher'   => 'E-Voucher',
                            ];
                            @endphp
                            <div>
                                <p class="text-sm text-slate-700">{{ $product->category->name }}</p>
                                <span class="text-xs px-1.5 py-0.5 rounded-full font-medium {{ $typeBadge[$product->category->type] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $typeLabel[$product->category->type] ?? $product->category->type }}
                                </span>
                            </div>
                            @else
                            <span class="text-xs text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="text-sm font-bold text-emerald-400 font-mono">
                                Rp {{ number_format((float)$product->base_price, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center hidden sm:table-cell">
                            <span class="text-xs px-2 py-0.5 rounded font-medium" style="background:var(--cc-sidebar);color:var(--cc-text-muted)">
                                {{ $product->unit }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @if($product->is_active)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('products.show', $product->id) }}"
                                   class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors"
                                   title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isGM() || auth()->user()->isManager() || auth()->user()->isDirector())
                                <a href="{{ route('products.edit', $product->id) }}"
                                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg cursor-pointer transition-colors"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg cursor-pointer transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-sm text-slate-400 font-medium">Tidak ada produk ditemukan</p>
                            @if(request('q') || request('type'))
                            <p class="text-xs text-slate-400 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
