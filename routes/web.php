<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DailyDeliveryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecurringOrderController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class)->except(['destroy']);
    Route::patch('/customers/{customer}/status', [CustomerController::class, 'setStatus'])->name('customers.status');

    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::patch('/brands/{brand}/status', [BrandController::class, 'setStatus'])->name('brands.status');

    Route::resource('products', ProductController::class)->except(['destroy', 'show']);
    Route::patch('/products/{product}/status', [ProductController::class, 'setStatus'])->name('products.status');

    Route::resource('recurring-orders', RecurringOrderController::class)->except(['destroy', 'show']);

    Route::get('/deliveries', [DailyDeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('/deliveries/create', [DailyDeliveryController::class, 'create'])->name('deliveries.create');
    Route::post('/deliveries', [DailyDeliveryController::class, 'store'])->name('deliveries.store');
    Route::post('/deliveries/generate', [DailyDeliveryController::class, 'generate'])->name('deliveries.generate');
    Route::get('/deliveries/{delivery}/edit', [DailyDeliveryController::class, 'edit'])->name('deliveries.edit');
    Route::put('/deliveries/{delivery}', [DailyDeliveryController::class, 'update'])->name('deliveries.update');

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
