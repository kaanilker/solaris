<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tarihi Fırtınalar — Güneş Fırtınaları | Solar Watch</title>
<meta name="description" content="Tarihi güneş fırtınalarını 3D interaktif görselleştirme ile keşfedin. Carrington 1859, Quebec 1989 ve daha fazlası.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700&family=Share+Tech+Mono&family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

{{-- Tailwind CSS CDN --}}
<script src="https://cdn.tailwindcss.com"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<style>
/* Navbar için mobile menu styles */
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
.divider-solar {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(253, 176, 34, 0.4), transparent);
}

*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
html, body {
  width: 100%; height: 100%;
  background: #000;
  overflow: hidden;
  font-family: 'Exo 2', sans-serif;
  color: #fff;
  cursor: grab;
}
body:active { cursor: grabbing; }
#canvas {
  position: fixed;
  inset: 0;
  display: block;
  width: 100%;
  height: 100%;
  z-index: 8;
}

/* ── FIRTINA SEÇİM EKRANI ── */
#storm-select {
  position: fixed;
  top: 72px; left: 0; right: 0; bottom: 0;
  background: radial-gradient(circle at center, rgba(5, 10, 28, 0.58), rgba(0, 0, 12, 0.72));
  backdrop-filter: blur(4px);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  z-index: 260; padding: 24px 20px; overflow-y: auto;
  transition: opacity .8s ease;
}
.ss-logo { font-family: 'Share Tech Mono', monospace; font-size: clamp(14px,2.5vw,22px); letter-spacing: 8px; color: #fff; margin-bottom: 6px; }
.ss-logo span { color: #ffd060; }
.ss-sub { font-size: 9px; letter-spacing: 4px; color: rgba(100,160,255,.35); text-transform: uppercase; margin-bottom: 10px; }
.ss-desc { font-size: 10px; color: rgba(255,255,255,.25); letter-spacing: .5px; margin-bottom: 32px; text-align: center; line-height: 1.7; }
.ss-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; width: 100%; max-width: 1000px; margin-bottom: 24px; }
.ss-card { background: rgba(255,255,255,.02); border: 1px solid rgba(100,150,255,.15); padding: 16px 16px 14px; cursor: pointer; transition: border-color .2s, background .2s, transform .15s; position: relative; overflow: hidden; }
.ss-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; background: var(--card-color, rgba(100,160,255,.4)); opacity: .6; transition: opacity .2s; }
.ss-card:hover { border-color: var(--card-color, rgba(100,160,255,.5)); background: rgba(255,255,255,.04); transform: translateY(-2px); }
.ss-card:hover::before { opacity: 1; }
.ss-card-year { font-family: 'Share Tech Mono', monospace; font-size: 9px; letter-spacing: 3px; color: var(--card-color, rgba(100,160,255,.5)); margin-bottom: 4px; }
.ss-card-name { font-family: 'Share Tech Mono', monospace; font-size: 12px; font-weight: 700; letter-spacing: 1px; color: #fff; margin-bottom: 8px; line-height: 1.3; }
.ss-card-desc { font-size: 9px; color: rgba(255,255,255,.35); line-height: 1.6; margin-bottom: 10px; }
.ss-card-params { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 6px; }
.ss-chip { font-family: 'Share Tech Mono', monospace; font-size: 8px; letter-spacing: .5px; padding: 2px 7px; border: 1px solid rgba(255,255,255,.1); color: rgba(255,255,255,.35); background: rgba(255,255,255,.03); }
.ss-chip.chip-warn { border-color: rgba(255,200,60,.3); color: rgba(255,200,60,.7); }
.ss-chip.chip-danger { border-color: rgba(255,80,60,.35); color: rgba(255,100,70,.8); }
.ss-card-g { font-family: 'Share Tech Mono', monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; padding: 3px 8px; border: 1px solid; display: inline-block; }

/* Yukleme bar */
#ld-wrap {
  position: fixed;
  top: 72px; left: 0; right: 0; bottom: 0;
  background: radial-gradient(circle at center, rgba(5, 10, 28, 0.62), rgba(0, 0, 12, 0.78));
  backdrop-filter: blur(4px);
  display: none; flex-direction: column;
  align-items: center; justify-content: center;
  z-index: 261; transition: opacity .8s;
}
.ld-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: clamp(13px,2vw,18px); letter-spacing: 6px;
  color: rgba(255,255,255,.9); margin-bottom: 8px;
}
.ld-bar-wrap { width: 220px; height: 1px; background: rgba(255,255,255,.08); }
.ld-bar { height: 1px; background: rgba(120,180,255,.8); width: 0; transition: width .3s; }
.ld-pct { font-family: 'Share Tech Mono', monospace; font-size: 11px; color: rgba(120,180,255,.4); margin-top: 10px; letter-spacing: 2px; }
/* ── TOP BAR ── */
#topbar {
  position: fixed; top: 72px; left: 0; right: 0;
  height: 52px;
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 22px;
  background: linear-gradient(to bottom, rgba(0,0,0,.85) 0%, rgba(0,0,0,0) 100%);
  pointer-events: none; z-index: 320;
}
#topbar .logo { font-family: 'Share Tech Mono', monospace; font-size: 13px; letter-spacing: 4px; color: rgba(255,255,255,.85); }
#topbar .logo span { color: rgba(100,160,255,.7); }
#topbar .hint { font-size: 10px; letter-spacing: 2px; color: rgba(255,255,255,.2); text-transform: uppercase; }

/* ── BOTTOM BAR ── */
#bottombar {
  position: fixed; bottom: 0; right: 0;
  left: clamp(240px, 22vw, 290px);
  height: 56px;
  display: flex; align-items: center;
  padding: 0 22px;
  background: linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,0) 100%);
  pointer-events: none; z-index: 320;
  gap: 6px; overflow-x: auto;
}
.planet-pill {
  flex-shrink: 0; padding: 5px 13px;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 20px;
  font-size: 10px; letter-spacing: 1.5px;
  color: rgba(255,255,255,.45);
  cursor: pointer; pointer-events: all;
  transition: all .25s; background: rgba(0,0,0,.4); white-space: nowrap;
}
.planet-pill:hover, .planet-pill.active { border-color: rgba(255,255,255,.5); color: #fff; background: rgba(255,255,255,.08); }
.planet-pill.sun-pill:hover, .planet-pill.sun-pill.active { border-color: rgba(255,200,80,.7); color: #ffd060; background: rgba(255,160,0,.1); }

/* ── INFO PANEL ── */
#info {
  position: fixed; right: 0; top: 50%;
  transform: translateY(-50%) translateX(100%);
  width: clamp(200px, 18vw, 240px);
  background: rgba(3,8,20,.92);
  border: 1px solid rgba(100,150,255,.18);
  border-right: none;
  padding: 20px 18px;
  z-index: 20; pointer-events: none;
  transition: transform .4s cubic-bezier(.4,0,.2,1);
  backdrop-filter: blur(8px);
}
#info.visible { transform: translateY(-50%) translateX(0%); pointer-events: all; }
#info.sun-active { border-color: rgba(255,180,50,.3); }
#info.sun-active .pname { border-bottom-color: rgba(255,180,50,.2); }
#info.peeking { transform: translateY(-50%) translateX(100%); pointer-events: all; }
#info-tab {
  position: absolute; left: -28px; top: 50%;
  transform: translateY(-50%);
  width: 28px; height: 52px;
  background: rgba(3,8,20,.92);
  border: 1px solid rgba(100,150,255,.18); border-right: none;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 13px; color: rgba(100,160,255,.7);
  transition: color .2s, background .2s; user-select: none; z-index: 25;
}
#info-tab:hover { color: #fff; background: rgba(20,40,80,.95); }
#info .pname {
  font-family: 'Share Tech Mono', monospace;
  font-size: 17px; font-weight: 700; letter-spacing: 2px;
  margin-bottom: 14px;
  border-bottom: 1px solid rgba(100,150,255,.15); padding-bottom: 10px;
}
#info .row { display: flex; justify-content: space-between; align-items: baseline; font-size: 11px; margin-bottom: 8px; }
#info .row .key { color: rgba(255,255,255,.3); letter-spacing: 1px; }
#info .row .v { color: rgba(200,220,255,.9); font-family: 'Share Tech Mono', monospace; font-size: 10px; }

/* ── STORM PANEL ── */
#storm-panel {
  position: fixed; left: 14px; top: 56px;
  width: clamp(220px, 20vw, 260px);
  max-height: calc(100vh - 70px);
  overflow-y: auto; overflow-x: hidden;
  background: rgba(3,8,20,.92);
  border: 1px solid rgba(100,150,255,.18);
  padding: 14px 14px 16px;
  z-index: 20; backdrop-filter: blur(8px);
  transition: opacity .3s, transform .3s;
  scrollbar-width: thin;
  scrollbar-color: rgba(100,160,255,.25) transparent;
}
#storm-panel::-webkit-scrollbar { width: 3px; }
#storm-panel::-webkit-scrollbar-thumb { background: rgba(100,160,255,.25); border-radius: 2px; }
#storm-panel.sp-hidden { opacity: 0; pointer-events: none; transform: translateY(-8px); }
#storm-tab {
  position: absolute; right: 10px; top: 10px;
  width: 22px; height: 22px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 11px; color: rgba(100,160,255,.5);
  transition: color .2s; user-select: none; z-index: 25;
}
#storm-tab:hover { color: #fff; }
#storm-toggle-btn {
  position: fixed; left: 14px; top: 14px;
  padding: 5px 12px;
  background: rgba(3,8,20,.88);
  border: 1px solid rgba(100,150,255,.2);
  color: rgba(100,160,255,.6);
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px; letter-spacing: 2px;
  cursor: pointer; z-index: 25;
  transition: all .2s; display: none;
}
#storm-toggle-btn:hover { border-color: rgba(100,160,255,.5); color: #fff; }

/* ── GERİ BUTONU ── */
#back-btn {
  position: fixed; left: 14px; top: 14px;
  padding: 5px 12px;
  background: rgba(3,8,20,.88);
  border: 1px solid rgba(100,150,255,.2);
  color: rgba(100,160,255,.6);
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px; letter-spacing: 2px;
  cursor: pointer; z-index: 26;
  transition: all .2s; display: none;
}
#back-btn:hover { border-color: rgba(100,160,255,.5); color: #fff; }

.sp-divider { border: none; border-top: 1px solid rgba(100,150,255,.1); margin: 12px 0; }
.sp-view-row { display: flex; gap: 6px; margin-bottom: 8px; }
.sp-vbtn {
  flex: 1; padding: 6px 4px;
  border: 1px solid rgba(255,255,255,.12); background: rgba(0,0,0,.3);
  color: rgba(255,255,255,.4); font-family: 'Share Tech Mono', monospace;
  font-size: 9px; letter-spacing: 1.5px; cursor: pointer; transition: all .2s; text-align: center;
}
.sp-vbtn:hover, .sp-vbtn.active { border-color: rgba(100,160,255,.5); color: rgba(150,200,255,.9); background: rgba(100,160,255,.08); }
.sp-title {
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px; letter-spacing: 3px; color: rgba(100,160,255,.5);
  text-transform: uppercase; margin-bottom: 14px;
  padding-bottom: 8px; border-bottom: 1px solid rgba(100,150,255,.12);
}
.sp-group { margin-bottom: 12px; }
.sp-label {
  font-family: 'Share Tech Mono', monospace;
  font-size: 9px; letter-spacing: 1.5px; color: rgba(255,255,255,.35);
  display: flex; justify-content: space-between; margin-bottom: 5px;
}
.sp-label span { color: rgba(100,160,255,.5); }
.sp-slider {
  width: 100%; height: 2px; -webkit-appearance: none;
  background: rgba(255,255,255,.08); outline: none; margin-top: 5px; cursor: pointer;
}
.sp-slider::-webkit-slider-thumb {
  -webkit-appearance: none; width: 10px; height: 10px; border-radius: 50%;
  background: rgba(100,160,255,.8); cursor: pointer;
}
.sp-storm-badge {
  margin-top: 12px; padding: 8px 12px; text-align: center;
  font-family: 'Share Tech Mono', monospace; font-size: 13px; font-weight: 700;
  letter-spacing: 3px; border: 1px solid; transition: all .6s;
}

