@extends('layouts.app')

@section('title', 'Canlı Güneş Verileri — Solaris')
@section('meta_description', 'Gerçek zamanlı güneş aktivitesi izleme ve analiz platformu.')

@section('extra_styles')
/* Skeleton loading */
@keyframes loadpulse {
    0%,100% { opacity: 1; }
    50% { opacity: .3; }
}
.skeleton {
    background: linear-gradient(90deg, rgba(255,255,255,0.03) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.03) 75%);
    background-size: 200% 100%;
    animation: loadpulse 1.2s infinite;
}

/* Data card hover */
.data-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.data-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(99, 102, 241, 0.15);
}

/* Live indicator */
@keyframes livePulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .3; }
}
.live-dot { animation: livePulse 1.5s ease-in-out infinite; }

/* Gradient border */
.gradient-border {
    position: relative;
}
.gradient-border::before {
    content: '';
    position: absolute;
    inset: 0;
    padding: 1px;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(99,102,241,0.5), rgba(251,176,34,0.5));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
}

/* 3D Solar System Canvas */
#solar-canvas-container {
    position: relative;
    width: 100%;
    height: 500px;
    border-radius: 1rem;
    overflow: hidden;
    background: radial-gradient(ellipse at center, #0a0a1a 0%, #000008 100%);
}
#solar-canvas {
    display: block;
    width: 100%;
    height: 100%;
    cursor: grab;
    touch-action: none;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}
