<?php

use App\Http\Controllers\{
    DashboardController,
    CreditCardController,
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
    Route::delete('/provision/{id}', 'destroy');
});

// Tags
Route::controller(TagController::class)->group(function () {
    Route::get('/tag', 'index');
    Route::post('/tag', 'store');
    Route::put('/tag/{id}', 'update');
    Route::delete('/tag/{id}', 'destroy');
});

// CreditCards
Route::controller(CreditCardController::class)->group(function () {
    Route::get('/credit-card', 'index');
    Route::post('/credit-card', 'store');
    Route::put('/credit-card/{id}', 'update');
    Route::delete('/credit-card/{id}', 'destroy');
});


require __DIR__ . '/auth.php';
