<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\TravelRecordController;

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

    Route::resource('travel-records', TravelRecordController::class)->except('show');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');

    Route::resource('messages', MessageController::class);
    Route::post('/messages/{id}/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.read');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::patch('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
});


// --- SEMUA ROUTE DI BAWAH INI WAJIB LOGIN ---
Route::middleware(['auth'])->group(function () {

    // Homepage: Jika belum login, otomatis ditendang ke /login
    // Jika sudah login, akan menampilkan view 'homepage'
    Route::get('/', function () {
        return view('front.homepage');
    })->name('home');

    Route::get('/products', [UserController::class, 'index'])->name('products');

    Route::get('/contact', function () {
        return view('front.contact');
    })->name('contact');

    //Track Record
    Route::get('/about', function (\Illuminate\Http\Request $request) {

        // 1. Logika Penentuan Tahun Terpilih
        if ($request->has('year')) {
            $selectedYear = $request->year;
        } else {
            $selectedYear = 2025; //
        }

        // 2. Query Data
        $query = App\Models\TrackRecord::query();

        if ($selectedYear) {
            $query->where('year', $selectedYear);
        }

        $trackRecords = $query->latest()->get();

        // 3. Generate List Tahun
        $availableYears = range(date('Y'), 2018);

        // Kirim variable 'selectedYear' ke view biar UI tau lagi nampilin tahun berapa
        return view('front.about', compact('trackRecords', 'availableYears', 'selectedYear'));
    })->name('about');

    Route::get('/track-record/{slug}', function ($slug) {

        $record = App\Models\TrackRecord::with('items')->where('slug', $slug)->firstOrFail();

        return view('front.show', compact('record'));
    })->name('track-record.show');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Booking
    Route::get('/profile/booking', function () {
        $activeBookings = auth()->user()
            ->bookings()
            ->with(['product', 'participants'])
            ->where('status', 'paid')
            ->latest()
            ->get();

        $unpaidBookings = auth()->user()
            ->bookings()
            ->with('product')
            ->where('status', 'unpaid')
            ->latest()
            ->get();

        return view('profile.booking', compact('activeBookings', 'unpaidBookings'));
    })->name('profile.booking');

    Route::post('/profile/booking/{booking}/repay', [App\Http\Controllers\CheckoutController::class, 'repay'])->name('profile.booking.repay');

    Route::delete('/profile/booking/{booking}/cancel', function (\App\Models\Booking $booking) {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
        if ($booking->status !== 'unpaid') {
            return back()->withErrors(['error' => 'Only unpaid bookings can be cancelled.']);
        }
        $booking->delete();
        return back()->with('success', 'Booking has been cancelled successfully.');
    })->name('profile.booking.cancel');

    // Checkout
    Route::get('/checkout/{product}/details', [App\Http\Controllers\CheckoutController::class, 'details'])->name('checkout.details');
    Route::post('/checkout/{product}', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [App\Http\Controllers\CheckoutController::class, 'cancel'])->name('checkout.cancel');
});

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

// Stripe Webhook
Route::post('/stripe/webhook', [App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

require __DIR__ . '/auth.php';
