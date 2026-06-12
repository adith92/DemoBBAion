@extends('layouts.app')

@section('header_title', 'Tambah Opportunity')

@section('content')
<div class="p-4 md:p-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pipeline.index') }}"
           class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-slate-900">Tambah Deal Baru</h1>
            <p class="text-sm text-slate-500">Buat opportunity baru di sales pipeline</p>
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
        action="{{ route('opportunities.store') }}"
        method="POST"
        x-data="{
            selectedProduct: null,
            estimatedValue: '',
            products: [],
            productSearch: '',
            productDropdownOpen: false,
            formatIDR(val) {
                if (!val) return '';
                return 'Rp ' + Number(val).toLocaleString('id-ID');
            },
            async fetchProducts(q) {
                if (q.length < 1) { this.products = []; return; }
                const res = await fetch('/api/products/search?q=' + encodeURIComponent(q));
                this.products = await res.json();
                this.productDropdownOpen = this.products.length > 0;
            },
            selectProduct(p) {
                this.selectedProduct = p;
                this.productSearch = p.name;
                this.estimatedValue = p.base_price;
                this.productDropdownOpen = false;
            }
        }"
        class="cc-card rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5"
    >
        @csrf

        {{-- Title --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Judul Deal <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="title"
                value="{{ old('title') }}"
                placeholder="Contoh: Sewa Kendaraan PT Maju Mundur Q1 2026"
                required
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >
        </div>

        {{-- Client + Stage Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Client <span class="text-red-500">*</span>
                </label>
                <select
                    name="client_id"
                    required
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer cc-card"
                >
                    <option value="">-- Pilih Client --</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Stage</label>
                <select
                    name="stage"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 text-slate-500 cursor-not-allowed"
                    disabled
                >
                    <option value="call_meeting" selected>Call/Meeting</option>
                </select>
                <input type="hidden" name="stage" value="call_meeting">
            </div>
        </div>

        {{-- Product (Alpine.js autocomplete) --}}
        <div class="relative">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Produk (opsional)</label>

            <input
                x-model="productSearch"
                @input.debounce.300ms="fetchProducts($event.target.value)"
                @focus="fetchProducts(productSearch)"
                @click.away="productDropdownOpen = false"
                type="text"
                placeholder="Ketik nama produk untuk mencari..."
                autocomplete="off"
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            >

            {{-- Hidden input for actual product_id --}}
            <input type="hidden" name="product_id" :value="selectedProduct ? selectedProduct.id : ''">

            {{-- Dropdown --}}
            <div
                x-show="productDropdownOpen"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute z-20 left-0 right-0 mt-1 cc-card border border-slate-200 rounded-xl shadow-lg overflow-hidden"
            >
                <template x-for="p in products" :key="p.id">
                    <button
                        type="button"
                        @click="selectProduct(p)"
                        class="w-full text-left px-4 py-3 hover:bg-blue-50 transition-colors cursor-pointer border-b border-slate-50 last:border-0"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-800" x-text="p.name"></p>
                                <p class="text-xs text-slate-400" x-text="p.sku + ' · ' + (p.category || '')"></p>
                            </div>
                            <span class="text-sm font-bold text-blue-700 ml-4" x-text="p.formatted_price"></span>
                        </div>
                    </button>
                </template>
            </div>

            {{-- Selected product info --}}
            <div x-show="selectedProduct" class="mt-2 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
                <div>
                    <p class="text-xs text-blue-700 font-semibold" x-text="selectedProduct?.name"></p>
                    <p class="text-xs text-blue-500" x-text="selectedProduct?.sku"></p>
                </div>
                <button type="button" @click="selectedProduct = null; productSearch = ''; estimatedValue = ''"
                        class="text-xs text-slate-400 hover:text-red-500 cursor-pointer transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Value + Pax Row --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estimasi Nilai (IDR)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                    <input
                        type="number"
                        name="estimated_value"
                        :value="estimatedValue || '{{ old('estimated_value') }}'"
                        @input="estimatedValue = $event.target.value"
                        min="0"
                        step="1000"
                        placeholder="0"
                        class="w-full text-sm border border-slate-200 rounded-lg pl-10 pr-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    >
                </div>
                <p class="text-xs text-slate-400 mt-1"
                   x-show="estimatedValue"
                   x-text="formatIDR(estimatedValue)">
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Pax</label>
                <input
                    type="number"
                    name="pax"
                    value="{{ old('pax') }}"
                    min="1"
                    placeholder="Jumlah pax / unit"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                >
            </div>
        </div>

        {{-- Sales + Expected Close --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @if(!auth()->user()->isSales())
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sales</label>
                <select
                    name="sales_id"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer cc-card"
                >
                    <option value="{{ auth()->id() }}">{{ auth()->user()->name }} (saya)</option>
                    @foreach($salesUsers as $su)
                    @if($su->id !== auth()->id())
                    <option value="{{ $su->id }}" {{ old('sales_id') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Target Close Date</label>
                <input
                    type="date"
                    name="expected_close_date"
                    value="{{ old('expected_close_date') }}"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors cursor-pointer"
                >
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
            <textarea
                name="notes"
                rows="3"
                placeholder="Detail tambahan, konteks, atau informasi penting lainnya..."
                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
            >{{ old('notes') }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route('pipeline.index') }}"
               class="px-5 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                Batal
            </a>
            <button
                type="submit"
                class="px-6 py-2.5 bg-blue-600 text-gray-900 text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 cursor-pointer shadow-sm"
            >
                Buat Deal
            </button>
        </div>
    </form>

</div>
@endsection
