<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolarDataController;
use App\Http\Controllers\LLMController;

Route::get('/', function () {
    return view('pages.canli-veri');
})->name('home');

Route::get('/hesaplama', function () {
    return view('pages.hesaplama');
})->name('hesaplama');

Route::get('/gecmis', function () {
    return view('pages.gecmis');
})->name('gecmis');

Route::get('/hakkimizda', function () {
    return view('pages.hakkimizda');
})->name('hakkimizda');

Route::get('/api/firtinalar', function () {
    $candidatePaths = [
        public_path('data/firtinalar.json'),
        base_path('public/data/firtinalar.json'),
    ];

    foreach ($candidatePaths as $path) {
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if (is_array($decoded)) {
                return response()->json($decoded);
            }
        }
    }

    return response()->json(['error' => 'Veri bulunamadı'], 404);
});

Route::get('/api/solar/plasma', [SolarDataController::class, 'getPlasma']);
Route::get('/api/solar/mag', [SolarDataController::class, 'getMag']);
Route::get('/api/solar/xray', [SolarDataController::class, 'getXray']);
Route::get('/api/solar/protons', [SolarDataController::class, 'getProtons']);
Route::get('/api/solar/k-index', [SolarDataController::class, 'getKIndex']);
Route::get('/api/solar/f107', [SolarDataController::class, 'getF107']);

Route::get('/api/solar/dst', [SolarDataController::class, 'getDst']);

Route::get('/api/solar/ae-indices', [SolarDataController::class, 'getAeIndices']);

Route::get('/api/solar/kp-nowcast', [SolarDataController::class, 'getKpNowcast']);

Route::get('/api/solar/geoelectric', [SolarDataController::class, 'getGeoelectric']);

Route::get('/api/solar/pc', [SolarDataController::class, 'getPcIndex']);

Route::get('/api/solar/tec', [SolarDataController::class, 'getTec']);

Route::get('/api/solar/all', [SolarDataController::class, 'getAllData']);

Route::post('/api/analyze-storm', [LLMController::class, 'analyze'])->name('analyze.storm');