.storm-g0 { color: #00ff88; border-color: rgba(0,255,136,.4); background: rgba(0,255,136,.06); }
.storm-g1 { color: #bbff00; border-color: rgba(187,255,0,.4); background: rgba(187,255,0,.06); }
.storm-g2 { color: #ffcc00; border-color: rgba(255,204,0,.4); background: rgba(255,204,0,.06); }
.storm-g3 { color: #ff7700; border-color: rgba(255,119,0,.4); background: rgba(255,119,0,.08); }
.storm-g4 { color: #ff3300; border-color: rgba(255,51,0,.4); background: rgba(255,51,0,.08); }
.storm-g5 { color: #ff0000; border-color: rgba(255,0,0,.5); background: rgba(255,0,0,.1); animation: stormflash 1s infinite; }
@keyframes stormflash { 0%,100%{opacity:1} 50%{opacity:.5} }

#storm-alert {
  position: fixed; top: 60px; left: 50%;
  transform: translateX(-50%);
  display: flex; align-items: center; gap: 8px;
  padding: 7px 24px;
  font-family: 'Share Tech Mono', monospace;
  font-size: 11px; font-weight: 700; letter-spacing: 3px;
  border: 1px solid; white-space: nowrap;
  z-index: 30; pointer-events: none;
  transition: all .6s; opacity: 0;
}
#storm-alert.show { opacity: 1; }
#storm-alert-q {
  display: inline-flex; align-items: center; justify-content: center;
  width: 16px; height: 16px; border-radius: 50%;
  border: 1px solid currentColor;
  font-size: 9px; font-weight: 700; letter-spacing: 0;
  cursor: pointer; pointer-events: all;
  opacity: 0.7; flex-shrink: 0; transition: opacity .2s;
  font-family: 'Share Tech Mono', monospace;
}
#storm-alert-q:hover { opacity: 1; }

/* ── G SKALA PANELİ — ANA PANELİN AYNI YERİNDE AÇILIR ── */
#g-info-panel {
  position: fixed;
  left: 14px; top: 56px;
  width: clamp(220px, 20vw, 260px);
  max-height: calc(100vh - 70px);
  overflow-y: auto;
  overflow-x: hidden;
  background: rgba(3,8,20,.97);
  border: 1px solid rgba(100,150,255,.25);
  padding: 14px 14px 16px;
  z-index: 22;
  backdrop-filter: blur(12px);
  display: none;
  opacity: 0;
  transform: translateY(-6px);
  transition: opacity .25s ease, transform .25s ease;
  scrollbar-width: thin;
  scrollbar-color: rgba(100,160,255,.25) transparent;
}
#g-info-panel::-webkit-scrollbar { width: 3px; }
#g-info-panel::-webkit-scrollbar-thumb { background: rgba(100,160,255,.25); border-radius: 2px; }
#g-info-panel.gp-visible { opacity: 1; transform: translateY(0); }
#g-info-panel .gp-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 14px; padding-bottom: 8px;
  border-bottom: 1px solid rgba(100,150,255,.15);
}
#g-info-panel .gp-title { font-family: 'Share Tech Mono', monospace; font-size: 9px; letter-spacing: 3px; color: rgba(100,160,255,.8); text-transform: uppercase; }
#g-info-panel .gp-close {
  width: 22px; height: 22px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 13px;
  color: rgba(255,255,255,.45);
  border: 1px solid rgba(255,255,255,.15);
  font-family: 'Share Tech Mono', monospace;
  transition: color .2s, border-color .2s, background .2s;
  flex-shrink: 0;
}
#g-info-panel .gp-close:hover { color: #fff; border-color: rgba(255,255,255,.5); background: rgba(255,255,255,.08); }
.g-level-block { margin-bottom: 10px; border: 1px solid rgba(255,255,255,.06); padding: 9px 11px; }
.g-level-block.active-level { border-color: rgba(100,160,255,.3); background: rgba(100,160,255,.04); }
.g-level-header { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; }
.g-badge { font-family: 'Share Tech Mono', monospace; font-size: 10px; font-weight: 700; letter-spacing: 2px; padding: 2px 7px; border: 1px solid; }
.g-level-name { font-family: 'Share Tech Mono', monospace; font-size: 9px; letter-spacing: 1px; color: rgba(255,255,255,.45); text-transform: uppercase; }
.g-section-title { font-family: 'Share Tech Mono', monospace; font-size: 8px; letter-spacing: 2px; color: rgba(100,160,255,.5); text-transform: uppercase; margin-bottom: 4px; margin-top: 7px; }
.g-section-title:first-of-type { margin-top: 0; }
.g-text { font-size: 9px; line-height: 1.6; color: rgba(255,255,255,.48); letter-spacing: .2px; }
.g-risk-list, .g-measure-list { list-style: none; padding: 0; margin: 0; }
.g-risk-list li, .g-measure-list li { font-size: 9px; line-height: 1.6; color: rgba(255,255,255,.48); padding-left: 12px; position: relative; letter-spacing: .2px; }
.g-risk-list li::before { content: '▸'; position: absolute; left: 0; color: rgba(255,80,80,.6); font-size: 8px; top: 2px; }
.g-measure-list li::before { content: '✓'; position: absolute; left: 0; color: rgba(80,200,120,.6); font-size: 8px; top: 2px; }

/* ── PARAMETRE GÖRÜNTÜLEME SATIRLARI (salt okunur) ── */
.sp-param-block {
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(100,150,255,.08);
}
.sp-param-block:last-child { border-bottom: none; margin-bottom: 0; }
.sp-param-cat {
  font-family: 'Share Tech Mono', monospace;
  font-size: 8px; letter-spacing: 2.5px;
  color: rgba(100,160,255,.4);
  text-transform: uppercase;
  margin-bottom: 6px;
}
.sp-param-row {
  display: flex; justify-content: space-between; align-items: baseline;
  margin-bottom: 5px;
}
.sp-param-key {
  font-size: 9px; color: rgba(255,255,255,.3);
  letter-spacing: .5px; flex: 1;
}
.sp-param-val {
  font-family: 'Share Tech Mono', monospace;
  font-size: 10px; color: rgba(180,210,255,.8);
  text-align: right;
}
.sp-param-val.warn { color: rgba(255,200,60,.85); }
.sp-param-val.danger { color: rgba(255,90,60,.9); }

.scanlines {
  position: fixed; inset: 0;
  background: repeating-linear-gradient(0deg, rgba(0,0,0,0) 0, rgba(0,0,0,0) 3px, rgba(0,0,0,.025) 3px, rgba(0,0,0,.025) 4px);
  pointer-events: none; z-index: 5;
}

/* ── TAM EKRAN ANİMASYONLU ARKA PLAN ── */
@keyframes gecmisGlowFlow {
  0%   { transform: translate3d(-5%, -3%, 0) scale(1); opacity: .25; }
  25%  { transform: translate3d(3%, 5%, 0) scale(1.05); opacity: .35; }
  50%  { transform: translate3d(8%, -2%, 0) scale(1.1); opacity: .4; }
  75%  { transform: translate3d(-3%, 4%, 0) scale(1.03); opacity: .3; }
  100% { transform: translate3d(-5%, -3%, 0) scale(1); opacity: .25; }
}

@keyframes auroraWave {
  0%   { transform: translateY(0) rotate(0deg); opacity: 0.15; }
  50%  { transform: translateY(-20px) rotate(2deg); opacity: 0.25; }
  100% { transform: translateY(0) rotate(0deg); opacity: 0.15; }
}

@keyframes sunPulse {
  0%   { transform: scale(1); opacity: 0.3; }
  50%  { transform: scale(1.15); opacity: 0.5; }
  100% { transform: scale(1); opacity: 0.3; }
}

@keyframes particleFloat {
  0%   { transform: translateY(100vh) rotate(0deg); opacity: 0; }
  10%  { opacity: 0.6; }
  90%  { opacity: 0.6; }
  100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
}

@keyframes stormFlicker {
  0%, 100% { opacity: 0.2; }
  25% { opacity: 0.35; }
  50% { opacity: 0.15; }
  75% { opacity: 0.4; }
}

/* Ana kaplayan animasyon konteyner */
.gecmis-fullscreen-backdrop {
  position: fixed;
  inset: 0;
  overflow: hidden;
  pointer-events: none;
  z-index: 0;
  background: linear-gradient(180deg, 
    rgba(0, 0, 15, 1) 0%,
    rgba(5, 10, 30, 1) 50%,
    rgba(0, 0, 20, 1) 100%);
}

/* Güneş - merkez glow */
.gecmis-sun-core {
  position: absolute;
  top: 15%;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  height: 400px;
  border-radius: 50%;
  background: radial-gradient(ellipse at center,
    rgba(255, 200, 100, 0.4) 0%,
    rgba(255, 150, 50, 0.25) 30%,
    rgba(255, 100, 50, 0.15) 50%,
    rgba(200, 50, 50, 0.05) 70%,
    transparent 100%);
  filter: blur(40px);
  animation: sunPulse 8s ease-in-out infinite;
}

/* Ana aurora dalgası - sol */
.gecmis-aurora-left {
  position: absolute;
  top: 0;
  left: -10%;
  width: 60%;
  height: 100%;
  background: linear-gradient(135deg,
    transparent 0%,
    rgba(56, 189, 248, 0.15) 20%,
    rgba(99, 102, 241, 0.2) 40%,
    rgba(168, 85, 247, 0.15) 60%,
    transparent 80%);
  filter: blur(60px);
  animation: auroraWave 12s ease-in-out infinite;
}

/* Ana aurora dalgası - sağ */
.gecmis-aurora-right {
  position: absolute;
  top: 10%;
  right: -10%;
  width: 50%;
  height: 100%;
  background: linear-gradient(-135deg,
    transparent 0%,
    rgba(16, 185, 129, 0.15) 25%,
    rgba(59, 130, 246, 0.2) 50%,
    rgba(56, 189, 248, 0.1) 75%,
    transparent 100%);
  filter: blur(70px);
  animation: auroraWave 15s ease-in-out infinite reverse;
  animation-delay: -5s;
}

/* Güneş fırtınası dalgaları */
.gecmis-storm-wave {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 50%;
  background: linear-gradient(0deg,
    rgba(255, 100, 50, 0.08) 0%,
    rgba(255, 150, 100, 0.05) 30%,
    transparent 100%);
  filter: blur(30px);
  animation: stormFlicker 6s ease-in-out infinite;
}

/* Hareketli parçacıklar */
.gecmis-particles {
  position: absolute;
  inset: 0;
}
.gecmis-particle {
  position: absolute;
  width: 3px;
  height: 3px;
  border-radius: 50%;
  background: rgba(150, 200, 255, 0.6);
  box-shadow: 0 0 6px rgba(150, 200, 255, 0.4);
  animation: particleFloat 20s linear infinite;
}
.gecmis-particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 18s; }
.gecmis-particle:nth-child(2) { left: 20%; animation-delay: -3s; animation-duration: 22s; width: 2px; height: 2px; }
.gecmis-particle:nth-child(3) { left: 35%; animation-delay: -7s; animation-duration: 25s; }
.gecmis-particle:nth-child(4) { left: 45%; animation-delay: -2s; animation-duration: 19s; width: 4px; height: 4px; }
.gecmis-particle:nth-child(5) { left: 55%; animation-delay: -10s; animation-duration: 21s; }
.gecmis-particle:nth-child(6) { left: 65%; animation-delay: -5s; animation-duration: 24s; width: 2px; height: 2px; }
.gecmis-particle:nth-child(7) { left: 75%; animation-delay: -12s; animation-duration: 20s; }
.gecmis-particle:nth-child(8) { left: 85%; animation-delay: -8s; animation-duration: 23s; width: 3px; height: 3px; }
.gecmis-particle:nth-child(9) { left: 92%; animation-delay: -15s; animation-duration: 26s; }
.gecmis-particle:nth-child(10) { left: 5%; animation-delay: -18s; animation-duration: 17s; }

