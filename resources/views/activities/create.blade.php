@extends('layouts.app')

@section('header_title', 'Catat Aktivitas')

@section('content')
<div x-data="{
    selectedType: '{{ old('type', $opportunity ? 'meeting' : '') }}',
    selectedClientId: '{{ old('client_id', $client?->id ?? $opportunity?->client_id ?? '') }}',
    opportunities: @json($opportunities->map(fn($o) => ['id' => $o->id, 'title' => $o->title, 'opp_number' => $o->opp_number, 'client_id' => $o->client_id])),
    get filteredOpportunities() {
        if (!this.selectedClientId) return this.opportunities;
        return this.opportunities.filter(o => String(o.client_id) === String(this.selectedClientId));
    }
}">

    <div class="max-w-3xl mx-auto">

        {{-- Back link --}}
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="text-sm text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </div>

        <div class="cc-card rounded-2xl border border-[var(--cc-border)]/50 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-5">
                <h2 class="text-xl font-bold text-gray-900">Catat Aktivitas</h2>
                <p class="text-indigo-200 text-sm mt-0.5">Rekam interaksi dengan klien atau prospek</p>
            </div>

            <form method="POST" action="{{ route('activities.store') }}" class="p-6 space-y-6">
                @csrf

                {{-- Activity Type Selector --}}
                <div>
                    <label class="block text-sm font-semibold text-[var(--cc-text)] mb-3">
                        Tipe Aktivitas <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @php
                            $typeOptions = [
                                ['value' => 'meeting',   'label' => 'Meeting',    'icon' => '🤝', 'desc' => 'Pertemuan langsung', 'color' => 'indigo'],
                                ['value' => 'call',      'label' => 'Panggilan',  'icon' => '📞', 'desc' => 'Telepon / video call', 'color' => 'emerald'],
                                ['value' => 'visit',     'label' => 'Kunjungan',  'icon' => '🚗', 'desc' => 'Kunjungi lokasi klien', 'color' => 'purple'],
                                ['value' => 'follow_up', 'label' => 'Follow-up',  'icon' => '🔄', 'desc' => 'Tindak lanjut', 'color' => 'amber'],
                                ['value' => 'email',     'label' => 'Email',      'icon' => '📧', 'desc' => 'Korespondensi email', 'color' => 'slate'],
                                ['value' => 'demo',      'label' => 'Demo',       'icon' => '💻', 'desc' => 'Presentasi produk', 'color' => 'indigo'],
                            ];
                        @endphp

                        @foreach($typeOptions as $opt)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $opt['value'] }}"
                                   x-model="selectedType"
                                   {{ old('type') === $opt['value'] ? 'checked' : '' }}
                                   class="sr-only">
                            <div :class="selectedType === '{{ $opt['value'] }}' ?
                                    'border-indigo-500 bg-indigo-500/10 ring-2 ring-indigo-500/20' :
                                    'border-[var(--cc-border)]/50 hover:border-indigo-500/40 bg-[var(--cc-bg-muted)]'"
                                 class="border-2 rounded-xl p-3 text-center transition-all">
                                <div class="text-2xl mb-1">{{ $opt['icon'] }}</div>
                                <div class="font-semibold text-sm text-[var(--cc-text)]">{{ $opt['label'] }}</div>
                                <div class="text-xs text-[var(--cc-text-muted)] mt-0.5">{{ $opt['desc'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Subject --}}
                <div>
                    <label for="subject" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Subjek <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="subject" id="subject"
                           value="{{ old('subject') }}"
                           placeholder="Contoh: Diskusi proposal kontrak armada 2025"
                           class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all @error('subject') border-red-400 @enderror">
                    @error('subject')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Client + Opportunity --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="client_id" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">Klien</label>
                        <select name="client_id" id="client_id"
                                x-model="selectedClientId"
                                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            <option value="" class="bg-[var(--cc-surface)]">-- Pilih Klien --</option>
                            @foreach($clients as $c)
                            <option value="{{ $c->id }}" class="bg-[var(--cc-surface)]" {{ old('client_id', $client?->id ?? $opportunity?->client_id) == $c->id ? 'selected' : '' }}>
                                {{ $c->company_name }}
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="opportunity_id" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">Opportunity</label>
                        <select name="opportunity_id" id="opportunity_id"
                                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            <option value="" class="bg-[var(--cc-surface)]">-- Pilih Opportunity --</option>
                            <template x-for="opp in filteredOpportunities" :key="opp.id">
                                <option :value="opp.id" class="bg-[var(--cc-surface)]"
                                        :selected="opp.id == {{ old('opportunity_id', $opportunity?->id ?? 'null') }}"
                                        x-text="opp.opp_number + ' — ' + opp.title"></option>
                            </template>
                        </select>
                        @error('opportunity_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Activity Date + Duration --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="activity_date" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                            Tanggal & Waktu Aktivitas <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="activity_date" id="activity_date"
                               value="{{ old('activity_date', now()->format('Y-m-d\TH:i')) }}"
                               class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all @error('activity_date') border-red-400 @enderror">
                        @error('activity_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="duration_minutes" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                            Durasi (menit) <span class="text-[var(--cc-text-muted)] font-normal">— opsional</span>
                        </label>
                        <input type="number" name="duration_minutes" id="duration_minutes"
                               value="{{ old('duration_minutes') }}"
                               min="1" max="1440" placeholder="Contoh: 60"
                               class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        @error('duration_minutes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Outcome --}}
                <div>
                    <label for="outcome" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Hasil / Outcome <span class="text-[var(--cc-text-muted)] font-normal">— opsional</span>
                    </label>
                    <textarea name="outcome" id="outcome" rows="3"
                              placeholder="Ringkasan hasil pertemuan, kesepakatan, atau poin penting..."
                              class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all resize-y">{{ old('outcome') }}</textarea>
                </div>

                {{-- Next Action + Next Action Date --}}
                <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-indigo-400 mb-3 flex items-center gap-2">
                        🎯 Rencana Tindak Lanjut
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="next_action" class="block text-xs font-semibold text-indigo-400 mb-1">Aksi Selanjutnya</label>
                            <input type="text" name="next_action" id="next_action"
                                   value="{{ old('next_action') }}"
                                   placeholder="Contoh: Kirim proposal revisi"
                                   class="w-full bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-lg px-3 py-2 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        </div>
                        <div>
                            <label for="next_action_date" class="block text-xs font-semibold text-indigo-400 mb-1">Tanggal Target</label>
                            <input type="date" name="next_action_date" id="next_action_date"
                                   value="{{ old('next_action_date') }}"
                                   min="{{ now()->addDay()->toDateString() }}"
                                   class="w-full bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-lg px-3 py-2 text-sm text-[var(--cc-text)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-semibold text-[var(--cc-text)] mb-1">
                        Catatan Tambahan <span class="text-[var(--cc-text-muted)] font-normal">— opsional</span>
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                              placeholder="Informasi tambahan, konteks, atau hal lain yang perlu diingat..."
                              class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-4 py-2.5 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all resize-y">{{ old('notes') }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-[var(--cc-border)]/50">
                    <a href="{{ route('activities.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-[var(--cc-text)] bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl hover:bg-[var(--cc-surface)] transition-all">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-gray-900 font-semibold px-6 py-2.5 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Aktivitas
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
