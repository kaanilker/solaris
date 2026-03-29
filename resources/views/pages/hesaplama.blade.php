@extends('layouts.app')

@section('title', 'Hesaplama — Solaris')
@section('meta_description', 'Güneş fırtınası simülasyonu ve etki hesaplama aracı.')

@section('extra_styles')
.param-input {
    transition: all 0.3s ease;
}
.param-input:focus {
    box-shadow: 0 0 0 2px rgba(251, 176, 34, 0.3);
    border-color: rgba(251, 176, 34, 0.5);
}
.param-input:not(:placeholder-shown) {
    border-color: rgba(251, 176, 34, 0.3);
    background: rgba(251, 176, 34, 0.05);
}
.storm-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}
.storm-card:hover {
    transform: translateY(-2px);
    border-color: rgba(99, 102, 241, 0.4);
}
.storm-card.selected {
    border-color: rgba(99, 102, 241, 0.6);
    background: rgba(99, 102, 241, 0.1);
    box-shadow: 0 0 30px rgba(99, 102, 241, 0.2);
}
.storm-card.selected .storm-indicator {
    background: #818cf8;
}
.report-glow {
    position: relative;
}
.report-glow::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(135deg, rgba(99,102,241,0.5), rgba(168,85,247,0.5), rgba(251,176,34,0.5));
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
}
@keyframes blink {
    0%, 50% { opacity: 1; }
    51%, 100% { opacity: 0; }
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.typing-cursor::after {
    content: '|';
    animation: blink 1s infinite;
    color: #818cf8;
}
.param-group {
    border-left: 3px solid;
}
.param-group-solar { border-color: #f97316; }
.param-group-photon { border-color: #a855f7; }
.param-group-iono { border-color: #22c55e; }
.param-group-geo { border-color: #3b82f6; }
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10 py-6 space-y-6">

    {{-- TARİHİ FIRTINALAR --}}
    <section id="historic-storms-section" class="glass rounded-xl p-5 border border-white/10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-display font-semibold text-lg text-white">Tarihteki Fırtınalar</h2>
            <button onclick="clearStormSelection()" class="px-3 py-1 rounded-lg text-xs text-slate-400 hover:text-white hover:bg-white/5">
                Temizle
            </button>
        </div>
        <div class="grid grid-cols-5 gap-2" id="storm-grid">
            {{-- JavaScript ile doldurulacak --}}
            <div class="text-center text-slate-500 text-sm col-span-full py-4">
                <div class="animate-pulse">Fırtınalar yükleniyor...</div>
            </div>
        </div>
        <div id="storm-details" class="hidden mt-4 p-4 rounded-lg bg-night-500/10 border border-night-500/20">
            <div class="flex items-center justify-between mb-2">
                <span id="selected-storm-name" class="text-white font-semibold">--</span>
                <button onclick="loadStormData()" class="px-3 py-1.5 rounded-lg bg-night-500/20 text-night-400 text-xs font-semibold hover:bg-night-500/30">
                    Verileri Yükle
                </button>
            </div>
            <p id="storm-date" class="text-xs text-slate-500">--</p>
        </div>
    </section>

    {{-- 24 PARAMETRE GİRİŞİ --}}
    <section id="parameter-entry-section" class="glass rounded-xl p-5 border border-white/10">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-display font-semibold text-lg text-white">Parametre Girişi</h2>
                <p class="text-xs text-slate-500">Dolu: <span id="filled-count" class="text-solar-400">0</span>/24 • Mod: <span id="calc-mode" class="text-night-400 font-semibold">Manuel</span></p>
            </div>
            <div class="flex gap-2">
                <button onclick="clearAllParams()" class="px-3 py-1 rounded-lg text-xs text-slate-400 hover:text-white hover:bg-white/5">Temizle</button>
            </div>
        </div>

        <div class="grid gap-4">
            {{-- Güneş Rüzgarı (6 param) --}}
            <div class="param-group param-group-solar pl-3">
                <p class="text-xs text-orange-400 font-semibold mb-2">Güneş Rüzgarı</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                    <input type="text" id="param-proton_yogunlugu_np" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="np (p/cm³)">
                    <input type="text" id="param-proton_hizi_vp" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="vp (km/s)">
                    <input type="text" id="param-kuzey_guney_imf_bz" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="Bz Şiddeti (nT)">
                    <select id="param-imf_yonu" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm cursor-pointer focus:outline-none">
                        <option value="Güney" class="text-slate-800">Güney Z (Riskli)</option>
                        <option value="Kuzey" class="text-slate-800">Kuzey Z (Güvenli)</option>
                    </select>
                    <input type="text" id="param-dinamik_basinc_pd" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="pd (nPa)">
                    <input type="text" id="param-etkin_basinc_pe" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="pe (nPa)">
                </div>
            </div>

            {{-- Güneş Fotonları (4 param) --}}
            <div class="param-group param-group-photon pl-3">
                <p class="text-xs text-purple-400 font-semibold mb-2">Güneş Fotonları</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <input type="text" id="param-x_ray_flux" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="X-Ray (sınıf)">
                    <input type="text" id="param-f10_7_cm_flux" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="F10.7 (sfu)">
                    <input type="text" id="param-proton_flux_10mev" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="≥10 MeV (pfu)">
                    <input type="text" id="param-proton_flux_100mev" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="≥100 MeV (pfu)">
                </div>
            </div>

            {{-- İyonosferik (5 param) --}}
            <div class="param-group param-group-iono pl-3">
                <p class="text-xs text-emerald-400 font-semibold mb-2">İyonosferik (TEC)</p>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                    <input type="text" id="param-tec" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="TEC (TECU)">
                    <input type="text" id="param-dtec" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="dTEC">
                    <input type="text" id="param-gtec" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="GTEC">
                    <input type="text" id="param-qtec" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="qTEC">
                    <input type="text" id="param-ftec" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="fTEC">
                </div>
            </div>

            {{-- Jeomanyetik (9 param) --}}
            <div class="param-group param-group-geo pl-3">
                <p class="text-xs text-blue-400 font-semibold mb-2">Jeomanyetik İndeksler</p>
                <div class="grid grid-cols-2 sm:grid-cols-5 lg:grid-cols-9 gap-2">
                    <input type="text" id="param-kp_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="Kp (0-9)">
                    <input type="text" id="param-k_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="K (0-9)">
                    <input type="text" id="param-dst_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="Dst (nT)">
                    <input type="text" id="param-sym_h_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="SYM-H (nT)">
                    <input type="text" id="param-asy_h_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="ASY-H (nT)">
                    <input type="text" id="param-ae_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="AE (nT)">
                    <input type="text" id="param-pc_indeksi" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="PC">
                    <input type="text" id="param-db_dt" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="dB/dt (nT/dk)">
                    <input type="text" id="param-jeoelektrik_alan_e" class="param-input px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white text-sm" placeholder="E (V/km)">
                </div>
            </div>
        </div>
    </section>

    {{-- HESAPLAMA BUTONU --}}
    <section id="calculation-button-section" class="glass rounded-xl p-5 border border-white/10">
        <button onclick="runCalculation()" 
                class="w-full py-3 rounded-xl bg-gradient-to-r from-solar-500 to-orange-500 text-white font-bold
                       hover:from-solar-400 hover:to-orange-400 transition-all shadow-lg shadow-solar-500/30
                       flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Hesapla
        </button>
    </section>

    {{-- SONUÇLAR --}}
    <section id="results-section" class="hidden space-y-4">
        {{-- Geri Dön / Yeni Hesaplama Butonu --}}
        <div class="flex justify-end mb-2">
            <button onclick="resetCalculation()" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Yeni Hesaplama Yap
            </button>
        </div>

        {{-- Sonuç Kartları Kaldırıldı --}}

        {{-- 20 Formül Sonuç Paneli --}}
        <div class="glass rounded-xl p-5 border border-white/10">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-solar-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Fiziksel Denklem Çıktıları
                </h3>
            </div>
            <div id="formulas-result-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                {{-- JS ile doldurulacak --}}
            </div>
        </div>

        {{-- Bulanık Mantık ve Vektör Benzerliği --}}
        <div id="fuzzy-logic-vector-sim" class="glass rounded-xl p-5 border border-purple-500/30 bg-purple-500/5">
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
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-purple-500/10 to-transparent flex items-center" style="transform: skewX(-20deg); animation: shimmer 3s linear infinite; background-size: 200% 100%;"></div>
                <div class="text-xs text-purple-300 mb-1 relative z-10">Teorik Bulanık Mantık Mükemmel Fırtına Potansiyeline Uyum Yüzdesi</div>
                <div class="text-4xl font-black text-white relative z-10" id="perfect-storm-similarity">%0.0</div>
            </div>

            <div id="weighted-cosine-box" class="mt-3 glass p-4 text-center rounded-xl bg-sky-900/30 border border-sky-500/40 relative overflow-hidden">
                <div class="text-xs text-sky-300 mb-1 relative z-10">Ağırlıklı Kosinüs Benzerliği (Tarihteki Fırtına Eşleşmesi)</div>
                <div class="text-lg font-bold text-white relative z-10" id="weighted-cosine-storm-name">--</div>
                <div class="text-3xl font-black text-sky-200 relative z-10 mt-1" id="weighted-cosine-score">%0.0</div>
            </div>

            <div id="final-risk-box" class="mt-4 mx-auto w-full max-w-md p-[1px] rounded-2xl bg-gradient-to-r from-rose-500/50 via-fuchsia-400/40 to-amber-300/40 shadow-[0_8px_30px_rgba(244,63,94,0.25)]">
                <div class="relative overflow-hidden rounded-2xl bg-slate-900/80 backdrop-blur-sm px-5 py-4 text-center border border-white/10">
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-rose-500/10 to-transparent" style="transform: skewX(-20deg); animation: shimmer 3.2s linear infinite; background-size: 200% 100%;"></div>
                    <div class="relative z-10 text-[11px] text-rose-200/90 uppercase tracking-[0.18em] mb-2">Nihai Risk</div>
                    <div class="relative z-10 text-4xl sm:text-5xl font-black text-white drop-shadow-[0_0_16px_rgba(251,113,133,0.45)]" id="final-risk-score">0.000</div>
                </div>
            </div>
        </div>

        {{-- Fırtına Öncesi ve Sonrası (LLM) --}}
        <div id="llm-storm-actions" class="glass rounded-xl p-5 border border-white/10 report-glow">
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
                    <p class="text-emerald-400/80 font-mono text-sm tracking-widest uppercase animate-pulse">Solaris Yapay Zeka Önerisi</p>
                    <p class="text-slate-400 text-xs mt-1">Sistem Fırtına Verilerini Analiz Ediyor...</p>
                </div>
                <div id="ai-content" class="hidden prose prose-invert prose-sm max-w-none prose-p:text-slate-300 prose-ul:text-slate-300">
                    sondaki hahaha {{-- AWS Bedrock yanıtı typewriter animasyonu ile buraya basılacak --}}
                </div>
            </div>
        </div>

    </section>

    {{-- FOOTER GITHUB kaldırıldı --}}
</div>

@endsection

@section('scripts')
<script>
// 24 Parametre keys
const paramKeys = [
    'proton_yogunlugu_np', 'proton_hizi_vp', 'kuzey_guney_imf_bz', 'imf_yonu', 'dinamik_basinc_pd', 'etkin_basinc_pe',
    'x_ray_flux', 'f10_7_cm_flux', 'proton_flux_10mev', 'proton_flux_100mev',
    'tec', 'dtec', 'gtec', 'qtec', 'ftec',
    'kp_indeksi', 'k_indeksi', 'dst_indeksi', 'sym_h_indeksi', 'asy_h_indeksi', 'ae_indeksi', 'pc_indeksi', 'db_dt', 'jeoelektrik_alan_e'
];
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

let storms = [];
let selectedStorm = null;

// Fırtınaları yükle
function loadStorms() {
    $.ajax({
        url: (window.SOLARIS_BASE || '') + '/api/firtinalar',
        method: 'GET',
        dataType: 'json',
        cache: false,
        success: function(data) {
            storms = Array.isArray(data) ? data : [];
            if (!storms.length) {
                document.getElementById('storm-grid').innerHTML = '<div class="col-span-full text-center text-red-400 text-sm py-4">Fırtına verisi boş döndü.</div>';
                return;
            }
            renderStormGrid();
            console.log('✓ Fırtınalar yüklendi:', storms.length);
        },
        error: function(xhr, status, error) {
            console.error('✗ Fırtına yükleme hatası:', error);
            document.getElementById('storm-grid').innerHTML = '<div class="col-span-full text-center text-red-400 text-sm py-4">Fırtına verileri yüklenemedi!</div>';
        }
    });
}

function renderStormGrid() {
    const grid = document.getElementById('storm-grid');
    grid.innerHTML = '';
    
    // Fırtınaları 5 üst, 5 alt olacak şekilde iki satıra böl
    storms.forEach((storm, i) => {
        const year = storm.tarih ? storm.tarih.split('-')[0] : '--';
        const name = storm.firtina_adi || `Fırtına ${storm.id}`;
        
        grid.innerHTML += `
            <div class="storm-card glass rounded-lg p-3 border border-white/10 text-center cursor-pointer hover:border-orange-500/30 transition-all" 
                 data-storm-id="${storm.id}" onclick="selectStorm(${storm.id})">
                <div class="storm-indicator w-2 h-2 rounded-full bg-orange-500/50 mx-auto mb-2"></div>
                <p class="text-xs font-bold text-white leading-tight truncate" title="${name}">${name}</p>
                <p class="text-[10px] text-slate-500">${year}</p>
            </div>
        `;
    });
}

function selectStorm(id) {
    document.querySelectorAll('.storm-card').forEach(c => c.classList.remove('selected'));
    const card = document.querySelector(`[data-storm-id="${id}"]`);
    if (card) card.classList.add('selected');
    
    selectedStorm = storms.find(s => s.id === id);
    
    if (selectedStorm) {
        document.getElementById('storm-details').classList.remove('hidden');
        document.getElementById('selected-storm-name').textContent = selectedStorm.firtina_adi || `Fırtına ${id}`;
        document.getElementById('storm-date').textContent = selectedStorm.tarih || '--';
        updateCalcMode();
    }
}

function clearStormSelection() {
    document.querySelectorAll('.storm-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('storm-details').classList.add('hidden');
    selectedStorm = null;
    updateCalcMode();
}

function loadStormData() {
    if (!selectedStorm || !selectedStorm.parametreler) return;
    
    const params = selectedStorm.parametreler;
    paramKeys.forEach(key => {
        const input = document.getElementById('param-' + key);
        if (input && params[key] !== undefined) {
            input.value = params[key];
        }
    });
    
    updateFilledCount();
    console.log('✓ Fırtına verileri yüklendi');
}

function clearAllParams() {
    paramKeys.forEach(key => {
        const input = document.getElementById('param-' + key);
        if (input) input.value = '';
    });
    updateFilledCount();
}

function updateFilledCount() {
    let count = 0;
    paramKeys.forEach(key => {
        const input = document.getElementById('param-' + key);
        if (input && input.value.trim()) count++;
    });
    document.getElementById('filled-count').textContent = count;
    updateCalcMode();
}

function updateCalcMode() {
    const count = parseInt(document.getElementById('filled-count').textContent);
    let mode = 'Manuel';
    if (selectedStorm && count > 0) mode = 'Hibrit';
    else if (selectedStorm) mode = 'Hazır';
    document.getElementById('calc-mode').textContent = mode;
}

function parseNumber(key, defaultValue = 0) {
    const inputEl = document.getElementById('param-' + key);
    if (!inputEl) return defaultValue;
    
    // Yön seçimi text değil option tabanlı olduğu için sayısallaştırmayı pas geçir
    if (key === 'imf_yonu') return inputEl.value || 'Güney';
    
    const raw = inputEl.value.trim();
    if (!raw) return defaultValue;
    const normalized = raw.replace(',', '.').replace(/[^\d.-]/g, '');
    const parsed = parseFloat(normalized);
    return Number.isFinite(parsed) ? parsed : defaultValue;
}

function computeStormRisk() {
    // 24 Parametrenin hepsi/kısmı UI'dan alındı
    const inputValues = {};
    paramKeys.forEach(key => {
        inputValues[key] = parseNumber(key); // varsayılan 0 dönüyor
    });

    const np = inputValues['proton_yogunlugu_np'] || 1;
    const vp = inputValues['proton_hizi_vp'] || 400;
    
    const bz_mag = Math.abs(inputValues['kuzey_guney_imf_bz'] || 0);
    const yon = inputValues['imf_yonu'] || 'Güney';
    const Bz = yon === 'Güney' ? -bz_mag : bz_mag;
    
    const Kp = inputValues['kp_indeksi'] || 1;
    const Dst = inputValues['dst_indeksi'] || 0;
    const AE = inputValues['ae_indeksi'] || 50;
    const proton10 = inputValues['proton_flux_10mev'] || 0.1;
    const tecInput = inputValues['tec'] || null;

    // Sabitler ve Simülasyon Değerleri
    const k_sbt = 10;
    const Bz0 = 0;
    const SMA_x = 400; 
    const ortalama_d = 20;
    const enlem = 90; const boylam = 180;
    const TEC_mock = tecInput !== null && tecInput > 0 ? tecInput : 35 + (Kp * 2);
    const CMA_TEC = 35;
    const qTEC_mock = 30;
    const dTEC_ref = 8;
    const TEC_n = 40;
    const fTEC_n = 42;
    const tau = 4;

    // 1. pd
    let pd = inputValues['dinamik_basinc_pd'] || 0.5 * np * Math.pow(vp, 2) * 1.6726e-6;
    // 2. i
    const i = (Bz - Bz0) / k_sbt;
    // 3. pe
    let pe = inputValues['etkin_basinc_pe'] || pd / (Math.exp(i) + 1);
    // 5. dx
    const dx = vp - SMA_x;
    // 8. GTEC
    let GTEC = inputValues['gtec'] || (1 / (enlem * boylam)) * (TEC_mock * enlem * boylam);
    // 9. dTEC
    let dTEC = inputValues['dtec'] || (TEC_mock - CMA_TEC);
    // 10. Delta_TEC
    const Delta_TEC = (dTEC / CMA_TEC) * 100;
    // 11. qTEC
    let qTEC = inputValues['qtec'] || qTEC_mock;
    // 12. fTEC
    let fTEC = inputValues['ftec'] || (qTEC + dTEC_ref);
    // 13. fTEC_adj
    const fTEC_adj = fTEC + (fTEC_n - TEC_n);
    // 15. Dst*
    const Dst_star = Dst - (0.2 * Math.sqrt(Math.abs(pd))) + 20;
    // 16. dDst_dt
    const Bs = Bz < 0 ? Math.abs(Bz) : 0;
    const Q_t = -1 * (vp * Bs) * 0.001;
    const dDst_dt = Q_t - (Dst / tau);
    // 17. AE
    const AU = AE / 2;
    const AL = -AE / 2;
    const computed_AE = AU - AL;
    // 18. PC
    let PC = inputValues['pc_indeksi'] || (Kp * 2.8);
    // 19. degisim_E (dB_dt)
    let dB_dt = inputValues['db_dt'] || (Kp * 1.5);
    const degisim_E = -dB_dt;
    // 20. E (Jeoelektrik Alan)
    let eField = inputValues['jeoelektrik_alan_e'] || (Math.abs(dB_dt) * 0.6);

    // 6. dE
    const var1 = Math.pow((vp - 400), 2);
    const var2 = Math.pow((Dst - (-20)), 2);
    const var3 = Math.pow((Kp - 4), 2);
    const dE = Math.sqrt((var1 + var2 + var3) / 3);

    // 7. q
    let q = 1 - (dE / ortalama_d);
    if(q < 0) q = 0.1;

    // SKORLAMA (Eski algoritma iyileştirildi, UI'daki eksik alanları formülden çıkanlarla kapattık)
    let score = 0;
    score += Math.min(30, Kp * 3.2);
    score += Math.min(22, Math.max(0, (Math.abs(Dst) - 40) * 0.14));
    score += Math.min(12, Math.max(0, (-Bz) * 0.7));
    score += Math.min(10, Math.max(0, (vp - 380) * 0.03));
    score += Math.min(8, Math.max(0, Math.log10(Math.max(proton10, 1))));
    score += Math.min(10, Math.max(0, dB_dt * 0.01));
    score += Math.min(8, Math.max(0, eField * 0.6));
    score += Math.min(8, Math.max(0, (TEC_mock - 30) * 0.08));
    score = Math.max(0, Math.min(100, score));

    // 4. Tc (Bileşik Tehdit seviyesi olarak simüle et)
    const Tc = Math.round(score);

    // JSON Objkemiz
    const stormJSON = {
        timestamp: new Date().toISOString(),
        raw_inputs: inputValues,
        formulas: {
            "1_pd": pd.toFixed(3), "2_i": i.toFixed(3), "3_pe": pe.toFixed(3), "4_Tc": Tc,
            "5_dx": dx.toFixed(2), "6_dE": dE.toFixed(2), "7_q": q.toFixed(3),
            "8_GTEC": GTEC.toFixed(1), "9_dTEC": dTEC.toFixed(1), "10_Delta_TEC": Delta_TEC.toFixed(1),
            "11_qTEC": qTEC.toFixed(1), "12_fTEC": fTEC.toFixed(1), "13_fTEC_adj": fTEC_adj.toFixed(1),
            "14_Dst_actual": Dst.toFixed(1), "15_Dst_star": Dst_star.toFixed(1), "16_dDst_dt": dDst_dt.toFixed(3),
            "17_AE": computed_AE.toFixed(1), "18_PC": PC.toFixed(2), "19_degisim_E": degisim_E.toFixed(2),
            "20_E_Field": eField.toFixed(2)
        }
    };

    localStorage.setItem("solaris_calc_storm", JSON.stringify(stormJSON));
    console.log("⚡ Fırtına formülleri kaydedildi (Hesaplama):", stormJSON);

    let risk = 'Düşük';
    let impact = 'G1';
    if (score >= 80) { risk = 'Kritik'; impact = 'G5'; }
    else if (score >= 62) { risk = 'Yüksek'; impact = 'G4'; }
    else if (score >= 45) { risk = 'Orta'; impact = 'G3'; }
    else if (score >= 30) { risk = 'Sınırlı'; impact = 'G2'; }

    const duration = Math.max(6, Math.round(6 + (score * 0.42)));
    const confidence = Math.min(98, Math.max(55, Math.round(55 + score * 0.42)));

    return { score, risk, impact, duration, confidence, kp: Kp, dst: Dst, bz: Bz };
}

function parseXRayFlux(fluxStr) {
    if (!fluxStr) return 0;
    fluxStr = fluxStr.toString().trim().toUpperCase();
    const match = fluxStr.match(/([A-Z])([\d.]+)/);
    if (!match) return 0;

    const coeffMap = { 'A': 1e-8, 'B': 1e-7, 'C': 1e-6, 'M': 1e-5, 'X': 1e-4 };
    const coeff = coeffMap[match[1]] || 0;
    const multiplier = parseFloat(match[2]) || 1;
    return coeff * multiplier;
}

function calculateStormPerfectSimilarity(stormParams) {
    // Tarihi fırtınanın parametrelerinden "mükemmel fırtına" skorunu hesapla
    const maxVals = {
        vp: 3000, np: 150, pd: 150, pe: 30,
        bz: 150, db_dt: 5000, dst: 1500, sym_h: 600, kp: 9, k: 9, e_field: 30, 
        ae: 4000, asy_h: 600, pc: 25,
        xray: 5e-3, proton10: 100000, proton100: 10000, f107: 400,
        dtec: 150, tec: 300, gtec: 300, ftec: 150, qtec: 50
    };

    const norm = (val, maxLim) => Math.min(1.0, Math.abs(parseFloat(val) || 0) / maxLim);

    // Kinetik
    let n_vp = norm(stormParams['proton_hizi_vp'], maxVals.vp);
    let n_np = norm(stormParams['proton_yogunlugu_np'], maxVals.np);
    let n_pd = norm(stormParams['dinamik_basinc_pd'], maxVals.pd);
    let n_pe = norm(stormParams['etkin_basinc_pe'], maxVals.pe);
    let scoreKinetic = (n_vp * 0.40) + (n_np * 0.30) + (n_pe * 0.18) + (n_pd * 0.12);

    // Manyetik
    let bz_val = parseFloat(stormParams['kuzey_guney_imf_bz']) || 0;
    let bz_risk = Math.abs(bz_val); // Tarihi fırtınalarda genelde Güney yönü baskın
    let n_bz = norm(bz_risk, maxVals.bz);
    
    let n_dst = norm(stormParams['dst_indeksi'], maxVals.dst);
    let n_dbdt = norm(stormParams['db_dt'], maxVals.db_dt);
    let n_symh = norm(stormParams['sym_h_indeksi'], maxVals.sym_h);
    let n_kp = norm(stormParams['kp_indeksi'], maxVals.kp);
    let n_e = norm(stormParams['jeoelektrik_alan_e'], maxVals.e_field);
    let n_ae = norm(stormParams['ae_indeksi'], maxVals.ae);
    let n_asyh = norm(stormParams['asy_h_indeksi'], maxVals.asy_h);
    let n_pc = norm(stormParams['pc_indeksi'], maxVals.pc);
    let n_k = norm(stormParams['k_indeksi'], maxVals.k);
    
    let scoreMagnetic = (n_bz * 0.25) + (n_dbdt * 0.20) + (n_dst * 0.12) + (n_symh * 0.10) + 
                        (n_kp * 0.08) + (n_e * 0.07) + (n_ae * 0.06) + (n_asyh * 0.05) + (n_pc * 0.04) + (n_k * 0.03);

    // Foton
    let rawXray = stormParams['x_ray_flux'] || '';
    let parsedXray = typeof rawXray === 'string' ? parseXRayFlux(rawXray) : parseFloat(rawXray) || 0;
    let n_xray = Math.min(1.0, parsedXray / maxVals.xray);
    let n_prot10 = norm(stormParams['proton_flux_10mev'], maxVals.proton10);
    let n_prot100 = norm(stormParams['proton_flux_100mev'], maxVals.proton100);
    let n_f107 = norm(stormParams['f10_7_cm_flux'], maxVals.f107);
    let scorePhoton = (n_xray * 0.35) + (n_prot10 * 0.30) + (n_prot100 * 0.20) + (n_f107 * 0.15);

    // İyonosferik
    let n_dtec = norm(stormParams['dtec'], maxVals.dtec);
    let n_tec = norm(stormParams['tec'], maxVals.tec);
    let n_gtec = norm(stormParams['gtec'], maxVals.gtec);
    let n_ftec = norm(stormParams['ftec'], maxVals.ftec);
    let n_qtec = norm(stormParams['qtec'], maxVals.qtec);
    
    let sumSurvIono = 0.58;
    let scoreIono = (n_dtec * (0.25/sumSurvIono)) + (n_tec * (0.12/sumSurvIono)) + 
                    (n_gtec * (0.09/sumSurvIono)) + (n_ftec * (0.07/sumSurvIono)) + 
                    (n_qtec * (0.05/sumSurvIono));

    // Global Score
    let perfectSimilarity = (scoreMagnetic * 0.39) + (scoreKinetic * 0.31) + (scorePhoton * 0.18) + (scoreIono * 0.12);
    return Math.max(0, Math.min(1.0, perfectSimilarity));
}

function calculateFuzzySimilarity(rawInputs, formulas) {
    const maxVals = {
        vp: 3000, np: 150, pd: 150, pe: 30,
        bz: 150, db_dt: 5000, dst: 1500, sym_h: 600, kp: 9, k: 9, e_field: 30, 
        ae: 4000, asy_h: 600, pc: 25,
        xray: 5e-3, proton10: 100000, proton100: 10000, f107: 400,
        dtec: 150, tec: 300, gtec: 300, ftec: 150, qtec: 50
    };

    const norm = (val, maxLim) => Math.min(1.0, Math.abs(parseFloat(val) || 0) / maxLim);

    // Kinetik
    let n_vp = norm(rawInputs['proton_hizi_vp'], maxVals.vp);
    let n_np = norm(rawInputs['proton_yogunlugu_np'], maxVals.np);
    let n_pd = norm(formulas['1_pd'] || rawInputs['dinamik_basinc_pd'], maxVals.pd);
    let n_pe = norm(formulas['3_pe'] || rawInputs['etkin_basinc_pe'], maxVals.pe);
    let scoreKinetic = (n_vp * 0.40) + (n_np * 0.30) + (n_pe * 0.18) + (n_pd * 0.12);

    // Manyetik
    let bz_val = parseFloat(rawInputs['kuzey_guney_imf_bz']) || 0;
    let yon = document.getElementById('param-imf_yonu')?.value || 'Güney';
    let bz_risk = (yon === 'Güney') ? Math.abs(bz_val) : (Math.abs(bz_val) * 0.1); 
    let n_bz = norm(bz_risk, maxVals.bz);
    
    let n_dst = norm(rawInputs['dst_indeksi'], maxVals.dst);
    let n_dbdt = norm(formulas['19_degisim_E'] || rawInputs['db_dt'], maxVals.db_dt);
    let n_symh = norm(rawInputs['sym_h_indeksi'], maxVals.sym_h);
    let n_kp = norm(rawInputs['kp_indeksi'], maxVals.kp);
    let n_e = norm(formulas['20_E_Field'] || rawInputs['jeoelektrik_alan_e'], maxVals.e_field);
    let n_ae = norm(formulas['17_AE'] || rawInputs['ae_indeksi'], maxVals.ae);
    let n_asyh = norm(rawInputs['asy_h_indeksi'], maxVals.asy_h);
    let n_pc = norm(formulas['18_PC'] || rawInputs['pc_indeksi'], maxVals.pc);
    let n_k = norm(rawInputs['k_indeksi'], maxVals.k);
    
    let scoreMagnetic = (n_bz * 0.25) + (n_dbdt * 0.20) + (n_dst * 0.12) + (n_symh * 0.10) + 
                        (n_kp * 0.08) + (n_e * 0.07) + (n_ae * 0.06) + (n_asyh * 0.05) + (n_pc * 0.04) + (n_k * 0.03);

    // Foton
    let rawXray = document.getElementById('param-x_ray_flux')?.value || '';
    let parsedXray = parseXRayFlux(rawXray);
    let n_xray = Math.min(1.0, parsedXray / maxVals.xray);
    let n_prot10 = norm(rawInputs['proton_flux_10mev'], maxVals.proton10);
    let n_prot100 = norm(rawInputs['proton_flux_100mev'], maxVals.proton100);
    let n_f107 = norm(rawInputs['f10_7_cm_flux'], maxVals.f107);
    let scorePhoton = (n_xray * 0.35) + (n_prot10 * 0.30) + (n_prot100 * 0.20) + (n_f107 * 0.15);

    // İyonosferik
    let n_dtec = norm(formulas['9_dTEC'] || rawInputs['dtec'], maxVals.dtec);
    let n_tec = norm(rawInputs['tec'], maxVals.tec);
    let n_gtec = norm(formulas['8_GTEC'] || rawInputs['gtec'], maxVals.gtec);
    let n_ftec = norm(formulas['12_fTEC'] || rawInputs['ftec'], maxVals.ftec);
    let n_qtec = norm(formulas['11_qTEC'] || rawInputs['qtec'], maxVals.qtec);
    
    let sumSurvIono = 0.58;
    let scoreIono = (n_dtec * (0.25/sumSurvIono)) + (n_tec * (0.12/sumSurvIono)) + 
                    (n_gtec * (0.09/sumSurvIono)) + (n_ftec * (0.07/sumSurvIono)) + 
                    (n_qtec * (0.05/sumSurvIono));

    // Global Score
    let perfectSimilarity = (scoreMagnetic * 0.39) + (scoreKinetic * 0.31) + (scorePhoton * 0.18) + (scoreIono * 0.12);
    perfectSimilarity = Math.max(0, Math.min(1.0, perfectSimilarity));

    // UI Updates
    document.getElementById('fuz-mag').textContent = '%' + (scoreMagnetic * 100).toFixed(1);
    document.getElementById('fuz-kin').textContent = '%' + (scoreKinetic * 100).toFixed(1);
    document.getElementById('fuz-rad').textContent = '%' + (scorePhoton * 100).toFixed(1);
    document.getElementById('fuz-ion').textContent = '%' + (scoreIono * 100).toFixed(1);
    document.getElementById('perfect-storm-similarity').textContent = '%' + (perfectSimilarity * 100).toFixed(1);

    // Güvenlik Katsayısı Renk Sistemi (0.8 eşik)
    // 0-0.4: Yeşil (Güvenli), 0.4-0.6: Sarı, 0.6-0.8: Turuncu, 0.8-1: Kırmızı (Tehlikeli)
    const simBox = document.getElementById('fuzzy-similarity-box');
    const simText = document.getElementById('perfect-storm-similarity');
    
    // Önceki renk sınıflarını temizle
    simBox.classList.remove('bg-green-900/40', 'bg-yellow-900/40', 'bg-orange-900/40', 'bg-red-900/40', 'bg-purple-900/40',
                            'border-green-500/50', 'border-yellow-500/50', 'border-orange-500/50', 'border-red-500/50', 'border-purple-500/50');
    simText.classList.remove('text-green-400', 'text-yellow-400', 'text-orange-400', 'text-red-400', 'text-white');
    
    let riskLabel = '';
    if (perfectSimilarity < 0.4) {
        // Yeşil - Güvenli
        simBox.classList.add('bg-green-900/40', 'border-green-500/50');
        simText.classList.add('text-green-400');
        riskLabel = 'GÜVENLİ';
    } else if (perfectSimilarity < 0.6) {
        // Sarı - Orta Risk
        simBox.classList.add('bg-yellow-900/40', 'border-yellow-500/50');
        simText.classList.add('text-yellow-400');
        riskLabel = 'DİKKAT';
    } else if (perfectSimilarity < 0.8) {
        // Turuncu - Yüksek Risk
        simBox.classList.add('bg-orange-900/40', 'border-orange-500/50');
        simText.classList.add('text-orange-400');
        riskLabel = 'YÜKSEK RİSK';
    } else {
        // Kırmızı - Tehlikeli
        simBox.classList.add('bg-red-900/40', 'border-red-500/50');
        simText.classList.add('text-red-400');
        riskLabel = 'TEHLİKELİ';
    }
    
    // Risk etiketini güncelle veya oluştur
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

    return perfectSimilarity;
}

function toNumericParamValueWCS(key, value) {
    if (key === 'x_ray_flux') return parseXRayFlux(value);
    const parsed = parseFloat((value ?? 0).toString().replace(',', '.').replace(/[^\d.-]/g, ''));
    if (!Number.isFinite(parsed)) return 0;
    return key === 'kuzey_guney_imf_bz' ? Math.abs(parsed) : parsed;
}

function normalizeWithRangeWCS(value, key) {
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
}

function getAHPWeightsWCS() {
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
}

function buildCurrentVectorWCS(rawInputs, formulas) {
    return {
        proton_yogunlugu_np: rawInputs['proton_yogunlugu_np'] || 0,
        proton_hizi_vp: rawInputs['proton_hizi_vp'] || 0,
        kuzey_guney_imf_bz: Math.abs(rawInputs['kuzey_guney_imf_bz'] || 0),
        dinamik_basinc_pd: parseFloat(formulas['1_pd']) || rawInputs['dinamik_basinc_pd'] || 0,
        etkin_basinc_pe: parseFloat(formulas['3_pe']) || rawInputs['etkin_basinc_pe'] || 0,
        x_ray_flux: rawInputs['x_ray_flux'] || (document.getElementById('param-x_ray_flux')?.value || 0),
        f10_7_cm_flux: rawInputs['f10_7_cm_flux'] || 0,
        proton_flux_10mev: rawInputs['proton_flux_10mev'] || 0,
        proton_flux_100mev: rawInputs['proton_flux_100mev'] || 0,
        tec: rawInputs['tec'] || parseFloat(formulas['8_GTEC']) || 0,
        dtec: parseFloat(formulas['9_dTEC']) || rawInputs['dtec'] || 0,
        gtec: parseFloat(formulas['8_GTEC']) || rawInputs['gtec'] || 0,
        qtec: parseFloat(formulas['11_qTEC']) || rawInputs['qtec'] || 0,
        ftec: parseFloat(formulas['12_fTEC']) || rawInputs['ftec'] || 0,
        kp_indeksi: rawInputs['kp_indeksi'] || 0,
        k_indeksi: rawInputs['k_indeksi'] || 0,
        dst_indeksi: rawInputs['dst_indeksi'] || 0,
        sym_h_indeksi: rawInputs['sym_h_indeksi'] || 0,
        asy_h_indeksi: rawInputs['asy_h_indeksi'] || 0,
        ae_indeksi: parseFloat(formulas['17_AE']) || rawInputs['ae_indeksi'] || 0,
        pc_indeksi: parseFloat(formulas['18_PC']) || rawInputs['pc_indeksi'] || 0,
        db_dt: Math.abs(parseFloat(formulas['19_degisim_E']) || rawInputs['db_dt'] || 0),
        jeoelektrik_alan_e: parseFloat(formulas['20_E_Field']) || rawInputs['jeoelektrik_alan_e'] || 0
    };
}

function updateWeightedCosineUI(bestMatch) {
    const nameEl = document.getElementById('weighted-cosine-storm-name');
    const scoreEl = document.getElementById('weighted-cosine-score');
    if (!nameEl || !scoreEl) return;
    if (!bestMatch) {
        nameEl.textContent = 'Tarihteki fırtına verisi bulunamadı';
        scoreEl.textContent = '%0.0';
        return;
    }
    nameEl.textContent = bestMatch.name;
    scoreEl.textContent = '%' + (bestMatch.similarity * 100).toFixed(1);
}

function updateFinalRiskScore(perfectSimilarity, weightedCosineSimilarity) {
    const scoreEl = document.getElementById('final-risk-score');
    if (!scoreEl) return;

    const s = Math.max(0, Math.min(1, parseFloat(perfectSimilarity) || 0));
    const adjustedC = parseFloat(weightedCosineSimilarity) || 0; // Artık zaten %40'lık kısmı içeriyor

    // Final Risk = Mükemmel Skor × %60 + Ayarlanmış Kosinüs (zaten %40'lık)
    const finalRisk = (s * 0.6) + adjustedC;

    scoreEl.textContent = finalRisk.toFixed(3);
}

function calculateWeightedCosineSimilarity(rawInputs, formulas) {
    if (!Array.isArray(storms) || !storms.length) {
        updateWeightedCosineUI(null);
        return 0;
    }
    const currentVector = buildCurrentVectorWCS(rawInputs, formulas);
    const weights = getAHPWeightsWCS();
    const normalizedA = {};

    WCS_PARAM_KEYS.forEach((key) => {
        const rawA = toNumericParamValueWCS(key, currentVector[key]);
        normalizedA[key] = normalizeWithRangeWCS(rawA, key);
    });

    let best = null;
    storms.forEach((storm) => {
        const params = storm?.parametreler || {};
        let pay = 0;
        let aTerm = 0;
        let bTerm = 0;

        WCS_PARAM_KEYS.forEach((key) => {
            const w = weights[key] || 0;
            const normalizedB = normalizeWithRangeWCS(toNumericParamValueWCS(key, params[key]), key);
            const a = normalizedA[key] || 0;
            pay += w * a * normalizedB;
            aTerm += w * a * a;
            bTerm += w * normalizedB * normalizedB;
        });

        const payda = Math.sqrt(aTerm) * Math.sqrt(bTerm);
        const similarity = payda > 0 ? (pay / payda) : 0;
        
        // En yüksek kosinüs benzerliğine sahip fırtınayı bul
        if (!best || similarity > best.similarity) {
            best = {
                name: storm.firtina_adi || `Fırtına ${storm.id}`,
                similarity: Math.max(0, Math.min(1, similarity)),
                params: params
            };
        }
    });

    if (!best) {
        updateWeightedCosineUI(null);
        return 0;
    }

    // En benzer fırtınanın "mükemmel fırtına" skorunu hesapla
    const stormPerfectScore = calculateStormPerfectSimilarity(best.params);
    
    // Kosinüs benzerliği × o fırtınanın mükemmel skoru × %40
    const finalValue = best.similarity * stormPerfectScore * 0.4;
    
    best.stormPerfectScore = stormPerfectScore;
    best.finalValue = finalValue;
    
    updateWeightedCosineUI(best);
    return finalValue;
}

function renderFormulasGrid(formulas) {
    const grid = document.getElementById('formulas-result-grid');
    grid.innerHTML = '';
    
    const fMap = {
        "1_pd": { label: "Dinamik Basınç", unit: "nPa", desc: "Güneş rüzgarının manyetosfere uyguladığı anlık mekanik baskıyı gösterir. Değer yükseldikçe kalkan daha fazla zorlanır." },
        "2_i": { label: "IMF Faktörü", unit: "Skaler", desc: "Gezegenlerarası manyetik alanın etkinlik katsayısıdır. Jeomanyetik bağlanmanın ne kadar verimli olacağını özetler." },
        "3_pe": { label: "Etkin Basınç", unit: "nPa", desc: "Fırtınanın sisteme aktardığı etkili enerji baskısını temsil eder. Operasyonel risk değerlendirmesinde doğrudan kullanılır." },
        "4_Tc": { label: "Bileşik Tehdit", unit: "Skor", desc: "Birden çok fiziksel göstergenin birleşik tehdit puanıdır. Genel fırtına şiddetini tek bir sayıda özetler." },
        "5_dx": { label: "Hız Farkı", unit: "km/s", desc: "Akış hızındaki farkı gösterir ve şok/kararsızlık potansiyeline işaret eder. Büyük farklar daha sert etki olasılığını artırır." },
        "6_dE": { label: "Delta Enerji", unit: "RMSE", desc: "Enerji değişiminin sapma büyüklüğünü ifade eder. Yüksek değer, sistemde normalden güçlü bir oynaklık olduğunu gösterir." },
        "7_q": { label: "Güç Katsayısı", unit: "Katsayı", desc: "Fırtına etkisinin göreli güç katsayısıdır. Katsayı arttıkça olayın etkinliği ve etkisi yükselir." },
        "8_GTEC": { label: "GTEC", unit: "TECU", desc: "Bölgesel/toplam iyonosferik elektron içeriğini temsil eder. Haberleşme ve GNSS performansını doğrudan etkiler." },
        "9_dTEC": { label: "TEC Sapması", unit: "TECU", desc: "TEC değerindeki anlık sapmayı gösterir. Yüksek sapma, iyonosferde düzensizlik ve konumlama hatası riskini artırır." },
        "10_Delta_TEC": { label: "TEC Oranı", unit: "%", desc: "TEC değişiminin göreli oranını yüzde olarak verir. İyonosferik bozulmanın ölçeğini hızlıca okumayı sağlar." },
        "11_qTEC": { label: "Normal TEC", unit: "TECU", desc: "Sakin koşullara yakın referans TEC seviyesini temsil eder. Karşılaştırma için baz çizgi görevi görür." },
        "12_fTEC": { label: "Fırtına TEC", unit: "TECU", desc: "Fırtına koşullarında beklenen TEC seviyesidir. Artış veya düşüş, sinyal kırılması ve gecikme riskini etkiler." },
        "13_fTEC_adj": { label: "Düzeltilmiş TEC", unit: "TECU", desc: "Model düzeltmeleri uygulanmış TEC çıktısıdır. Operasyonel yorum için daha dengeli bir tahmin sunar." },
        "14_Dst_actual": { label: "Gerçek Dst", unit: "nT", desc: "Halka akımının jeomanyetik etkisini gösteren ölçülmüş Dst değeridir. Daha negatif değerler daha güçlü fırtına anlamına gelir." },
        "15_Dst_star": { label: "Düzenlenmiş Dst*", unit: "nT", desc: "Basınç etkilerinden arındırılmış Dst tahminidir. Fırtınanın saf manyetik şiddetini daha net izlemeyi sağlar." },
        "16_dDst_dt": { label: "Burton dDst/dt", unit: "nT/sa", desc: "Dst'nin zamana göre değişim hızını verir. Hızlı düşüşler, fırtınanın güçlenmekte olduğuna işaret eder." },
        "17_AE": { label: "AE İndeksi", unit: "nT", desc: "Auroral elektrojet aktivitesini gösterir. Yükselmesi kutupsal akımların ve auroral etkilerin arttığını belirtir." },
        "18_PC": { label: "PC İndeksi", unit: "mV/m", desc: "Polar cap bölgesindeki jeo-uzaysal sürücüyü temsil eder. Yüksek değerler enerji girişinin kuvvetli olduğunu gösterir." },
        "19_degisim_E": { label: "dB/dt", unit: "nT/sa", desc: "Manyetik alan değişim hızını ifade eder. Yükselmesi indüklenen akım ve altyapı stresi riskini artırır." },
        "20_E_Field": { label: "Jeoelektrik E", unit: "V/km", desc: "Yeryüzünde indüklenen jeoelektrik alan şiddetidir. Özellikle uzun iletim hatlarında GIC riskini yükseltir." },
    };

    Object.keys(formulas).forEach(key => {
        let val = formulas[key];
        let meta = fMap[key] || { label: key, unit: "" };
        
        const description = meta.desc || `${meta.label} çıktısı hesaplanan fiziksel parametrenin büyüklüğünü gösterir.`;

        let box = `
            <div class="bg-white/5 border border-white/10 p-3 rounded-lg text-center hover:bg-white/10 hover:border-solar-500/30 transition-all cursor-default relative group">
                <span class="absolute top-2 right-2 w-4 h-4 rounded-full border border-slate-500/70 text-slate-300 text-[10px] leading-4 text-center font-bold cursor-help">?</span>
                <div class="hidden group-hover:block absolute right-2 top-7 z-20 w-56 p-2 rounded-lg bg-slate-900 border border-slate-700 text-[10px] leading-4 text-slate-200 text-left shadow-lg">
                    ${description}
                </div>
                <p class="text-[10px] text-slate-400 font-semibold truncate pr-5" title="${meta.label}">${meta.label}</p>
                <p class="text-lg font-bold text-white">${val}</p>
                <p class="text-[10px] text-slate-500">${meta.unit}</p>
            </div>
        `;
        grid.innerHTML += box;
    });
}

function updateAnimationIntensity(score) {
    const wind = document.getElementById('anim-wind');
    const shield = document.getElementById('anim-shield');
    const msg = document.getElementById('anim-status-msg');
    if (!wind || !shield || !msg) return;
    
    // Clear previous dynamic classes
    wind.classList.remove('via-red-500/60', 'via-orange-500/40', 'via-orange-400/30');
    shield.classList.remove('border-l-red-500', 'border-l-orange-400', 'border-l-cyan-400');
    
    if(score >= 80) {
        wind.style.animationDuration = '0.5s';
        wind.classList.add('via-red-500/60');
        shield.classList.add('border-l-red-500'); 
        shield.style.transform = 'scale(0.8) translateX(-15px) skewX(10deg)';
        msg.textContent = "🛡️ Manyetosfer yoğun basınca maruz kalıyor! Kritik çökme riski.";
        msg.className = "absolute bottom-4 left-1/2 -translate-x-1/2 text-xs font-bold text-red-500";
    } else if (score >= 45) {
        wind.style.animationDuration = '1.2s';
        wind.classList.add('via-orange-500/50');
        shield.classList.add('border-l-orange-400');
        shield.style.transform = 'scale(0.9) translateX(-5px)';
        msg.textContent = "⚠️ Kalkan esnemesi gözlendi. Korona dalgası ulaştı.";
        msg.className = "absolute bottom-4 left-1/2 -translate-x-1/2 text-xs font-semibold text-orange-400";
    } else {
        wind.style.animationDuration = '3s';
        wind.classList.add('via-orange-400/20');
        shield.classList.add('border-l-cyan-400');
        shield.style.transform = 'scale(1)';
        msg.textContent = "✅ Manyetosferik kalkan stabil durumda.";
        msg.className = "absolute bottom-4 left-1/2 -translate-x-1/2 text-xs text-slate-400";
    }
}

function runCalculation() {
    // 0. Tüm parametrelerin doldurulup doldurulmadığını kontrol et
    const missingParams = [];
    const paramLabels = {
        'proton_yogunlugu_np': 'Proton Yoğunluğu (np)',
        'proton_hizi_vp': 'Proton Hızı (vp)',
        'kuzey_guney_imf_bz': 'IMF Bz',
        'dinamik_basinc_pd': 'Dinamik Basınç (pd)',
        'etkin_basinc_pe': 'Etkin Basınç (pe)',
        'x_ray_flux': 'X-Ray Flux',
        'f10_7_cm_flux': 'F10.7 cm Flux',
        'proton_flux_10mev': 'Proton Flux (>10 MeV)',
        'proton_flux_100mev': 'Proton Flux (>100 MeV)',
        'tec': 'TEC',
        'dtec': 'dTEC',
        'gtec': 'GTEC',
        'qtec': 'qTEC',
        'ftec': 'fTEC',
        'kp_indeksi': 'Kp İndeksi',
        'k_indeksi': 'K İndeksi',
        'dst_indeksi': 'Dst İndeksi',
        'sym_h_indeksi': 'SYM-H İndeksi',
        'asy_h_indeksi': 'ASY-H İndeksi',
        'ae_indeksi': 'AE İndeksi',
        'pc_indeksi': 'PC İndeksi',
        'db_dt': 'dB/dt',
        'jeoelektrik_alan_e': 'Jeoelektrik Alan (E)'
    };
    
    paramKeys.forEach(key => {
        if (key === 'imf_yonu') return; // Dropdown her zaman değer içerir
        const inputEl = document.getElementById('param-' + key);
        if (!inputEl) return;
        const val = inputEl.value.trim();
        if (val === '' || val === null || val === undefined) {
            missingParams.push(paramLabels[key] || key);
        }
    });
    
    if (missingParams.length > 0) {
        alert('⚠️ Lütfen tüm parametreleri doldurun!\n\nEksik alanlar:\n• ' + missingParams.join('\n• '));
        return;
    }
    
    // 1. Ekranı Değiştir (Formları Gizle)
    document.getElementById('historic-storms-section').classList.add('hidden');
    document.getElementById('parameter-entry-section').classList.add('hidden');
    document.getElementById('calculation-button-section').classList.add('hidden');
    
    // 2. Sonuç Alanını Göster
    document.getElementById('results-section').classList.remove('hidden');
    
    const result = computeStormRisk();

    // 20 Formül Grid Render & Fuzzy Logic Simülasyonu
    const stormJSONStr = localStorage.getItem("solaris_calc_storm");
    let parsedStormInfo = null;
    if(stormJSONStr) {
        parsedStormInfo = JSON.parse(stormJSONStr);
        renderFormulasGrid(parsedStormInfo.formulas);
        const perfectSimilarity = calculateFuzzySimilarity(parsedStormInfo.raw_inputs, parsedStormInfo.formulas);
        const weightedCosineSimilarity = calculateWeightedCosineSimilarity(parsedStormInfo.raw_inputs, parsedStormInfo.formulas);
        updateFinalRiskScore(perfectSimilarity, weightedCosineSimilarity);
    }
    
    // LLM API Çağrısı
    invokeBedrockLLM(parsedStormInfo, result);

    // Yukarı kaydır
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetCalculation() {
    document.getElementById('results-section').classList.add('hidden');
    document.getElementById('historic-storms-section').classList.remove('hidden');
    document.getElementById('parameter-entry-section').classList.remove('hidden');
    document.getElementById('calculation-button-section').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function invokeBedrockLLM(stormInfo, resultObj) {
    if (!stormInfo) return; // Güvenlik kontrolü
    
    document.getElementById('ai-loading').classList.remove('hidden');
    document.getElementById('ai-content').classList.add('hidden');
    document.getElementById('ai-content').innerHTML = ""; // Temizle

    // 1. Prompt Verisi Hazırlığı (Dinamik Fırtına Verileri)
    const maxSim = document.getElementById('perfect-storm-similarity')?.textContent || "%0.0";
    
    let stormDetails = `
    - Dinamik Basınç: ${stormInfo.formulas['1_pd']} nPa
    - Etkin Basınç: ${stormInfo.formulas['3_pe']} nPa
    - Manyetik Oran (Bz): ${stormInfo.raw_inputs['kuzey_guney_imf_bz']} nT
    - Kp İndeksi: ${stormInfo.raw_inputs['kp_indeksi'] || resultObj.kp}
    - Manyetik Akı Değişimi (dB/dt): ${stormInfo.formulas['19_degisim_E']} nT/sa
    - X-Ray Sınıfı: ${stormInfo.raw_inputs['x_ray_flux']}
    - İyonosferik Sapma (dTEC): ${stormInfo.formulas['9_dTEC']} TECU
    - Fırtına Genel Tehdit Skoru (0-100): ${resultObj.score.toFixed(1)}
    - Mükemmel Fırtına Uyum Yüzdesi: ${maxSim}
    `;

    // Bedrock System Prompt
    const systemPrompt = `Sen "SOLARIS" projesinin C-Level Uzay Havası (Space Weather) analiz yapay zekasısın.
    Amacın aşağıdaki parametrelere sahip güneş fırtınasının yeryüzündeki kurumlar ve kritik altyapılar (elektrik ağları, uydular, navigasyon) üzerindeki etkilerini tahmin edip acil bir Operasyon Planı sunmaktır.
    
    Görev Formatı ve Kuralları:
    1. Yalnızca geçerli HTML etiketleri kullan (<h4 class="text-rose-400 font-bold mb-3 uppercase flex items-center gap-2">, <ul class="text-slate-300 text-sm space-y-2 mb-5 list-disc pl-5">, vb.).
    2. Giriş yapma, "Merhaba", "İşte sonuçlar" gibi kalıplar HİÇ kullanma! Sadece HTML taslağını yaz.
    3. Ciddi, askeri ve bilimsel bir dil kullan. Aciliyet hissi ver.
    4. Raporu tam olarak 2 ana başlığa ("Hızlı Alınması Gereken Önlemler (Fırtına Öncesi)" ve "Kritik Altyapı Riskleri (Olay Esnası)") ayır. Her başlık altına özel tavsiyeler ver.

    Mevcut Fırtına Parametreleri:
    ${stormDetails}
    `;

    // 2. Laravel Backend'e Gerçek FETCH İsteği
    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        const response = await fetch("{{ route('analyze.storm') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ prompt: systemPrompt })
        });

        // 500 HTML hatalarını direkt yakalamak için RAW text alalım:
        const responseText = await response.text();
        let data;
        
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error("HAM Gelen Yanıt:", responseText); // Console'a HTML'i bas
            throw new Error("Sunucu JSON yerine geçersiz bir format (HTML) döndürdü. Sorunu görmek için F12 (Geliştirici Araçları) -> Console ekranına bakınız.");
        }

        if (!response.ok) {
            throw new Error(data.details || data.error || 'Yapay Zeka sunucusundan hata alındı.');
        }

        document.getElementById('ai-loading').classList.add('hidden');
        document.getElementById('ai-content').classList.remove('hidden');
        
        // AWS'den dönen Markdown/HTML içeriği ekranda gösterilir
        document.getElementById('ai-content').innerHTML = `
            <div class="typing-cursor break-words">
                ${data.html}
            </div>
        `;

    } catch (error) {
        document.getElementById('ai-loading').classList.add('hidden');
        document.getElementById('ai-content').classList.remove('hidden');
        document.getElementById('ai-content').innerHTML = `
            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
                <svg class="w-5 h-5 mb-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <strong>Bağlantı Hatası:</strong> ${error.message}
                <br><br><span class="text-xs text-red-500/70">Not: Eğer AWS_BEDROCK_KEY hatalıysa veya kurulum yapılmadıysa bu hatayı alırsınız. Lütfen .env dosyanızı kontrol edip sunucuyu yeniden başlatın.</span>
            </div>
        `;
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    loadStorms();
    
    paramKeys.forEach(key => {
        const input = document.getElementById('param-' + key);
        if (input) {
            input.addEventListener('input', updateFilledCount);
        }
    });
});
</script>
@endsection