#solar-canvas:active { cursor: grabbing; }
.solar-overlay {
    position: absolute;
    top: 12px;
    left: 12px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 10px;
    letter-spacing: 2px;
    color: rgba(100,160,255,0.6);
    text-transform: uppercase;
    pointer-events: none;
    z-index: 10;
}
.storm-indicator-3d {
    position: absolute;
    bottom: 12px;
    right: 12px;
    padding: 6px 14px;
    font-family: 'Share Tech Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    border: 1px solid;
    z-index: 10;
    transition: all 0.6s;
}
.storm-g0 { color: #00ff88; border-color: rgba(0,255,136,.4); background: rgba(0,255,136,.06); }
.storm-g1 { color: #bbff00; border-color: rgba(187,255,0,.4); background: rgba(187,255,0,.06); }
.storm-g2 { color: #ffcc00; border-color: rgba(255,204,0,.4); background: rgba(255,204,0,.06); }
.storm-g3 { color: #ff7700; border-color: rgba(255,119,0,.4); background: rgba(255,119,0,.08); }
.storm-g4 { color: #ff3300; border-color: rgba(255,51,0,.4); background: rgba(255,51,0,.08); }
.storm-g5 { color: #ff0000; border-color: rgba(255,0,0,.5); background: rgba(255,0,0,.1); animation: stormflash 1s infinite; }
@keyframes stormflash { 0%,100%{opacity:1} 50%{opacity:.5} }

/* Planet Info Panel */
#planet-info-panel {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%) translateX(110%);
    width: 200px;
    background: rgba(3,8,20,.95);
    border: 1px solid rgba(100,150,255,.25);
    border-right: none;
    padding: 16px 14px;
    z-index: 15;
    pointer-events: none;
    transition: transform .4s cubic-bezier(.4,0,.2,1);
    backdrop-filter: blur(10px);
    border-radius: 8px 0 0 8px;
}
#planet-info-panel.visible { 
    transform: translateY(-50%) translateX(0%); 
    pointer-events: all; 
}
#planet-info-panel.sun-active { border-color: rgba(255,180,50,.4); }
#planet-info-panel .pname { 
    font-family: 'Share Tech Mono', monospace; 
    font-size: 14px; 
    font-weight: 700; 
    letter-spacing: 2px; 
    margin-bottom: 12px; 
    border-bottom: 1px solid rgba(100,150,255,.2); 
    padding-bottom: 8px; 
}
#planet-info-panel .row { 
    display: flex; 
    justify-content: space-between; 
    align-items: baseline; 
    font-size: 10px; 
    margin-bottom: 6px; 
}
#planet-info-panel .row .key { color: rgba(255,255,255,.35); letter-spacing: 0.5px; }
#planet-info-panel .row .v { color: rgba(200,220,255,.9); font-family: 'Share Tech Mono', monospace; font-size: 9px; }
#planet-info-panel .close-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 11px;
    color: rgba(100,160,255,.5);
    transition: color .2s;
}
#planet-info-panel .close-btn:hover { color: #fff; }
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    {{-- ════════════════════════════════════════════════════════
         HERO SECTION - Canlı Durum
    ════════════════════════════════════════════════════════ --}}
    <section class="relative">
        <div class="glass gradient-border rounded-3xl p-8 lg:p-12 relative overflow-hidden">
            {{-- Animated background elements --}}
            <div class="absolute top-0 right-0 w-96 h-96 bg-night-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-solar-500/10 rounded-full blur-3xl"></div>

            {{-- Orbiting sun visualization --}}
            <div class="absolute top-1/2 right-12 -translate-y-1/2 w-64 h-64 hidden xl:block">
                <div class="relative w-full h-full">
                    {{-- Sun core --}}
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 rounded-full bg-gradient-to-br from-solar-300 via-solar-500 to-solar-700 shadow-glow-solar"></div>
                </div>
            </div>

            <div class="relative z-10 max-w-2xl">
                {{-- Live badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 mb-6">
                    <span class="live-dot w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="text-emerald-400 text-sm font-semibold tracking-wide">CANLI VERİ AKIŞI</span>
                </div>

                <h1 class="font-display font-black text-4xl sm:text-5xl lg:text-6xl text-white leading-tight mb-4">
                    <span class="text-solar-400">S</span>OLARIS<br>
                    <span class="bg-gradient-to-r from-night-400 via-purple-400 to-solar-400 bg-clip-text text-transparent text-2xl sm:text-3xl lg:text-4xl">
                        Güneş Aktivitesi Canlı Alarm ve Risk İstihbarat Sistemi
                    </span>
                </h1>

                <p class="text-slate-400 text-lg leading-relaxed mb-8">
                    NASA ve NOAA kaynaklarından gerçek zamanlı güneş verileri. Güneş patlamaları, 
                    CME olayları ve jeomanyetik aktiviteleri anlık takip edin.
                </p>

                {{-- Quick stats --}}
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10">
                        <span class="text-sm text-slate-300">Son Güncelleme: <span class="text-night-400 font-semibold" id="last-update">--:--</span></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         REAL-TIME PARAMETER CARDS (En Önemli 10 Parametre)
    ════════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-4">
            <h2 class="font-display font-bold text-xl text-white">Anlık Parametreler</h2>
            <p class="text-sm text-slate-400">Hesaplama için kullanılan kritik güneş aktivitesi verileri</p>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @php
            $dataCards = [
                [
                    'id' => 'kp-index',
                    'label' => 'Kp İndeksi',
                    'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'color' => 'night',
                    'unit' => '',
                    'desc' => 'Jeomanyetik aktivite'
                ],
                [
                    'id' => 'dst-index',
                    'label' => 'Dst İndeksi',
                    'icon' => 'M19 14l-7 7m0 0l-7-7m7 7V3',
                    'color' => 'blue',
                    'unit' => 'nT',
                    'desc' => 'Manyetik fırtına şiddeti'
                ],
                [
                    'id' => 'solar-wind',
                    'label' => 'Güneş Rüzgarı',
                    'icon' => 'M14 5l7 7m0 0l-7 7m7-7H3',
                    'color' => 'solar',
                    'unit' => 'km/s',
                    'desc' => 'Plazma akış hızı'
                ],
                [
                    'id' => 'bz-field',
                    'label' => 'IMF Bz',
                    'icon' => 'M7 11l5-5m0 0l5 5m-5-5v12',
                    'color' => 'cyan',
                    'unit' => 'nT',
                    'desc' => 'Kuzey-Güney bileşen'
                ],
                [
                    'id' => 'xray-flux',
                    'label' => 'X-Ray Akısı',
                    'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                    'color' => 'purple',
                    'unit' => '',
                    'desc' => 'GOES ölçümü'
                ],
                [
                    'id' => 'proton-flux',
                    'label' => 'Proton Akısı',
                    'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
                    'color' => 'emerald',
                    'unit' => 'pfu',
                    'desc' => '>10 MeV partiküller'
                ],
                [
                    'id' => 'f107-flux',
                    'label' => 'F10.7 Akısı',
                    'icon' => 'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z',
                    'color' => 'orange',
                    'unit' => 'sfu',
                    'desc' => 'Radyo akısı'
                ],
                [
                    'id' => 'ae-index',
                    'label' => 'AE İndeksi',
                    'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
                    'color' => 'rose',
                    'unit' => 'nT',
                    'desc' => 'Auroral aktivite'
                ],
                [
                    'id' => 'plasma-density',
                    'label' => 'Plazma Yoğunluğu',
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'color' => 'teal',
                    'unit' => 'p/cm³',
                    'desc' => 'Proton yoğunluğu'
                ],
                [
                    'id' => 'bt-field',
                    'label' => 'IMF Bt',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'color' => 'indigo',
                    'unit' => 'nT',
                    'desc' => 'Toplam manyetik alan'
                ],
            ];
            @endphp

            @foreach($dataCards as $card)
            <div class="data-card glass rounded-xl p-4 border border-white/10 hover:border-{{ $card['color'] }}-500/30 cursor-default" id="card-{{ $card['id'] }}">
                <div class="flex items-center justify-between mb-3">
                    <div class="p-2 rounded-lg bg-{{ $card['color'] }}-500/10">
                        <svg class="w-4 h-4 text-{{ $card['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="live-dot w-1.5 h-1.5 rounded-full bg-{{ $card['color'] }}-400"></span>
                        <span class="text-[11px]">Canlı</span>
                    </span>
                </div>

                {{-- Value (skeleton while loading) --}}
                <div class="mb-1">
                    <div class="skeleton h-7 w-full rounded-md" id="value-{{ $card['id'] }}"></div>
                </div>

                <p class="text-xs font-medium text-slate-300">{{ $card['label'] }}</p>
                <p class="text-[11px] text-slate-500 mt-1">{{ $card['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         BULANIK MANTIK TEHDİT ANALİZİ
    ════════════════════════════════════════════════════════ --}}
    <section>
        <div id="fuzzy-logic-section" class="glass rounded-xl p-5 border border-purple-500/30 bg-purple-500/5">
            <h3 class="font-display font-semibold text-purple-300 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                Bütünsel Fırtına Tehdit Analizi
            </h3>
            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div class="glass p-3 rounded-lg border border-purple-500/20 text-center">
                    <div class="text-[10px] text-purple-300/70 uppercase tracking-wider mb-1">Manyetik Etki</div>
                    <div class="text-xl font-bold text-purple-100" id="fuz-mag">--%</div>
                </div>
                <div class="glass p-3 rounded-lg border border-purple-500/20 text-center">
                    <div class="text-[10px] text-purple-300/70 uppercase tracking-wider mb-1">Kinetik Güç</div>
                    <div class="text-xl font-bold text-purple-100" id="fuz-kin">--%</div>
                </div>
                <div class="glass p-3 rounded-lg border border-purple-500/20 text-center">
                    <div class="text-[10px] text-purple-300/70 uppercase tracking-wider mb-1">Foton/Radyasyon</div>
                    <div class="text-xl font-bold text-purple-100" id="fuz-rad">--%</div>
                </div>
                <div class="glass p-3 rounded-lg border border-purple-500/20 text-center">
                    <div class="text-[10px] text-purple-300/70 uppercase tracking-wider mb-1">İyonosferik</div>
                    <div class="text-xl font-bold text-purple-100" id="fuz-ion">--%</div>
                </div>
            </div>

            <div id="fuzzy-similarity-box" class="glass p-4 text-center rounded-xl bg-purple-900/40 border border-purple-500/50 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-purple-500/10 to-transparent flex items-center" style="transform: skewX(-20deg);"></div>
                <div class="text-xs text-purple-300 mb-1 relative z-10">Teorik Bulanık Mantık Mükemmel Fırtına Potansiyeline Uyum Yüzdesi</div>
                <div class="text-4xl font-black text-white relative z-10" id="perfect-storm-similarity">%0.0</div>
            </div>

            <div id="weighted-cosine-box" class="mt-3 glass p-4 text-center rounded-xl bg-sky-900/30 border border-sky-500/40 relative overflow-hidden">
                <div class="text-xs text-sky-300 mb-1 relative z-10">Ağırlıklı Kosinüs Benzerliği (Tarihi Fırtına Eşleşmesi)</div>
                <div class="text-lg font-bold text-white relative z-10" id="weighted-cosine-storm-name">--</div>
                <div class="text-3xl font-black text-sky-200 relative z-10 mt-1" id="weighted-cosine-score">%0.0</div>
            </div>

            <div id="final-risk-box" class="mt-4 mx-auto w-full max-w-md p-[1px] rounded-2xl bg-gradient-to-r from-rose-500/50 via-fuchsia-400/40 to-amber-300/40 shadow-[0_8px_30px_rgba(244,63,94,0.25)]">
                <div class="relative overflow-hidden rounded-2xl bg-slate-900/80 backdrop-blur-sm px-5 py-4 text-center border border-white/10">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-rose-500/10 to-transparent" style="transform: skewX(-20deg);"></div>
                    <div class="relative z-10 text-[11px] text-rose-200/90 uppercase tracking-[0.18em] mb-2">Nihai Risk</div>
                    <div class="relative z-10 text-4xl sm:text-5xl font-black text-white drop-shadow-[0_0_16px_rgba(251,113,133,0.45)]" id="final-risk-score">0.000</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         LLM YORUM BÖLÜMÜ
    ════════════════════════════════════════════════════════ --}}
    <section>
        <div id="llm-storm-actions" class="glass rounded-xl p-5 border border-white/10">
            <div class="flex items-center gap-2 mb-3">
                <div class="p-1.5 rounded-lg bg-emerald-500/10">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-white">Fırtına Bilgi Paneli</h3>
            </div>
            <div id="ai-result" class="p-4 rounded-lg bg-white/5 border border-emerald-400/20 shadow-lg shadow-emerald-500/10 min-h-[150px] relative">
                <div id="ai-loading" class="text-center py-8 flex flex-col items-center justify-center">
                    <div class="animate-spin w-10 h-10 border-4 border-emerald-400 border-t-transparent rounded-full mb-3 shadow-[0_0_15px_rgba(52,211,153,0.5)]"></div>
                    <p class="text-emerald-400/80 font-mono text-sm tracking-widest uppercase animate-pulse">Solaris Yapay Zeka Analizi</p>
                    <p class="text-slate-400 text-xs mt-1">Canlı Veriler Analiz Ediliyor...</p>
                </div>
                <div id="ai-content" class="hidden prose prose-invert prose-sm max-w-none prose-p:text-slate-300 prose-ul:text-slate-300">
                    {{-- AWS Bedrock yanıtı buraya yazılacak --}}
                </div>
            </div>
        </div>
    </section>

    {{-- ════════════════════════════════════════════════════════
         3D GÜNEŞ SİSTEMİ ANİMASYONU
    ════════════════════════════════════════════════════════ --}}
    <section>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-display font-bold text-xl text-white">Canlı Güneş Sistemi Simülasyonu</h2>
                <p class="text-sm text-slate-400">Gerçek zamanlı verilerle senkronize 3D görselleştirme</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                </svg>
                <span>Tıkla: Bilgi • Sürükle: Döndür • Scroll/Pinch: Yakınlaştır</span>
            </div>
        </div>
        
        <div class="glass rounded-2xl border border-white/10 overflow-hidden">
            <div id="solar-canvas-container">
                <div class="solar-overlay">
                    <span class="live-dot w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block mr-2"></span>
                    CANLI SİMÜLASYON
                </div>
                <div id="storm-badge-3d" class="storm-indicator-3d storm-g0">G0</div>
                
                {{-- Planet Info Panel --}}
                <div id="planet-info-panel">
                    <span class="close-btn" id="planet-info-close">✕</span>
                    <div class="pname" id="planet-name">DÜNYA</div>
                    <div id="planet-rows"></div>
                </div>
                
                <canvas id="solar-canvas"></canvas>
            </div>
        </div>
    </section>

</div>

@endsection

@section('scripts')
{{-- Three.js CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script>
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SOLARIS 3D GÜNEŞ SİSTEMİ ANİMASYONU
 * Canlı verilerle senkronize Three.js görselleştirme
 * ═══════════════════════════════════════════════════════════════════════════
 */
const SolarSystem3D = {
    scene: null,
    camera: null,
    renderer: null,
    sunGroup: null,
    coronaMeshes: [],
    swGeo: null,
    swPos: null,
    swVel: null,
    swLife: null,
    planetMeshes: [],
    planetAngles: [],
    animationId: null,
    clock: null,
    elapsed: 0,
    stormLevel: 0,
    sunLight: null,
    swMat: null,
    
    // Canlı veri parametreleri
    liveData: {
        kp: 2,
        dst: -10,
        vp: 400,
        np: 10,
        bz: 0
    },
    
    init: function() {
        const container = document.getElementById('solar-canvas-container');
        const canvas = document.getElementById('solar-canvas');
        if (!canvas || !container) return;
        
        // Renderer
        this.renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.setSize(container.clientWidth, container.clientHeight);
        this.renderer.setClearColor(0x00000a, 1);
        
        // Scene
        this.scene = new THREE.Scene();
        this.clock = new THREE.Clock();
        
        // Camera
        this.camera = new THREE.PerspectiveCamera(50, container.clientWidth / container.clientHeight, 0.01, 5000);
        this.camera.position.set(80, 40, 80);
        this.camera.lookAt(0, 0, 0);
        
        // Lights
        const sunLight = new THREE.PointLight(0xfff0cc, 4.0, 600);
        sunLight.position.set(0, 0, 0);
        this.scene.add(sunLight);
        this.sunLight = sunLight;
        this.scene.add(new THREE.AmbientLight(0x0d0d1a, 2.5));
        
        // Stars
        this.createStars();
        
        // Sun
        this.createSun();
        
        // Solar Wind
        this.createSolarWind();
        
        // Orbits
        this.createOrbits();
        
        // Planets
        this.createPlanets();
        
        // Controls
        this.setupControls(canvas);
        
        // Resize
        window.addEventListener('resize', () => this.onResize(container));
        
        // Start animation
        this.animate();
    },
    
    createStars: function() {
        const N = 8000;
        const pos = new Float32Array(N * 3);
        for (let i = 0; i < N; i++) {
            const r = 400 + Math.random() * 600;
            const t = Math.random() * Math.PI * 2;
            const p = Math.acos(2 * Math.random() - 1);
            pos[i * 3] = r * Math.sin(p) * Math.cos(t);
            pos[i * 3 + 1] = r * Math.sin(p) * Math.sin(t);
            pos[i * 3 + 2] = r * Math.cos(p);
        }
        const geo = new THREE.BufferGeometry();
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        this.scene.add(new THREE.Points(geo, new THREE.PointsMaterial({
            color: 0xffffff, size: 0.5, sizeAttenuation: true, transparent: true, opacity: 0.85
        })));
    },
    
    createSun: function() {
        this.sunGroup = new THREE.Group();
        this.scene.add(this.sunGroup);
        
        // Sun core
        this.sunCore = new THREE.Mesh(
            new THREE.SphereGeometry(4.5, 64, 64),
            new THREE.MeshBasicMaterial({ color: 0xffd060 })
        );
        this.sunGroup.add(this.sunCore);
        this.sunGLBMeshes = [];
        
        // Load GLB model
        const gltfLoader = new THREE.GLTFLoader();
        const modelsPath = window.SOLARIS_BASE ? window.SOLARIS_BASE + '/models/' : '/models/';
        
        gltfLoader.load(modelsPath + 'sun.glb', (gltf) => {
            const m = gltf.scene;
            const box = new THREE.Box3().setFromObject(m);
            const sz = new THREE.Vector3();
            box.getSize(sz);
            const sf = 9.0 / Math.max(sz.x, sz.y, sz.z);
            m.scale.setScalar(sf);
            const c = new THREE.Vector3();
            box.getCenter(c);
            m.position.sub(c.multiplyScalar(sf));
            m.traverse(ch => {
                if (ch.isMesh) {
                    ch.material = new THREE.MeshBasicMaterial({
                        map: ch.material.map || null,
                        color: ch.material.map ? 0xffffff : 0xffd060
                    });
                    this.sunGLBMeshes.push(ch);
                }
            });
            this.sunCore.visible = false;
            this.sunGroup.add(m);
        }, undefined, () => { this.sunCore.visible = true; });
        
        // Corona
        const coronaData = [[5.2, 0xff9900, 0.18], [6.5, 0xff6600, 0.10], [9.0, 0xff3300, 0.05], [14, 0xff1100, 0.025]];
        this.coronaMeshes = coronaData.map(([r, c, o]) => {
            const m = new THREE.Mesh(
                new THREE.SphereGeometry(r, 32, 32),
                new THREE.MeshBasicMaterial({ color: c, transparent: true, opacity: o, side: THREE.BackSide, blending: THREE.AdditiveBlending })
            );
            this.sunGroup.add(m);
            return m;
        });
    },
    
    createSolarWind: function() {
        const SW_N = 1200;
        this.swPos = new Float32Array(SW_N * 3);
        this.swVel = new Float32Array(SW_N * 3);
        this.swLife = new Float32Array(SW_N);
        const swColArr = new Float32Array(SW_N * 3);
        const swColors = [0xff2200, 0xff6600, 0xff9900, 0xffcc00, 0xffee44];
        
        const spawnWind = (i) => {
            const t = Math.random() * Math.PI * 2;
            const p = (Math.random() - 0.5) * Math.PI * 0.65;
            const r = 5 + Math.random() * 0.8;
            this.swPos[i * 3] = r * Math.cos(t) * Math.cos(p);
            this.swPos[i * 3 + 1] = r * Math.sin(p) * 0.6;
            this.swPos[i * 3 + 2] = r * Math.sin(t) * Math.cos(p);
            const sp = 0.04 + Math.random() * 0.07;
            const n = Math.hypot(this.swPos[i * 3], this.swPos[i * 3 + 1], this.swPos[i * 3 + 2]);
            this.swVel[i * 3] = this.swPos[i * 3] / n * sp;
            this.swVel[i * 3 + 1] = this.swPos[i * 3 + 1] / n * sp * 0.2;
            this.swVel[i * 3 + 2] = this.swPos[i * 3 + 2] / n * sp;
            this.swLife[i] = Math.random();
            
            const col = new THREE.Color(swColors[Math.floor(Math.random() * swColors.length)]);
            swColArr[i * 3] = col.r;
            swColArr[i * 3 + 1] = col.g;
            swColArr[i * 3 + 2] = col.b;
        };
        
        for (let i = 0; i < SW_N; i++) {
            spawnWind(i);
            const f = 1 + Math.random() * 18;
            this.swPos[i * 3] *= f;
            this.swPos[i * 3 + 2] *= f;
        }
        
        this.swGeo = new THREE.BufferGeometry();
        this.swGeo.setAttribute('position', new THREE.BufferAttribute(this.swPos, 3));
        this.swGeo.setAttribute('color', new THREE.BufferAttribute(swColArr, 3));
        
        const swMat = new THREE.PointsMaterial({
            vertexColors: true, size: 0.16, sizeAttenuation: true,
            transparent: true, opacity: 0.7, blending: THREE.AdditiveBlending, depthWrite: false
        });
        this.swMat = swMat;
        this.scene.add(new THREE.Points(this.swGeo, swMat));
        
        this.spawnWind = spawnWind;
    },
    
    createOrbits: function() {
        const makeOrbit = (r) => {
            const pts = [];
            for (let i = 0; i <= 256; i++) {
                const a = (i / 256) * Math.PI * 2;
                pts.push(new THREE.Vector3(Math.cos(a) * r, 0, Math.sin(a) * r));
            }
            return new THREE.Line(
                new THREE.BufferGeometry().setFromPoints(pts),
                new THREE.LineBasicMaterial({ color: 0x334466, transparent: true, opacity: 0.35 })
            );
        };
        
        const orbitRadii = [8, 13.5, 19, 26, 43, 62, 80, 96];
        orbitRadii.forEach(r => this.scene.add(makeOrbit(r)));
        
        // Asteroid belt
        const N = 1200;
        const pos = new Float32Array(N * 3);
        for (let i = 0; i < N; i++) {
            const a = Math.random() * Math.PI * 2;
            const r = 31 + Math.random() * 6;
            const y = (Math.random() - 0.5) * 1.2;
            pos[i * 3] = Math.cos(a) * r;
            pos[i * 3 + 1] = y;
            pos[i * 3 + 2] = Math.sin(a) * r;
        }
        const geo = new THREE.BufferGeometry();
        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        this.scene.add(new THREE.Points(geo, new THREE.PointsMaterial({
            color: 0x888888, size: 0.12, transparent: true, opacity: 0.55
        })));
    },
    
    createPlanets: function() {
        const PLANETS = [
            { name: 'Merkür', r: 0.38, orbitR: 8, color: 0xb5b5b5, speed: 4.74, tilt: 0.034, model: 'mercury1.glb', info: {'Yarıçap':'2,439 km','Uzaklık':'57.9 M km','Yörünge':'88 gün','Yüzey':'-180/+430°C','Uydu':'0','Tip':'Kayalık'} },
            { name: 'Venüs', r: 0.95, orbitR: 13.5, color: 0xe8c97a, speed: 3.50, tilt: 177.4, model: 'venus.glb', info: {'Yarıçap':'6,051 km','Uzaklık':'108.2 M km','Yörünge':'225 gün','Yüzey':'+465°C','Uydu':'0','Tip':'Kayalık'} },
            { name: 'Dünya', r: 1.0, orbitR: 19, color: 0x2a7dc2, speed: 2.98, tilt: 23.4, model: 'earth.glb', info: {'Yarıçap':'6,371 km','Uzaklık':'149.6 M km','Yörünge':'365.25 gün','Yüzey':'-88/+58°C','Uydu':'1','Tip':'Kayalık'} },
            { name: 'Mars', r: 0.53, orbitR: 26, color: 0xc1440e, speed: 2.41, tilt: 25.2, model: 'mars.glb', info: {'Yarıçap':'3,389 km','Uzaklık':'227.9 M km','Yörünge':'687 gün','Yüzey':'-125/+20°C','Uydu':'2','Tip':'Kayalık'} },
            { name: 'Jüpiter', r: 3.2, orbitR: 43, color: 0xc88b3a, speed: 1.31, tilt: 3.1, model: 'jupiter.glb', info: {'Yarıçap':'69,911 km','Uzaklık':'778.5 M km','Yörünge':'11.86 yıl','Yüzey':'-110°C','Uydu':'95','Tip':'Gaz devi'} },
            { name: 'Satürn', r: 2.6, orbitR: 62, color: 0xe4d191, speed: 0.97, tilt: 26.7, model: 'saturn.glb', info: {'Yarıçap':'58,232 km','Uzaklık':'1,432 M km','Yörünge':'29.46 yıl','Yüzey':'-140°C','Uydu':'146','Tip':'Gaz devi'} },
            { name: 'Uranüs', r: 1.8, orbitR: 80, color: 0x7de8e8, speed: 0.68, tilt: 97.8, model: 'uranus.glb', info: {'Yarıçap':'25,362 km','Uzaklık':'2,867 M km','Yörünge':'84.01 yıl','Yüzey':'-195°C','Uydu':'28','Tip':'Buz devi'} },
            { name: 'Neptün', r: 1.7, orbitR: 96, color: 0x4b70dd, speed: 0.54, tilt: 28.3, model: 'neptune.glb', info: {'Yarıçap':'24,622 km','Uzaklık':'4,495 M km','Yörünge':'164.8 yıl','Yüzey':'-200°C','Uydu':'16','Tip':'Buz devi'} }
        ];
        
        // Sun info
        this.sunInfo = {'Tip':'G2V Sarı Cüce','Yaş':'~4.6 Milyar Yıl','Yarıçap':'696,000 km','Kütle':'1.989×10³⁰ kg','Yüzey °C':'~5,500','Çekirdek °C':'~15,000,000'};
        
        const gltfLoader = new THREE.GLTFLoader();
        const modelsPath = window.SOLARIS_BASE ? window.SOLARIS_BASE + '/models/' : '/models/';
        
        PLANETS.forEach((pd, i) => {
            const group = new THREE.Group();
            this.scene.add(group);
            
            const fb = new THREE.Mesh(
                new THREE.SphereGeometry(pd.r, 48, 48),
                new THREE.MeshPhongMaterial({ color: pd.color, emissive: 0x111111, emissiveIntensity: 0.4, shininess: 20 })
            );
            fb.rotation.z = THREE.MathUtils.degToRad(pd.tilt);
            group.add(fb);
            
            this.planetMeshes.push({ group, mesh: fb, data: pd, raycastMeshes: [fb] });
            this.planetAngles.push(Math.random() * Math.PI * 2);
            
            // Load GLB
            gltfLoader.load(modelsPath + pd.model, (gltf) => {
                const model = gltf.scene;
                const box = new THREE.Box3().setFromObject(model);
                const size = new THREE.Vector3();
                box.getSize(size);
                const sf = (pd.r * 2) / Math.max(size.x, size.y, size.z);
                model.scale.setScalar(sf);
                const c = new THREE.Vector3();
                box.getCenter(c);
                model.position.sub(c.multiplyScalar(sf));
                model.rotation.z = THREE.MathUtils.degToRad(pd.tilt);
                group.remove(fb);
                group.add(model);
                this.planetMeshes[i].mesh = model;
                this.planetMeshes[i].raycastMeshes = [];
                model.traverse(ch => { if (ch.isMesh) this.planetMeshes[i].raycastMeshes.push(ch); });
            }, undefined, () => {});
        });
    },
    
    setupControls: function(canvas) {
        let camTheta = 0.4, camPhi = 0.52, camRadius = 100;
        let targetTheta = 0.4, targetPhi = 0.52, targetRadius = 100;
        let drag = false, mx0 = 0, my0 = 0;
        let pinchDist0 = 0;
        let mouseStill = true, mouseDownPos = { x: 0, y: 0 };
        let lockedPlanet = null, sunLocked = false;
        let targetLookAt = new THREE.Vector3();
        let currentLookAt = new THREE.Vector3();
        
        // Raycaster
        const raycaster = new THREE.Raycaster();
        const mouse2d = new THREE.Vector2();
        
        // Info panel elements
        const infoPanel = document.getElementById('planet-info-panel');
        const planetName = document.getElementById('planet-name');
        const planetRows = document.getElementById('planet-rows');
        const closeBtn = document.getElementById('planet-info-close');
        
        // Close button
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                infoPanel.classList.remove('visible', 'sun-active');
                lockedPlanet = null;
                sunLocked = false;
            });
        }
        
        const showSunInfo = () => {
            sunLocked = true;
            lockedPlanet = null;
            infoPanel.classList.add('sun-active', 'visible');
            planetName.textContent = '☉ GÜNEŞ';
            planetName.style.color = '#ffd060';
            planetRows.innerHTML = Object.entries(this.sunInfo).map(([k, v]) => 
                `<div class="row"><span class="key">${k}</span><span class="v">${v}</span></div>`
            ).join('');
            targetRadius = 22;
            targetLookAt.set(0, 0, 0);
        };
        
        const showPlanetInfo = (idx) => {
            const pm = this.planetMeshes[idx];
            if (!pm) return;
            sunLocked = false;
            lockedPlanet = idx;
            infoPanel.classList.remove('sun-active');
            infoPanel.classList.add('visible');
            planetName.textContent = pm.data.name.toUpperCase();
            planetName.style.color = '#' + pm.data.color.toString(16).padStart(6, '0');
            planetRows.innerHTML = Object.entries(pm.data.info).map(([k, v]) => 
                `<div class="row"><span class="key">${k}</span><span class="v">${v}</span></div>`
            ).join('');
            targetRadius = Math.max(pm.data.r * 5, Math.min(25, pm.data.r * 7));
        };
        
        const applyCamera = () => {
            const x = camRadius * Math.sin(camTheta) * Math.cos(camPhi);
            const y = camRadius * Math.sin(camPhi);
            const z = camRadius * Math.cos(camTheta) * Math.cos(camPhi);
            this.camera.position.set(currentLookAt.x + x, currentLookAt.y + y, currentLookAt.z + z);
            this.camera.lookAt(currentLookAt);
        };
        
        // Mouse controls
        canvas.addEventListener('mousedown', (e) => { 
            drag = true; 
            mx0 = e.clientX; 
            my0 = e.clientY;
            mouseDownPos.x = e.clientX;
            mouseDownPos.y = e.clientY;
            mouseStill = true;
            canvas.style.cursor = 'grabbing';
        });
        canvas.addEventListener('contextmenu', (e) => e.preventDefault());
        window.addEventListener('mouseup', () => { 
            drag = false; 
            canvas.style.cursor = 'grab';
        });
        window.addEventListener('mousemove', (e) => {
            // Check if mouse moved significantly
            if (Math.hypot(e.clientX - mouseDownPos.x, e.clientY - mouseDownPos.y) > 4) {
                mouseStill = false;
            }
            
            // Update mouse2d for raycaster
            const rect = canvas.getBoundingClientRect();
            mouse2d.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            mouse2d.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
            
            // Update cursor based on hover
            raycaster.setFromCamera(mouse2d, this.camera);
            const sunTargets = this.sunGLBMeshes && this.sunGLBMeshes.length ? this.sunGLBMeshes : [this.sunCore];
            const allPlanetMeshes = [];
            this.planetMeshes.forEach(pm => {
                if (pm.raycastMeshes && pm.raycastMeshes.length) allPlanetMeshes.push(...pm.raycastMeshes);
                else if (pm.mesh && pm.mesh.isMesh) allPlanetMeshes.push(pm.mesh);
            });
            
            if (!drag) {
                const hitSun = raycaster.intersectObjects(sunTargets, false).length > 0;
                const hitPlanet = raycaster.intersectObjects(allPlanetMeshes, false).length > 0;
                canvas.style.cursor = (hitSun || hitPlanet) ? 'pointer' : 'grab';
            }
            
            if (!drag) return;
            const dx = e.clientX - mx0, dy = e.clientY - my0;
            mx0 = e.clientX; my0 = e.clientY;
            targetTheta -= dx * 0.004;
            targetPhi = Math.max(0.05, Math.min(1.45, targetPhi - dy * 0.004));
        });
        
        // Click detection
        canvas.addEventListener('mouseup', (e) => {
            if (!mouseStill || e.button !== 0) return;
            
            const rect = canvas.getBoundingClientRect();
            mouse2d.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
            mouse2d.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
            raycaster.setFromCamera(mouse2d, this.camera);
            
            // Check sun
            const sunTargets = this.sunGLBMeshes && this.sunGLBMeshes.length ? this.sunGLBMeshes : [this.sunCore];
            if (raycaster.intersectObjects(sunTargets, false).length > 0) {
                showSunInfo();
                return;
            }
            
            // Check planets
            let hitIdx = -1, closestDist = Infinity;
            this.planetMeshes.forEach((pm, i) => {
                const meshes = (pm.raycastMeshes && pm.raycastMeshes.length) ? pm.raycastMeshes : 
                               (pm.mesh && pm.mesh.isMesh ? [pm.mesh] : []);
                if (!meshes.length) return;
                const hits = raycaster.intersectObjects(meshes, false);
                if (hits.length && hits[0].distance < closestDist) {
                    closestDist = hits[0].distance;
                    hitIdx = i;
                }
            });
            
            if (hitIdx !== -1) {
                showPlanetInfo(hitIdx);
            } else if (infoPanel.classList.contains('visible')) {
                infoPanel.classList.remove('visible', 'sun-active');
                lockedPlanet = null;
                sunLocked = false;
            }
        });
        
        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            targetRadius = Math.max(15, Math.min(200, targetRadius + e.deltaY * 0.06));
        }, { passive: false });
        
        // Touch controls
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            if (e.touches.length === 1) {
                drag = true;
                mx0 = e.touches[0].clientX;
                my0 = e.touches[0].clientY;
                mouseDownPos.x = e.touches[0].clientX;
                mouseDownPos.y = e.touches[0].clientY;
                mouseStill = true;
            } else if (e.touches.length === 2) {
                drag = false;
                const dx = e.touches[1].clientX - e.touches[0].clientX;
                const dy = e.touches[1].clientY - e.touches[0].clientY;
                pinchDist0 = Math.hypot(dx, dy);
            }
        }, { passive: false });
        
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (e.touches.length === 1 && drag) {
                const dx = e.touches[0].clientX - mx0;
                const dy = e.touches[0].clientY - my0;
                if (Math.hypot(dx, dy) > 4) mouseStill = false;
                mx0 = e.touches[0].clientX;
                my0 = e.touches[0].clientY;
                targetTheta -= dx * 0.006;
                targetPhi = Math.max(0.05, Math.min(1.45, targetPhi - dy * 0.006));
            } else if (e.touches.length === 2) {
                const dx = e.touches[1].clientX - e.touches[0].clientX;
                const dy = e.touches[1].clientY - e.touches[0].clientY;
                const pinchDist = Math.hypot(dx, dy);
                const delta = pinchDist0 - pinchDist;
                targetRadius = Math.max(15, Math.min(200, targetRadius + delta * 0.15));
                pinchDist0 = pinchDist;
            }
        }, { passive: false });
        
        // Touch tap detection
        canvas.addEventListener('touchend', (e) => {
            if (e.touches.length === 0 && mouseStill) {
                const rect = canvas.getBoundingClientRect();
                mouse2d.x = ((mouseDownPos.x - rect.left) / rect.width) * 2 - 1;
                mouse2d.y = -((mouseDownPos.y - rect.top) / rect.height) * 2 + 1;
                raycaster.setFromCamera(mouse2d, this.camera);
                
                // Check sun
                const sunTargets = this.sunGLBMeshes && this.sunGLBMeshes.length ? this.sunGLBMeshes : [this.sunCore];
                if (raycaster.intersectObjects(sunTargets, false).length > 0) {
                    showSunInfo();
                    drag = false;
                    return;
                }
                
                // Check planets
                let hitIdx = -1, closestDist = Infinity;
                this.planetMeshes.forEach((pm, i) => {
                    const meshes = (pm.raycastMeshes && pm.raycastMeshes.length) ? pm.raycastMeshes : 
                                   (pm.mesh && pm.mesh.isMesh ? [pm.mesh] : []);
                    if (!meshes.length) return;
                    const hits = raycaster.intersectObjects(meshes, false);
                    if (hits.length && hits[0].distance < closestDist) {
                        closestDist = hits[0].distance;
                        hitIdx = i;
                    }
                });
                
                if (hitIdx !== -1) {
                    showPlanetInfo(hitIdx);
                }
            }
            
            if (e.touches.length === 0) {
                drag = false;
            } else if (e.touches.length === 1) {
                drag = true;
                mx0 = e.touches[0].clientX;
                my0 = e.touches[0].clientY;
            }
        });
        
        // Set initial cursor
        canvas.style.cursor = 'grab';
        
        // Store for camera tracking
        this.lockedPlanet = () => lockedPlanet;
        this.sunLocked = () => sunLocked;
        this.targetLookAt = targetLookAt;
        this.currentLookAt = currentLookAt;
        
        // Update camera in animation loop
        this.updateCamera = () => {
            // Update lookAt for locked planet
            if (lockedPlanet !== null && this.planetMeshes[lockedPlanet]) {
                targetLookAt.lerp(this.planetMeshes[lockedPlanet].group.position, 0.06);
            } else if (sunLocked) {
                targetLookAt.lerp(new THREE.Vector3(0, 0, 0), 0.08);
            } else {
                targetLookAt.lerp(new THREE.Vector3(0, 0, 0), 0.04);
            }
            
            camTheta += (targetTheta - camTheta) * 0.08;
            camPhi += (targetPhi - camPhi) * 0.08;
            camRadius += (targetRadius - camRadius) * 0.08;
            currentLookAt.lerp(targetLookAt, 0.08);
            applyCamera();
        };
    },
    
    onResize: function(container) {
        this.camera.aspect = container.clientWidth / container.clientHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(container.clientWidth, container.clientHeight);
    },
    
    updateFromLiveData: function(data) {
        if (data.kp !== undefined) this.liveData.kp = data.kp;
        if (data.dst !== undefined) this.liveData.dst = data.dst;
        if (data.vp !== undefined) this.liveData.vp = data.vp;
        if (data.np !== undefined) this.liveData.np = data.np;
        if (data.bz !== undefined) this.liveData.bz = data.bz;
        
        // Update storm level (index.html ile aynı mantık: Dst + Kp -> max)
        const kp = this.liveData.kp;
        const dst = this.liveData.dst;
        let lD = 0;
        if (dst <= -350) lD = 5;
        else if (dst <= -200) lD = 4;
        else if (dst <= -100) lD = 3;
        else if (dst <= -50) lD = 2;
        else if (dst <= -30) lD = 1;
        let lK = 0;
        if (kp >= 9) lK = 5;
        else if (kp >= 8) lK = 4;
        else if (kp >= 7) lK = 3;
        else if (kp >= 6) lK = 2;
        else if (kp >= 5) lK = 1;
        const g = Math.max(lD, lK);
        
        const badge = document.getElementById('storm-badge-3d');
        if (badge) {
            badge.textContent = 'G' + g;
            badge.className = 'storm-indicator-3d storm-g' + g;
        }

        this.stormLevel = g;
        this.updateSunVisual(g);
    },

    updateSunVisual: function(level) {
        const t = level / 5;
        const normalColors = [0xff9900, 0xff6600, 0xff3300, 0xff1100];
        const stormColors = [0xff4400, 0xff1100, 0xcc0000, 0x880000];
        const normalOpacity = [0.18, 0.10, 0.05, 0.025];
        const stormOpacity = [0.42, 0.28, 0.18, 0.10];

        this.coronaMeshes.forEach((mesh, i) => {
            mesh.material.color.lerpColors(new THREE.Color(normalColors[i]), new THREE.Color(stormColors[i]), t);
            mesh.material.opacity = normalOpacity[i] + (stormOpacity[i] - normalOpacity[i]) * t;
            mesh.scale.setScalar(1 + t * 0.6);
        });

        if (this.sunCore && this.sunCore.material) {
            this.sunCore.material.color.lerpColors(new THREE.Color(0xffd060), new THREE.Color(0xff4400), t);
        }
        if (this.sunLight) {
            this.sunLight.intensity = 4 + t * 8;
        }
        if (this.swMat) {
            this.swMat.size = 0.16 + t * 0.22;
            this.swMat.opacity = 0.7 + t * 0.2;
        }
    },
    
    animate: function() {
        this.animationId = requestAnimationFrame(() => this.animate());
        const dt = this.clock ? this.clock.getDelta() : 0.016;
        this.elapsed += dt;

        // Camera
        if (this.updateCamera) this.updateCamera();

        // Sun pulse (index.html ile aynı akış)
        const sT = this.stormLevel / 5;
        const pulseFreq = 1.2 + sT * 3.0;
        const pulseAmp = 0.015 + sT * 0.065;
        if (this.sunCore) {
            this.sunCore.scale.setScalar(1 + Math.sin(this.elapsed * pulseFreq) * pulseAmp + Math.sin(this.elapsed * 3.7) * 0.005);
        }
        
        // Solar wind
        const SW_N = 1200;
        const speedFactor = (this.liveData.vp / 400) * (1 + sT * 1.2);
        for (let i = 0; i < SW_N; i++) {
            this.swPos[i * 3] += this.swVel[i * 3] * speedFactor;
            this.swPos[i * 3 + 1] += this.swVel[i * 3 + 1] * speedFactor;
            this.swPos[i * 3 + 2] += this.swVel[i * 3 + 2] * speedFactor;
            this.swLife[i] += 0.003 * speedFactor;
            if (this.swLife[i] > 1 || Math.hypot(this.swPos[i * 3], this.swPos[i * 3 + 1], this.swPos[i * 3 + 2]) > 100) {
                this.spawnWind(i);
            }
        }
        this.swGeo.attributes.position.needsUpdate = true;
        
        // Sun rotation
        if (this.sunGroup) this.sunGroup.rotation.y += 0.0012;
        
        // Planet orbits
        this.planetMeshes.forEach((pm, i) => {
            this.planetAngles[i] += pm.data.speed * 0.0003;
            pm.group.position.x = Math.cos(this.planetAngles[i]) * pm.data.orbitR;
            pm.group.position.z = Math.sin(this.planetAngles[i]) * pm.data.orbitR;
            if (pm.mesh && pm.mesh.rotation) pm.mesh.rotation.y += 0.005;
        });
        
        this.renderer.render(this.scene, this.camera);
    },
    
    destroy: function() {
        if (this.animationId) cancelAnimationFrame(this.animationId);
    }
};

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SOLARIS - Canlı Veri AJAX Güncelleme Sistemi
 * 10 Kritik Parametre için çoklu periyot güncelleme
 * ═══════════════════════════════════════════════════════════════════════════
 */

