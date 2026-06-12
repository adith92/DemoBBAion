@extends('layouts.app')

@section('header_title', 'Tambah Kontrak Berlangganan')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('subscriptions.index'), 'label' => 'Subscription Billing'],
    ['url' => '#', 'label' => 'Tambah Kontrak'],
]" />

<div class="max-w-3xl mx-auto">
    <div class="cc-card rounded-2xl border border-[var(--cc-border)]/50 shadow p-6">
        <h2 class="text-xl font-bold text-[var(--cc-text)] mb-6">Tambah Kontrak Berlangganan</h2>

        @if($errors->any())
        <div class="mb-4 bg-rose-500/10 border border-rose-500/20 rounded-xl p-4">
            <p class="font-semibold text-rose-400 mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-sm text-rose-300 space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('subscriptions.store') }}" x-data="subscriptionForm()" class="space-y-5">
            @csrf

            {{-- Client --}}
            <div>
                <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                    Client <span class="text-red-500">*</span>
                </label>
                <select name="client_id" required
                        class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="" class="bg-[var(--cc-surface)]">— Pilih Client —</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" class="bg-[var(--cc-surface)]" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->company_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Vehicle & Driver --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Kendaraan <span class="text-[var(--cc-text-muted)] font-normal">(opsional)</span>
                    </label>
                    <select name="vehicle_id"
                            class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                        <option value="" class="bg-[var(--cc-surface)]">— Pilih Kendaraan —</option>
                        @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" class="bg-[var(--cc-surface)]" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->plate_number }} — {{ $vehicle->brand }} {{ $vehicle->model }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Driver <span class="text-[var(--cc-text-muted)] font-normal">(opsional)</span>
                    </label>
                    <select name="driver_id"
                            class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                        <option value="" class="bg-[var(--cc-surface)]">— Pilih Driver —</option>
                        @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" class="bg-[var(--cc-surface)]" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Product --}}
            <div>
                <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                    Produk <span class="text-[var(--cc-text-muted)] font-normal">(opsional)</span>
                </label>
                <select name="product_id"
                        class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="" class="bg-[var(--cc-surface)]">— Pilih Produk —</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" class="bg-[var(--cc-surface)]" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }} ({{ $product->sku }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Start & End Date --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" required
                           value="{{ old('start_date') }}"
                           class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" required
                           value="{{ old('end_date') }}"
                           class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            {{-- Monthly Rate --}}
            <div>
                <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                    Monthly Rate (IDR) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--cc-text-muted)] text-sm font-semibold">Rp</span>
                    <input type="text" id="monthly_rate_display" placeholder="0"
                           value="{{ old('monthly_rate') ? number_format((float)old('monthly_rate'), 0, ',', '.') : '' }}"
                           oninput="formatRupiah(this, 'monthly_rate')"
                           class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl pl-10 pr-3 py-2.5 text-sm focus:outline-none focus:border-indigo-500">
                    <input type="hidden" name="monthly_rate" id="monthly_rate" value="{{ old('monthly_rate') }}">
                </div>
                <p class="mt-1 text-xs text-[var(--cc-text-muted)]">Rate per siklus penagihan yang dipilih di bawah</p>
            </div>

            {{-- Billing Cycle --}}
            <div>
                <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                    Siklus Penagihan <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['monthly' => 'Bulanan', 'quarterly' => 'Kuartalan (3 bulan)', 'yearly' => 'Tahunan'] as $val => $lbl)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="billing_cycle" value="{{ $val }}"
                               {{ old('billing_cycle', 'monthly') === $val ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-[var(--cc-text)]">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Auto Renew --}}
            <div class="flex items-center justify-between p-4 bg-[var(--cc-bg-muted)] rounded-xl border border-[var(--cc-border)]/50">
                <div>
                    <p class="text-sm font-semibold text-[var(--cc-text)]">Auto Renew</p>
                    <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">Otomatis perpanjang kontrak saat jatuh tempo</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="auto_renew" value="1"
                           {{ old('auto_renew', '1') == '1' ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-[var(--cc-border)]/50 peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5
                                after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-semibold text-[var(--cc-text)] mb-1">Catatan</label>
                <textarea name="notes" rows="3" placeholder="Catatan tambahan..."
                          class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2 border-t border-[var(--cc-border)]/50">
                <button type="submit"
                        class="bg-indigo-600 text-gray-900 px-6 py-2.5 rounded-xl hover:bg-indigo-500 transition-all text-sm font-semibold shadow-lg shadow-indigo-600/20">
                    Simpan Kontrak
                </button>
                <a href="{{ route('subscriptions.index') }}"
                   class="bg-[var(--cc-bg-muted)] text-[var(--cc-text)] border border-[var(--cc-border)] px-6 py-2.5 rounded-xl hover:bg-[var(--cc-surface)] transition-all text-sm font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function formatRupiah(input, hiddenId) {
    let raw = input.value.replace(/\D/g, '');
    input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
    document.getElementById(hiddenId).value = raw;
}

function subscriptionForm() {
    return {};
}
</script>
@endsection
