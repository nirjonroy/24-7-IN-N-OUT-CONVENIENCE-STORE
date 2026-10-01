<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactInfoController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SiteinfoController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SpecialBusinessHourController;
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
    Route::get('/admin/about', [AboutController::class, 'edit'])->name('admin.about.edit');
    Route::post('/admin/about', [AboutController::class, 'update'])->name('admin.about.update');
    Route::resource('/admin/businesses', BusinessController::class)->names('admin.businesses');
    Route::resource('/admin/locations', LocationController::class)->names('admin.locations');
    Route::get('/admin/locations/{location}/hours', [BusinessHourController::class, 'edit'])->name('admin.locations.hours.edit');
    Route::post('/admin/locations/{location}/hours', [BusinessHourController::class, 'update'])->name('admin.locations.hours.update');
    Route::resource('/admin/locations/{location}/special-hours', SpecialBusinessHourController::class)
        ->except(['show'])
        ->parameters(['special-hours' => 'specialHour'])
        ->names('admin.locations.special-hours');
    Route::resource('/admin/businesses/{business}/social-links', SocialLinkController::class)
        ->except(['show'])
        ->parameters(['social-links' => 'socialLink'])
        ->names('admin.businesses.social-links');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