// Base URL for cPanel compatibility (must be before object)
const _BASE = window.SOLARIS_BASE || '';
const WCS_PARAM_KEYS = [
    'proton_yogunlugu_np', 'proton_hizi_vp', 'kuzey_guney_imf_bz', 'dinamik_basinc_pd', 'etkin_basinc_pe',
    'x_ray_flux', 'f10_7_cm_flux', 'proton_flux_10mev', 'proton_flux_100mev',
    'tec', 'dtec', 'gtec', 'qtec', 'ftec',
    'kp_indeksi', 'k_indeksi', 'dst_indeksi', 'sym_h_indeksi', 'asy_h_indeksi', 'ae_indeksi', 'pc_indeksi', 'db_dt', 'jeoelektrik_alan_e'
];
const WCS_DEFAULT_RANGES = {
    proton_yogunlugu_np: { min: 0, max: 80 },
    proton_hizi_vp: { min: 250, max: 3000 },
    kuzey_guney_imf_bz: { min: 0, max: 60 },
    dinamik_basinc_pd: { min: 0, max: 900 },
    etkin_basinc_pe: { min: 0, max: 150 },
    x_ray_flux: { min: 1e-8, max: 5e-3 },
    f10_7_cm_flux: { min: 60, max: 300 },
    proton_flux_10mev: { min: 0, max: 100000 },
    proton_flux_100mev: { min: 0, max: 10000 },
    tec: { min: 0, max: 300 },
    dtec: { min: 0, max: 180 },
    gtec: { min: 0, max: 60 },
    qtec: { min: 0, max: 35 },
    ftec: { min: 0, max: 300 },
    kp_indeksi: { min: 0, max: 9 },
    k_indeksi: { min: 0, max: 9 },
    dst_indeksi: { min: -20, max: -1200 },
    sym_h_indeksi: { min: -20, max: -1200 },
    asy_h_indeksi: { min: 0, max: 400 },
    ae_indeksi: { min: 0, max: 4500 },
    pc_indeksi: { min: 0, max: 25 },
    db_dt: { min: 0, max: 2500 },
    jeoelektrik_alan_e: { min: 0, max: 20 }
};
const WCS_DEFAULT_AHP_WEIGHTS = {
    proton_yogunlugu_np: 0.03, proton_hizi_vp: 0.05, kuzey_guney_imf_bz: 0.09, dinamik_basinc_pd: 0.04, etkin_basinc_pe: 0.03,
    x_ray_flux: 0.05, f10_7_cm_flux: 0.025, proton_flux_10mev: 0.045, proton_flux_100mev: 0.03,
    tec: 0.03, dtec: 0.045, gtec: 0.03, qtec: 0.02, ftec: 0.03,
    kp_indeksi: 0.09, k_indeksi: 0.03, dst_indeksi: 0.08, sym_h_indeksi: 0.05, asy_h_indeksi: 0.025, ae_indeksi: 0.045,
    pc_indeksi: 0.035, db_dt: 0.06, jeoelektrik_alan_e: 0.045
};

