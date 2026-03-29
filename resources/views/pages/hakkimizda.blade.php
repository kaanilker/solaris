@extends('layouts.app')

@section('title', 'Hakkımızda — Solaris')
@section('meta_description', 'SOLARIS projesi hakkında bilgi, kullanılan kaynaklar ve formüller.')

@section('extra_styles')
/* Formula cards */
.formula-card {
    position: relative;
    overflow: hidden;
}
.formula-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981, #059669);
}

/* Source card hover */
.source-card {
    transition: all 0.3s ease;
}
.source-card:hover {
    transform: translateX(8px);
    border-left-color: rgba(99, 102, 241, 0.6);
}

/* Team member glow */
.member-avatar {
    position: relative;
}
.member-avatar::after {
    content: '';
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(99,102,241,0.4), rgba(168,85,247,0.4));
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s;
}
.member-card:hover .member-avatar::after {
    opacity: 1;
}

@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ════════════════════════════════════════════════════════
         HERO SECTION
    ════════════════════════════════════════════════════════ --}}
    <section class="glass rounded-3xl p-8 lg:p-12 relative overflow-hidden border border-white/10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-night-500/10 rounded-full blur-3xl"></div>
        
        {{-- Decorative grid --}}
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>

        <div class="relative z-10 max-w-3xl">
            <h1 class="font-display font-black text-4xl sm:text-5xl lg:text-6xl text-white leading-tight mb-6">
                <span class="text-emerald-400">S</span>OLARIS<br>
                <span class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-400">
                    Güneş Aktivitesi Canlı Alarm ve Risk İstihbarat Sistemi
                </span>
            </h1>

            <p class="text-slate-400 text-lg leading-relaxed mb-8">
                SOLARIS, güneş aktivitelerini gerçek zamanlı izleyen, analiz eden ve potansiyel riskleri 
                önceden tahmin eden kapsamlı bir uzay hava durumu platformudur. NASA ve NOAA verilerini 
                kullanarak dünya üzerindeki elektromanyetik etkileri görselleştirir.
            </p>

            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-slate-300">Açık Kaynak</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <svg class="w-5 h-5 text-night-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-sm text-slate-300">Gerçek Zamanlı</span>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span class="text-sm text-slate-300">AI Destekli</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         ÖZELLİKLER
    ════════════════════════════════════════════════════════ --}}
    <section class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $features = [
            ['Canlı İzleme', 'Güneş aktivitelerini anlık takip', 'M13 10V3L4 14h7v7l9-11h-7z', 'night'],
            ['Simülasyon', 'Tarihi fırtına senaryoları', 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'solar'],
            ['AI Analiz', 'Makine öğrenmesi tahminleri', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'purple'],
            ['Raporlama', 'Detaylı etki analizi raporları', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'emerald'],
        ];
        @endphp

        @foreach($features as [$title, $desc, $icon, $color])
        <div class="glass rounded-2xl p-6 border border-white/10 hover:border-{{ $color }}-500/30 transition-all duration-300 group">
            <div class="p-3 rounded-xl bg-{{ $color }}-500/10 w-fit mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 text-{{ $color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <h3 class="font-display font-bold text-lg text-white mb-2">{{ $title }}</h3>
            <p class="text-sm text-slate-400">{{ $desc }}</p>
        </div>
        @endforeach
    </section>

    {{-- ════════════════════════════════════════════════════════
         VERİ KAYNAKLARI
    ════════════════════════════════════════════════════════ --}}
    <section class="glass rounded-2xl p-6 lg:p-8 border border-white/10">
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 rounded-xl bg-night-500/10">
                <svg class="w-6 h-6 text-night-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display font-bold text-2xl text-white">Veri Kaynakları</h2>
                <p class="text-sm text-slate-400">Kullandığımız resmi ve güvenilir API kaynakları</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            @php
            $sources = [
                [
                    'name' => 'NOAA SWPC',
                    'desc' => 'Space Weather Prediction Center - Real-time solar data',
                    'url' => 'https://services.swpc.noaa.gov',
                    'data' => ['Kp Index', 'Solar Wind', 'X-Ray Flux', 'Proton Flux'],
                    'color' => 'cyan'
                ],
                [
                    'name' => 'Kyoto WDC',
                    'desc' => 'World Data Center for Geomagnetism - Dst, AE indices',
                    'url' => 'https://wdc.kugi.kyoto-u.ac.jp',
                    'data' => ['Dst Index', 'AE Index', 'SYM-H', 'ASY-H'],
                    'color' => 'blue'
                ],
                [
                    'name' => 'GFZ Potsdam',
                    'desc' => 'German Research Centre for Geosciences - Kp Index',
                    'url' => 'https://kp.gfz-potsdam.de',
                    'data' => ['Kp Nowcast', 'Ap Index'],
                    'color' => 'orange'
                ],
                [
                    'name' => 'ACE Real-Time',
                    'desc' => 'Advanced Composition Explorer - L1 Lagrange Point Data',
                    'url' => 'https://www.swpc.noaa.gov/products/ace-real-time-solar-wind',
                    'data' => ['IMF', 'Solar Wind Plasma', 'High Energy Particles'],
                    'color' => 'green'
                ],
            ];
            @endphp

            @foreach($sources as $source)
            <div class="source-card p-5 rounded-xl bg-white/5 border-l-4 border-{{ $source['color'] }}-500/30 hover:bg-white/[0.07]">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-semibold text-white">{{ $source['name'] }}</h3>
                    <a href="{{ $source['url'] }}" target="_blank" class="text-{{ $source['color'] }}-400 hover:text-{{ $source['color'] }}-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
                <p class="text-sm text-slate-400 mb-3">{{ $source['desc'] }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($source['data'] as $item)
                    <span class="px-2 py-0.5 rounded text-xs bg-{{ $source['color'] }}-500/10 text-{{ $source['color'] }}-400 border border-{{ $source['color'] }}-500/20">
                        {{ $item }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         FORMÜLLER
    ════════════════════════════════════════════════════════ --}}
    <section class="glass rounded-2xl p-6 lg:p-8 border border-white/10">
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 rounded-xl bg-emerald-500/10">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display font-bold text-2xl text-white">Kullanılan Formüller</h2>
                <p class="text-sm text-slate-400">Hesaplamalarda kullanılan 20 fizik denklemi</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @php
            $formulas = [
                ['1. Dinamik Basınç', 'pd = 0.5·nₚ·vₚ²·m', 'Güneş rüzgarı basıncı'],
                ['2. Bz İndeksi', 'i = (Bz - Bz₀) / k', 'IMF normalize değeri'],
                ['3. Etkin Basınç', 'pₑ = pd / (eⁱ + 1)', 'Yeniden bağlanma basıncı'],
                ['4. Bileşik Tehdit', 'Tc = f(Kp,Dst,vₚ,...)', 'Toplam risk skoru'],
                ['5. Hız Sapması', 'dx = vₚ - SMA', 'Ortalamadan sapma'],
                ['6. Euclidean Mesafe', 'dE = √(Σ(xᵢ-μᵢ)²/3)', 'Parametre mesafesi'],
                ['7. Benzerlik (q)', 'q = 1 - (dE / d̄)', 'Fuzzy benzerlik'],
                ['8. GTEC', 'GTEC = TEC·(φ·λ)/(φ·λ)', 'Küresel TEC'],
                ['9. dTEC', 'dTEC = TEC - CMA', 'TEC farkı'],
                ['10. ΔTEC %', 'Δ = (dTEC/CMA)·100', 'Yüzde değişim'],
                ['11. qTEC', 'qTEC = sakin referans', 'Sakin TEC'],
                ['12. fTEC', 'fTEC = qTEC + dTEC_ref', 'Fırtına TEC'],
                ['13. fTEC Ayarlı', 'fTEC_adj = fTEC + Δn', 'Düzeltilmiş fTEC'],
                ['14. Dst Gerçek', 'Dst = giriş değeri', 'Halka akım indeksi'],
                ['15. Dst*', 'Dst* = Dst - 0.2√pd + 20', 'Basınç düzeltmeli Dst'],
                ['16. dDst/dt', 'dDst/dt = Q(t) - Dst/τ', 'Dst türevi'],
                ['17. AE İndeksi', 'AE = AU - AL', 'Auroral elektrojet'],
                ['18. PC İndeksi', 'PC = Kp × 2.8', 'Polar cap indeksi'],
                ['19. dB/dt', 'dE = -dB/dt', 'Manyetik değişim'],
                ['20. Jeoelektrik Alan', 'E = |dB/dt| × 0.6', 'İndüklenmiş alan'],
            ];
            @endphp

            @foreach($formulas as [$name, $formula, $desc])
            <div class="formula-card glass rounded-lg p-3 border border-white/10 hover:border-emerald-500/30 transition-all">
                <h4 class="font-semibold text-white text-xs mb-2">{{ $name }}</h4>
                <div class="p-2 rounded bg-black/30 font-mono text-xs text-emerald-400 text-center mb-2 overflow-x-auto">
                    {{ $formula }}
                </div>
                <p class="text-[10px] text-slate-500 leading-tight">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         TEKNOLOJİ STACK
    ════════════════════════════════════════════════════════ --}}
    <section class="glass rounded-2xl p-6 lg:p-8 border border-white/10">
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 rounded-xl bg-purple-500/10">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display font-bold text-2xl text-white">Teknoloji Stack</h2>
                <p class="text-sm text-slate-400">Projede kullanılan teknolojiler ve araçlar</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
            $techs = [
                ['Laravel 11', 'Backend Framework', 'red'],
                ['PHP 8.2', 'Server Language', 'indigo'],
                ['Tailwind CSS', 'Styling', 'cyan'],
                ['Vite', 'Build Tool', 'yellow'],
                ['JSON', 'Data Storage', 'blue'],
                ['Chart.js', 'Visualization', 'pink'],
            ];
            @endphp

            @foreach($techs as [$name, $type, $color])
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center hover:border-{{ $color }}-500/30 transition-colors group">
                <p class="font-semibold text-white group-hover:text-{{ $color }}-400 transition-colors">{{ $name }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $type }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         REFERANSLAR
    ════════════════════════════════════════════════════════ --}}
    <section class="glass rounded-2xl p-6 lg:p-8 border border-white/10">
        <div class="flex items-center gap-3 mb-8">
            <div class="p-3 rounded-xl bg-solar-500/10">
                <svg class="w-6 h-6 text-solar-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display font-bold text-2xl text-white">Akademik Referanslar</h2>
                <p class="text-sm text-slate-400">Projenin temelini oluşturan bilimsel kaynaklar</p>
            </div>
        </div>

        <div class="space-y-4">
            @php
            $references = [
                'Gonzalez, W.D., et al. (1994). "What is a geomagnetic storm?" Journal of Geophysical Research.',
                'Bothmer, V., & Daglis, I.A. (2007). "Space Weather: Physics and Effects." Springer.',
                'Pulkkinen, T. (2007). "Space Weather: Terrestrial Perspective." Living Reviews in Solar Physics.',
                'Baker, D.N., et al. (2013). "A major solar eruptive event in July 2012." Space Weather.',
                'Schwenn, R. (2006). "Space Weather: The Solar Perspective." Living Reviews in Solar Physics.',
            ];
            @endphp

            @foreach($references as $i => $ref)
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 hover:bg-white/[0.07] transition-colors">
                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-solar-500/20 text-solar-400 flex items-center justify-center text-sm font-bold">
                    {{ $i + 1 }}
                </span>
                <p class="text-sm text-slate-300 leading-relaxed">{{ $ref }}</p>
            </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
