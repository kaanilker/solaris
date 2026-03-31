# ☀️ SOLARIS
### Uzay Havası Erken Uyarı Paneli

> **TUA Astro Hackathon 2026** | Quasar Takımı

SOLARIS, güneş kaynaklı uzay hava olaylarını gerçek zamanlı izleyen, fizik tabanlı denklemler ve analog tahmin yöntemiyle jeomanyetik fırtınaları önceden tespit eden bir erken uyarı sistemidir.

---

## 🌐 Nedir?

Güneş'ten fırlayan koronal kütle ejeksiyonları (CME) ve güneş rüzgarı, Dünya'nın manyetosferini etkiler. Bu etkileşim uydu sistemlerini, elektrik şebekelerini ve GPS altyapısını tehdit edebilir. SOLARIS bu tehdidi önceden haber verir.

---

## ⚙️ Özellikler

| Modül | Açıklama |
|---|---|
| **Gerçek Zamanlı Veri** | NOAA Space Weather ve NASA DONKI API'larından canlı veri çekimi |
| **Fizik Motoru** | Burton (1975), O'Brien & McPherron (2000), Perreault & Akasofu (1978) denklemleri |
| **Dst İndeksi Tahmini** | Güneş rüzgarı parametrelerinden jeomanyetik fırtına şiddet tahmini |
| **CME Varış Süresi** | Drag-tabanlı model ile CME'nin Dünya'ya ulaşma süresinin hesabı |
| **Analog Tahmin** | Geçmiş uzay hava olaylarından benzer örüntü tespiti (DTW + kosinüs benzerliği + k-NN) |
| **LLM Yorumlama** | Analog sonuçlarının bağlamsal dil modeliyle yorumlanması |
| **Uyarı Sistemi** | Kp indeksine göre renk kodlu (G1–G5) fırtına uyarıları |
| **Interaktif Panel** | Tüm metriklerin anlık görselleştirilmesi |

---

## 🔬 Analog Tahmin Yöntemi

Sistem, geçmiş uzay hava olaylarını vektör uzayında temsil ederek mevcut koşullara en yakın analoglara kNN ile ulaşır:

1. Zaman serisi normalizasyonu
2. DTW (Dynamic Time Warping) ve kosinüs benzerliği ile mesafe hesabı
3. En yakın k analog seçimi
4. LLM ile bağlamsal yorum üretimi

---

## 🛠️ Teknoloji Yığını

```
Backend   →  PHP / Laravel
Frontend  →  Blade + HTML/CSS/JS
Veri      →  NOAA SWPC API, NASA DONKI API
Veri Deposu→  JSON
```

---

## 🚀 Kurulum

```bash
# Repoyu klonla
git clone https://github.com/kaanilker/SOLARIS.git
cd SOLARIS

# Bağımlılıkları yükle
composer install

# Ortam değişkenlerini ayarla
cp .env.example .env
php artisan key:generate
```


```bash
# Sunucuyu başlat
php artisan serve
```

---

## 📡 Veri Kaynakları

- [NOAA Space Weather Prediction Center](https://www.swpc.noaa.gov/)
- [NASA DONKI (Space Weather Database)](https://kauai.ccmc.gsfc.nasa.gov/DONKI/)

---

## 📚 Bilimsel Referanslar

- Burton, R.K. et al. (1975). *An empirical relationship between interplanetary conditions and Dst.* JGR.
- O'Brien, T.P. & McPherron, R.L. (2000). *An empirical phase space analysis of ring current dynamics.* JGR.
- Perreault, P. & Akasofu, S.-I. (1978). *A study of geomagnetic storms.* Geophysical Journal International.

---

## 👥 Takım

**Quasar** — TUA Astro Hackathon 2026

---

## 📄 Lisans

GPL-2.0 — Ayrıntılar için [LICENSE](LICENSE) dosyasına bakın.
