@extends('layouts.app')

@section('header_title', 'Subscription Billing')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Subscription Billing'],
]" />

{{-- Due Today Warning --}}
@if($dueTodayCount > 0)
<div class="mb-4 flex items-center gap-3 bg-amber-50 border border-amber-300 text-amber-800 rounded-lg px-4 py-3">
    <span class="text-xl">⚠️</span>
    <div>
        <span class="font-semibold">{{ $dueTodayCount }} kontrak</span> jatuh tempo hari ini dan belum ditagih.
        @can('role:gm,finance,manager')
        <a href="{{ route('subscriptions.billing.run') }}"
           onclick="return confirm('Jalankan billing sekarang?')"
           class="ml-2 underline font-semibold hover:text-amber-900">Proses Sekarang</a>
        @endcan
    </div>
</div>
@endif

{{-- Flash Messages --}}
@if(session('success'))
<div class="mb-4 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3">
    ✅ {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3">
    ❌ {{ session('error') }}
</div>
@endif

<div class="cc-card rounded-lg shadow p-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-[var(--cc-text)]">Subscription Billing</h2>
            <p class="text-sm text-[var(--cc-text-muted)] mt-0.5">Kelola kontrak berlangganan kendaraan</p>
        </div>
        @can('role:gm,finance,manager')
        <a href="{{ route('subscriptions.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
            ➕ Tambah Kontrak
        </a>
        @endcan
    </div>

    {{-- Status Filter Tabs --}}
    <div class="flex flex-wrap gap-2 mb-6 border-b border-[var(--cc-border)] pb-4">
        @php
        $tabs = [
            ''            => 'Semua',
            'active'      => 'Aktif',
            'paused'      => 'Ditangguhkan',
            'terminated'  => 'Terminasi',
            'expired'     => 'Kedaluwarsa',
        ];
        @endphp
        @foreach($tabs as $val => $label)
        <a href="{{ route('subscriptions.index', array_merge(request()->query(), ['status' => $val])) }}"
           class="px-4 py-1.5 rounded-full text-sm font-medium transition-colors
           {{ $status === $val ? 'bg-blue-600 text-white' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] hover:bg-gray-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Client Filter --}}
    <form method="GET" action="{{ route('subscriptions.index') }}" class="mb-4 flex gap-3 items-end">
        <input type="hidden" name="status" value="{{ $status }}">
        <div>
            <label class="block text-xs font-medium text-[var(--cc-text-muted)] mb-1">Filter Client</label>
            <select name="client_id" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                <option value="">— Semua Client —</option>
                @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>
                    {{ $c->company_name }}
                </option>
                @endforeach
            </select>
        </div>
        @if($clientId)
        <a href="{{ route('subscriptions.index', ['status' => $status]) }}"
           class="text-sm text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] underline pb-2">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm resizable-table" data-table-id="subscriptions-table">
            <thead class="bg-[var(--cc-bg-muted)] border-b">
                <tr class="text-[var(--cc-text-muted)] text-left">
                    <th class="py-3 px-4">Sub #</th>
                    <th class="py-3 px-4">Client</th>
                    <th class="py-3 px-4">Kendaraan</th>
                    <th class="py-3 px-4">Produk</th>
                    <th class="py-3 px-4 text-right">Monthly Rate</th>
                    <th class="py-3 px-4">Mulai / Selesai</th>
                    <th class="py-3 px-4">Next Billing</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                @php
                $isOverdue = $sub->status === 'active' && $sub->next_billing_date && $sub->next_billing_date->isPast();
                @endphp
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors {{ $isOverdue ? 'bg-amber-50' : '' }}">
                    <td class="py-3 px-4">
                        <a href="{{ route('subscriptions.show', $sub) }}"
                           class="text-blue-600 hover:underline font-mono font-medium text-xs">
                            {{ $sub->sub_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-[var(--cc-text)]">{{ $sub->client->company_name ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                        {{ $sub->vehicle ? $sub->vehicle->plate_number . ' (' . $sub->vehicle->brand . ')' : '—' }}
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">
                        {{ $sub->product->name ?? '—' }}
                    </td>
                    <td class="py-3 px-4 text-right font-medium text-[var(--cc-text)]">
                        Rp {{ number_format((float)$sub->monthly_rate, 0, ',', '.') }}
                        <div class="text-xs text-gray-400 font-normal">
                            /{{ $sub->billing_cycle === 'monthly' ? 'bulan' : ($sub->billing_cycle === 'quarterly' ? '3 bulan' : 'tahun') }}
                        </div>
                    </td>
                    <td class="py-3 px-4 text-xs text-[var(--cc-text-muted)]">
                        <div>{{ $sub->start_date?->format('d M Y') ?? '—' }}</div>
                        <div class="text-gray-400">s/d {{ $sub->end_date?->format('d M Y') ?? '—' }}</div>
                    </td>
                    <td class="py-3 px-4">
                        @if($sub->next_billing_date)
                        <span class="text-xs {{ $isOverdue ? 'text-red-600 font-semibold' : 'text-[var(--cc-text-muted)]' }}">
                            {{ $sub->next_billing_date->format('d M Y') }}
                            @if($isOverdue) <span class="text-red-500">⚠</span> @endif
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        @php
                        $badge = match($sub->status) {
                            'active'     => 'bg-green-100 text-green-700',
                            'paused'     => 'bg-yellow-100 text-yellow-700',
                            'terminated' => 'bg-red-100 text-red-700',
                            'expired'    => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                            default      => 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)]',
                        };
                        $label = match($sub->status) {
                            'active'     => 'Aktif',
                            'paused'     => 'Ditangguhkan',
                            'terminated' => 'Terminasi',
                            'expired'    => 'Kedaluwarsa',
                            default      => ucfirst($sub->status),
                        };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                            {{ $label }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('subscriptions.show', $sub) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs hover:underline">Detail</a>
                            @if($sub->status === 'active')
                            <button type="button"
                                    onclick="openTerminationModal('{{ route('subscriptions.terminate', $sub) }}', '{{ $sub->sub_number }}')"
                                    class="text-red-600 hover:text-red-800 text-xs hover:underline">
                                Terminasi
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-12 text-center text-gray-400">
                        <div class="text-3xl mb-2">📋</div>
                        <div>Belum ada kontrak berlangganan</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $subscriptions->appends(request()->query())->links() }}
    </div>

    @include('subscriptions.charts')
