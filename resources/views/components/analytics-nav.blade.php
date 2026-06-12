@php
$tabs = [
    ['route' => 'analytics.index', 'label' => 'Overview', 'active' => request()->routeIs('analytics.index')],
    ['route' => 'analytics.pipeline', 'label' => 'Sales Pipeline', 'active' => request()->routeIs('analytics.pipeline')],
    ['route' => 'analytics.crosssell', 'label' => 'Cross-sell', 'active' => request()->routeIs('analytics.crosssell')],
    ['route' => 'analytics.sales', 'label' => 'Sales Performance', 'active' => request()->routeIs('analytics.sales')],
];
@endphp
<div class="flex flex-wrap items-center gap-2 mb-4">
@foreach ($tabs as $tab)
    <a href="{{ route($tab['route']) }}"
        class="px-4 py-2 rounded-lg text-sm font-medium {{ $tab['active'] ? 'bg-primary text-white' : 'bg-cc-card border border-cc text-cc-muted hover:bg-cc-card' }}">
        {{ $tab['label'] }}
    </a>
@endforeach
</div>