/* Orijinal glow efekti - güncellendi */
.gecmis-ambient-anim {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 80vw;
  height: 80vh;
  border-radius: 50%;
  pointer-events: none;
  background: radial-gradient(ellipse at center,
    rgba(56, 189, 248, 0.2) 0%,
    rgba(59, 130, 246, 0.15) 30%,
    rgba(99, 102, 241, 0.1) 50%,
    rgba(16, 185, 129, 0.08) 70%,
    transparent 100%);
  filter: blur(80px);
  animation: gecmisGlowFlow 10s ease-in-out infinite;
}

/* Grid overlay efekti */
.gecmis-grid-overlay {
  position: absolute;
  inset: 0;
  background-image: 
    linear-gradient(rgba(100, 150, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(100, 150, 255, 0.03) 1px, transparent 1px);
  background-size: 50px 50px;
  opacity: 0.5;
}
</style>
</head>
<body>

<header id="main-header" class="bg-slate-900 sticky top-0 z-[500] border-b border-slate-800">
    <nav class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            
            <!-- LOGO -->
            <div class="flex-shrink-0">
                <a href="/" class="flex items-center gap-1.5" title="SOLARIS">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c-.61 0-1.1-.49-1.1-1.1s.49-1.1 1.1-1.1 1.1.49 1.1 1.1-.49 1.1-1.1 1.1z"/>
                    </svg>
                    <span class="font-bold text-xl text-white">Solaris</span>
                </a>
            </div>

            <!-- DESKTOP NAVBAR -->
            <div class="hidden lg:flex flex-1 items-center justify-center">
                <ul class="flex items-center gap-6">
                    <li>
                        <a href="{{ url('/') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->routeIs('home')) text-white @else text-slate-400 hover:text-white @endif">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Canlı Veri
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/hesaplama') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('hesaplama')) text-white @else text-slate-400 hover:text-white @endif">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Hesaplama
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/gecmis') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('gecmis')) text-white @else text-slate-400 hover:text-white @endif">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tarihi Fırtınalar
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/hakkimizda') }}" class="flex items-center gap-2 px-3 py-2 text-sm font-medium @if(request()->is('hakkimizda')) text-white @else text-slate-400 hover:text-white @endif">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Hakkımızda
                        </a>
                    </li>
                </ul>
            </div>

            <!-- MOBILE MENU BUTTON -->
            <div class="flex items-center gap-3">
                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/5 transition-all duration-200" aria-label="Menüyü aç/kapat">
                    <svg class="w-6 h-6" id="icon-menu" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg class="w-6 h-6 hidden" id="icon-close" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- MOBILE MENU -->
        <div id="mobile-menu" class="lg:hidden pb-4">
            <ul class="flex flex-col gap-2 pt-3">
                <li>
                    <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300 @if(request()->routeIs('home')) text-blue-400 bg-blue-500/15 border border-blue-500/30 @else text-slate-300 hover:text-white hover:bg-white/5 @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Canlı Veri
                    </a>
                </li>
                <li>
                    <a href="{{ url('/hesaplama') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300 @if(request()->is('hesaplama')) text-solar-400 bg-solar-500/15 border border-solar-500/30 @else text-slate-300 hover:text-white hover:bg-white/5 @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Hesaplama
                    </a>
                </li>
                <li>
                    <a href="{{ url('/gecmis') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300 @if(request()->is('gecmis')) text-amber-400 bg-amber-500/15 border border-amber-500/30 @else text-slate-300 hover:text-white hover:bg-white/5 @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tarihi Fırtınalar
                    </a>
                </li>
                <li>
                    <a href="{{ url('/hakkimizda') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-300 @if(request()->is('hakkimizda')) text-emerald-400 bg-emerald-500/15 border border-emerald-500/30 @else text-slate-300 hover:text-white hover:bg-white/5 @endif">
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

<div class="gecmis-fullscreen-backdrop" aria-hidden="true">
  <!-- Güneş çekirdeği -->
  <div class="gecmis-sun-core"></div>
  
  <!-- Aurora dalgaları -->
  <div class="gecmis-aurora-left"></div>
  <div class="gecmis-aurora-right"></div>
  
  <!-- Güneş fırtınası dalgası -->
  <div class="gecmis-storm-wave"></div>
  
  <!-- Merkez glow -->
  <div class="gecmis-ambient-anim"></div>
  
  <!-- Grid overlay -->
  <div class="gecmis-grid-overlay"></div>
  
  <!-- Hareketli parçacıklar -->
  <div class="gecmis-particles">
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
    <div class="gecmis-particle"></div>
  </div>
</div>

<!-- Fırtına Seçim Ekranı -->
<div id="storm-select">
  <div class="ss-logo">☉ SOLAR<span>IS</span>:</div>
  <div class="ss-sub" style="color: rgba(255,70,70,.85);">Uyarı Sistemi</div>
  <div class="ss-desc">Simüle etmek istediğiniz tarihi güneş fırtınasını seçin.<br>Güneş sistemi o fırtınanın gerçek parametreleriyle yüklenecek.</div>
  <div class="ss-grid" id="ss-grid"></div>
</div>

<!-- Yukleme Ekrani -->
<div id="ld-wrap">
  <div class="ld-title">☉ SOLARIS</div>
  <div class="ld-bar-wrap"><div class="ld-bar" id="ldbar"></div></div>
  <div class="ld-pct" id="ldpct">0%</div>
</div>

<div class="scanlines"></div>
<canvas id="canvas"></canvas>

<!-- Top bar -->
<div id="topbar">
  <div class="logo">☉ SOLAR <span>WATCH</span></div>
  <div class="hint">Sol Tık: Döndür &nbsp;·&nbsp; Sağ Tık: Kaydır &nbsp;·&nbsp; Tekerlek: Yaklaştır &nbsp;·&nbsp; Gezegen/Güneş Tıkla: Bilgi</div>
</div>

<!-- Info panel -->
<div id="info">
  <div id="info-tab">❮</div>
  <div class="pname" id="pname">—</div>
  <div id="prows"></div>
</div>

<!-- Bottom planet pills -->
<div id="bottombar"></div>

<!-- Storm Panel -->
<div id="storm-panel" class="sp-hidden">
  <div id="storm-tab" title="Küçült">−</div>
  <div class="sp-title">☉ SOLARIS</div>

  <div class="sp-storm-badge storm-g0" id="storm-badge">G0 — NORMAL</div>

  <hr class="sp-divider">

  <!-- 1. Güneş Rüzgarı ve Manyetik Alan -->
  <div class="sp-param-block">
    <div class="sp-param-cat">Güneş Rüzgarı &amp; Manyetik Alan</div>
    <div class="sp-param-row">
      <span class="sp-param-key">Proton Hızı (vₚ)</span>
      <span class="sp-param-val" id="pv-vp">— km/s</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Proton Yoğunluğu (nₚ)</span>
      <span class="sp-param-val" id="pv-np">— cm⁻³</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">IMF Bz</span>
      <span class="sp-param-val" id="pv-bz">— nT</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Dinamik Basınç (pₐ)</span>
      <span class="sp-param-val" id="pv-pd">— nPa</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Etkin Basınç (pₑ)</span>
      <span class="sp-param-val" id="pv-pe">— nPa</span>
    </div>
  </div>

  <!-- 2. Jeomanyetik İndeksler -->
  <div class="sp-param-block">
    <div class="sp-param-cat">Jeomanyetik İndeksler</div>
    <div class="sp-param-row">
      <span class="sp-param-key">Dst İndeksi</span>
      <span class="sp-param-val" id="pv-dst">— nT</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Kp İndeksi</span>
      <span class="sp-param-val" id="pv-kp">—</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">K İndeksi</span>
      <span class="sp-param-val" id="pv-k">—</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">SYM-H</span>
      <span class="sp-param-val" id="pv-symh">— nT</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">ASY-H</span>
      <span class="sp-param-val" id="pv-asymh">— nT</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">AE İndeksi</span>
      <span class="sp-param-val" id="pv-ae">— nT</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">PC İndeksi</span>
      <span class="sp-param-val" id="pv-pc">—</span>
    </div>
  </div>

  <!-- 3. İyonküre ve TEC -->
  <div class="sp-param-block">
    <div class="sp-param-cat">İyonküre &amp; TEC</div>
    <div class="sp-param-row">
      <span class="sp-param-key">TEC</span>
      <span class="sp-param-val" id="pv-tec">— TECU</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">dTEC</span>
      <span class="sp-param-val" id="pv-dtec">— TECU/dk</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">GTEC (Küresel Ort.)</span>
      <span class="sp-param-val" id="pv-gtec">— TECU</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">qTEC (Sakin Referans)</span>
      <span class="sp-param-val" id="pv-qtec">— TECU</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">fTEC (Fırtına Tahmini)</span>
      <span class="sp-param-val" id="pv-ftec">— TECU</span>
    </div>
  </div>

  <!-- 4. Güneş Aktivitesi -->
  <div class="sp-param-block">
    <div class="sp-param-cat">Güneş Aktivitesi</div>
    <div class="sp-param-row">
      <span class="sp-param-key">X-Ray Flux</span>
      <span class="sp-param-val" id="pv-xray">— W/m²</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">F10.7 cm Flux</span>
      <span class="sp-param-val" id="pv-f107">— sfu</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Proton Flux ≥10 MeV</span>
      <span class="sp-param-val" id="pv-pf10">— pfu</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Proton Flux ≥100 MeV</span>
      <span class="sp-param-val" id="pv-pf100">— pfu</span>
    </div>
  </div>

  <!-- 5. Elektromanyetik Etkiler -->
  <div class="sp-param-block">
    <div class="sp-param-cat">Elektromanyetik Etkiler</div>
    <div class="sp-param-row">
      <span class="sp-param-key">dB/dt</span>
      <span class="sp-param-val" id="pv-dbdt">— nT/s</span>
    </div>
    <div class="sp-param-row">
      <span class="sp-param-key">Jeoelektrik Alan (E)</span>
      <span class="sp-param-val" id="pv-geo">— mV/km</span>
    </div>
  </div>

  <hr class="sp-divider">
  <div class="sp-group">
    <div class="sp-label">Görünüm &amp; Sıfırla</div>
    <div class="sp-view-row">
      <button class="sp-vbtn active" id="v3d">3D</button>
      <button class="sp-vbtn" id="vtop">ÜST</button>
      <button class="sp-vbtn" id="resetbtn">⟳</button>
    </div>
  </div>
  <hr class="sp-divider">
  <div class="sp-group">
    <div class="sp-label">Simülasyon Hızı <span id="speedval">1.0x</span></div>
    <input class="sp-slider" type="range" id="speedslider" min="0" max="5" step="0.1" value="1">
  </div>
</div>

<!-- Panel göster/gizle butonu -->
<button id="storm-toggle-btn">⚡ PARAMETRELER</button>

<!-- Geri butonu -->
<button id="back-btn">← FİRTINA SEÇ</button>

<!-- Fırtına Uyarı Banner -->
<div id="storm-alert" class="storm-g0">● SİSTEM NORMAL<span id="storm-alert-q" title="G Skalası Nedir?">?</span></div>

<!-- G Skalası Bilgi Paneli -->
<div id="g-info-panel">
  <div class="gp-header">
    <span class="gp-title">⚡ Jeomanyetik Fırtına Skalası</span>
    <span class="gp-close" id="g-info-close">✕</span>
  </div>

  <div class="g-level-block" data-level="0">
    <div class="g-level-header"><span class="g-badge storm-g0">G0</span><span class="g-level-name">Normal — Fırtına Yok</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Uzay havası koşulları normaldir. Dünya'nın manyetosfer kalkanı herhangi bir baskı altında değildir.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list"><li>Herhangi bir risk bulunmamaktadır.</li></ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list"><li>Rutin izleme yeterlidir.</li></ul>
  </div>

  <div class="g-level-block" data-level="1">
    <div class="g-level-header"><span class="g-badge storm-g1">G1</span><span class="g-level-name">Minör Fırtına</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Hafif jeomanyetik aktivite. Yıllık ortalama 1700 gün bu seviyede geçirilir. Kutup bölgelerinde kuzey ışıkları (aurora) görülebilir.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list">
      <li>Güç şebekelerinde zayıf dalgalanmalar görülebilir.</li>
      <li>Uydu yörüngelerinde çok küçük sürüklenme yaşanabilir.</li>
      <li>Hayvan göçlerinde hafif yön bozukluğu riski.</li>
    </ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list">
      <li>Uydu operatörleri rutin izlemeyi sürdürür.</li>
      <li>Enerji şirketleri şebeke parametrelerini gözlemler.</li>
    </ul>
  </div>

  <div class="g-level-block" data-level="2">
    <div class="g-level-header"><span class="g-badge storm-g2">G2</span><span class="g-level-name">Orta Şiddetli Fırtına</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Belirgin jeomanyetik aktivite. Kuzey ışıkları 55°'ye kadar (İsveç, Alaska düzeyi) alçalabilir. Yıllık yaklaşık 600 gün yaşanır.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list">
      <li>HF (kısa dalga) radyo iletişiminde kayıplar.</li>
      <li>Yüksek enlemlerde güç şebekelerinde alarm tetiklenebilir.</li>
      <li>Transformer voltaj düzensizlikleri.</li>
      <li>Uydu yüzey yükü ve sürüklenmesi artabilir.</li>
    </ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list">
      <li>Enerji şirketleri yedek sistemleri devreye alır.</li>
      <li>Havacılık radyo frekanslarını yedekle.</li>
      <li>GPS doğruluğu azalabileceğinden kritik navigasyonda dikkat.</li>
    </ul>
  </div>

  <div class="g-level-block" data-level="3">
    <div class="g-level-header"><span class="g-badge storm-g3">G3</span><span class="g-level-name">Güçlü Fırtına</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Ciddi jeomanyetik aktivite. Kuzey ışıkları 50° enlemine (Orta Avrupa, Kanada) kadar görülebilir. Yıllık yaklaşık 130 gün yaşanır.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list">
      <li>Güç şebekelerinde voltaj sorunları ve koruma sistemi tetiklenmeleri.</li>
      <li>HF radyosu geniş bölgelerde çalışamaz hale gelebilir.</li>
      <li>GPS hatası birkaç metreye çıkabilir; hassas ölçümler bozulur.</li>
      <li>Yüksek irtifada uçan uçuşlarda radyasyon maruziyeti artar.</li>
      <li>Uzay araçlarının yüzey yükü ve sürüklenme sorunları belirginleşir.</li>
    </ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list">
      <li>Kritik yük merkezlerinde yedek güç devreye alınmalı.</li>
      <li>Kutup rotasındaki uçuşlar daha alçak irtifaya veya alternatif güzergaha yönlendirilmeli.</li>
      <li>Uydu operatörleri güvenli mod prosedürlerini başlatmalı.</li>
      <li>Boru hatları jeomanyetik kaynaklı korozyon için izlenmeli.</li>
    </ul>
  </div>

  <div class="g-level-block" data-level="4">
    <div class="g-level-header"><span class="g-badge storm-g4">G4</span><span class="g-level-name">Şiddetli Fırtına</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Çok güçlü jeomanyetik aktivite. Kuzey ışıkları 45° enlemine (Akdeniz, ABD orta kesimleri) kadar inebilir. Yıllık yalnızca 4 gün civarında yaşanır.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list">
      <li>Geniş bölgelerde elektrik kesintileri ve transformatör hasarı riski.</li>
      <li>GPS ve navigasyon sistemlerinde ağır kesintiler.</li>
      <li>Tüm HF radyo iletişimi devre dışı kalabilir.</li>
      <li>Uydu yörünge bozulmaları ve konum hatası kritik düzeye ulaşır.</li>
      <li>Alçak yörüngeli uydularda atmosfer sürtünmesi artışıyla ömür kısalması.</li>
      <li>Uzay istasyonlarında ekipler için radyasyon tehdidi oluşabilir.</li>
    </ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list">
      <li>Güç şebekeleri koruyucu kapatma moduna geçmeli.</li>
      <li>Tüm kritik uçuşlar kutup rotasından çıkarılmalı.</li>
      <li>Uzay istasyonu mürettebatı korunaklı bölümlere taşınmalı.</li>
      <li>Banka ve finans sistemleri yedek iletişim kanallarına geçmeli.</li>
      <li>Acil durum jeneratörleri hazır tutulmalı.</li>
    </ul>
  </div>

  <div class="g-level-block" data-level="5">
    <div class="g-level-header"><span class="g-badge storm-g5">G5</span><span class="g-level-name">Aşırı (Extreme) Fırtına</span></div>
    <div class="g-section-title">Açıklama</div>
    <p class="g-text">Skalanın en üst seviyesi. Tarihin en büyük güneş fırtınası olan 1859 Carrington Olayı bu sınıfta yer alır. Günümüzde yaşanması küresel çaplı felaket senaryosuna yol açabilir. Yıllık yaklaşık 4 günden az yaşanır.</p>
    <div class="g-section-title">Olası Riskler</div>
    <ul class="g-risk-list">
      <li>Tüm kıtalarda yaygın elektrik kesintileri; haftalarca veya aylarca sürebilir.</li>
      <li>Büyük transformatörlerin kalıcı olarak hasar görmesi; üretimi yıllar alır.</li>
      <li>GPS, internet, mobil ağlar ve uydu TV tamamen devre dışı kalabilir.</li>
      <li>Boru hatları, demiryolları ve petrol rafinerileri ağır korozyon hasarı.</li>
      <li>Düşük yörüngeli uydular atmosferde yeniden giriş riskiyle karşılaşır.</li>
      <li>Aurora kuzeyde 40° enlemine kadar (Türkiye'den görülebilir).</li>
      <li>Nükleer santral soğutma sistemleri güç kaybı riski altında.</li>
    </ul>
    <div class="g-section-title">Alınabilecek Önlemler</div>
    <ul class="g-measure-list">
      <li>Ulusal acil durum planları derhal devreye alınmalı.</li>
      <li>Kritik altyapılar (hastane, su arıtma, haberleşme) jeneratöre bağlanmalı.</li>
      <li>Tüm uydu operatörleri güvenli park moduna geçmeli.</li>
      <li>Havacılık otoriteleri tüm uçuşları geçici olarak durdurabilir.</li>
      <li>Halk, birkaç gün yetecek su, gıda ve pil stoku yapmalı.</li>
      <li>Elektronik cihazlar Faraday kafesi veya metal kutu içinde korunabilir.</li>
      <li>Uluslararası koordinasyon için uzay hava durumu acil protokolleri aktive edilmeli.</li>
    </ul>
  </div>
</div>






<script>

// RENDERER 
const canvas = document.getElementById('canvas');
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: false });
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.setSize(innerWidth, innerHeight);
renderer.setClearColor(0x00000a, 1);
const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(50, innerWidth / innerHeight, 0.01, 5000);
window.addEventListener('resize', () => {
  camera.aspect = innerWidth / innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(innerWidth, innerHeight);
});
const gltfLoader = new THREE.GLTFLoader();

