<?php

use App\Http\Controllers\{
    DashboardController,
    CreditCardController,
    CreditCardInvoiceController,
    CreditCardInvoiceExpenseController,
    PeopleController,
    ProvisionController,
    TagController,
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

// Provisionamentos
Route::controller(ProvisionController::class)->group(function () {
    Route::get('/provision', 'index');
    Route::post('/provision', 'store');
    Route::put('/provision/{id}', 'update');
    Route::delete('/provision/{id}', 'delete');
});

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
    Route::post('/credit-card/{creditCardId}/invoice', 'store');
    Route::delete('/credit-card/{creditCardId}/invoice/{id}', 'delete');
    Route::get('/credit-card/{creditCardId}/invoice/{id}', 'show');
});

// CreditCardInvoiceExpense
Route::controller(CreditCardInvoiceExpenseController::class)->group(function () {
    Route::post('/credit-card/{creditCardId}/invoice/{invoiceId}/expense', 'store');
    Route::put('/credit-card/{creditCardId}/invoice/{invoiceId}/expense/{id}', 'update');
    Route::delete('/credit-card/{creditCardId}/invoice/{invoiceId}/expense/{id}', 'delete');
    Route::delete('/credit-card/{creditCardId}/invoice/{invoiceId}/expense/{id}/delete-all-portions', 'deletePortions');
    // Route::post('/credit-card/{creditCardId}/invoice/{invoiceId}/expense-import-excel', 'importExcel');
});


require __DIR__ . '/auth.php';
