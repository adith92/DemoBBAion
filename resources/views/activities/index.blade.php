@extends('layouts.app')

@section('header_title', 'Log Aktivitas')

@section('content')
<div x-data="{ showFilters: false }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[var(--cc-text)]">Log Aktivitas</h1>
            <p class="text-sm text-[var(--cc-text-muted)] mt-0.5">Rekam setiap interaksi dengan klien & prospek</p>
        </div>
        <a href="{{ route('activities.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-gray-900 font-semibold px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Aktivitas
        </a>
    </div>

    {{-- Upcoming Follow-up Reminders --}}
    @if($upcomingFollowUps->isNotEmpty())
    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <span class="text-2xl">🔔</span>
            <div class="flex-1">
                <h3 class="font-semibold text-amber-400 mb-2">Pengingat Follow-up (7 Hari Ke Depan)</h3>
                <div class="space-y-2">
                    @foreach($upcomingFollowUps as $fu)
                    <div class="flex flex-wrap items-center gap-2 text-sm text-[var(--cc-text-muted)]">
                        <span class="font-medium text-amber-300">{{ $fu->next_action_date->format('d M') }}</span>
                        <span>—</span>
                        <span class="text-[var(--cc-text)] font-medium">{{ $fu->next_action ?? $fu->subject }}</span>
                        @if($fu->client)
                        <span class="text-xs bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full font-medium">{{ $fu->client->company_name }}</span>
                        @endif
                        @if($fu->opportunity)
                        <a href="{{ route('opportunities.show', $fu->opportunity_id) }}"
                           class="text-xs text-amber-400 underline hover:text-amber-300 font-mono font-semibold">{{ $fu->opportunity->opp_number }}</a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Type Filter Tabs --}}
    <div class="cc-card rounded-xl border border-[var(--cc-border)]/50 mb-4">
        <div class="flex overflow-x-auto">
            @php
                $types = [
                    '' => ['label' => 'Semua', 'icon' => '📋'],
                    'meeting' => ['label' => 'Meeting', 'icon' => '🤝'],
                    'call' => ['label' => 'Panggilan', 'icon' => '📞'],
                    'visit' => ['label' => 'Kunjungan', 'icon' => '🚗'],
                    'follow_up' => ['label' => 'Follow-up', 'icon' => '🔄'],
                    'email' => ['label' => 'Email', 'icon' => '📧'],
                    'demo' => ['label' => 'Demo', 'icon' => '💻'],
                ];
                $currentType = request('type', '');
            @endphp
            @foreach($types as $value => $info)
            <a href="{{ request()->fullUrlWithQuery(['type' => $value, 'page' => 1]) }}"
               class="flex-shrink-0 flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 transition-colors
                      {{ $currentType === $value ? 'border-indigo-500 text-indigo-400 font-semibold' : 'border-transparent text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] hover:border-[var(--cc-border)]/30' }}">
                <span>{{ $info['icon'] }}</span>
                <span>{{ $info['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Additional Filters --}}
    <div class="cc-card rounded-xl p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="type" value="{{ request('type') }}">

            {{-- Sales filter (non-sales roles) --}}
            @if(isset($salesUsers) && $salesUsers->isNotEmpty())
            <div>
                <label class="block text-xs font-medium text-cc-muted mb-1">Sales</label>
                <select name="sales_id" class="dark-input text-sm px-3 py-2 rounded-lg min-w-36">
                    <option value="">Semua Sales</option>
                    @foreach($salesUsers as $su)
                    <option value="{{ $su->id }}" {{ request('sales_id') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-cc-muted mb-1">Klien</label>
                <select name="client_id" class="dark-input text-sm px-3 py-2 rounded-lg min-w-36">
                    <option value="">Semua Klien</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-cc-muted mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="dark-input text-sm px-3 py-2 rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-medium text-cc-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="dark-input text-sm px-3 py-2 rounded-lg">
            </div>

            <div>
                <label class="block text-xs font-medium text-cc-muted mb-1">Urutkan</label>
                <select name="sort_by" class="dark-input text-sm px-3 py-2 rounded-lg">
                    <option value="newest"  {{ ($sortBy ?? 'newest') === 'newest'  ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest"  {{ ($sortBy ?? '') === 'oldest'  ? 'selected' : '' }}>Terlama</option>
                    <option value="type"    {{ ($sortBy ?? '') === 'type'    ? 'selected' : '' }}>Jenis</option>
                    <option value="sales"   {{ ($sortBy ?? '') === 'sales'   ? 'selected' : '' }}>Sales A-Z</option>
                </select>
            </div>

            <button type="submit" class="btn-primary text-sm px-4 py-2">Filter</button>
            <a href="{{ route('activities.index') }}" class="text-cc-muted hover:text-cc font-medium text-sm px-3 py-2">Reset</a>
        </form>
    </div>

    {{-- Activity Table --}}
    <div class="cc-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[var(--cc-border)] text-[11px] uppercase text-cc-muted font-semibold">
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Tipe</th>
                        <th class="text-left px-4 py-3">Klien</th>
                        <th class="text-left px-4 py-3">Opportunity</th>
                        <th class="text-left px-4 py-3">Subjek & Outcome</th>
                        <th class="text-left px-4 py-3">Next Action</th>
                        @if(!auth()->user()->isSales())
                        <th class="text-left px-4 py-3">Sales</th>
                        @endif
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--cc-border)]">
                    @forelse($activities as $activity)
                    @php
                        $typeBadge = match($activity->type) {
                            'meeting'   => ['bg-blue-900/40 text-blue-300',   '🤝', 'Meeting'],
                            'call'      => ['bg-green-900/40 text-green-300',  '📞', 'Panggilan'],
                            'visit'     => ['bg-purple-900/40 text-purple-300','🚗', 'Kunjungan'],
                            'follow_up' => ['bg-yellow-900/40 text-yellow-300','🔄', 'Follow-up'],
                            'email'     => ['bg-slate-700/60 text-cc font-medium',  '📧', 'Email'],
                            'demo'      => ['bg-indigo-900/40 text-indigo-300','💻', 'Demo'],
                            default     => ['bg-slate-700/60 text-cc font-medium',  '📌', ucfirst($activity->type)],
                        };
                    @endphp
                    <tr class="hover:bg-[var(--cc-row-hover)] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-cc font-medium">{{ $activity->activity_date->format('d M Y') }}</div>
                            <div class="text-xs text-cc-muted">{{ $activity->activity_date->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $typeBadge[0] }}">
                                {{ $typeBadge[1] }} {{ $typeBadge[2] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($activity->client)
                            <div class="font-medium text-cc font-medium">{{ $activity->client->company_name }}</div>
                            <div class="text-xs text-cc-muted">{{ $activity->client->pic_name }}</div>
                            @else
                            <span class="text-cc-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($activity->opportunity)
                            <a href="{{ route('opportunities.show', $activity->opportunity_id) }}"
                               class="text-blue-400 hover:underline text-xs font-mono">{{ $activity->opportunity->opp_number }}</a>
                            <div class="text-xs text-cc-muted truncate max-w-28">{{ $activity->opportunity->title }}</div>
                            @else
                            <span class="text-cc-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            <div class="font-medium text-cc font-medium truncate">{{ $activity->subject }}</div>
                            @if($activity->outcome)
                            <div class="text-xs text-cc-muted truncate mt-0.5">{{ Str::limit($activity->outcome, 60) }}</div>
                            @endif
                            @if($activity->duration_minutes)
                            <div class="text-xs text-cc-muted mt-0.5">⏱ {{ $activity->duration_minutes }} menit</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($activity->next_action)
                            <div class="text-xs text-cc-muted">{{ Str::limit($activity->next_action, 50) }}</div>
                            @if($activity->next_action_date)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays($activity->next_action_date, false);
                                $dateColor = $daysLeft < 0 ? 'text-red-400 font-semibold' : ($daysLeft <= 2 ? 'text-orange-400 font-medium' : 'text-cc-muted');
                            @endphp
                            <div class="text-xs {{ $dateColor }} mt-0.5">{{ $activity->next_action_date->format('d M Y') }}</div>
                            @endif
                            @else
                            <span class="text-cc-muted text-xs">—</span>
                            @endif
                        </td>
                        @if(!auth()->user()->isSales())
                        <td class="px-4 py-3">
                            <div class="text-xs text-cc-muted">{{ $activity->sales?->name ?? '—' }}</div>
                        </td>
                        @endif
                        <td class="px-4 py-3 text-right">
                            @if($activity->sales_id === auth()->id() && $activity->created_at->isToday())
                            <form method="POST" action="{{ route('activities.destroy', $activity) }}"
                                  onsubmit="return confirm('Hapus aktivitas ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSales() ? 7 : 8 }}" class="px-4 py-12 text-center text-cc-muted">
                            <div class="text-4xl mb-3">📋</div>
                            <div class="font-medium text-cc font-medium">Belum ada aktivitas tercatat</div>
                            <div class="text-sm mt-1">Mulai catat aktivitas pertama Anda</div>
                            <a href="{{ route('activities.create') }}"
                               class="inline-block mt-3 btn-primary text-sm font-medium px-4 py-2 rounded-lg">
                                Catat Aktivitas
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div class="px-4 py-3 border-t border-[var(--cc-border)]">
            {{ $activities->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