// GÜNEŞ BİLGİSİ
const SUN_INFO = {
  info: {
    'Tip': 'G2V Sarı Cüce Yıldız', 'Yaş': '~4.6 Milyar Yıl',
    'Yarıçap': '696,000 km', 'Kütle': '1.989 × 10³⁰ kg',
    'Yüzey Sıcaklığı': '~5,500 °C', 'Çekirdek Sıcaklığı': '~15,000,000 °C',
    'Dönüş Süresi': '25–35 gün', 'Parlaklık': '3.83 × 10²⁶ W',
  }
};

const MODEL_BASE_CANDIDATES = [
  "{{ asset('models2') }}",
  "{{ url('models2') }}",
  "/models2",
  "models2"
];

function loadModelWithFallback(loader, fileName, onLoad, onError) {
  const candidates = MODEL_BASE_CANDIDATES.map(base => `${base.replace(/\/$/, '')}/${fileName}`);
  let idx = 0;
  const tryLoad = () => {
    loader.load(candidates[idx], onLoad, undefined, (err) => {
      idx += 1;
      if (idx < candidates.length) {
        tryLoad();
      } else if (onError) {
        onError(err);
      }
    });
  };
  tryLoad();
}

// PLANET DATA 
const PLANETS = [
  { name: 'Merkür', key: 'mercury', r: 0.38, orbitR: 8, color: 0xb5b5b5, emissive: 0x222222, speed: 4.74, tilt: 0.034, modelFile: 'mercury1.glb', info: { 'Yarıçap': '2,439 km', 'Güneşe Uzaklık': '57.9 M km', 'Yörünge Süresi': '88 gün', 'Yüzey Sıcaklığı': '-180 / +430 °C', 'Uydu Sayısı': '0', 'Tip': 'Kayalık gezegen' } },
  { name: 'Venüs', key: 'venus', r: 0.95, orbitR: 13.5, color: 0xe8c97a, emissive: 0x331100, speed: 3.50, tilt: 177.4, modelFile: 'venus.glb', info: { 'Yarıçap': '6,051 km', 'Güneşe Uzaklık': '108.2 M km', 'Yörünge Süresi': '225 gün', 'Yüzey Sıcaklığı': '+465 °C', 'Uydu Sayısı': '0', 'Tip': 'Kayalık gezegen' } },
  { name: 'Dünya', key: 'earth', r: 1.0, orbitR: 19, color: 0x2a7dc2, emissive: 0x001133, speed: 2.98, tilt: 23.4, modelFile: 'earth.glb', info: { 'Yarıçap': '6,371 km', 'Güneşe Uzaklık': '149.6 M km', 'Yörünge Süresi': '365.25 gün', 'Yüzey Sıcaklığı': '-88 / +58 °C', 'Uydu Sayısı': '1', 'Tip': 'Kayalık gezegen' } },
  { name: 'Mars', key: 'mars', r: 0.53, orbitR: 26, color: 0xc1440e, emissive: 0x200500, speed: 2.41, tilt: 25.2, modelFile: 'mars.glb', info: { 'Yarıçap': '3,389 km', 'Güneşe Uzaklık': '227.9 M km', 'Yörünge Süresi': '687 gün', 'Yüzey Sıcaklığı': '-125 / +20 °C', 'Uydu Sayısı': '2', 'Tip': 'Kayalık gezegen' } },
  { name: 'Jüpiter', key: 'jupiter', r: 3.2, orbitR: 43, color: 0xc88b3a, emissive: 0x1a0800, speed: 1.31, tilt: 3.1, modelFile: 'jupiter.glb', info: { 'Yarıçap': '69,911 km', 'Güneşe Uzaklık': '778.5 M km', 'Yörünge Süresi': '11.86 yıl', 'Yüzey Sıcaklığı': '-110 °C', 'Uydu Sayısı': '95', 'Tip': 'Gaz devi' } },
  { name: 'Satürn', key: 'saturn', r: 2.6, orbitR: 62, color: 0xe4d191, emissive: 0x1a1200, speed: 0.97, tilt: 26.7, modelFile: 'saturn.glb', info: { 'Yarıçap': '58,232 km', 'Güneşe Uzaklık': '1,432 M km', 'Yörünge Süresi': '29.46 yıl', 'Yüzey Sıcaklığı': '-140 °C', 'Uydu Sayısı': '146', 'Tip': 'Gaz devi' } },
  { name: 'Uranüs', key: 'uranus', r: 1.8, orbitR: 80, color: 0x7de8e8, emissive: 0x001515, speed: 0.68, tilt: 97.8, modelFile: 'uranus.glb', info: { 'Yarıçap': '25,362 km', 'Güneşe Uzaklık': '2,867 M km', 'Yörünge Süresi': '84.01 yıl', 'Yüzey Sıcaklığı': '-195 °C', 'Uydu Sayısı': '28', 'Tip': 'Buz devi' } },
  { name: 'Neptün', key: 'neptune', r: 1.7, orbitR: 96, color: 0x4b70dd, emissive: 0x000820, speed: 0.54, tilt: 28.3, modelFile: 'neptune.glb', info: { 'Yarıçap': '24,622 km', 'Güneşe Uzaklık': '4,495 M km', 'Yörünge Süresi': '164.8 yıl', 'Yüzey Sıcaklığı': '-200 °C', 'Uydu Sayısı': '16', 'Tip': 'Buz devi' } }
];

