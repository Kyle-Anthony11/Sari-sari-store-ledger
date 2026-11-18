<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Sari-Sari Store Credit Ledger</title>
    {{-- PWA --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Credit Ledger">
    <link rel="apple-touch-icon" href="/images/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icon-512x512.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- ============ MOBILE TOP HEADER ============ --}}
        <header class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200"
                style="padding-top: env(safe-area-inset-top);">
            <div class="h-14 px-3 flex items-center justify-between gap-3">
                {{-- Hamburger / Menu --}}
                <button type="button"
                        @click="sidebarOpen = true"
                        class="flex items-center justify-center w-10 h-10 rounded-xl text-slate-700 hover:bg-slate-100 active:bg-slate-200 active:scale-95 transition"
                        aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Store Name / Brand --}}
                <div class="flex items-center gap-2.5 min-w-0 flex-1 justify-center">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-sm shadow-indigo-500/30 flex-shrink-0">
                        <span class="text-white font-bold text-sm">S</span>
                    </div>
                    <h1 class="text-sm font-bold text-slate-900 truncate">
                        {{ \App\Services\AuthService::user()?->store_name ?? 'Sari-Sari Store' }}
                    </h1>
                </div>

                {{-- Right spacer (or quick action) --}}
                <div class="w-10 h-10 flex items-center justify-center">
                    {{-- Reserved for notifications / profile menu in future --}}
                </div>
            </div>
        </header>

        <!-- Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-40 lg:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside id="sidebar"
               class="w-64 flex-shrink-0 bg-gradient-to-b from-slate-900 via-slate-900 to-indigo-950 text-white flex flex-col transition-transform duration-300 ease-in-out fixed inset-y-0 left-0 z-50 lg:relative lg:translate-x-0 transform -translate-x-full lg:transform-none border-r border-slate-800"
               :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
               style="padding-top: env(safe-area-inset-top); padding-bottom: env(safe-area-inset-bottom);">
            <!-- Brand -->
            <div class="p-6 border-b border-slate-800/60">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <span class="text-white font-bold text-lg">S</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-sm font-bold tracking-tight truncate">{{ \App\Services\AuthService::user()?->store_name ?? 'Sari-Sari Store' }}</h1>
                        <p class="text-xs text-slate-400 mt-0.5">Credit Ledger</p>
                    </div>
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 active:bg-slate-700 transition" aria-label="Close menu">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-1 overscroll-contain">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                        ['route' => 'customers', 'label' => 'Customers', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['route' => 'product-list', 'label' => 'Products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                        ['route' => 'transactions', 'label' => 'Credit Transactions', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                        ['route' => 'transactions-history', 'label' => 'Paid Transaction History', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'reports', 'label' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['route' => 'store-account', 'label' => 'Management', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @if($item['route'] === 'store-account' && !\App\Services\AuthService::check())
                        @continue
                    @endif
                    <a href="{{ route($item['route']) }}" @click="sidebarOpen = false"
                       class="group flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium transition-all active:scale-[0.98]
                              {{ request()->routeIs($item['route'])
                                  ? 'bg-gradient-to-r from-indigo-500/20 to-blue-500/10 text-white border border-indigo-500/30 shadow-sm'
                                  : 'text-slate-400 hover:bg-slate-800/60 hover:text-white border border-transparent' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0 {{ request()->routeIs($item['route']) ? 'text-indigo-400' : 'text-slate-500 group-hover:text-indigo-400' }} transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Logout -->
            @if(\App\Services\AuthService::check())
            <div class="p-3 border-t border-slate-800/60">
                <button type="button"
                        @click="$dispatch('open-logout-modal')"
                        class="flex items-center gap-3 w-full px-3 py-3 rounded-xl text-sm font-medium text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 border border-transparent hover:border-rose-500/20 transition-all active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </button>
            </div>
            @endif
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-7xl mx-auto px-3 py-4 sm:px-6 sm:py-8 lg:px-8 lg:pt-8 lg:pb-12
                            pt-[calc(env(safe-area-inset-top)+4rem)] lg:pt-8
                            pb-[calc(env(safe-area-inset-bottom)+5.5rem)] lg:pb-8">
                    @if (request()->routeIs('dashboard'))
                        @livewire(\App\Http\Livewire\Dashboard::class)
                    @elseif (request()->routeIs('customers'))
                        @livewire(\App\Http\Livewire\Customers::class)
                    @elseif (request()->routeIs('product-list'))
                        @livewire(\App\Http\Livewire\ProductLists::class)
                    @elseif (request()->routeIs('transactions'))
                        @livewire(\App\Http\Livewire\Transactions::class)
                    @elseif (request()->routeIs('transactions-history'))
                        @livewire(\App\Http\Livewire\TransactionsHistory::class)
                    @elseif (request()->routeIs('store-account'))
                        @livewire(\App\Http\Livewire\StoreAccount::class)
                    @elseif (request()->routeIs('reports'))
                        @livewire(\App\Http\Livewire\Reports::class)
                    @else
                        @livewire(\App\Http\Livewire\Dashboard::class)
                    @endif
                </div>
            </main>
        </div>
    </div>

    {{-- ============ MOBILE BOTTOM NAVIGATION ============ --}}
    @if(\App\Services\AuthService::check())
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-[0_-4px_20px_-4px_rgba(15,23,42,0.08)]"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="grid grid-cols-5 gap-0">
            @php
                $bottomNav = [
                    ['route' => 'dashboard', 'label' => 'Home', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10'],
                    ['route' => 'customers', 'label' => 'Customers', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'transactions', 'label' => 'New', 'icon' => 'M12 4v16m8-8H4', 'primary' => true],
                    ['route' => 'transactions-history', 'label' => 'History', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'reports', 'label' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
            @endphp
            @foreach($bottomNav as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                @if(!empty($item['primary']))
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center justify-center py-2 -mt-4 active:scale-95 transition-transform">
                        <span class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white shadow-lg shadow-indigo-500/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                        </span>
                        <span class="text-[10px] font-semibold mt-0.5 text-indigo-600">{{ $item['label'] }}</span>
                    </a>
                @else
                    <a href="{{ route($item['route']) }}"
                       class="flex flex-col items-center justify-center py-2.5 active:scale-95 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $isActive ? 'text-indigo-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="text-[10px] font-semibold mt-0.5 {{ $isActive ? 'text-indigo-600' : 'text-slate-500' }}">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
    @endif

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>

    <!-- Logout Modal -->
    <div x-data="{ showLogoutModal: false }"
         x-on:open-logout-modal.window="showLogoutModal = true"
         x-cloak
         class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center sm:p-4"
         style="display: none;"
         x-show="showLogoutModal"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showLogoutModal = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
             style="padding-bottom: env(safe-area-inset-bottom);"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mt-3 sm:hidden"></div>
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full bg-rose-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Confirm Logout</h2>
                        <p class="text-sm text-slate-500 mt-1">Are you sure you want to log out of your account?</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button type="button" @click="showLogoutModal = false" class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-100 active:scale-[0.98] transition">Cancel</button>
                <button type="button" onclick="document.getElementById('logout-form').submit()" class="w-full sm:w-auto rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white hover:bg-rose-700 shadow-sm active:scale-[0.98] transition">Logout</button>
            </div>
        </div>
    </div>

    <livewire:alert-modal />

    @livewireScripts

    {{-- PWA Service Worker --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/serviceworker.js')
                .then((reg) => console.log('SW registered:', reg.scope))
                .catch((err) => console.warn('SW registration failed:', err));
        });
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(session()->has('message'))
            Livewire.dispatch('showAlert', @json(session('message')));
        @endif
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Survives Livewire morphs (which strip data-* attributes)
        const protectedElements = new WeakSet();

        function applyAutofillProtection(element) {
            if (!element || element.nodeType !== 1) return;
            const tag = element.tagName;
            if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') return;

            // 1) Autofill-deterrent attributes — always safe to (re)apply
            if (element.type === 'password') {
                element.setAttribute('autocomplete', 'new-password');
            } else {
                element.setAttribute('autocomplete', 'new-password');
                element.setAttribute('data-lpignore', 'true');
                element.setAttribute('data-1p-ignore', 'true');
                element.setAttribute('data-bwignore', 'true');
                element.setAttribute('data-form-type', 'other');
            }

            // 2) Readonly lock — SKIP the field the user is currently typing in
            const skipTypes = ['checkbox','radio','file','date','submit','button','hidden'];
            const isFocused = document.activeElement === element;

            if (!skipTypes.includes(element.type) && !isFocused) {
                if (!element.hasAttribute('readonly')) {
                    element.setAttribute('readonly', 'readonly');
                }
            }

            element.dataset.autofillProtected = 'true';
            protectedElements.add(element);
        }

        function handleFocus(e) {
            const el = e.target;
            if (el && el.hasAttribute('readonly') && protectedElements.has(el)) {
                el.removeAttribute('readonly');
            }
        }

        document.querySelectorAll('input, select, textarea').forEach(applyAutofillProtection);
        document.addEventListener('focusin', handleFocus, true);

        // 3) Observer: only watch autocomplete (NOT readonly)
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) return;
                        if (['INPUT','SELECT','TEXTAREA'].includes(node.tagName)) {
                            applyAutofillProtection(node);
                        }
                        if (node.querySelectorAll) {
                            node.querySelectorAll('input, select, textarea')
                                .forEach(applyAutofillProtection);
                        }
                    });
                } else if (
                    mutation.type === 'attributes' &&
                    mutation.attributeName === 'autocomplete'
                ) {
                    // Livewire morph stripped our autocomplete — put it back
                    const el = mutation.target;
                    if (el.nodeType === 1 && el.getAttribute('autocomplete') !== 'new-password') {
                        applyAutofillProtection(el);
                    }
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['autocomplete'],
        });

        // Re-scan after Livewire navigations
        document.addEventListener('livewire:navigated', function () {
            document.querySelectorAll('input, select, textarea').forEach(applyAutofillProtection);
        });
    });
    </script>
</body>
</html>