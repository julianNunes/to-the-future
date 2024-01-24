<?php

use App\Http\Controllers\{
    DashboardController,
    CreditCardController,
    CreditCardInvoiceController,
    CreditCardInvoiceExpenseController,
    PeopleController,
    ProvisionController,
    TagController,
    FinancingController,
    FinancingInstallmentController,
    FixExpenseController,
};

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('/people', PeopleController::class)->except(['show']);

// Tags
Route::controller(TagController::class)->group(function () {
    Route::get('/tag', 'index');
    Route::post('/tag', 'store');
    Route::put('/tag/{id}', 'update');
    Route::delete('/tag/{id}', 'delete');
    Route::get('/tag/search/{name}', 'search');
});

// CreditCard
Route::controller(CreditCardController::class)->group(function () {
    Route::get('/credit-card', 'index');
    Route::post('/credit-card', 'store');
    Route::put('/credit-card/{id}', 'update');
    Route::delete('/credit-card/{id}', 'delete');
});

// CreditCardInvoice
Route::controller(CreditCardInvoiceController::class)->group(function () {
    Route::get('/credit-card/{creditCardId}/invoice', 'index');
    Route::post('/credit-card/invoice', 'store');
    Route::delete('/credit-card/invoice/{id}', 'delete');
    Route::get('/credit-card/{creditCardId}/invoice/{id}', 'show');
    Route::delete('/credit-card/invoice/{id}', 'delete');
    Route::post('/credit-card/invoice/download-template', 'downloadTemplate');
    Route::post('/credit-card/invoice/file', 'storeFile');
    Route::delete('/credit-card/invoice/file/{fileId}', 'deleteFile');
});

// CreditCardInvoiceExpense
Route::controller(CreditCardInvoiceExpenseController::class)->group(function () {
    Route::post('/credit-card/invoice/expense', 'store');
    Route::put('/credit-card/invoice/expense/{id}', 'update');
    Route::delete('/credit-card/invoice/expense/{id}', 'delete');
    Route::delete('/credit-card/invoice/expense/{id}/delete-all-portions', 'deletePortions');
    Route::post('/credit-card/invoice/expense-import-excel', 'storeImportExcel');
});

// Financing
Route::controller(FinancingController::class)->group(function () {
    Route::get('/financing', 'index');
    Route::post('/financing', 'store');
    Route::put('/financing/{id}', 'update');
    Route::delete('/financing/{id}', 'delete');
});

// FinancingInstallment
Route::controller(FinancingInstallmentController::class)->group(function () {
    Route::get('/financing/{financingId}/installment', 'index');
    Route::put('/financing/installment/{id}', 'update');
});

// Despesas Fixas
Route::controller(FixExpenseController::class)->group(function () {
    Route::get('/fix-expense', 'index');
    Route::post('/fix-expense', 'store');
    Route::put('/fix-expense/{id}', 'update');
    Route::delete('/fix-expense/{id}', 'delete');
});

// Provisionamentos
Route::controller(ProvisionController::class)->group(function () {
    Route::get('/provision', 'index');
    Route::post('/provision', 'store');
    Route::put('/provision/{id}', 'update');
    Route::delete('/provision/{id}', 'delete');
});

require __DIR__ . '/auth.php';