//  LIGHTING 
const sunLight = new THREE.PointLight(0xfff0cc, 4.0, 600);
sunLight.position.set(0, 0, 0);
scene.add(sunLight);
scene.add(new THREE.AmbientLight(0x0d0d1a, 2.5));

// STARS 
function buildStars() {
  const N = 14000;
  const pos = new Float32Array(N * 3);
  const sizes = new Float32Array(N);
  for (let i = 0; i < N; i++) {
    const r = 600 + Math.random() * 800;
    const t = Math.random() * Math.PI * 2;
    const p = Math.acos(2 * Math.random() - 1);
    pos[i*3] = r * Math.sin(p) * Math.cos(t);
    pos[i*3+1] = r * Math.sin(p) * Math.sin(t);
    pos[i*3+2] = r * Math.cos(p);
    sizes[i] = 0.3 + Math.random() * 0.9;
  }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  g.setAttribute('size', new THREE.BufferAttribute(sizes, 1));
  return new THREE.Points(g, new THREE.PointsMaterial({ color: 0xffffff, size: 0.5, sizeAttenuation: true, transparent: true, opacity: 0.85 }));
}
scene.add(buildStars());
{
  const N = 800, pos = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) {
    const r = 500 + Math.random() * 600;
    const t = Math.random() * Math.PI * 2;
    const p = Math.acos(2 * Math.random() - 1);
    pos[i*3]=r*Math.sin(p)*Math.cos(t); pos[i*3+1]=r*Math.sin(p)*Math.sin(t); pos[i*3+2]=r*Math.cos(p);
  }
  const g = new THREE.BufferGeometry();
  g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  scene.add(new THREE.Points(g, new THREE.PointsMaterial({ color: 0x8899ff, size: 1.0, sizeAttenuation: true, transparent: true, opacity: 0.2, blending: THREE.AdditiveBlending })));
}

// SUN 
const sunGroup = new THREE.Group();
scene.add(sunGroup);
const sunCore = new THREE.Mesh(new THREE.SphereGeometry(4.5, 64, 64), new THREE.MeshBasicMaterial({ color: 0xffd060 }));
sunCore.name = 'sun';
sunGroup.add(sunCore);
loadModelWithFallback(gltfLoader, 'sun.glb', (gltf) => {
  const sunModel = gltf.scene;
  const box = new THREE.Box3().setFromObject(sunModel);
  const size = new THREE.Vector3(); box.getSize(size);
  const maxDim = Math.max(size.x, size.y, size.z);
  const scaleFactor = 9.0 / maxDim;
  sunModel.scale.setScalar(scaleFactor);
  const center = new THREE.Vector3(); box.getCenter(center);
  sunModel.position.sub(center.multiplyScalar(scaleFactor));
  sunModel.traverse((child) => {
    if (child.isMesh) {
      child.material = new THREE.MeshBasicMaterial({ map: child.material.map || null, color: child.material.map ? 0xffffff : 0xffd060 });
      child.name = 'sun';
    }
  });
  sunCore.visible = false;
  sunGroup.add(sunModel);
  window._sunGLBMeshes = [];
  sunModel.traverse((child) => { if (child.isMesh) window._sunGLBMeshes.push(child); });
}, (err) => { sunCore.visible = true; console.warn('Güneş GLB yüklenemedi.', err); });

const coronaData = [[5.2, 0xff9900, 0.18],[6.5, 0xff6600, 0.10],[9.0, 0xff3300, 0.05],[14, 0xff1100, 0.025]];
const coronaMeshes = coronaData.map(([r, c, o]) => {
  const m = new THREE.Mesh(new THREE.SphereGeometry(r, 32, 32), new THREE.MeshBasicMaterial({ color: c, transparent: true, opacity: o, side: THREE.BackSide, blending: THREE.AdditiveBlending }));
  sunGroup.add(m); return m;
});

//  SOLAR WIND 
const SW_N = 5000;
const swPos  = new Float32Array(SW_N * 3);
const swVel  = new Float32Array(SW_N * 3);
const swLife = new Float32Array(SW_N);
const swCol  = new Float32Array(SW_N * 3); // her parçacığın rengi (r,g,b)

// Renk paleti: kırmızı / turuncu / sarı
const SW_COLORS = [
  [1.0, 0.15, 0.0],  // kırmızı
  [1.0, 0.40, 0.0],  // koyu turuncu
  [1.0, 0.60, 0.1],  // turuncu
  [1.0, 0.75, 0.2],  // açık turuncu
  [1.0, 0.90, 0.1],  // sarı-turuncu
  [1.0, 1.00, 0.2],  // sarı
];

function spawnWind(i) {
  const t = Math.random() * Math.PI * 2;
  const p = (Math.random() - 0.5) * Math.PI * 0.6;
  const r = 5 + Math.random() * 0.5;
  swPos[i*3]   = r * Math.cos(t) * Math.cos(p);
  swPos[i*3+1] = r * Math.sin(p) * 0.5;
  swPos[i*3+2] = r * Math.sin(t) * Math.cos(p);
  const sp = 0.03 + Math.random() * 0.06;
  const n = Math.hypot(swPos[i*3], swPos[i*3+1], swPos[i*3+2]);
  swVel[i*3]   = swPos[i*3]/n*sp;
  swVel[i*3+1] = swPos[i*3+1]/n*sp*0.2;
  swVel[i*3+2] = swPos[i*3+2]/n*sp;
  swLife[i] = Math.random();
  // Rastgele renk ata
  const c = SW_COLORS[Math.floor(Math.random() * SW_COLORS.length)];
  swCol[i*3]   = c[0];
  swCol[i*3+1] = c[1];
  swCol[i*3+2] = c[2];
}
for (let i = 0; i < SW_N; i++) {
  spawnWind(i);
  const f = 1 + Math.random() * 18;
  swPos[i*3] *= f; swPos[i*3+2] *= f;
}
const swGeo = new THREE.BufferGeometry();
swGeo.setAttribute('position', new THREE.BufferAttribute(swPos, 3));
swGeo.setAttribute('color',    new THREE.BufferAttribute(swCol, 3));
const swMat = new THREE.PointsMaterial({
  vertexColors: true,
  size: 0.1, sizeAttenuation: true,
  transparent: true, opacity: 0.55,
  blending: THREE.AdditiveBlending, depthWrite: false
});
scene.add(new THREE.Points(swGeo, swMat));

// STORM STATE 
let stormLevel = 0;
function updateSunVisual(level) {
  const t = level / 5;
  const normalColors = [0xff9900, 0xff6600, 0xff3300, 0xff1100];
  const stormColors  = [0xff4400, 0xff1100, 0xcc0000, 0x880000];
  const normalOp = [0.18, 0.10, 0.05, 0.025];
  const stormOp  = [0.42, 0.28, 0.18, 0.10];
  const stormScale = 1 + t * 0.6;
  coronaMeshes.forEach((m, i) => {
    const nc = new THREE.Color(normalColors[i]); const sc = new THREE.Color(stormColors[i]);
    m.material.color.lerpColors(nc, sc, t);
    m.material.opacity = normalOp[i] + (stormOp[i] - normalOp[i]) * t;
    const baseR = coronaData[i][0]; m.geometry.dispose();
    m.geometry = new THREE.SphereGeometry(baseR * stormScale, 32, 32);
  });
  const coreNormal = new THREE.Color(0xffd060); const coreStorm = new THREE.Color(0xff4400);
  sunCore.material.color.lerpColors(coreNormal, coreStorm, t);
  sunLight.intensity = 4 + t * 6;
  swMat.opacity = 0.55 + t * 0.35;
}

// ORBITS + ASTEROID BELT 
function makeOrbit(r) {
  const pts = [];
  for (let i = 0; i <= 256; i++) { const a = (i/256)*Math.PI*2; pts.push(new THREE.Vector3(Math.cos(a)*r,0,Math.sin(a)*r)); }
  return new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), new THREE.LineBasicMaterial({ color: 0x334466, transparent: true, opacity: 0.35 }));
}
PLANETS.forEach(p => scene.add(makeOrbit(p.orbitR)));
{
  const N = 1800; const pos = new Float32Array(N * 3);
  for (let i = 0; i < N; i++) { const a=Math.random()*Math.PI*2; const r=31+Math.random()*6; const y=(Math.random()-.5)*1.2; pos[i*3]=Math.cos(a)*r; pos[i*3+1]=y; pos[i*3+2]=Math.sin(a)*r; }
  const g = new THREE.BufferGeometry(); g.setAttribute('position', new THREE.BufferAttribute(pos,3));
  scene.add(new THREE.Points(g, new THREE.PointsMaterial({ color: 0x888888, size: 0.12, transparent: true, opacity: 0.55 })));
}

