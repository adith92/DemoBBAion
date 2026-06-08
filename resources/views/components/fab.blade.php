{{--
    FAB — Floating Action Button + Quick-Add Deal Modal
    Included in layouts/app.blade.php just before </body>
--}}

@php $role = Auth::user()->role ?? ''; @endphp
@if(in_array($role, ['gm','manager','sales']))

<div x-data="quickAddFab()" @keydown.escape.window="close()">

    {{-- FAB button --}}
    <button id="fab-quick-add"
            @click="toggle()"
            class="fab-btn"
            :class="{ open: showModal }"
            title="New Opportunity (N)">
        <span class="material-symbols-outlined text-[24px]">add</span>
    </button>

    {{-- Quick-Add Modal --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         style="display:none;position:fixed;bottom:90px;right:28px;z-index:490;width:320px"
         class="cc-card p-5 shadow-2xl"
         @click.stop>

        <div class="flex items-center justify-between mb-4">
            <div class="text-[13px] font-bold" style="color:var(--cc-text)">
                ✨ New Opportunity
            </div>
            <button @click="close()" class="topbar-icon-btn w-6 h-6">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <form action="{{ route('opportunities.store') }}" method="POST" class="space-y-3">
            @csrf

            {{-- Title --}}
            <div>
                <label class="dark-label block mb-1">Deal Title</label>
                <input type="text" name="title" class="dark-input w-full px-3 py-2 text-[13px]"
                       placeholder="e.g. Fleet Contract PT Astra" required
                       x-ref="titleInput" />
            </div>

            {{-- Client quick-select --}}
            <div>
                <label class="dark-label block mb-1">Client</label>
                <select name="client_id" class="dark-input w-full px-3 py-2 text-[13px]">
                    <option value="">— Select client —</option>
                    @foreach(\App\Models\Client::where('status','active')->orderBy('company_name')->limit(30)->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Value --}}
            <div>
                <label class="dark-label block mb-1">Estimated Value (Rp)</label>
                <input type="number" name="estimated_value" class="dark-input w-full px-3 py-2 text-[13px]"
                       placeholder="500000000" min="0" step="1000000" />
            </div>

            {{-- Expected close --}}
            <div>
                <label class="dark-label block mb-1">Expected Close</label>
                <input type="date" name="expected_close_date" class="dark-input w-full px-3 py-2 text-[13px]"
                       value="{{ now()->addDays(30)->format('Y-m-d') }}" />
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-primary w-full justify-center mt-1">
                <span class="material-symbols-outlined text-[15px]">add_circle</span>
                Create Deal
            </button>
        </form>

        {{-- Quick nav to full form --}}
        <a href="{{ route('opportunities.create') }}"
           class="block text-center mt-2 text-[11px]" style="color:var(--cc-text-faint)">
            Full form →
        </a>
    </div>
</div>

@push('scripts')
<script>
function quickAddFab() {
    return {
        showModal: false,
        toggle() { this.showModal = !this.showModal; if (this.showModal) this.$nextTick(() => this.$refs.titleInput?.focus()); },
        close()   { this.showModal = false; }
    };
}
</script>
@endpush

@endif
