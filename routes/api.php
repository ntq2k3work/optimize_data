<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products',[ProductController::class,'index'])->name('products');
Route::get('/products',[ProductController::class,'search'])->name('products.search');
Route::post('/products/order',[OrderController::class,'index'])->name('order.index');
Route::delete('/products/destroy/{id}',[ProductController::class,'destroy'])->name('products.destroy');
Route::get('/products/inventory/{id}',[ProductController::class,'delay']);
Route::get('/products/inventory/status/{id}',[ProductController::class,'checkInventoryStatus']);
