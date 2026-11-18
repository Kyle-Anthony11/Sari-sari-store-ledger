<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Sari-Sari Store Credit Ledger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased relative min-h-screen"
      style="padding-top: env(safe-area-inset-top);">
    {{-- Blurred Background Image --}}
    <div class="fixed inset-0 -z-20 bg-cover bg-center"
         style="background-image: url('{{ asset('images/store-bg.jpg') }}');
                filter: blur(8px);
                transform: scale(1.1);">
    </div>

    {{-- Dark overlay for readability --}}
    <div class="fixed inset-0 -z-10 bg-slate-900/45"></div>

    <div class="min-h-screen flex items-start sm:items-center justify-center p-4 py-6 sm:py-12 relative z-10"
         style="padding-bottom: calc(env(safe-area-inset-bottom) + 1.5rem);">
        <div class="w-full max-w-md">
            {{-- Livewire / slot-based content --}}
            @isset($slot)
                {{ $slot }}
            @endisset

            {{-- Traditional @extends / @section('content') content --}}
            @hasSection('content')
                @yield('content')
            @endif
        </div>
    </div>
    <livewire:alert-modal />
    @livewireScripts
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