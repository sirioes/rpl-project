<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\UserController;

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'id', 'nl', 'de', 'pt'])) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('lang.switch');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class);
    Route::post('/products/{id}/publish', [ProductController::class, 'publish'])->name('products.publish');
    Route::patch('/admin/products/{product}/toggle', [App\Http\Controllers\Admin\ProductController::class, 'togglePublish'])
        ->name('products.toggle');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
});


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

    Route::get('/products', [UserController::class, 'index'])->name('products');

    Route::get('/contact', function () {
        return view('front.contact');
    })->name('contact');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__ . '/auth.php';
