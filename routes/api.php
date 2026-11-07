<?php

use App\Http\Controllers\Api\MedicineController;
use Illuminate\Support\Facades\Route;


Route::get('/medicines', [MedicineController::class, 'index']);
Route::post('/medicines', [MedicineController::class, 'store']);
Route::get('/medicines/low-stock', [MedicineController::class, 'lowStock']);

use App\Http\Controllers\Api\SaleController;


Route::get('/sales', [SaleController::class, 'index']);
Route::post('/sales', [SaleController::class, 'store']);

use App\Http\Controllers\Api\ReportController;


Route::get('/reports/financial', [ReportController::class, 'financialReport']);
Route::get('/reports/top-medicines', [ReportController::class, 'topSellingMedicines']);

use App\Http\Controllers\Api\AuthController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
});

use App\Http\Controllers\Api\SupplierController;

Route::get('/suppliers', [SupplierController::class, 'index']);
Route::post('/suppliers', [SupplierController::class, 'store']);

Route::get('/medicines/expiry-alerts', [MedicineController::class, 'expiryAlerts']);

?>
