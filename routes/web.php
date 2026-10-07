<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\ContactInfoController;
use App\Http\Controllers\Admin\AboutController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\BusinessHourController;
use App\Http\Controllers\Admin\CatalogCategoryController;
use App\Http\Controllers\Admin\CatalogItemController;
use App\Http\Controllers\Admin\CatalogItemVariantController;
use App\Http\Controllers\Admin\DeviceBrandController;
use App\Http\Controllers\Admin\DeviceModelController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryCategoryController;
use App\Http\Controllers\Admin\GalleryItemController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RepairServiceController;
use App\Http\Controllers\Admin\RepairServicePriceController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SectionItemController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\SiteinfoController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SpecialBusinessHourController;
use App\Http\Controllers\Frontend\AboutController as FrontendAboutController;
use App\Http\Controllers\Frontend\CatalogController;
use App\Http\Controllers\Frontend\ContactController as FrontendContactController;
use App\Http\Controllers\Frontend\FaqController as FrontendFaqController;
use App\Http\Controllers\Frontend\GalleryController as FrontendGalleryController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PhoneRepairController;
use App\Http\Controllers\Frontend\RobotsController;
use App\Http\Controllers\Frontend\SitemapController;
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

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap.xml');
Route::get('/robots.txt', RobotsController::class)->name('robots.txt');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/convenience-store', [CatalogController::class, 'convenienceStore'])->name('frontend.convenience-store');
Route::get('/phone-repair/models', [PhoneRepairController::class, 'models'])->name('frontend.phone-repair.models');
Route::get('/phone-repair/estimate', [PhoneRepairController::class, 'estimate'])->name('frontend.phone-repair.estimate');
Route::get('/phone-repair', [PhoneRepairController::class, 'index'])->name('frontend.phone-repair');
Route::get('/smoothies', [CatalogController::class, 'smoothies'])->name('frontend.smoothies');
Route::get('/vape-tobacco', [CatalogController::class, 'adultRetail'])->name('frontend.vape-tobacco');
Route::get('/about', [FrontendAboutController::class, 'index'])->name('frontend.about');
Route::get('/faq', [FrontendFaqController::class, 'index'])->name('frontend.faq');
Route::get('/gallery', [FrontendGalleryController::class, 'index'])->name('frontend.gallery');
Route::get('/contact', [FrontendContactController::class, 'index'])->name('frontend.contact');

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
    Route::get('/admin/media/picker', [MediaController::class, 'picker'])->name('admin.media.picker');
    Route::resource('/admin/media', MediaController::class)->parameters(['media' => 'medium'])->names('admin.media');
    Route::get('/admin/seo/settings', [SeoSettingController::class, 'edit'])->name('admin.seo.settings.edit');
    Route::match(['put', 'patch'], '/admin/seo/settings', [SeoSettingController::class, 'update'])->name('admin.seo.settings.update');
    Route::resource('/admin/seo/redirects', RedirectController::class)
        ->parameters(['redirects' => 'redirect'])
        ->names('admin.seo.redirects');
    Route::resource('/admin/reviews', ReviewController::class)->names('admin.reviews');
    Route::resource('/admin/menus', MenuController::class)->names('admin.menus');
    Route::resource('/admin/menus/{menu}/items', MenuItemController::class)
        ->parameters(['items' => 'item'])
        ->names('admin.menus.items');
    Route::resource('/admin/pages', PageController::class)->names('admin.pages');
    Route::resource('/admin/pages/{page}/sections', PageSectionController::class)
        ->parameters(['sections' => 'section'])
        ->names('admin.pages.sections');
    Route::resource('/admin/pages/{page}/sections/{section}/items', SectionItemController::class)
        ->parameters(['items' => 'item'])
        ->names('admin.pages.sections.items');
    Route::resource('/admin/catalog/categories', CatalogCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->names('admin.catalog.categories');
    Route::resource('/admin/catalog/items', CatalogItemController::class)
        ->parameters(['items' => 'item'])
        ->names('admin.catalog.items');
    Route::resource('/admin/catalog/items/{item}/variants', CatalogItemVariantController::class)
        ->parameters(['variants' => 'variant'])
        ->names('admin.catalog.items.variants');
    Route::resource('/admin/faq/categories', FaqCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->names('admin.faq.categories');
    Route::resource('/admin/faq/questions', FaqController::class)
        ->parameters(['questions' => 'question'])
        ->names('admin.faq.questions');
    Route::resource('/admin/gallery/categories', GalleryCategoryController::class)
        ->parameters(['categories' => 'category'])
        ->names('admin.gallery.categories');
    Route::resource('/admin/gallery/items', GalleryItemController::class)
        ->parameters(['items' => 'item'])
        ->names('admin.gallery.items');
    Route::resource('/admin/phone-repair/brands', DeviceBrandController::class)
        ->parameters(['brands' => 'brand'])
        ->names('admin.phone-repair.brands');
    Route::resource('/admin/phone-repair/models', DeviceModelController::class)
        ->parameters(['models' => 'model'])
        ->names('admin.phone-repair.models');
    Route::resource('/admin/phone-repair/services', RepairServiceController::class)
        ->parameters(['services' => 'service'])
        ->names('admin.phone-repair.services');
    Route::get('/admin/phone-repair/services/{service}/prices', [RepairServicePriceController::class, 'edit'])
        ->name('admin.phone-repair.services.prices.edit');
    Route::post('/admin/phone-repair/services/{service}/prices', [RepairServicePriceController::class, 'update'])
        ->name('admin.phone-repair.services.prices.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
