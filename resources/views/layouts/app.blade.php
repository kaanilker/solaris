<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <title>@yield('title', 'Solaris - Space Weather Monitoring')</title>
    <meta name="description" content="@yield('meta_description', 'Solaris - Uzay hava durumu izleme ve risk analiz platformu')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Tailwind Custom Config --}}
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                        display: ['Outfit', 'ui-sans-serif'],
                    },
                    colors: {
                        solar: {
                            50:  '#fff9eb',
                            100: '#fef0c7',
                            200: '#fedf89',
                            300: '#fec84b',
                            400: '#fdb022',
                            500: '#f79009',
                            600: '#dc6803',
                            700: '#b54708',
                            800: '#93370d',
                            900: '#792e0d',
                        },
                        night: {
                            50:  '#f0f4ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#1e1b4b',
                            900: '#0f0c29',
                            950: '#08070f',
                        },
                    },
                    animation: {
                        'ray-pulse': 'rayPulse 3s ease-in-out infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'slide-down': 'slideDown 0.25s ease-out',
                        'fade-in': 'fadeIn 0.3s ease-out',
                    },
                    keyframes: {
                        rayPulse: {
                            '0%, 100%': { opacity: '0.6', transform: 'scaleY(1)' },
                            '50%': { opacity: '1', transform: 'scaleY(1.08)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        slideDown: {
                            '0%': { opacity: '0', transform: 'translateY(-8px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                    boxShadow: {
                        'glow-solar': '0 0 30px rgba(251, 176, 34, 0.35)',
                        'glow-night': '0 0 30px rgba(99, 102, 241, 0.35)',
                        'inner-glow': 'inset 0 1px 0 rgba(255,255,255,0.1)',
                    },
                    backdropBlur: {
                        xs: '2px',
                    },
                },
            },
        }
    </script>

    {{-- Custom Styles --}}
    <style>
        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: linear-gradient(135deg, #0f0c29 0%, #1a1040 40%, #0f2027 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #e2e8f0;
        }

        /* Glassmorphism card */
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-dark {
            background: rgba(15, 12, 41, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(90deg, #fdb022, #f79009, #dc6803);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Navbar dropdown */
        .nav-dropdown:hover .nav-dropdown-menu,
        .nav-dropdown:focus-within .nav-dropdown-menu {
            display: block;
            animation: slideDown 0.25s ease-out;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        ::-webkit-scrollbar-thumb { background: rgba(251,176,34,0.4); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(251,176,34,0.7); }

        /* Active nav link */
        .nav-link-active {
            color: #fdb022;
            border-bottom: 2px solid #fdb022;
            padding-bottom: 2px;
        }

        /* Section divider */
        .divider-solar {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(253, 176, 34, 0.4), transparent);
        }

        /* Mobile menu */
        #mobile-menu {
            transition: max-height 0.35s ease, opacity 0.35s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 600px;
            opacity: 1;
        }

        @yield('extra_styles')
    </style>

    {{-- Vite Assets (if applicable) --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}

    @yield('head')
</head>

<body class="antialiased min-h-screen flex flex-col">

    {{-- ============================================================
         NAVIGATION BAR
    ============================================================ --}}
    <header id="main-header" class="bg-slate-900 sticky top-0 z-50 border-b border-slate-800">
        <nav class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">

                {{-- ── LOGO ──────────────────────── --}}
                <div class="flex-shrink-0">
                    <a href="{{ url('/') }}" class="flex items-center gap-1.5" title="SOLARIS">
                        <svg class="w-8 h-8 text-night-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c-.61 0-1.1-.49-1.1-1.1s.49-1.1 1.1-1.1 1.1.49 1.1 1.1-.49 1.1-1.1 1.1z"/>
                        </svg>
                        <span class="font-bold text-xl text-white">Solaris</span>
                    </a>
                </div>

                {{-- ── DESKTOP NAVBAR ─ --}}
                <div class="hidden lg:flex flex-1 items-center justify-center">
                    <ul class="flex items-center gap-6" id="desktop-nav">
                        <li>
                            <a href="{{ url('/') }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->routeIs('home')) text-white @else text-slate-400 hover:text-white @endif">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Canlı Veri
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/hesaplama') }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('hesaplama')) text-white @else text-slate-400 hover:text-white @endif">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Hesaplama
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/gecmis') }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('gecmis')) text-white @else text-slate-400 hover:text-white @endif">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Geçmiş
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/hakkimizda') }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('hakkimizda')) text-white @else text-slate-400 hover:text-white @endif">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Hakkımızda
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- ── SAĞ: Aksiyonlar ─────────────────────────── --}}
                <div class="flex items-center gap-3">
                    {{-- Mobil menü butonu --}}
                    <button id="mobile-menu-btn"
                            class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-solar-400
                                   hover:bg-white/5 transition-all duration-200"
                            aria-label="Menüyü aç/kapat">
                        <svg class="w-6 h-6" id="icon-menu" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg class="w-6 h-6 hidden" id="icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

            </div>

            {{-- ── MOBİL MENÜ ──────────────────────────────────── --}}
            <div id="mobile-menu" class="lg:hidden pb-4">
                <ul class="flex flex-col gap-2 pt-3">
                    <li>
                        <a href="{{ url('/') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300
                                  @if(request()->routeIs('home')) text-night-400 bg-night-500/15 border border-night-500/30 @else text-slate-300 hover:text-night-400 hover:bg-white/5 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Canlı Veri
                            @if(request()->routeIs('home'))
                            <span class="ml-auto w-2 h-2 rounded-full bg-night-400 animate-pulse"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/hesaplama') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300
                                  @if(request()->is('hesaplama')) text-solar-400 bg-solar-500/15 border border-solar-500/30 @else text-slate-300 hover:text-solar-400 hover:bg-white/5 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Hesaplama
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/gecmis') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300
                                  @if(request()->is('gecmis')) text-amber-400 bg-amber-500/15 border border-amber-500/30 @else text-slate-300 hover:text-amber-400 hover:bg-white/5 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Geçmiş
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/hakkimizda') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300
                                  @if(request()->is('hakkimizda')) text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 @else text-slate-300 hover:text-emerald-400 hover:bg-white/5 @endif">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Hakkımızda
                        </a>
                    </li>
                </ul>
            </div>

        </nav>
        <div class="divider-solar"></div>
    </header>

    {{-- ============================================================
         MAIN CONTENT
    ============================================================ --}}
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    {{-- ============================================================
         FOOTER
    ============================================================ --}}
    <footer class="glass-dark mt-auto border-t border-white/5">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-center gap-8">
                <span class="text-slate-400 text-sm font-medium">SOLARIS by Quasar Team</span>
                <a href="https://github.com/kaanilker/solaris" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                    Açık Kaynak Kodları
                </a>
            </div>
        </div>
    </footer>

    {{-- ============================================================
         JQUERY + GLOBAL AJAX SETUP
    ============================================================ --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    // Global base URL (cPanel /public alt dizini için)
    window.SOLARIS_BASE = document.querySelector('meta[name="base-url"]')?.content?.replace(/\/$/, '') || '';

    // CSRF token tüm AJAX isteklerine otomatik ekle
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    });
    </script>

    {{-- ============================================================
         JAVASCRIPT
    ============================================================ --}}
    <script>
    // ── Mobil Menü Toggle ──────────────────────────────────────────
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu    = document.getElementById('mobile-menu');
    const iconMenu      = document.getElementById('icon-menu');
    const iconClose     = document.getElementById('icon-close');

    mobileMenuBtn.addEventListener('click', () => {
        const isOpen = mobileMenu.classList.toggle('open');
        iconMenu.classList.toggle('hidden', isOpen);
        iconClose.classList.toggle('hidden', !isOpen);
        mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
    });

    // ── Header gölgesi scroll'da ──────────────────────────────────
    const header = document.getElementById('main-header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 10) {
            header.classList.add('shadow-2xl');
        } else {
            header.classList.remove('shadow-2xl');
        }
    }, { passive: true });

    // ── Dışarı tıklandığında dropdown kapat ───────────────────────
    document.addEventListener('click', (e) => {
        document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                const menu = dropdown.querySelector('.nav-dropdown-menu');
                if (menu) menu.classList.add('hidden');
            }
        });
    });

    // ── Dropdown buton toggle ──────────────────────────────────────
    document.querySelectorAll('.nav-dropdown > button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = btn.nextElementSibling;
            const isHidden = menu.classList.contains('hidden');

            // Tüm açık dropdownları kapat
            document.querySelectorAll('.nav-dropdown-menu').forEach(m => m.classList.add('hidden'));

            if (isHidden) menu.classList.remove('hidden');
            btn.setAttribute('aria-expanded', String(isHidden));
        });
    });
    </script>

    @yield('scripts')
</body>
</html>