// BUILD PLANETS 
const planetMeshes = [];
const planetAngles = PLANETS.map(() => Math.random() * Math.PI * 2);
PLANETS.forEach((pd, i) => {
  const group = new THREE.Group(); scene.add(group);
  const fallbackMesh = new THREE.Mesh(new THREE.SphereGeometry(pd.r,48,48), new THREE.MeshPhongMaterial({ color: pd.color, emissive: pd.emissive, emissiveIntensity: 0.4, shininess: pd.key==='earth'?60:20, specular: pd.key==='earth'?0x224488:0x111111 }));
  fallbackMesh.rotation.z = THREE.MathUtils.degToRad(pd.tilt); group.add(fallbackMesh);
  const glow = new THREE.Mesh(new THREE.SphereGeometry(pd.r*1.5,20,20), new THREE.MeshBasicMaterial({ color: pd.color, transparent: true, opacity: 0, side: THREE.BackSide, blending: THREE.AdditiveBlending }));
  fallbackMesh.add(glow);
  planetMeshes.push({ group, mesh: fallbackMesh, glow, data: pd, idx: i });
  loadModelWithFallback(gltfLoader, pd.modelFile, (gltf) => {
    const model = gltf.scene;
    const box = new THREE.Box3().setFromObject(model); const size = new THREE.Vector3(); box.getSize(size);
    const maxDim = Math.max(size.x,size.y,size.z); const scaleFactor = (pd.r*2)/maxDim;
    model.scale.setScalar(scaleFactor);
    const center = new THREE.Vector3(); box.getCenter(center); model.position.sub(center.multiplyScalar(scaleFactor));
    model.rotation.z = THREE.MathUtils.degToRad(pd.tilt);
    model.traverse((child) => { if (child.isMesh) { child.castShadow=false; child.receiveShadow=false; } });
    group.remove(fallbackMesh); group.add(model);
    glow.geometry.dispose(); glow.geometry = new THREE.SphereGeometry(pd.r*1.5,20,20); model.add(glow);
    planetMeshes[i].mesh = model; planetMeshes[i].glow = glow;
    planetMeshes[i].raycastMeshes = [];
    model.traverse((child) => { if (child.isMesh) planetMeshes[i].raycastMeshes.push(child); });
  }, (err) => { console.warn(`GLB yüklenemedi: ${pd.modelFile}`, err); });
});

// BOTTOM PILLS
const bb = document.getElementById('bottombar');
const sunPill = document.createElement('button');
sunPill.className = 'planet-pill sun-pill'; sunPill.textContent = '☉ GÜNEŞ';
sunPill.addEventListener('click', () => showSunInfo()); bb.appendChild(sunPill);
PLANETS.forEach((pd, i) => {
  const btn = document.createElement('button');
  btn.className = 'planet-pill'; btn.textContent = pd.name.toUpperCase();
  btn.addEventListener('click', () => showPlanetInfo(i)); bb.appendChild(btn);
});
const pills = document.querySelectorAll('.planet-pill:not(.sun-pill)');

// CAMERA
let camTheta=0.4, camPhi=0.52, camRadius=130;
let targetTheta=camTheta, targetPhi=camPhi, targetRadius=camRadius;
let targetLookAt=new THREE.Vector3(0,0,0);
let currentLookAt=new THREE.Vector3(0,0,0);
function applyCamera() {
  const x=camRadius*Math.sin(camTheta)*Math.cos(camPhi);
  const y=camRadius*Math.sin(camPhi);
  const z=camRadius*Math.cos(camTheta)*Math.cos(camPhi);
  camera.position.set(currentLookAt.x+x, currentLookAt.y+y, currentLookAt.z+z);
  camera.lookAt(currentLookAt);
}
applyCamera();
let drag=false, rmb=false, mx0=0, my0=0;
canvas.addEventListener('mousedown', e => { drag=true; rmb=e.button===2; mx0=e.clientX; my0=e.clientY; });
canvas.addEventListener('contextmenu', e => e.preventDefault());
window.addEventListener('mouseup', () => drag=false);
window.addEventListener('mousemove', e => {
  if (!drag) return;
  const dx=e.clientX-mx0, dy=e.clientY-my0; mx0=e.clientX; my0=e.clientY;
  if (!rmb) { targetTheta-=dx*0.004; targetPhi=Math.max(0.05,Math.min(1.45,targetPhi-dy*0.004)); }
  else { if (lockedPlanet===null&&!sunLocked) { const right=new THREE.Vector3(); const up=new THREE.Vector3(); right.crossVectors(camera.getWorldDirection(new THREE.Vector3()),camera.up).normalize(); up.copy(camera.up); const pan=camRadius*0.0012; targetLookAt.addScaledVector(right,-dx*pan); targetLookAt.addScaledVector(up,dy*pan); } }
});
canvas.addEventListener('wheel', e => { targetRadius=Math.max(2,Math.min(350,targetRadius+e.deltaY*0.06)); }, { passive:true });
let lastDist=0;
canvas.addEventListener('touchstart', e => { if(e.touches.length===1){drag=true;mx0=e.touches[0].clientX;my0=e.touches[0].clientY;} if(e.touches.length===2){drag=false;lastDist=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);} });
canvas.addEventListener('touchend', () => { drag=false; });
canvas.addEventListener('touchmove', e => {
  if(e.touches.length===1&&drag){const dx=e.touches[0].clientX-mx0,dy=e.touches[0].clientY-my0;mx0=e.touches[0].clientX;my0=e.touches[0].clientY;targetTheta-=dx*0.004;targetPhi=Math.max(0.05,Math.min(1.45,targetPhi-dy*0.004));}
  if(e.touches.length===2){const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);targetRadius=Math.max(2,Math.min(350,targetRadius-(d-lastDist)*0.25));lastDist=d;}
}, { passive:true });

// RAYCASTER
const raycaster = new THREE.Raycaster();
const mouse2d = new THREE.Vector2();
let mouseStill=true, mouseDownPos={x:0,y:0};
canvas.addEventListener('mousedown', e => { mouseDownPos.x=e.clientX; mouseDownPos.y=e.clientY; mouseStill=true; });
window.addEventListener('mousemove', e => {
  if(Math.hypot(e.clientX-mouseDownPos.x,e.clientY-mouseDownPos.y)>4) mouseStill=false;
  mouse2d.x=(e.clientX/innerWidth)*2-1; mouse2d.y=-(e.clientY/innerHeight)*2+1;
  raycaster.setFromCamera(mouse2d, camera);
  const sunTargets=(window._sunGLBMeshes&&window._sunGLBMeshes.length>0)?window._sunGLBMeshes:[sunCore];
  const sunHover=raycaster.intersectObjects(sunTargets,false);
  const allPM=[];
  planetMeshes.forEach(pm => { if(pm.raycastMeshes&&pm.raycastMeshes.length>0) allPM.push(...pm.raycastMeshes); else allPM.push(pm.mesh); });
  const planetHover=raycaster.intersectObjects(allPM,false);
  canvas.style.cursor=(sunHover.length>0||planetHover.length>0)?'pointer':(drag?'grabbing':'grab');
});
canvas.addEventListener('mouseup', e => {
  if(!mouseStill) return;
  mouse2d.x=(e.clientX/innerWidth)*2-1; mouse2d.y=-(e.clientY/innerHeight)*2+1;
  raycaster.setFromCamera(mouse2d, camera);
  const sunClickTargets=(window._sunGLBMeshes&&window._sunGLBMeshes.length>0)?window._sunGLBMeshes:[sunCore];
  const sunHits=raycaster.intersectObjects(sunClickTargets,false);
  if(sunHits.length>0){ showSunInfo(); return; }
  let hitIdx=-1, closestDist=Infinity;
  planetMeshes.forEach((pm,i) => {
    const meshList=(pm.raycastMeshes&&pm.raycastMeshes.length>0)?pm.raycastMeshes:[pm.mesh];
    const hits=raycaster.intersectObjects(meshList,false);
    if(hits.length>0&&hits[0].distance<closestDist){ closestDist=hits[0].distance; hitIdx=i; }
  });
  if(hitIdx!==-1){ showPlanetInfo(hitIdx); } else { if(panelState==='visible') setPanelState('peeking'); }
});

// INFO PANEL
const infoPanel=document.getElementById('info');
const infoTab=document.getElementById('info-tab');
const pnameEl=document.getElementById('pname');
const prowsEl=document.getElementById('prows');
let panelState='hidden';
function setPanelState(state) {
  panelState=state; infoPanel.classList.remove('visible','peeking');
  if(state==='visible'){ infoPanel.classList.add('visible'); infoTab.textContent='❯'; }
  else if(state==='peeking'){ infoPanel.classList.add('peeking'); infoTab.textContent='❮'; }
}
infoTab.addEventListener('click', () => { if(panelState==='visible') setPanelState('peeking'); else if(panelState==='peeking') setPanelState('visible'); });
let sunLocked=false;
function showSunInfo() {
  sunLocked=true; lockedPlanet=null;
  infoPanel.classList.add('sun-active');
  pnameEl.textContent='☉ GÜNEŞ'; pnameEl.style.color='#ffd060';
  prowsEl.innerHTML=Object.entries(SUN_INFO.info).map(([k,v])=>`<div class="row"><span class="key">${k}</span><span class="v">${v}</span></div>`).join('');
  setPanelState('visible');
  pills.forEach(p=>p.classList.remove('active')); sunPill.classList.add('active');
  targetRadius=22; targetLookAt.set(0,0,0);
}
function showPlanetInfo(idx) {
  sunLocked=false; sunPill.classList.remove('active'); infoPanel.classList.remove('sun-active');
  const pd=PLANETS[idx];
  pnameEl.textContent=pd.name.toUpperCase();
  pnameEl.style.color='#'+pd.color.toString(16).padStart(6,'0');
  prowsEl.innerHTML=Object.entries(pd.info).map(([k,v])=>`<div class="row"><span class="key">${k}</span><span class="v">${v}</span></div>`).join('');
  setPanelState('visible'); flyToPlanet(idx);
}
let lockedPlanet=null;
function flyToPlanet(idx) {
  sunLocked=false; sunPill.classList.remove('active'); infoPanel.classList.remove('sun-active');
  lockedPlanet=idx;
  pills.forEach((p,i)=>p.classList.toggle('active',i===idx));
  const pd=PLANETS[idx]; targetRadius=Math.max(pd.r*5,Math.min(25,pd.r*7));
}

// VIEW PRESETS
function resetView() {
  targetTheta=0.4; targetPhi=0.52; targetRadius=130; targetLookAt.set(0,0,0);
  lockedPlanet=null; sunLocked=false;
  document.getElementById('v3d').classList.add('active');
  document.getElementById('vtop').classList.remove('active');
  pills.forEach(p=>p.classList.remove('active')); sunPill.classList.remove('active');
  infoPanel.classList.remove('sun-active'); setPanelState('hidden');
}
document.getElementById('v3d').addEventListener('click', resetView);
document.getElementById('vtop').addEventListener('click', () => {
  targetTheta=0; targetPhi=1.45; targetRadius=140; targetLookAt.set(0,0,0);
  lockedPlanet=null; sunLocked=false;
  document.getElementById('vtop').classList.add('active');
  document.getElementById('v3d').classList.remove('active');
  pills.forEach(p=>p.classList.remove('active')); sunPill.classList.remove('active');
  infoPanel.classList.remove('sun-active');
});
document.getElementById('resetbtn').addEventListener('click', resetView);

// G BİLGİ PANELİ
// stormPanel değişkeni sonraki bölümde tanımlanıyor — ref için geciktirme yok
const gInfoPanel  = document.getElementById('g-info-panel');
const gInfoClose  = document.getElementById('g-info-close');

