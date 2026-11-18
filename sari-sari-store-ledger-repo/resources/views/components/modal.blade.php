@props(['maxWidth' => 'md', 'id' => null, 'closeOnBackdropClick' => true])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm', 'md' => 'sm:max-w-md', 'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl', '2xl' => 'sm:max-w-2xl', '3xl' => 'sm:max-w-3xl', '4xl' => 'sm:max-w-4xl',
][$maxWidth] ?? 'sm:max-w-md';
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    x-cloak
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <div class="flex min-h-screen items-end sm:items-center justify-center sm:p-4">
        <div x-show="show"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="{{ $closeOnBackdropClick ? 'show = false' : '' }}"></div>

        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95"
             class="relative w-full {{ $maxWidth }} bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-h-[92dvh] sm:max-h-[90vh] flex flex-col"
             style="padding-bottom: env(safe-area-inset-bottom);">

            {{-- Mobile drag handle --}}
            <div class="sm:hidden pt-3 pb-1 flex justify-center flex-shrink-0">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full"></div>
            </div>

            <div class="overflow-y-auto overscroll-contain flex-1" style="-webkit-overflow-scrolling: touch;">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>