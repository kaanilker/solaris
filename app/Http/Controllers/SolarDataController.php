<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SolarDataController extends Controller
{
    private function proxyJson(string $url, string $source, int $cacheTTL = 60)
    {
        $cacheKey = 'proxy_'.md5($url);

        return Cache::remember($cacheKey, $cacheTTL, function () use ($url, $source) {
            $response = Http::timeout(30)->get($url);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => "{$source} verisi alınamadı",
                    'status' => $response->status(),
                ], 502);
            }

            $decoded = $response->json();
            if (! is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'error' => "{$source} verisi beklenen formatta değil",
                ], 502);
            }

            return response()->json([
                'success' => true,
                'data' => $decoded,
                'source' => $source,
                'updated_at' => now()->toIso8601String(),
            ]);
        });
    }

    public function getPlasma()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/products/solar-wind/plasma-1-day.json',
            'NOAA SWPC Plasma',
            60
        );
    }

    public function getMag()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/products/solar-wind/mag-1-day.json',
            'NOAA SWPC Magnetometer',
            60
        );
    }

    public function getXray()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/json/goes/primary/xrays-1-day.json',
            'NOAA SWPC X-Ray',
            60
        );
    }

    public function getProtons()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/json/goes/primary/integral-protons-1-day.json',
            'NOAA SWPC Protons',
            60
        );
    }

    public function getKIndex()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/products/noaa-boulder-k-index.json',
            'NOAA Boulder K-Index',
            10800
        );
    }

    public function getF107()
    {
        return $this->proxyJson(
            'https://services.swpc.noaa.gov/json/f107_cm_flux.json',
            'NOAA F10.7 Flux',
            86400
        );
    }

    /**
     * GFZ Potsdam Kp nowcast verisini backend proxy üzerinden döner.
     */
    public function getKpNowcast()
    {
        $cacheKey = 'solar_kp_nowcast';
        $cacheTTL = 900; // 15 dakika

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $response = Http::timeout(30)->get('https://kp.gfz-potsdam.de/app/json/?file=Kp_ap_nowcast');

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'GFZ Kp verisi alınamadı',
                    'status' => $response->status(),
                ], 502);
            }

            $decoded = $response->json();

            if (! is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'error' => 'GFZ Kp verisi beklenen formatta değil',
                ], 502);
            }

            return response()->json([
                'success' => true,
                'data' => $decoded,
                'source' => 'GFZ Potsdam',
                'updated_at' => now()->toIso8601String(),
            ]);
        });
    }

    /**
     * SWPC jeoelektrik verisini backend proxy üzerinden döner.
     */
    public function getGeoelectric()
    {
        $cacheKey = 'solar_geoelectric_1d';
        $cacheTTL = 900; // 15 dakika

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $response = Http::timeout(30)->get('https://services.swpc.noaa.gov/json/geoelectric/1D/');

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Jeoelektrik veri alınamadı',
                    'status' => $response->status(),
                ], 502);
            }

            $decoded = $response->json();
            if (! is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Jeoelektrik veri beklenen formatta değil',
                ], 502);
            }

            return response()->json([
                'success' => true,
                'data' => $decoded,
                'source' => 'NOAA SWPC',
                'updated_at' => now()->toIso8601String(),
            ]);
        });
    }

    public function getDst()
    {
        $cacheKey = 'solar_dst_index';
        $cacheTTL = 3600; // 1 saat

        return Cache::remember($cacheKey, $cacheTTL, function () {
            try {
                $response = Http::timeout(30)->get('https://wdc.kugi.kyoto-u.ac.jp/dst_realtime/presentmonth/index.html');
                
                if ($response->successful()) {
                    $html = $response->body();
                    $dst = $this->parseDstFromHtml($html);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $dst,
                        'source' => 'Kyoto WDC',
                        'updated_at' => now()->toIso8601String()
                    ]);
                }
                
                return response()->json(['success' => false, 'error' => 'Veri alınamadı'], 500);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        });
    }

    private function parseDstFromHtml($html)
    {
        $dst = null;
        
        // Kyoto WDC formatı: PRE tag içinde tablo şeklinde
        // Son satırdaki son değeri al
        preg_match_all('/<pre[^>]*>(.*?)<\/pre>/si', $html, $matches);
        
        if (!empty($matches[1])) {
            $preContent = end($matches[1]);
            $lines = explode("\n", trim($preContent));
            
            // Son veri satırını bul (sayılarla başlayan)
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (preg_match('/^\d/', $line)) {
                    // Satırdaki sayıları ayıkla
                    preg_match_all('/-?\d+/', $line, $numbers);
                    if (!empty($numbers[0])) {
                        // Son geçerli Dst değerini bul (9999 veya 99999 hariç)
                        $values = array_reverse($numbers[0]);
                        foreach ($values as $val) {
                            $intVal = intval($val);
                            if (abs($intVal) < 1000) {
                                $dst = $intVal;
                                break;
                            }
                        }
                        if ($dst !== null) break;
                    }
                }
            }
        }
        
        return [
            'dst_indeksi' => $dst,
            'unit' => 'nT'
        ];
    }

    public function getAeIndices()
    {
        $cacheKey = 'solar_ae_indices';
        $cacheTTL = 60; // 1 dakika

        return Cache::remember($cacheKey, $cacheTTL, function () {
            try {
                $response = Http::timeout(30)->get('https://wdc.kugi.kyoto-u.ac.jp/ae_realtime/today/today.html');
                
                if ($response->successful()) {
                    $html = $response->body();
                    $indices = $this->parseAeIndicesFromHtml($html);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $indices,
                        'source' => 'Kyoto WDC',
                        'updated_at' => now()->toIso8601String()
                    ]);
                }
                
                return response()->json(['success' => false, 'error' => 'Veri alınamadı'], 500);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
        });
    }

    /**
     * AE indices HTML'den parse et
     */
    private function parseAeIndicesFromHtml($html)
    {
        $result = [
            'sym_h_indeksi' => null,
            'asy_h_indeksi' => null,
            'ae_indeksi' => null,
            'unit' => 'nT'
        ];
        
        // PRE tag içindeki veriyi al
        preg_match_all('/<pre[^>]*>(.*?)<\/pre>/si', $html, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $preContent) {
                $lines = explode("\n", trim($preContent));
                
                // Son veri satırını bul
                for ($i = count($lines) - 1; $i >= 0; $i--) {
                    $line = trim($lines[$i]);
                    
                    // Sayılarla başlayan satır (tarih formatı: YYMM DD HH)
                    if (preg_match('/^\d{4}\s+\d+\s+\d+/', $line)) {
                        preg_match_all('/-?\d+/', $line, $numbers);
                        
                        if (count($numbers[0]) >= 7) {
                            $values = $numbers[0];

                            // Önce SYM-H/ASY-H formatını dene: YYMM DD HH SYM-H SYM-D ASY-H ASY-D
                            if (isset($values[3], $values[5]) && abs((int) $values[3]) < 2000 && abs((int) $values[5]) < 1000) {
                                $result['sym_h_indeksi'] = (int) $values[3];
                                $result['asy_h_indeksi'] = (int) $values[5];
                            }

                            // Sonra AE formatını dene: YYMM DD HH AE AL AU AO
                            if (isset($values[3]) && (int) $values[3] >= 0 && (int) $values[3] <= 5000) {
                                $result['ae_indeksi'] = (int) $values[3];
                            }

                            if ($result['sym_h_indeksi'] !== null && $result['ae_indeksi'] !== null) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function estimatePcFromIndices(?int $ae, ?float $kp): ?float
    {
        if ($ae === null && $kp === null) {
            return null;
        }

        if ($ae !== null) {
            return round(min(30, max(0, $ae / 180)), 2);
        }

        return round(min(30, max(0, ($kp ?? 0) * 2.8)), 2);
    }

    private function estimateTecByHour(int $hour): float
    {
        if ($hour >= 10 && $hour <= 16) {
            return 45.0;
        }
        if ($hour >= 6 && $hour < 10) {
            return 30.0;
        }
        if ($hour > 16 && $hour <= 20) {
            return 28.0;
        }
        return 16.0;
    }

    public function getPcIndex()
    {
        $cacheKey = 'solar_pc_index';
        $cacheTTL = 900; // 15 dakika

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $ae = null;
            $kp = null;

            $aeResp = $this->getAeIndices();
            if (method_exists($aeResp, 'getData')) {
                $payload = $aeResp->getData(true);
                $ae = $payload['data']['ae_indeksi'] ?? null;
            }

            $kpResp = $this->getKpNowcast();
            if (method_exists($kpResp, 'getData')) {
                $payload = $kpResp->getData(true);
                $last = is_array($payload['data'] ?? null) ? end($payload['data']) : null;
                if (is_array($last) && isset($last['Kp'])) {
                    $kp = (float) $last['Kp'];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pc_indeksi' => $this->estimatePcFromIndices($ae, $kp),
                    'unit' => '',
                    'note' => 'Tahmini hesaplama (AE/Kp üzerinden)',
                ],
                'source' => 'Estimated from AE/Kp',
                'updated_at' => now()->toIso8601String()
            ]);
        });
    }

    public function getTec()
    {
        $cacheKey = 'solar_tec_data';
        $cacheTTL = 86400; // 24 saat

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $hour = intval(date('H'));
            $baseTec = $this->estimateTecByHour($hour);
            $variation = ($hour % 3) * 1.5;
            $tec = round($baseTec + $variation, 1);

            return response()->json([
                'success' => true,
                'data' => [
                    'tec' => $tec,
                    'dtec' => round(($tec - 20) / 2, 1),
                    'gtec' => round($tec * 0.78, 1),
                    'qtec' => 25.0,
                    'ftec' => round($tec * 1.05, 1),
                    'unit' => 'TECU',
                    'note' => 'Saatlik profile dayali tahmini model'
                ],
                'source' => 'IONEX (Estimated)',
                'updated_at' => now()->toIso8601String()
            ]);
        });
    }

    public function getAllData()
    {
        $dst = $this->fetchDstDirect();
        $ae = $this->fetchAeIndicesDirect();
        $tec = $this->fetchTecDirect();
        
        return response()->json([
            'success' => true,
            'data' => array_merge(
                $dst,
                $ae,
                $tec
            ),
            'updated_at' => now()->toIso8601String()
        ]);
    }

    private function fetchDstDirect()
    {
        try {
            $response = Http::timeout(30)->get('https://wdc.kugi.kyoto-u.ac.jp/dst_realtime/presentmonth/index.html');
            if ($response->successful()) {
                return $this->parseDstFromHtml($response->body());
            }
        } catch (\Exception $e) {}
        return ['dst_indeksi' => null];
    }

    private function fetchAeIndicesDirect()
    {
        try {
            $response = Http::timeout(30)->get('https://wdc.kugi.kyoto-u.ac.jp/ae_realtime/today/today.html');
            if ($response->successful()) {
                return $this->parseAeIndicesFromHtml($response->body());
            }
        } catch (\Exception $e) {}
        return ['sym_h_indeksi' => null, 'asy_h_indeksi' => null, 'ae_indeksi' => null];
    }

    private function fetchTecDirect()
    {
        $hour = intval(date('H'));
        $baseTec = ($hour >= 6 && $hour <= 18) ? 40 : 15;
        return [
            'tec' => $baseTec,
            'dtec' => 0,
            'gtec' => $baseTec * 0.8,
            'qtec' => 25,
            'ftec' => $baseTec
        ];
    }
}
