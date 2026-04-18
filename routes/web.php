<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route untuk ganti bahasa (tidak perlu login)
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id', 'nl', 'de', 'fr'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');


// --- SEMUA ROUTE DI BAWAH INI WAJIB LOGIN ---
Route::middleware(['auth', 'verified'])->group(function () {

    // Homepage: Jika belum login, otomatis ditendang ke /login
    // Jika sudah login, akan menampilkan view 'homepage'
    Route::get('/', function () {
        return view('front.homepage');
    })->name('home');

    Route::get('/about', function () {
        return view('front.about');
    })->name('about');

    Route::get('/products', function () {
        return view('front.products');
    })->name('product');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