function openGInfoPanel(level) {
  // Storm panelini gizle (doc14 iyileştirmesi: g-info açıkken parametreler gizlenir)
  document.getElementById('storm-panel').classList.add('sp-hidden');

  gInfoPanel.style.display = 'block';
  requestAnimationFrame(() => {
    requestAnimationFrame(() => { gInfoPanel.classList.add('gp-visible'); });
  });
  document.querySelectorAll('.g-level-block').forEach(b => {
    b.classList.toggle('active-level', parseInt(b.dataset.level) === level);
  });
  const activeBlock = gInfoPanel.querySelector(`.g-level-block[data-level="${level}"]`);
  if (activeBlock) setTimeout(() => activeBlock.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 200);
}

function closeGInfoPanel() {
  gInfoPanel.classList.remove('gp-visible');
  setTimeout(() => {
    gInfoPanel.style.display = 'none';
    // Storm panelini geri getir (doc14 iyileştirmesi)
    document.getElementById('storm-panel').classList.remove('sp-hidden');
  }, 260);
}

gInfoClose.addEventListener('click', closeGInfoPanel);

// Panel dışına tıklanınca kapat
document.addEventListener('click', (e) => {
  if (gInfoPanel.style.display === 'block' &&
      !gInfoPanel.contains(e.target) &&
      !e.target.closest('#storm-alert')) {
    closeGInfoPanel();
  }
});

// SPEED SLIDER
const speedSlider=document.getElementById('speedslider');
const speedVal=document.getElementById('speedval');
let simSpeed=1.0;
speedSlider.addEventListener('input', () => { simSpeed=parseFloat(speedSlider.value); speedVal.textContent=simSpeed.toFixed(1)+'x'; });



// FIRTINA VERİSİ
const STORMS = [
  {
    id: 'carrington', year: '1 EYLÜL 1859', name: 'Carrington Olayı',
    desc: 'Tarihin kayıtlara geçmiş en büyük güneş fırtınası. Telgraf sistemleri çöktü, aurora tropik bölgelere kadar görüldü.',
    color: 'rgba(255,60,40,.8)', gLevel: 5,
    params: { vp:2500, np:55, bz:-55, pd:570, pe:150, dst:-950, kp:9, k:9, symh:-1050, asymh:400, ae:4500, pc:25, tec:250, dtec:180, gtec:60, qtec:25, ftec:280, xray:'X45', f107:275, pf10:100000, pf100:5500, dbdt:2500, geo:20 }
  },
  {
    id: 'newyork1921', year: '13 MAYIS 1921', name: 'New York Demiryolu Fırtınası',
    desc: 'New York demiryolu sinyal sistemleri tamamen çöktü. Carrington\'dan sonraki en şiddetli fırtınalardan biri.',
    color: 'rgba(255,80,40,.75)', gLevel: 5,
    params: { vp:2000, np:45, bz:-40, pd:300, pe:100, dst:-900, kp:9, k:9, symh:-920, asymh:350, ae:3800, pc:22, tec:200, dtec:150, gtec:55, qtec:22, ftec:220, xray:'X25', f107:180, pf10:30000, pf100:2000, dbdt:2000, geo:15 }
  },
  {
    id: 'agustos1972', year: '4 AĞUSTOS 1972', name: 'Ağustos 1972 Fırtınası',
    desc: 'Vietnam Savaşı sırasında ABD\'nin mayın sistemlerini tetikledi. Astronomik açıdan rekor CME hızı.',
    color: 'rgba(255,120,30,.7)', gLevel: 5,
    params: { vp:2000, np:25, bz:-10, pd:167, pe:30, dst:-125, kp:9, k:8, symh:-135, asymh:150, ae:1500, pc:12, tec:95, dtec:45, gtec:40, qtec:20, ftec:110, xray:'X20', f107:150, pf10:50000, pf100:3000, dbdt:800, geo:4.5 }
  },
  {
    id: 'quebec1989', year: '13 MART 1989', name: 'Quebec Kesintisi',
    desc: 'Hydro-Québec elektrik şebekesi 9 saat çöktü. 6 milyon kişi elektriksiz kaldı.',
    color: 'rgba(255,140,30,.7)', gLevel: 5,
    params: { vp:1000, np:30, bz:-30, pd:50, pe:45, dst:-589, kp:9, k:9, symh:-600, asymh:280, ae:2500, pc:18, tec:160, dtec:100, gtec:50, qtec:28, ftec:180, xray:'X15', f107:245, pf10:20000, pf100:1500, dbdt:1500, geo:12 }
  },
  {
    id: 'bastille2000', year: '14 TEMMUZ 2000', name: 'Bastille Günü Fırtınası',
    desc: 'Fransız Milli Bayramı\'na denk gelen X5.7 sınıfı patlama. Birçok uydu hasar gördü.',
    color: 'rgba(100,200,255,.7)', gLevel: 5,
    params: { vp:1100, np:20, bz:-60, pd:40, pe:50, dst:-301, kp:9, k:9, symh:-315, asymh:200, ae:2100, pc:15, tec:130, dtec:85, gtec:45, qtec:30, ftec:140, xray:'X5.7', f107:200, pf10:24000, pf100:450, dbdt:1100, geo:8 }
  },
  {
    id: 'halloween2003', year: '28 EKİM 2003', name: 'Cadılar Bayramı Fırtınaları',
    desc: 'X45 sınıfı patlama — ölçüm cihazlarını doyuma uğrattı. İsveç\'te elektrik kesintisi, birçok uydu hasar gördü.',
    color: 'rgba(255,170,0,.7)', gLevel: 5,
    params: { vp:2400, np:28, bz:-50, pd:268, pe:80, dst:-383, kp:9, k:9, symh:-400, asymh:250, ae:3000, pc:20, tec:180, dtec:120, gtec:55, qtec:32, ftec:200, xray:'X45', f107:250, pf10:29500, pf100:2800, dbdt:1800, geo:14 }
  },
  {
    id: 'stereo2012', year: '23 TEMMUZ 2012', name: 'Temmuz 2012 (STEREO-A)',
    desc: 'Dünya\'yı ıskaladı; STEREO-A uydusundan ölçüldü. Carrington seviyesinde tahmin ediliyor.',
    color: 'rgba(255,60,180,.7)', gLevel: 5,
    params: { vp:2500, np:80, bz:-52, pd:835, pe:130, dst:-1150, kp:9, k:9, symh:-1200, asymh:380, ae:4200, pc:24, tec:220, dtec:160, gtec:50, qtec:20, ftec:250, xray:'X20', f107:150, pf10:60000, pf100:4000, dbdt:2200, geo:18 }
  },
  {
    id: 'stpatrick2015', year: '17 MART 2015', name: 'St. Patrick Günü Fırtınası',
    desc: '24. Güneş döngüsünün en büyük jeomanyetik fırtınası. Kuzey ışıkları Orta Avrupa\'ya kadar görüldü.',
    color: 'rgba(80,220,120,.7)', gLevel: 4,
    params: { vp:700, np:15, bz:-24, pd:12, pe:25, dst:-222, kp:8.6, k:8, symh:-235, asymh:140, ae:1200, pc:10, tec:90, dtec:50, gtec:40, qtec:25, ftec:110, xray:'C9.1', f107:115, pf10:10, pf100:0, dbdt:600, geo:5 }
  },
  {
    id: 'eylul2017', year: '6 EYLÜL 2017', name: 'Eylül 2017 Fırtınası',
    desc: '24. döngünün en büyük X sınıfı patlaması. Puerto Rico iletişim altyapısı ciddi hasar gördü.',
    color: 'rgba(255,200,60,.7)', gLevel: 4,
    params: { vp:800, np:18, bz:-30, pd:19, pe:35, dst:-142, kp:8, k:8, symh:-155, asymh:160, ae:1400, pc:11, tec:110, dtec:60, gtec:42, qtec:22, ftec:125, xray:'X9.3', f107:130, pf10:1000, pf100:50, dbdt:750, geo:6.5 }
  },
  {
    id: 'mayis2024', year: '10 MAYIS 2024', name: 'Mayıs 2024 Fırtınaları',
    desc: '20 yılın en büyük güneş fırtınası. Aurora Türkiye\'den bile gözlemlendi. G5 seviyesine ulaştı.',
    color: 'rgba(160,100,255,.75)', gLevel: 5,
    params: { vp:1100, np:22, bz:-50, pd:44, pe:65, dst:-412, kp:9, k:9, symh:-430, asymh:270, ae:2800, pc:19, tec:190, dtec:130, gtec:58, qtec:28, ftec:210, xray:'X5.8', f107:220, pf10:10000, pf100:800, dbdt:1600, geo:13 }
  },
];

// KART OLUŞTURMA
const gClassMap = ['storm-g0','storm-g1','storm-g2','storm-g3','storm-g4','storm-g5'];
const gLabelMap = ['G0','G1','G2','G3','G4','G5'];

function buildStormCards() {
  const grid = document.getElementById('ss-grid');
  STORMS.forEach(s => {
    const card = document.createElement('div');
    card.className = 'ss-card';
    card.style.setProperty('--card-color', s.color);
    const p = s.params;
    const chips = [
      { label: `vₚ ${p.vp} km/s`, warn: p.vp > 800,  danger: p.vp > 1500 },
      { label: `Bz ${p.bz} nT`,   warn: p.bz < -10,  danger: p.bz < -30  },
      { label: `Dst ${p.dst} nT`, warn: p.dst < -50,  danger: p.dst < -200 },
      { label: `Kp ${p.kp}`,      warn: p.kp >= 5,    danger: p.kp >= 8   },
    ].map(c => `<span class="ss-chip${c.danger?' chip-danger':c.warn?' chip-warn':''}">${c.label}</span>`).join('');
    card.innerHTML = `
      <div class="ss-card-year">${s.year}</div>
      <div class="ss-card-name">${s.name}</div>
      <div class="ss-card-desc">${s.desc}</div>
      <div class="ss-card-params">${chips}</div>
      <span class="ss-card-g ${gClassMap[s.gLevel]}">${gLabelMap[s.gLevel]}</span>
    `;
    card.addEventListener('click', () => startWithStorm(s));
    grid.appendChild(card);
  });
}
buildStormCards();

// SEÇİM → YÜKLEME
function startWithStorm(storm) {
  const sel = document.getElementById('storm-select');
  sel.style.opacity = '0';
  setTimeout(() => {
    sel.style.display = 'none';
    const ldWrap = document.getElementById('ld-wrap');
    ldWrap.style.display = 'flex';
    const ldbar = document.getElementById('ldbar');
    const ldpct = document.getElementById('ldpct');
    let prog = 0;
    const ldInt = setInterval(() => {
      prog += 14 + Math.random() * 18;
      const p = Math.min(100, prog);
      ldbar.style.width = p + '%';
      ldpct.textContent = Math.floor(p) + '%';
      if (p >= 100) {
        clearInterval(ldInt);
        setTimeout(() => {
          ldWrap.style.opacity = '0';
          setTimeout(() => {
            ldWrap.style.display = 'none';
            document.getElementById('storm-toggle-btn').style.display = 'block';
            document.getElementById('back-btn').style.display = 'block';
            document.getElementById('storm-panel').classList.remove('sp-hidden');
            calcStorm(storm.params);
          }, 800);
        }, 300);
      }
    }, 200);
  }, 800);
}

