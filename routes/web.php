<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactInfoController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SiteinfoController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/admin/siteinfo', [SiteinfoController::class, 'edit'])->name('admin.siteinfo.edit');
    Route::post('/admin/siteinfo', [SiteinfoController::class, 'update'])->name('admin.siteinfo.update');
    Route::resource('/admin/sliders', SliderController::class)->names('admin.sliders');
    Route::get('/admin/contact-info', [ContactInfoController::class, 'edit'])->name('admin.contact-info.edit');
    Route::post('/admin/contact-info', [ContactInfoController::class, 'update'])->name('admin.contact-info.update');
    Route::resource('/admin/contacts', ContactController::class)->only(['index', 'show', 'update', 'destroy'])->names('admin.contacts');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