</div>

<!-- Modal PIN Terminasi -->
<div id="termination-pin-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeTerminationModal()"></div>

    <!-- Modal Content Wrapper -->
    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full mx-4 overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="termination-modal-box">
        <!-- Header -->
        <div class="p-6 pb-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-gradient-to-r from-red-500/10 to-transparent">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Terminasi Kontrak</h3>
                    <p class="text-xs text-slate-400 font-medium font-mono" id="termination-sub-number">GB-2026-0001</p>
                </div>
            </div>
            <button type="button" onclick="closeTerminationModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Form -->
        <form id="termination-form" method="POST" action="" class="p-6 space-y-5">
            @csrf
            <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed text-center">
                Silakan masukkan <strong>PIN 6-digit</strong> Anda untuk mengonfirmasi terminasi kontrak ini.
            </p>

            <!-- PIN Inputs -->
            <div class="flex justify-center gap-2" id="pin-input-container">
                @for ($i = 0; $i < 6; $i++)
                <input type="password" 
                       maxlength="1" 
                       pattern="[0-9]" 
                       inputmode="numeric" 
                       required 
                       class="w-12 h-12 text-center text-2xl font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
                       data-index="{{ $i }}"
                       autocomplete="off">
                @endfor
            </div>
            
            <!-- Hidden input to store full pin before submit -->
            <input type="hidden" name="pin" id="full-pin-value">

            <!-- Error message area -->
            <div id="pin-error-msg" class="text-xs text-red-500 text-center font-semibold hidden">
                PIN harus berupa 6 digit angka!
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeTerminationModal()" class="flex-1 px-4 py-2.5 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition-colors">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-red-500/20 hover:shadow-red-600/30 transition-all">
                    Konfirmasi Terminasi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openTerminationModal(actionUrl, subNumber) {
        const modal = document.getElementById('termination-pin-modal');
        const modalBox = document.getElementById('termination-modal-box');
        const form = document.getElementById('termination-form');
        const subNumberText = document.getElementById('termination-sub-number');
        const errorMsg = document.getElementById('pin-error-msg');
        
        form.action = actionUrl;
        subNumberText.textContent = subNumber;
        
        // Clear inputs
        const inputs = document.querySelectorAll('#pin-input-container input');
        inputs.forEach(input => input.value = '');
        document.getElementById('full-pin-value').value = '';
        errorMsg.classList.add('hidden');
        
        // Show modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
            modalBox.classList.add('scale-100', 'opacity-100');
            inputs[0].focus();
        }, 20);
    }

    function closeTerminationModal() {
        const modal = document.getElementById('termination-pin-modal');
        const modalBox = document.getElementById('termination-modal-box');
        
        modalBox.classList.remove('scale-100', 'opacity-100');
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 150);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('#pin-input-container input');
        const form = document.getElementById('termination-form');
        const errorMsg = document.getElementById('pin-error-msg');

        // Handle Auto-focus flow
        inputs.forEach((input, index) => {
            // Only allow numbers
            input.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });

            input.addEventListener('input', function() {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateFullPin();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace') {
                    if (input.value === '' && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                    } else {
                        input.value = '';
                    }
                    updateFullPin();
                }
            });

            // Allow paste of 6 digits
            input.addEventListener('paste', function(e) {
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                if (/^\d{6}$/.test(pasteData)) {
                    pasteData.split('').forEach((char, idx) => {
                        if (inputs[idx]) {
                            inputs[idx].value = char;
                        }
                    });
                    inputs[5].focus();
                    updateFullPin();
                    e.preventDefault();
                }
            });
        });

        function updateFullPin() {
            const pinVal = Array.from(inputs).map(inp => inp.value).join('');
            document.getElementById('full-pin-value').value = pinVal;
        }

        form.addEventListener('submit', function(e) {
            const pinVal = document.getElementById('full-pin-value').value;
            if (pinVal.length !== 6 || !/^\d{6}$/.test(pinVal)) {
                e.preventDefault();
                errorMsg.classList.remove('hidden');
                errorMsg.textContent = 'PIN harus berupa 6 digit angka!';
            }
        });
    });
</script>
@endpush

@endsection
