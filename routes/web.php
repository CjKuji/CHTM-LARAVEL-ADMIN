<?php

use App\Http\Controllers\ArchivedController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontOfficeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Livewire\ReservationsDesk;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Livewire Architecture: Points directly to your component class instead of an old controller
    Route::middleware('role:super_admin,reservation')->group(function () {
        Route::get('/reservation', ReservationsDesk::class)->name('reservation');
    });

    Route::middleware('role:super_admin,frontoffice')->group(function () {
        Route::get('/frontoffice', [FrontOfficeController::class, 'index'])->name('frontoffice');
        Route::put('/frontoffice/{booking}', [FrontOfficeController::class, 'update'])->name('frontoffice.update');
        Route::post('/frontoffice/receipts', [FrontOfficeController::class, 'storeReceipt'])->name('frontoffice.receipts.store');
        Route::get('/frontoffice/receipts/{receipt}', [FrontOfficeController::class, 'viewReceipt'])->name('frontoffice.receipt');

        // Note: You can migrate these pages to Livewire components later using the exact same approach!
        Route::get('/archived', [ArchivedController::class, 'index'])->name('archived');
        Route::get('/audit', [AuditController::class, 'index'])->name('audit');
    });

    Route::middleware('role:super_admin,frontoffice,housekeeper')->group(function () {
        Route::get('/room', [RoomController::class, 'index'])->name('room');
        Route::post('/room', [RoomController::class, 'store'])->name('room.store');
        Route::put('/room/{room}', [RoomController::class, 'update'])->name('room.update');
        Route::delete('/room/{room}', [RoomController::class, 'destroy'])->name('room.destroy');
        Route::post('/room/{room}/flag', [RoomController::class, 'toggleFlag'])->name('room.flag');
        Route::post('/housekeeping/{task}/start', [RoomController::class, 'startCleaning'])->name('housekeeping.start');
        Route::post('/housekeeping/{task}/complete', [RoomController::class, 'completeCleaning'])->name('housekeeping.complete');
        Route::post('/housekeeping/templates', [RoomController::class, 'storeTemplate'])->name('housekeeping.templates.store');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';