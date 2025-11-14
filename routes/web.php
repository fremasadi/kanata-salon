<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JenisLayananController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\GajiController;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Front\CheckoutController;

Route::get('/', [FrontController::class, 'index'])->name('landing');

Route::middleware(['auth'])->group(function () {
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('user', UserController::class)->names('user');
        Route::resource('jenis-layanan', JenisLayananController::class)->names('jenis-layanan');
        Route::resource('shift', ShiftController::class)->names('shift');
        Route::resource('pegawai', PegawaiController::class);
        Route::resource('komisi', \App\Http\Controllers\Admin\KomisiController::class)->only(['index']);
        Route::get('gaji', [GajiController::class, 'index'])->name('gaji.index');
        Route::put('gaji/{gaji}', [GajiController::class, 'update'])->name('gaji.update');
        Route::resource('reservasi', \App\Http\Controllers\Admin\ReservasiController::class);

    });
    Route::middleware('role:pegawai')->prefix('pegawai')->name('pegawai.')->group(function () {
        Route::get('shift', [\App\Http\Controllers\Pegawai\ShiftController::class, 'index'])
            ->name('shift.index');
        Route::get('gaji', [\App\Http\Controllers\Pegawai\GajiController::class, 'index'])->name('gaji.index');
        Route::get('/komisi', [\App\Http\Controllers\Pegawai\KomisiController::class, 'index'])->name('komisi.index');
        Route::get('reservasi', [\App\Http\Controllers\Pegawai\ReservasiController::class, 'index'])->name('reservasi.index');
        Route::put('reservasi/{id}/status', [\App\Http\Controllers\Pegawai\ReservasiController::class, 'updateStatus'])->name('reservasi.update-status');
    });
    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    
    // Payment
    Route::get('/payment/create/{reservasiId}', [PaymentController::class, 'create'])->name('payment.create');
    Route::get('/payment/{id}', [PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment/{id}/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check');

    Route::get('/history', [App\Http\Controllers\Front\HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [App\Http\Controllers\Front\HistoryController::class, 'show'])->name('history.show');

});
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/finish', [PaymentController::class, 'finish'])->name('payment.finish');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
