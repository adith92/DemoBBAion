@if (session('success') || session('error') || session('warning'))
<div class="px-6 pt-4 space-y-2">
    @if (session('success'))
        <div class="flash-success flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="flash-error flex items-center gap-2">
            <span class="material-symbols-outlined text-[16px] text-red-600 dark:text-red-400">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if (session('warning'))
        <div class="flash-error flex items-center gap-2" style="background:rgba(245,158,11,0.1); border-color:rgba(245,158,11,0.25);">
            <span class="material-symbols-outlined text-[16px] text-amber-600 dark:text-amber-400">warning</span>
            <span class="text-amber-700 dark:text-amber-300">{{ session('warning') }}</span>
        </div>
    @endif
</div>
@endif
