@extends('layouts.app')

@section('header_title', 'Tambah Produk')

@section('content')
<div class="p-4 md:p-6 max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('products.index') }}"
           class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Tambah Produk Baru</h1>
            <p class="text-sm text-slate-500">Tambahkan produk ke katalog Golden Bird</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form
        action="{{ route('products.store') }}"
        method="POST"
        x-data="{
            name: '{{ old('name') }}',
            sku: '{{ old('sku') }}',
            autoSku: true,
            generateSku(name) {
                if (!this.autoSku) return;
                // Generate SKU: GB-FIRSTWORD-XXX
                const words = name.toUpperCase().replace(/[^A-Z0-9 ]/g, '').trim().split(/\s+/);
                const prefix = words.slice(0, 2).map(w => w.substring(0, 4)).join('-');
                this.sku = 'GB-' + prefix + '-' + Math.floor(100 + Math.random() * 900);
            },
            basePriceRaw: '{{ old('base_price', '') }}',
            get formattedPrice() {
                if (!this.basePriceRaw) return '';
                return 'Rp ' + Number(this.basePriceRaw).toLocaleString('id-ID');
            },
            isActive: {{ old('is_active', '1') == '1' ? 'true' : 'false' }},
        }"
        class="cc-card rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5"
    >
        @csrf

        {{-- Category --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Kategori <span class="text-red-500">*</span>
            </label>
            <select
                name="product_category_id"
                required
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer cc-card"
            >
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $cat)
                @php
                $typeLabel = ['short_term' => 'Short Term', 'long_term' => 'Long Term', 'evoucher' => 'E-Voucher'];
                @endphp
                <option value="{{ $cat->id }}" {{ old('product_category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }} ({{ $typeLabel[$cat->type] ?? $cat->type }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Nama Produk <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="name"
                x-model="name"
                @input="generateSku(name)"
                value="{{ old('name') }}"
                required
                placeholder="Contoh: Sewa Bus Pariwisata 40 Pax"
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
        </div>

        {{-- SKU --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="text-sm font-semibold text-slate-700">
                    SKU <span class="text-red-500">*</span>
                </label>
                <label class="flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
                    <input type="checkbox" x-model="autoSku" @change="if(autoSku) generateSku(name)"
                           class="rounded border-slate-300 text-blue-600">
                    Auto-generate
                </label>
            </div>
            <input
                type="text"
                name="sku"
                x-model="sku"
                @focus="autoSku = false"
                required
                placeholder="GB-PRODUK-001"
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono"
            >
            <p class="text-xs text-slate-400 mt-1">SKU harus unik. Format saran: GB-NAMA-XXX</p>
        </div>

        {{-- Base Price + Unit --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Harga Dasar <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                    <input
                        type="number"
                        name="base_price"
                        x-model="basePriceRaw"
                        min="0"
                        step="1000"
                        required
                        placeholder="0"
                        class="w-full text-sm border border-slate-200 rounded-lg pl-10 pr-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                </div>
                <p class="text-xs text-slate-400 mt-1" x-show="basePriceRaw" x-text="formattedPrice"></p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Unit <span class="text-red-500">*</span>
                </label>
                <select
                    name="unit"
                    required
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer cc-card"
                >
                    <option value="pax" {{ old('unit','pax') === 'pax' ? 'selected' : '' }}>Pax (per orang)</option>
                    <option value="unit" {{ old('unit') === 'unit' ? 'selected' : '' }}>Unit (per kendaraan)</option>
                    <option value="trip" {{ old('unit') === 'trip' ? 'selected' : '' }}>Trip (per perjalanan)</option>
                </select>
            </div>
        </div>

        {{-- Min/Max Pax + Duration --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Min. Pax</label>
                <input
                    type="number"
                    name="min_pax"
                    value="{{ old('min_pax', 1) }}"
                    min="1"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Max. Pax</label>
                <input
                    type="number"
                    name="max_pax"
                    value="{{ old('max_pax') }}"
                    min="1"
                    placeholder="Tidak terbatas"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi (hari)</label>
                <input
                    type="number"
                    name="duration_days"
                    value="{{ old('duration_days') }}"
                    min="1"
                    placeholder="—"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
            </div>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
            <textarea
                name="description"
                rows="3"
                placeholder="Deskripsi produk, fitur unggulan, atau syarat dan ketentuan..."
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
            >{{ old('description') }}</textarea>
        </div>

        {{-- Active Toggle --}}
        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
            <div>
                <p class="text-sm font-semibold text-slate-700">Status Produk</p>
                <p class="text-xs text-slate-400 mt-0.5">Produk aktif akan muncul di katalog dan bisa dipilih saat membuat opportunity</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    x-model="isActive"
                    class="sr-only peer"
                    {{ old('is_active', true) ? 'checked' : '' }}
                >
                <div class="w-11 h-6 bg-slate-200 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:cc-card after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                <span class="ml-3 text-sm font-medium" :class="isActive ? 'text-emerald-600' : 'text-slate-400'" x-text="isActive ? 'Aktif' : 'Nonaktif'"></span>
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route('products.index') }}"
               class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                Batal
            </a>
            <button
                type="submit"
                class="px-6 py-2.5 bg-blue-600 text-gray-900 text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 cursor-pointer shadow-sm"
            >
                Simpan Produk
            </button>
        </div>
    </form>

</div>
@endsection
