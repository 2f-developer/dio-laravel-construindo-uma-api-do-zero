<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CepController;
use App\Http\Controllers\Api\ImportacaoController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (Request $request) {
    return response()->json([
        'status' => 'ok',
        'message' => 'API no ar',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
})->name('api.health');

// Products API
Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    Route::get('/statistics', [ProductController::class, 'statistics'])->name('statistics');
    Route::get('/{product}', [ProductController::class, 'show'])->name('show');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    Route::patch('/{product}', [ProductController::class, 'update'])->name('update.patch');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    Route::patch('/{product}/stock', [ProductController::class, 'updateStock'])->name('update-stock');
});

// Categories API
Route::prefix('categories')->name('api.categories.')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/statistics', [CategoryController::class, 'statistics'])->name('statistics');
    Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::patch('/{category}', [CategoryController::class, 'update'])->name('update.patch');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

// External API

// Consumo de API externa
Route::get('/cep/{cep}', [CepController::class, 'show'])->where('cep', '[0-9]{8}')->name('api.cep');

// Fila: importação de produtos de um catálogo externo
Route::post('/importacoes', [ImportacaoController::class, 'store'])->middleware('throttle:5,1')->name('api.importacoes');
