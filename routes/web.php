<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
| De website. Elke regel is: URL -> controllerfunctie -> naam van de route.
| In Blade gebruik je die naam zo: route('home')
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/over-ons', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/afspraak', [PageController::class, 'appointment'])->name('appointment');

/*
| Beheer.
| auth = ingelogd, admin = de gebruiker is ook echt beheerder.
| throttle:admin-login beperkt het aantal inlogpogingen.
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
    Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
    });
});
