<?php

use App\Http\Controllers\{
    BudgetController,
    BudgetExpenseController,
    BudgetGoalController,
    BudgetIncomeController,
    BudgetProvisionController,
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
    PrepaidCardController,
    PrepaidCardExtractController,
    PrepaidCardExtractExpenseController,
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
    Route::put('/credit-card/invoice/{id}', 'update');
    Route::delete('/credit-card/invoice/{id}', 'delete');
    Route::get('/credit-card/invoice/{id}', 'show');
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
    Route::post('/credit-card/invoice/expense/import-excel', 'storeImportExcel');
});

// PrepaidCard
Route::controller(PrepaidCardController::class)->group(function () {
    Route::get('/prepaid-card', 'index');
    Route::post('/prepaid-card', 'store');
    Route::put('/prepaid-card/{id}', 'update');
    Route::delete('/prepaid-card/{id}', 'delete');
});

// PrepaidCardExtract
Route::controller(PrepaidCardExtractController::class)->group(function () {
    Route::get('/prepaid-card/{prepaidCardId}/extract', 'index');
    Route::post('/prepaid-card/extract', 'store');
    Route::put('/prepaid-card/extract/{id}', 'update');
    Route::delete('/prepaid-card/extract/{id}', 'delete');
    Route::get('/prepaid-card/extract/{id}', 'show');
    Route::delete('/prepaid-card/extract/{id}', 'delete');
});

// PrepaidCardExtractExpense
Route::controller(PrepaidCardExtractExpenseController::class)->group(function () {
    Route::post('/prepaid-card/extract/expense', 'store');
    Route::put('/prepaid-card/extract/expense/{id}', 'update');
    Route::delete('/prepaid-card/extract/expense/{id}', 'delete');
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

// Orçamento
Route::controller(BudgetController::class)->group(function () {
    Route::get('/budget/{year}', 'index');
    Route::post('/budget', 'store');
    Route::put('/budget/{id}/clone', 'clone');
    Route::put('/budget/{id}', 'update');
    Route::delete('/budget/{id}', 'delete');
    Route::get('/budget/find/{year}/{month}', 'findByYearMonth');
    Route::get('/budget/show/{id}', 'show')->name('budget.show');
    Route::post('/budget/{id}/include-fix-expenses', 'includeFixExpenses');
    Route::post('/budget/{id}/include-provisions', 'includeProvisions');
});

// Despesas do Orçamento
Route::controller(BudgetExpenseController::class)->group(function () {
    Route::post('/budget-expense', 'store');
    Route::put('/budget-expense/{id}', 'update');
    Route::delete('/budget-expense/{id}', 'delete');
});

// Receitas do Orçamento
Route::controller(BudgetIncomeController::class)->group(function () {
    Route::post('/budget-income', 'store');
    Route::put('/budget-income/{id}', 'update');
    Route::delete('/budget-income/{id}', 'delete');
});

// Provisionamento do Orçamento
Route::controller(BudgetProvisionController::class)->group(function () {
    Route::post('/budget-provision', 'store');
    Route::put('/budget-provision/{id}', 'update');
    Route::delete('/budget-provision/{id}', 'delete');
});

// Metas do Orçamento
Route::controller(BudgetGoalController::class)->group(function () {
    Route::post('/budget-goal', 'store');
    Route::put('/budget-goal/{id}', 'update');
    Route::delete('/budget-goal/{id}', 'delete');
});

require __DIR__ . '/auth.php';