// STORM PANEL
const stormPanel=document.getElementById('storm-panel');
const stormTab=document.getElementById('storm-tab');
const stormAlert=document.getElementById('storm-alert');
const stormBadge=document.getElementById('storm-badge');
const toggleBtn=document.getElementById('storm-toggle-btn');
function toggleStormPanel() {
  const hidden=stormPanel.classList.contains('sp-hidden');
  stormPanel.classList.toggle('sp-hidden',!hidden);
  stormTab.textContent=hidden?'−':'+';
}
stormTab.addEventListener('click', toggleStormPanel);
toggleBtn.addEventListener('click', () => { stormPanel.classList.remove('sp-hidden'); stormTab.textContent='−'; });

// GERİ BUTONU
document.getElementById('back-btn').addEventListener('click', () => {
  // UI'ı gizle
  document.getElementById('storm-toggle-btn').style.display = 'none';
  document.getElementById('back-btn').style.display = 'none';
  stormPanel.classList.add('sp-hidden');
  stormAlert.classList.remove('show');
  if (gInfoPanel.style.display === 'block') closeGInfoPanel();
  // Gezegen/güneş panelini sıfırla
  setPanelState('hidden');
  pills.forEach(p => p.classList.remove('active'));
  sunPill.classList.remove('active');
  infoPanel.classList.remove('sun-active');
  lockedPlanet = null; sunLocked = false;
  // Seçim ekranını göster
  const sel = document.getElementById('storm-select');
  sel.style.display = 'flex';
  sel.style.opacity = '0';
  requestAnimationFrame(() => requestAnimationFrame(() => { sel.style.opacity = '1'; }));
});

// HESAPLA
function bindStormAlertQ() {
  const q = document.getElementById('storm-alert-q');
  if (!q) return;
  q.addEventListener('click', () => {
    if (gInfoPanel.style.display === 'block') { closeGInfoPanel(); }
    else { openGInfoPanel(stormLevel); }
  });
}

// FIRTINA SEVİYESİ GÜNCELLE
function setParamVal(id, text, level) {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = text;
  el.className = 'sp-param-val';
  if (level === 'warn')   el.classList.add('warn');
  if (level === 'danger') el.classList.add('danger');
}
function fmt(val, unit, decimals) {
  return val != null ? (decimals != null ? Number(val).toFixed(decimals) : val) + ' ' + unit : '— ' + unit;
}

function calcStorm(params) {
  const p = params || {};

  // ── Türetilmiş değerler ──
  const vp  = p.vp  ?? 450;
  const np  = p.np  ?? 10;
  const bz  = p.bz  ?? 0;
  const dst = p.dst ?? -32;
  const kp  = p.kp  ?? 1;

  // Dinamik basınç: pd ≈ np * vp² * 1.67e-6 nPa
  const pdCalc = +(np * vp * vp * 1.67e-6).toFixed(2);
  const pd = p.pd ?? pdCalc;

  // Etkin basınç (lojistik yaklaşım — ileride gerçek formülle değiştirilir)
  const pe = p.pe ?? +(pd * 0.85).toFixed(2);

  // ── G seviyesi (Kp'ye göre) ──
  let level;
  if      (kp < 5) level = 0;
  else if (kp < 6) level = 1;
  else if (kp < 7) level = 2;
  else if (kp < 8) level = 3;
  else if (kp < 9) level = 4;
  else             level = 5;

  const gClasses = ['storm-g0','storm-g1','storm-g2','storm-g3','storm-g4','storm-g5'];
  const gLabels  = ['G0 — NORMAL','G1 — MİNÖR','G2 — ORTA','G3 — GÜÇLÜ','G4 — ŞİDDETLİ','G5 — AŞIRI!'];

  stormBadge.className   = 'sp-storm-badge ' + gClasses[level];
  stormBadge.textContent = gLabels[level];

  const bannerTxt = ['● SİSTEM NORMAL','▲ G1 UYARISI','⚠ G2 FIRTINA','⚡ G3 GÜÇLÜ','☢ G4 ŞİDDETLİ','☢ G5 AŞIRI!'];
  stormAlert.className = gClasses[level] + ' show';
  stormAlert.innerHTML = bannerTxt[level] + '<span id="storm-alert-q" title="G Skalası Nedir?">?</span>';
  bindStormAlertQ();

  // ── 1. Güneş Rüzgarı & Manyetik Alan ──
  setParamVal('pv-vp',  fmt(vp,  'km/s'), vp > 800  ? 'danger' : vp > 600  ? 'warn' : null);
  setParamVal('pv-np',  fmt(np,  'cm⁻³'), np > 50   ? 'danger' : np > 20   ? 'warn' : null);
  setParamVal('pv-bz',  fmt(bz,  'nT'),   bz < -20  ? 'danger' : bz < -10  ? 'warn' : null);
  setParamVal('pv-pd',  fmt(pd,  'nPa'),  pd > 10   ? 'danger' : pd > 5    ? 'warn' : null);
  setParamVal('pv-pe',  fmt(pe,  'nPa'),  pe > 8    ? 'danger' : pe > 4    ? 'warn' : null);

  // ── 2. Jeomanyetik İndeksler ──
  setParamVal('pv-dst',   fmt(dst,       'nT'), dst < -100  ? 'danger' : dst < -50   ? 'warn' : null);
  setParamVal('pv-kp',    kp != null ? String(kp) : '—',   kp >= 7 ? 'danger' : kp >= 5 ? 'warn' : null);
  setParamVal('pv-k',     p.k    != null ? String(p.k)   : '—',     p.k   != null && p.k   >= 7 ? 'danger' : p.k   != null && p.k   >= 5 ? 'warn' : null);
  setParamVal('pv-symh',  fmt(p.symh,   'nT'), p.symh  != null && p.symh  < -100 ? 'danger' : p.symh  != null && p.symh  < -50  ? 'warn' : null);
  setParamVal('pv-asymh', fmt(p.asymh,  'nT'), p.asymh != null && p.asymh > 100  ? 'danger' : p.asymh != null && p.asymh > 50   ? 'warn' : null);
  setParamVal('pv-ae',    fmt(p.ae,     'nT'), p.ae    != null && p.ae    > 1000 ? 'danger' : p.ae    != null && p.ae    > 500  ? 'warn' : null);
  setParamVal('pv-pc',    p.pc   != null ? String(p.pc)  : '—',    p.pc  != null && p.pc  > 3   ? 'danger' : p.pc  != null && p.pc  > 1.5 ? 'warn' : null);

  // ── 3. İyonküre & TEC ──
  setParamVal('pv-tec',  fmt(p.tec,  'TECU'), p.tec  != null && p.tec  > 80 ? 'danger' : p.tec  != null && p.tec  > 50 ? 'warn' : null);
  setParamVal('pv-dtec', fmt(p.dtec, 'TECU/dk'), p.dtec != null && Math.abs(p.dtec) > 5 ? 'danger' : p.dtec != null && Math.abs(p.dtec) > 2 ? 'warn' : null);
  setParamVal('pv-gtec', fmt(p.gtec, 'TECU'), null);
  setParamVal('pv-qtec', fmt(p.qtec, 'TECU'), null);
  setParamVal('pv-ftec', fmt(p.ftec, 'TECU'), p.ftec != null && p.ftec > 80 ? 'danger' : p.ftec != null && p.ftec > 50 ? 'warn' : null);

  // ── 4. Güneş Aktivitesi ──
  setParamVal('pv-xray',  p.xray  != null ? p.xray  : '— W/m²', p.xray  != null && String(p.xray).startsWith('X')  ? 'danger' : p.xray  != null && String(p.xray).startsWith('M')  ? 'warn' : null);
  setParamVal('pv-f107',  fmt(p.f107,  'sfu'),  p.f107 != null && p.f107 > 200 ? 'warn' : null);
  setParamVal('pv-pf10',  fmt(p.pf10,  'pfu'),  p.pf10  != null && p.pf10  > 100 ? 'danger' : p.pf10  != null && p.pf10  > 10  ? 'warn' : null);
  setParamVal('pv-pf100', fmt(p.pf100, 'pfu'),  p.pf100 != null && p.pf100 > 1   ? 'danger' : null);

  // ── 5. Elektromanyetik Etkiler ──
  setParamVal('pv-dbdt', fmt(p.dbdt, 'nT/s'),   p.dbdt != null && p.dbdt > 2 ? 'danger' : p.dbdt != null && p.dbdt > 1 ? 'warn' : null);
  setParamVal('pv-geo',  fmt(p.geo,  'mV/km'),   p.geo  != null && p.geo  > 100 ? 'danger' : p.geo  != null && p.geo  > 50  ? 'warn' : null);

  stormLevel = level;
  updateSunVisual(level);
}
// İlk yüklemede soru işaretini bağla
bindStormAlertQ();

// ANIMATION LOOP
const clock = new THREE.Clock();
let elapsed = 0;
function animate() {
  requestAnimationFrame(animate);
  const dt = clock.getDelta();
  elapsed += dt * simSpeed;
  const stormT = stormLevel / 5;
  const pFreq = 1.2 + stormT * 3.0;
  const pAmp = 0.015 + stormT * 0.065;
  sunCore.scale.setScalar(1 + Math.sin(elapsed * pFreq) * pAmp + Math.sin(elapsed * 3.7) * 0.005);
  sunGroup.rotation.y += 0.0012;
  const ws = 1 + stormT * 2.5;
  for (let i = 0; i < SW_N; i++) {
    swPos[i*3]+=swVel[i*3]*ws; swPos[i*3+1]+=swVel[i*3+1]*ws; swPos[i*3+2]+=swVel[i*3+2]*ws;
    swLife[i]+=0.003;
    if(Math.hypot(swPos[i*3],swPos[i*3+1],swPos[i*3+2])>105||swLife[i]>1){ spawnWind(i); swLife[i]=0; }
  }
  swGeo.attributes.position.needsUpdate = true;
  if (swGeo.attributes.color) swGeo.attributes.color.needsUpdate = true;
  planetMeshes.forEach(({ group, mesh, data }, i) => {
    const a = planetAngles[i] + elapsed * data.speed * 0.04;
    group.position.set(Math.cos(a) * data.orbitR, 0, Math.sin(a) * data.orbitR);
    if (mesh) mesh.rotation.y += dt * simSpeed * 0.5;
  });
  if (lockedPlanet !== null) { const pm = planetMeshes[lockedPlanet]; targetLookAt.lerp(pm.group.position, 0.06); }
  else if (sunLocked) { targetLookAt.lerp(new THREE.Vector3(0,0,0), 0.08); }
  else { targetLookAt.lerp(new THREE.Vector3(0,0,0), 0.04); }
  camTheta+=(targetTheta-camTheta)*0.07;
  camPhi+=(targetPhi-camPhi)*0.07;
  camRadius+=(targetRadius-camRadius)*0.07;
  currentLookAt.lerp(targetLookAt, 0.08);
  applyCamera();
  renderer.render(scene, camera);
}
animate();


</script>

<!-- MOBILE MENU TOGGLE SCRIPT -->
<script>
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const iconMenu = document.getElementById('icon-menu');
    const iconClose = document.getElementById('icon-close');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = mobileMenu.classList.toggle('open');
            iconMenu.classList.toggle('hidden', isOpen);
            iconClose.classList.toggle('hidden', !isOpen);
            mobileMenuBtn.setAttribute('aria-expanded', String(isOpen));
        });
    }

    // Header shadow on scroll
    const header = document.getElementById('main-header');
    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 10) {
                header.classList.add('shadow-2xl');
            } else {
                header.classList.remove('shadow-2xl');
            }
        }, { passive: true });
    }
</script>

</body>
</html>
