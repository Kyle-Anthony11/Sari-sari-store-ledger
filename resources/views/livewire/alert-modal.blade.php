<div x-data="{ show: @entangle('show') }" x-show="show" x-cloak
     x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="show = false"></div>
    <div class="relative w-full sm:max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
         style="padding-bottom: env(safe-area-inset-bottom);"
         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95">
        <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-base font-semibold text-slate-900">Notification</h2>
                    <p class="text-sm text-slate-600 mt-1 break-words">{{ $message }}</p>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 bg-slate-50 flex justify-end">
            <button type="button" wire:click="close" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">OK</button>
        </div>
    </div>
</div>