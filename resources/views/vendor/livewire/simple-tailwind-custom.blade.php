@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        <div class="flex flex-wrap gap-1 sm:gap-1.5 items-center justify-center">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            {{-- Page Links --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-200 bg-white text-slate-400">
                        {{ $element }}
                    </span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-semibold rounded-lg bg-indigo-600 text-white shadow-sm shadow-indigo-500/30">
                                {{ $page }}
                            </span>
                        @else
                            <button wire:click="gotoPage({{ $page }})"
                                    class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="inline-flex items-center justify-center min-w-[2rem] sm:min-w-[2.25rem] h-8 sm:h-9 px-2 sm:px-3 text-xs sm:text-sm font-medium rounded-lg border border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>
    </nav>
@endif