const SolarisLive = {
    // ═══════════════════════════════════════════════════════════════════════
    // API ENDPOINTS - Tüm veri kaynakları
    // ═══════════════════════════════════════════════════════════════════════
    endpoints: {
        // 1 DAKİKA - DSCOVR/GOES Anlık
        plasma: _BASE + '/api/solar/plasma',
        mag: _BASE + '/api/solar/mag',
        xray: _BASE + '/api/solar/xray',
        protons: _BASE + '/api/solar/protons',
        
        // 15 DAKİKA - Hesaplanan indeksler
        kpNowcast: _BASE + '/api/solar/kp-nowcast',
        
        // 3 SAAT - Yerel indeksler
        kIndex: _BASE + '/api/solar/k-index',
        
        // 24 SAAT - Günlük
        f107: _BASE + '/api/solar/f107',
        
        // BACKEND PROXY ENDPOINTS
        backendDst: _BASE + '/api/solar/dst',
        backendAeIndices: _BASE + '/api/solar/ae-indices'
    },

    // Veri deposu
    data: {
        kp_indeksi: null,
        dst_indeksi: null,
        proton_hizi_vp: null,
        proton_yogunlugu_np: null,
        kuzey_guney_imf_bz: null,
        bt: null,
        x_ray_flux: null,
        proton_flux_10mev: null,
        f10_7_cm_flux: null,
        ae_indeksi: null
    },

    currentStormJSON: null,

    // Interval ID'leri
    intervals: {
        minute: null,
        quarter: null,
        threeHour: null,
        hourly: null,
        daily: null
    },
    
    // LLM çağrı durumu
    llmCalled: false,
    latestPerfectSimilarity: 0,
    currentStormJSON: null,
    historicStorms: [],

    // ═══════════════════════════════════════════════════════════════════════
    // BAŞLATMA
    // ═══════════════════════════════════════════════════════════════════════
    init: function() {
        console.log('🌞 Solaris Live Data System başlatılıyor...');
        this.loadHistoricStorms();
        
        // SABİT DEĞERLER - API'ler çalışmadığı için default
        this.setStaticValues();
        
        // İlk yüklemede tüm verileri çek
        this.fetchMinuteData();
        this.fetchQuarterData();
        this.fetchHourlyData();
        this.fetchDailyData();
        
        // Periyodik güncellemeler
        this.intervals.minute = setInterval(() => this.fetchMinuteData(), 60000);
        this.intervals.quarter = setInterval(() => { this.fetchQuarterData(); this.calculateStormFormulas(); }, 15 * 60000);
        this.intervals.hourly = setInterval(() => this.fetchHourlyData(), 60 * 60000);
        this.intervals.daily = setInterval(() => this.fetchDailyData(), 24 * 60 * 60000);

        // Fırtına formüllerini düzenli hesapla
        setInterval(() => this.calculateStormFormulas(), 5000);
        
        console.log("✅ Interval'ler ayarlandı");
    },


    // ═══════════════════════════════════════════════════════════════════════
    // SABİT DEĞERLER
    // ═══════════════════════════════════════════════════════════════════════
    setStaticValues: function() {
        this.data.kp_indeksi = this.data.kp_indeksi || 4.1;
        this.updateCard('kp-index', this.data.kp_indeksi, '');
        
        this.data.ae_indeksi = this.data.ae_indeksi || 156;
        this.updateCard('ae-index', this.data.ae_indeksi, 'nT');

        this.data.dst_indeksi = this.data.dst_indeksi || -20;
        this.updateCard('dst-index', this.data.dst_indeksi, 'nT');
        
        this.data.x_ray_flux = this.data.x_ray_flux || 1e-6; // C1.0 seviyesi
        this.updateCard('xray-flux', this.formatXRay(this.data.x_ray_flux), '');
    },

    /**
     * Fırtına Formülleri (20 Denklem) Hesaplanması
     */
    calculateStormFormulas: function() {
        const np = this.data.proton_yogunlugu_np || 1;
        const vp = this.data.proton_hizi_vp || 400;
        const Bz = this.data.kuzey_guney_imf_bz || 0;
        const Kp = this.data.kp_indeksi || 1;
        const Dst = this.data.dst_indeksi || 0;
        const AE = this.data.ae_indeksi || 50;
        
        const k_sbt = 10;
        const Bz0 = 0;
        const SMA_x = 400; 
        const ortalama_d = 20;
        const enlem = 90; const boylam = 180;
        const TEC_mock = 35 + (Kp * 2);
        const CMA_TEC = 35;
        const qTEC_mock = 30;
        const dTEC_ref = 8;
        const TEC_n = 40;
        const fTEC_n = 42;
        const tau = 4;

        // 1
        const pd = 0.5 * np * Math.pow(vp, 2) * 1.6726e-6;
        // 2
        const i = (Bz - Bz0) / k_sbt;
        // 3
        const pe = pd / (Math.exp(i) + 1);
        // 5
        const dx = vp - SMA_x;
        // 8
        const GTEC = (1 / (enlem * boylam)) * (TEC_mock * enlem * boylam);
        // 9
        const dTEC = TEC_mock - CMA_TEC;
        // 10
        const Delta_TEC = (dTEC / CMA_TEC) * 100;
        // 11
        const qTEC = qTEC_mock;
        // 12
        const fTEC = qTEC + dTEC_ref;
        // 13
        const fTEC_adj = fTEC + (fTEC_n - TEC_n);
        // 15
        const Dst_star = Dst - (0.2 * Math.sqrt(Math.abs(pd))) + 20;
        // 16
        const Bs = Bz < 0 ? Math.abs(Bz) : 0;
        const Q_t = -1 * (vp * Bs) * 0.001;
        const dDst_dt = Q_t - (Dst / tau);
        // 17
        const AU = AE / 2;
        const AL = -AE / 2;
        const computed_AE = AU - AL;
        // 18
        const PC = Kp * 2.8;
        // 19
        const dB_dt = Kp * 1.5;
        const degisim_E = -dB_dt;
        // 20
        const E = Math.abs(dB_dt) * 0.6;
        // 6
        const var1 = Math.pow((vp - 400), 2);
        const var2 = Math.pow((Dst - (-20)), 2);
        const var3 = Math.pow((Kp - 4), 2);
        const dE = Math.sqrt((var1 + var2 + var3) / 3);
        // 7
        let q = 1 - (dE / ortalama_d);
        if(q < 0) q = 0.1;
        // 4
        const tj1 = pd > 5 ? 20 : 5;
        const tj2 = Bz < -10 ? 30 : 5;
        const tj3 = Kp * 4;
        const Tj_toplam = tj1 + tj2 + tj3;
        const Tc = Math.round((1/2) * Tj_toplam);

        this.currentStormJSON = {
            timestamp: new Date().toISOString(),
            raw_data: this.data,
            formulas: {
                "1_pd": pd.toFixed(3), "2_i": i.toFixed(3), "3_pe": pe.toFixed(3), "4_Tc": Tc,
                "5_dx": dx.toFixed(2), "6_dE": dE.toFixed(2), "7_q": q.toFixed(3),
                "8_GTEC": GTEC.toFixed(1), "9_dTEC": dTEC.toFixed(1), "10_Delta_TEC": Delta_TEC.toFixed(1) + "%",
                "11_qTEC": qTEC.toFixed(1), "12_fTEC": fTEC.toFixed(1), "13_fTEC_adj": fTEC_adj.toFixed(1),
                "14_Dst_actual": Dst.toFixed(1), "15_Dst_star": Dst_star.toFixed(1), "16_dDst_dt": dDst_dt.toFixed(3),
                "17_AE": computed_AE.toFixed(1), "18_PC": PC.toFixed(2), "19_degisim_E": degisim_E.toFixed(2),
                "20_E_Field": E.toFixed(2)
            }
        };

        localStorage.setItem("solaris_live_storm", JSON.stringify(this.currentStormJSON));
        console.log("⚡ Fırtına formülleri kaydedildi:", this.currentStormJSON);
        
        // Bulanık mantık analizi
        this.calculateFuzzyLogic();
    },
    
    /**
     * Bulanık Mantık Tehdit Analizi
     */
    calculateFuzzyLogic: function() {
        const data = this.data;
        const formulas = this.currentStormJSON?.formulas || {};
        
        // Normalizasyon fonksiyonu
        const norm = (val, max) => Math.min(1.0, Math.abs(parseFloat(val) || 0) / max);
        
        // Maksimum değerler (eşik)
        const maxVals = {
            vp: 2000, np: 100, pd: 100, pe: 50,
            bz: 50, dst: 500, db_dt: 50, sym_h: 200, kp: 9, e_field: 50,
            ae: 2500, asy_h: 200, pc: 20, k: 9,
            xray: 1e-3, proton10: 100000, proton100: 10000, f107: 350,
            dtec: 50, tec: 100, gtec: 60, ftec: 80, qtec: 50
        };
        
        // Kinetik Skor
        const n_vp = norm(data.proton_hizi_vp, maxVals.vp);
        const n_np = norm(data.proton_yogunlugu_np, maxVals.np);
        const n_pd = norm(parseFloat(formulas['1_pd']) || 0, maxVals.pd);
        const n_pe = norm(parseFloat(formulas['3_pe']) || 0, maxVals.pe);
        const scoreKinetic = (n_vp * 0.40) + (n_np * 0.30) + (n_pe * 0.18) + (n_pd * 0.12);
        
        // Manyetik Skor
        const bz_val = parseFloat(data.kuzey_guney_imf_bz) || 0;
        const bz_risk = bz_val < 0 ? Math.abs(bz_val) : Math.abs(bz_val) * 0.1;
        const n_bz = norm(bz_risk, maxVals.bz);
        const n_dst = norm(data.dst_indeksi, maxVals.dst);
        const n_kp = norm(data.kp_indeksi, maxVals.kp);
        const n_ae = norm(data.ae_indeksi, maxVals.ae);
        const n_dbdt = norm(parseFloat(formulas['19_degisim_E']) || 0, maxVals.db_dt);
        const n_e = norm(parseFloat(formulas['20_E_Field']) || 0, maxVals.e_field);
        const scoreMagnetic = (n_bz * 0.30) + (n_dbdt * 0.22) + (n_dst * 0.18) + (n_kp * 0.15) + (n_ae * 0.10) + (n_e * 0.05);
        
        // Foton/Radyasyon Skor
        const xray = data.x_ray_flux || 1e-7;
        const n_xray = Math.min(1.0, xray / maxVals.xray);
        const n_prot10 = norm(data.proton_flux_10mev || 0, maxVals.proton10);
        const n_f107 = norm(data.f10_7_cm_flux || 100, maxVals.f107);
        const scorePhoton = (n_xray * 0.45) + (n_prot10 * 0.35) + (n_f107 * 0.20);
        
        // İyonosferik Skor
        const n_dtec = norm(parseFloat(formulas['9_dTEC']) || 0, maxVals.dtec);
        const n_gtec = norm(parseFloat(formulas['8_GTEC']) || 0, maxVals.gtec);
        const n_ftec = norm(parseFloat(formulas['12_fTEC']) || 0, maxVals.ftec);
        const scoreIono = (n_dtec * 0.45) + (n_gtec * 0.30) + (n_ftec * 0.25);
        
        // Global Skor (Mükemmel Fırtına Benzerliği)
        let perfectSimilarity = (scoreMagnetic * 0.39) + (scoreKinetic * 0.31) + (scorePhoton * 0.18) + (scoreIono * 0.12);
        perfectSimilarity = Math.max(0, Math.min(1.0, perfectSimilarity));
        this.latestPerfectSimilarity = perfectSimilarity;
        
        // UI Güncelle
        document.getElementById('fuz-mag').textContent = '%' + (scoreMagnetic * 100).toFixed(1);
        document.getElementById('fuz-kin').textContent = '%' + (scoreKinetic * 100).toFixed(1);
        document.getElementById('fuz-rad').textContent = '%' + (scorePhoton * 100).toFixed(1);
        document.getElementById('fuz-ion').textContent = '%' + (scoreIono * 100).toFixed(1);
        document.getElementById('perfect-storm-similarity').textContent = '%' + (perfectSimilarity * 100).toFixed(1);
        
        // Renk sistemi (0.8 eşik)
        const simBox = document.getElementById('fuzzy-similarity-box');
        const simText = document.getElementById('perfect-storm-similarity');
        
        simBox.classList.remove('bg-green-900/40', 'bg-yellow-900/40', 'bg-orange-900/40', 'bg-red-900/40', 'bg-purple-900/40',
                                'border-green-500/50', 'border-yellow-500/50', 'border-orange-500/50', 'border-red-500/50', 'border-purple-500/50');
        simText.classList.remove('text-green-400', 'text-yellow-400', 'text-orange-400', 'text-red-400', 'text-white');
        
        let riskLabel = '';
        if (perfectSimilarity < 0.4) {
            simBox.classList.add('bg-green-900/40', 'border-green-500/50');
            simText.classList.add('text-green-400');
            riskLabel = 'GÜVENLİ';
        } else if (perfectSimilarity < 0.6) {
            simBox.classList.add('bg-yellow-900/40', 'border-yellow-500/50');
            simText.classList.add('text-yellow-400');
            riskLabel = 'DİKKAT';
        } else if (perfectSimilarity < 0.8) {
            simBox.classList.add('bg-orange-900/40', 'border-orange-500/50');
            simText.classList.add('text-orange-400');
            riskLabel = 'YÜKSEK RİSK';
        } else {
            simBox.classList.add('bg-red-900/40', 'border-red-500/50');
            simText.classList.add('text-red-400');
            riskLabel = 'TEHLİKELİ';
        }
        
        // Risk etiketi
        let riskBadge = document.getElementById('fuzzy-risk-badge');
        if (!riskBadge) {
            riskBadge = document.createElement('div');
            riskBadge.id = 'fuzzy-risk-badge';
            riskBadge.className = 'text-xs font-bold uppercase tracking-wider mt-2 relative z-10';
            simBox.appendChild(riskBadge);
        }
        riskBadge.textContent = riskLabel;
        riskBadge.className = 'text-xs font-bold uppercase tracking-wider mt-2 relative z-10 ' + 
            (perfectSimilarity < 0.4 ? 'text-green-300' : 
             perfectSimilarity < 0.6 ? 'text-yellow-300' : 
             perfectSimilarity < 0.8 ? 'text-orange-300' : 'text-red-300');
        
        // LLM Analizi (ilk yüklemede bir kez çağır)
        if (!this.llmCalled) {
            this.llmCalled = true;
            this.invokeLLMAnalysis(perfectSimilarity);
        }

        this.calculateWeightedCosineConfirmation();
    },
    
    /**
     * AWS Bedrock LLM Analizi
     */
    invokeLLMAnalysis: async function(similarity) {
        const data = this.data;
        const formulas = this.currentStormJSON?.formulas || {};
        
        document.getElementById('ai-loading').classList.remove('hidden');
        document.getElementById('ai-content').classList.add('hidden');
        document.getElementById('ai-content').innerHTML = "";
        
        const stormDetails = `
        - Proton Yoğunluğu: ${(data.proton_yogunlugu_np || 0).toFixed(1)} p/cm³
        - Güneş Rüzgarı Hızı: ${(data.proton_hizi_vp || 0).toFixed(0)} km/s
        - Manyetik Alan (Bz): ${(data.kuzey_guney_imf_bz || 0).toFixed(1)} nT
        - Kp İndeksi: ${(data.kp_indeksi || 0).toFixed(1)}
        - Dst İndeksi: ${(data.dst_indeksi || 0).toFixed(0)} nT
        - AE İndeksi: ${(data.ae_indeksi || 0).toFixed(0)} nT
        - X-Ray Sınıfı: ${this.formatXRay(data.x_ray_flux)}
        - Dinamik Basınç: ${formulas['1_pd'] || 'N/A'} nPa
        - Mükemmel Fırtına Uyum: %${(similarity * 100).toFixed(1)}
        `;
        
        // Prompt şablonu backend'de (LLMController) yönetilir; frontend yalnızca güncel fırtına verisini gönderir.
        const llmInput = stormDetails.trim();
        
        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
            
            const response = await fetch(_BASE + '/api/analyze-storm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ prompt: llmInput })
            });
            
            const responseText = await response.text();
            let responseData;
            
            try {
                responseData = JSON.parse(responseText);
            } catch (parseError) {
                console.error("LLM Ham Yanıt:", responseText);
                throw new Error("Sunucu geçersiz format döndürdü.");
            }
            
            if (!response.ok) {
                throw new Error(responseData.details || responseData.error || 'AI sunucusundan hata alındı.');
            }
            
            document.getElementById('ai-loading').classList.add('hidden');
            document.getElementById('ai-content').classList.remove('hidden');
            document.getElementById('ai-content').innerHTML = `<div class="break-words">${responseData.html}</div>`;
            
        } catch (error) {
            document.getElementById('ai-loading').classList.add('hidden');
            document.getElementById('ai-content').classList.remove('hidden');
            document.getElementById('ai-content').innerHTML = `
                <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
                    <svg class="w-5 h-5 mb-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <strong>LLM Bağlantı Hatası:</strong> ${error.message}
                    <br><br><span class="text-xs text-red-500/70">Not: AWS Bedrock yapılandırması gereklidir.</span>
                </div>
            `;
        }
    },

    // ═══════════════════════════════════════════════════════════════════════
    // AJAX İstekleri
    // ═══════════════════════════════════════════════════════════════════════
    fetchMinuteData: function() {
        const self = this;
        console.log('⏱️ [1 DK] Dakikalık veriler çekiliyor...');

        // Plasma (np, vp)
        $.ajax({
            url: this.endpoints.plasma, method: 'GET', dataType: 'json', timeout: 10000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 1) {
                    const latest = response.data[response.data.length - 1];
                    self.data.proton_yogunlugu_np = parseFloat(latest[1]) || 0;
                    self.data.proton_hizi_vp = parseFloat(latest[2]) || 0;
                    self.updateCard('solar-wind', Math.round(self.data.proton_hizi_vp), 'km/s');
                    self.updateCard('plasma-density', self.data.proton_yogunlugu_np.toFixed(1), 'p/cm³');
                }
            }
        });

        // Manyetik (Bz, Bt)
        $.ajax({
            url: this.endpoints.mag, method: 'GET', dataType: 'json', timeout: 10000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 1) {
                    const latest = response.data[response.data.length - 1];
                    self.data.kuzey_guney_imf_bz = parseFloat(latest[3]) || 0;
                    self.data.bt = parseFloat(latest[6]) || 0;
                    self.updateCard('bz-field', self.data.kuzey_guney_imf_bz.toFixed(1), 'nT');
                    self.updateCard('bt-field', self.data.bt.toFixed(1), 'nT');
                }
            }
        });

        // Proton Flux
        $.ajax({
            url: this.endpoints.protons, method: 'GET', dataType: 'json', timeout: 10000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    const filtered10 = response.data.filter(d => d.energy === '>=10 MeV');
                    const latest10 = filtered10[filtered10.length - 1];
                    if (latest10) {
                        self.data.proton_flux_10mev = parseFloat(latest10.flux) || 0;
                        self.updateCard('proton-flux', self.data.proton_flux_10mev.toFixed(1), 'pfu');
                    }
                }
            }
        });

        // X-Ray
        $.ajax({
            url: this.endpoints.xray, method: 'GET', dataType: 'json', timeout: 10000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    const latest = response.data[response.data.length - 1];
                    self.data.x_ray_flux = parseFloat(latest.flux) || self.data.x_ray_flux;
                    self.updateCard('xray-flux', self.formatXRay(self.data.x_ray_flux), '');
                }
            }
        });

        // Ölçekleri güncelle ve zaman damgası
        setTimeout(() => {
            self.calculateScales();
            self.updateTimestamp();
            // 3D animasyona veri gönder
            self.sync3DAnimation();
        }, 2000);
    },

    sync3DAnimation: function() {
        if (window.SolarSystem3D && typeof SolarSystem3D.updateFromLiveData === 'function') {
            SolarSystem3D.updateFromLiveData({
                kp: this.data.kp_indeksi,
                dst: this.data.dst_indeksi,
                vp: this.data.proton_hizi_vp,
                np: this.data.proton_yogunlugu_np,
                bz: this.data.kuzey_guney_imf_bz
            });
        }
    },

    fetchQuarterData: function() {
        const self = this;
        console.log('⏱️ [15 DK] Kp verisi çekiliyor...');
        $.ajax({
            url: this.endpoints.kpNowcast, method: 'GET', dataType: 'json', timeout: 10000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    const latest = response.data[response.data.length - 1];
                    if (latest && latest[1]) {
                        self.data.kp_indeksi = parseFloat(latest[1]) || self.data.kp_indeksi;
                        self.updateCard('kp-index', self.data.kp_indeksi.toFixed(1), '');
                    }
                }
            }
        });
    },

    fetchHourlyData: function() {
        const self = this;
        console.log('⏱️ [1 SAAT] Dst ve AE İndeksleri çekiliyor...');
        
        $.ajax({
            url: this.endpoints.backendDst, method: 'GET', dataType: 'json', timeout: 15000,
            success: function(response) {
                if (response.success && response.data !== null) {
                    self.data.dst_indeksi = parseFloat(response.data) || self.data.dst_indeksi;
                    self.updateCard('dst-index', self.data.dst_indeksi.toFixed(0), 'nT');
                }
            }
        });

        $.ajax({
            url: this.endpoints.backendAeIndices, method: 'GET', dataType: 'json', timeout: 15000,
            success: function(response) {
                if (response.success && response.data && response.data.ae !== null) {
                    self.data.ae_indeksi = parseFloat(response.data.ae) || self.data.ae_indeksi;
                    self.updateCard('ae-index', self.data.ae_indeksi.toFixed(0), 'nT');
                }
            }
        });
    },

    fetchDailyData: function() {
        const self = this;
        console.log('⏱️ [24 SAAT] Günlük veriler çekiliyor...');
        $.ajax({
            url: this.endpoints.f107, method: 'GET', dataType: 'json', timeout: 15000,
            success: function(response) {
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    const latest = response.data[response.data.length - 1];
                    if (latest && latest.flux) {
                        self.data.f10_7_cm_flux = parseFloat(latest.flux) || 0;
                        self.updateCard('f107-flux', self.data.f10_7_cm_flux.toFixed(0), 'sfu');
                    }
                }
            }
        });
    },

    loadHistoricStorms: function() {
        const self = this;
        $.ajax({
            url: _BASE + '/api/firtinalar',
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(data) {
                self.historicStorms = Array.isArray(data) ? data : [];
            },
            error: function() {
                self.historicStorms = [];
            }
        });
    },

    // ═══════════════════════════════════════════════════════════════════════
    // YARDIMCI FONKSİYONLAR
    // ═══════════════════════════════════════════════════════════════════════
    parseXRayFluxValue: function(value) {
        if (typeof value === 'number' && Number.isFinite(value)) return Math.max(0, value);
        const text = (value || '').toString().trim().toUpperCase();
        if (!text) return 0;
        const cls = text.match(/([ABCMX])\s*([\d.]+)/);
        if (cls) {
            const coeff = { A: 1e-8, B: 1e-7, C: 1e-6, M: 1e-5, X: 1e-4 }[cls[1]] || 0;
            return coeff * (parseFloat(cls[2]) || 0);
        }
        const numeric = parseFloat(text.replace(',', '.').replace(/[^\d.-]/g, ''));
        return Number.isFinite(numeric) ? Math.max(0, numeric) : 0;
    },

    toNumericParamValue: function(key, value) {
        if (key === 'x_ray_flux') return this.parseXRayFluxValue(value);
        const parsed = parseFloat((value ?? 0).toString().replace(',', '.').replace(/[^\d.-]/g, ''));
        if (!Number.isFinite(parsed)) return 0;
        return key === 'kuzey_guney_imf_bz' ? Math.abs(parsed) : parsed;
    },

    getAHPWeights: function() {
        let candidate = null;
        if (window.SOLARIS_AHP_WEIGHTS && typeof window.SOLARIS_AHP_WEIGHTS === 'object') {
            candidate = window.SOLARIS_AHP_WEIGHTS;
        } else {
            const stored = localStorage.getItem('solaris_ahp_weights');
            if (stored) {
                try { candidate = JSON.parse(stored); } catch (e) { candidate = null; }
            }
        }
        const base = (candidate && typeof candidate === 'object') ? candidate : WCS_DEFAULT_AHP_WEIGHTS;
        const normalized = {};
        let total = 0;
        WCS_PARAM_KEYS.forEach((key) => {
            const w = Math.max(0, parseFloat(base[key]) || 0);
            normalized[key] = w;
            total += w;
        });
        if (total <= 0) {
            const eq = 1 / WCS_PARAM_KEYS.length;
            WCS_PARAM_KEYS.forEach((key) => { normalized[key] = eq; });
            return normalized;
        }
        WCS_PARAM_KEYS.forEach((key) => { normalized[key] = normalized[key] / total; });
        return normalized;
    },

    normalizeWithRange: function(value, key) {
        const range = WCS_DEFAULT_RANGES[key] || { min: 0, max: 1 };
        let minVal = parseFloat(range.min);
        let maxVal = parseFloat(range.max);
        
        // Logaritmik ölçekli parametreler için özel işlem
        const logScaleParams = ['x_ray_flux', 'proton_flux_10mev', 'proton_flux_100mev'];
        if (logScaleParams.includes(key)) {
            const safeVal = Math.max(value, 1e-10);
            const safeMin = Math.max(minVal, 1e-10);
            const safeMax = Math.max(maxVal, 1e-10);
            const logVal = Math.log10(safeVal);
            const logMin = Math.log10(safeMin);
            const logMax = Math.log10(safeMax);
            if (logMax === logMin) return 0.5;
            const normalized = (logVal - logMin) / (logMax - logMin);
            return Math.min(1.0, Math.max(0, normalized));
        }
        
        // Negatif değer aralıkları için (DST, SYM-H gibi) - daha negatif = daha şiddetli
        if (minVal > maxVal) {
            // min=-20, max=-1200 gibi durumlar: değeri pozitif aralığa çevir
            const temp = minVal;
            minVal = maxVal;
            maxVal = temp;
        }
        
        const span = Math.abs(maxVal - minVal);
        if (span === 0) return 0.5;
        
        // Değeri [0,1] aralığına normalize et
        const clampedValue = Math.max(minVal, Math.min(maxVal, value));
        const normalized = (clampedValue - minVal) / span;
        return Math.min(1.0, Math.max(0, normalized));
    },

    getCurrentVectorForWCS: function() {
        const formulas = this.currentStormJSON?.formulas || {};
        return {
            proton_yogunlugu_np: this.data.proton_yogunlugu_np || 0,
            proton_hizi_vp: this.data.proton_hizi_vp || 0,
            kuzey_guney_imf_bz: Math.abs(this.data.kuzey_guney_imf_bz || 0),
            dinamik_basinc_pd: parseFloat(formulas['1_pd']) || 0,
            etkin_basinc_pe: parseFloat(formulas['3_pe']) || 0,
            x_ray_flux: this.data.x_ray_flux || 0,
            f10_7_cm_flux: this.data.f10_7_cm_flux || 0,
            proton_flux_10mev: this.data.proton_flux_10mev || 0,
            proton_flux_100mev: this.data.proton_flux_100mev || 0,
            tec: parseFloat(formulas['8_GTEC']) || 0,
            dtec: parseFloat(formulas['9_dTEC']) || 0,
            gtec: parseFloat(formulas['8_GTEC']) || 0,
            qtec: parseFloat(formulas['11_qTEC']) || 0,
            ftec: parseFloat(formulas['12_fTEC']) || 0,
            kp_indeksi: this.data.kp_indeksi || 0,
            k_indeksi: this.data.k_indeksi || 0,
            dst_indeksi: this.data.dst_indeksi || 0,
            sym_h_indeksi: this.data.sym_h_indeksi || 0,
            asy_h_indeksi: this.data.asy_h_indeksi || 0,
            ae_indeksi: this.data.ae_indeksi || 0,
            pc_indeksi: parseFloat(formulas['18_PC']) || this.data.pc_indeksi || 0,
            db_dt: Math.abs(parseFloat(formulas['19_degisim_E']) || this.data.db_dt || 0),
            jeoelektrik_alan_e: parseFloat(formulas['20_E_Field']) || this.data.jeoelektrik_alan_e || 0
        };
    },

    calculateWeightedCosineConfirmation: function() {
        if (!Array.isArray(this.historicStorms) || !this.historicStorms.length) {
            this.updateWeightedCosineUI(null);
            this.updateFinalRiskScore(this.latestPerfectSimilarity, 0);
            return;
        }

        const currentVector = this.getCurrentVectorForWCS();
        const weights = this.getAHPWeights();
        const normalizedA = {};

        WCS_PARAM_KEYS.forEach((key) => {
            const rawA = this.toNumericParamValue(key, currentVector[key]);
            normalizedA[key] = this.normalizeWithRange(rawA, key);
        });

        let best = null;
        this.historicStorms.forEach((storm) => {
            const params = storm?.parametreler || {};
            let pay = 0;
            let aTerm = 0;
            let bTerm = 0;

            WCS_PARAM_KEYS.forEach((key) => {
                const weight = weights[key] || 0;
                const rawB = this.toNumericParamValue(key, params[key]);
                const normalizedB = this.normalizeWithRange(rawB, key);
                const a = normalizedA[key] || 0;
                pay += weight * a * normalizedB;
                aTerm += weight * a * a;
                bTerm += weight * normalizedB * normalizedB;
            });

            const payda = Math.sqrt(aTerm) * Math.sqrt(bTerm);
            const similarity = payda > 0 ? (pay / payda) : 0;
            if (!best || similarity > best.similarity) {
                best = {
                    name: storm.firtina_adi || `Fırtına ${storm.id}`,
                    similarity: Math.max(0, Math.min(1, similarity))
                };
            }
        });

        this.updateWeightedCosineUI(best);
        this.updateFinalRiskScore(this.latestPerfectSimilarity, best ? best.similarity : 0);
    },

    updateWeightedCosineUI: function(bestMatch) {
        const nameEl = document.getElementById('weighted-cosine-storm-name');
        const scoreEl = document.getElementById('weighted-cosine-score');
        if (!nameEl || !scoreEl) return;
        if (!bestMatch) {
            nameEl.textContent = 'Tarihi fırtına verisi bekleniyor...';
            scoreEl.textContent = '%0.0';
            return;
        }
        nameEl.textContent = bestMatch.name;
        scoreEl.textContent = '%' + (bestMatch.similarity * 100).toFixed(1);
    },

    updateFinalRiskScore: function(perfectSimilarity, weightedCosineSimilarity) {
        const scoreEl = document.getElementById('final-risk-score');
        if (!scoreEl) return;

        const s = Math.max(0, Math.min(1, parseFloat(perfectSimilarity) || 0));
        const rawC = parseFloat(weightedCosineSimilarity) || 0;
        const c = Math.max(0, Math.min(1, rawC));

        const weightedS = s * 0.6;
        const weightedC = c * 0.4;
        const finalRisk = Math.sqrt((Math.pow(weightedS, 2) + Math.pow(weightedC, 2)) / 2);

        scoreEl.textContent = finalRisk.toFixed(3);
    },

    formatXRay: function(flux) {
        if (!flux || flux <= 0) return 'N/A';
        const logFlux = Math.log10(flux);
        if (logFlux >= -4) return 'X' + (flux / 1e-4).toFixed(1);
        if (logFlux >= -5) return 'M' + (flux / 1e-5).toFixed(1);
        if (logFlux >= -6) return 'C' + (flux / 1e-6).toFixed(1);
        if (logFlux >= -7) return 'B' + (flux / 1e-7).toFixed(1);
        return 'A' + (flux / 1e-8).toFixed(1);
    },

    calculateScales: function() {
        let g = 0; const kp = this.data.kp_indeksi || 0;
        if (kp >= 9) g = 5; else if (kp >= 8) g = 4; else if (kp >= 7) g = 3; else if (kp >= 6) g = 2; else if (kp >= 5) g = 1;

        let s = 0; const pf = this.data.proton_flux_10mev || 0;
        if (pf >= 100000) s = 5; else if (pf >= 10000) s = 4; else if (pf >= 1000) s = 3; else if (pf >= 100) s = 2; else if (pf >= 10) s = 1;

        let r = 0; const xr = this.data.x_ray_flux || 0;
        if (xr >= 1e-3) r = 5; else if (xr >= 5e-4) r = 4; else if (xr >= 1e-4) r = 3; else if (xr >= 5e-5) r = 2; else if (xr >= 1e-5) r = 1;

        this.updateScaleCard('G', g);
        this.updateScaleCard('S', s);
        this.updateScaleCard('R', r);
    },

    updateScaleCard: function(type, level) {
        const colorClass = this.getScaleColor(level);
        const bgClass = this.getScaleBg(level);
        const cards = document.querySelectorAll('.glass.rounded-2xl .grid.grid-cols-3 > div');
        cards.forEach(card => {
            const label = card.querySelector('span.text-xs');
            if (label && label.textContent.includes(type + ' (')) {
                const valueEl = card.querySelector('span.text-lg');
                if (valueEl) {
                    valueEl.textContent = type + level;
                    valueEl.className = `text-lg font-bold ${colorClass}`;
                }
                card.className = `flex flex-col items-center justify-center p-4 rounded-xl ${bgClass} border border-white/20`;
            }
        });
    },

    getScaleColor: function(level) {
        if (level >= 4) return 'text-red-400';
        if (level >= 3) return 'text-orange-400';
        if (level >= 2) return 'text-yellow-400';
        if (level >= 1) return 'text-emerald-400';
        return 'text-emerald-400';
    },

    getScaleBg: function(level) {
        if (level >= 4) return 'bg-red-500/10';
        if (level >= 3) return 'bg-orange-500/10';
        if (level >= 2) return 'bg-yellow-500/10';
        return 'bg-emerald-500/10';
    },

    updateCard: function(cardId, value, unit) {
        const el = document.getElementById('value-' + cardId);
        if (el) {
            el.classList.remove('skeleton');
            el.innerHTML = `<span class="text-lg font-bold text-white">${value}</span><span class="text-xs text-slate-400 ml-1">${unit}</span>`;
        }
    },

    updateTimestamp: function() {
        const now = new Date();
        const el = document.getElementById('last-update');
        if (el) {
            el.textContent = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    },

    stop: function() {
        Object.values(this.intervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });
        console.log('🛑 Solaris Live Data System durduruldu');
    }
};

$(document).ready(function() {
    // 3D Güneş Sistemi Animasyonunu Başlat
    SolarSystem3D.init();
    
    // Canlı veri sistemini başlat
    SolarisLive.init();
});

$(window).on('beforeunload', function() {
    SolarisLive.stop();
    SolarSystem3D.destroy();
});

window.SolarisLive = SolarisLive;
window.SolarSystem3D = SolarSystem3D;
</script>
@endsection

