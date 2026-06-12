<div
    x-data
    class="flex w-16 h-8 p-1 rounded-full cursor-pointer transition-all duration-300 select-none items-center"
    :class="$store.theme.mode === 'dark' ? 'bg-zinc-950 border border-zinc-800' : 'bg-white border border-zinc-200'"
    @click="$store.theme.toggle()"
    role="button"
    tabindex="0"
    title="Toggle theme ⌘D"
>
    <div class="flex justify-between items-center w-full">
        <!-- Circle 1 (Active Moon in dark / Active Sun in light) -->
        <div
            class="flex justify-center items-center w-6 h-6 rounded-full transition-transform duration-300"
            :class="$store.theme.mode === 'dark' ? 'transform translate-x-0 bg-zinc-800' : 'transform translate-x-8 bg-gray-200'"
        >
            <!-- Moon Icon (Dark Mode Active) -->
            <svg x-show="$store.theme.mode === 'dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-900">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
            </svg>
            <!-- Sun Icon (Light Mode Active) -->
            <svg x-show="$store.theme.mode !== 'dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-700">
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
            </svg>
        </div>

        <!-- Circle 2 (Inactive Sun in dark / Inactive Moon in light) -->
        <div
            class="flex justify-center items-center w-6 h-6 rounded-full transition-transform duration-300"
            :class="$store.theme.mode === 'dark' ? 'bg-transparent' : 'transform -translate-x-8'"
        >
            <!-- Sun Icon (Dark Mode Inactive) -->
            <svg x-show="$store.theme.mode === 'dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-gray-500">
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
            </svg>
            <!-- Moon Icon (Light Mode Active) -->
            <svg x-show="$store.theme.mode !== 'dark'" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 text-black">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
            </svg>
        </div>
    </div>
</div>
