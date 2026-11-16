<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ParentAuthController;
use App\Http\Controllers\ParentGoogleAuthController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TrainingDataController;

Route::view('/', 'home')->name('home');
Route::view('/profil-sekolah', 'school.about')->name('school.about');
Route::view('/profil-guru', 'school.staff')->name('school.profile');
Route::view('/galeri-kegiatan', 'school.gallery')->name('activities.index');
Route::get('/akun', [ParentAuthController::class, 'access'])->name('account.access');
Route::get('/spmb', [ParentAuthController::class, 'spmb'])->name('spmb.entry');
Route::get('/pendaftaran', [RegistrationController::class, 'create'])->middleware('parent.auth')->name('registration.create');
Route::post('/pendaftaran', [RegistrationController::class, 'store'])->middleware('parent.auth')->name('registration.store');
Route::get('/status-pendaftaran', [StatusController::class, 'show'])->name('status.lookup');
Route::post('/status-pendaftaran', [StatusController::class, 'lookup'])->name('status.find');
Route::get('/orang-tua/daftar', [ParentAuthController::class, 'registerForm'])->name('parent.register');
Route::post('/orang-tua/daftar', [ParentAuthController::class, 'register'])->middleware('throttle:5,1')->name('parent.register.submit');
Route::get('/orang-tua/masuk', [ParentAuthController::class, 'show'])->name('parent.login');
Route::post('/orang-tua/masuk', [ParentAuthController::class, 'login'])->middleware('throttle:5,1')->name('parent.login.submit');
Route::get('/orang-tua/google', [ParentGoogleAuthController::class, 'redirect'])->name('parent.google.redirect');
Route::get('/orang-tua/google/callback', [ParentGoogleAuthController::class, 'callback'])->name('parent.google.callback');
Route::get('/orang-tua/google/selesaikan', [ParentGoogleAuthController::class, 'completeForm'])->name('parent.google.complete');
Route::post('/orang-tua/google/selesaikan', [ParentGoogleAuthController::class, 'complete'])->middleware('throttle:5,1')->name('parent.google.complete.submit');

Route::prefix('orang-tua')->name('parent.')->middleware('parent.auth')->group(function (): void {
    Route::post('/keluar', [ParentAuthController::class, 'logout'])->name('logout');
    Route::get('/beranda', [ParentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/pendaftaran/{id}/cetak', [ParentPortalController::class, 'printRegistration'])->whereNumber('id')->name('registration.print');
    Route::get('/tagihan/{id}', [ParentPortalController::class, 'invoice'])->whereNumber('id')->name('invoice');
    Route::post('/tagihan/{id}/pembayaran', [ParentPortalController::class, 'reportPayment'])->whereNumber('id')->name('payment.report');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('school.auth')->name('logout');

    Route::middleware('school.auth')->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/pendaftar', [ApplicantController::class, 'index'])->name('applicants.index');
        Route::get('/pendaftar/{id}', [ApplicantController::class, 'show'])->whereNumber('id')->name('applicants.show');
        Route::patch('/pendaftar/{id}/status', [ApplicantController::class, 'updateStatus'])->whereNumber('id')->middleware('school.admin')->name('applicants.status');
        Route::get('/screening', [ScreeningController::class, 'index'])->name('screening.index');
        Route::post('/screening', [ScreeningController::class, 'store'])->name('screening.store');
        Route::get('/screening/riwayat', [ScreeningController::class, 'history'])->name('screening.history');
        Route::get('/screening/hasil/{id}', [ScreeningController::class, 'result'])->whereNumber('id')->name('screening.result');
        Route::get('/data-latih', [TrainingDataController::class, 'index'])->name('training.index');
        Route::post('/data-latih', [TrainingDataController::class, 'store'])->middleware('school.admin')->name('training.store');
        Route::delete('/data-latih/{id}', [TrainingDataController::class, 'destroy'])->whereNumber('id')->middleware('school.admin')->name('training.destroy');
        Route::get('/evaluasi', EvaluationController::class)->name('evaluation');

        Route::middleware('school.admin')->group(function (): void {
            Route::get('/pembayaran', [AdminPaymentController::class, 'index'])->name('payments.index');
            Route::post('/pembayaran/pengaturan', [AdminPaymentController::class, 'saveSettings'])->name('payments.settings');
            Route::post('/pembayaran/buat-tagihan', [AdminPaymentController::class, 'createMissing'])->name('payments.create-missing');
            Route::post('/pembayaran/{id}/setujui', [AdminPaymentController::class, 'process'])->defaults('decision', 'approve')->whereNumber('id')->name('payments.approve');
            Route::post('/pembayaran/{id}/tolak', [AdminPaymentController::class, 'process'])->defaults('decision', 'reject')->whereNumber('id')->name('payments.reject');
            Route::post('/pembayaran/tunai', [AdminPaymentController::class, 'recordCash'])->name('payments.cash');
            Route::get('/pembayaran/{id}/bukti', [AdminPaymentController::class, 'proof'])->whereNumber('id')->name('payments.proof');
        });
    });
});
