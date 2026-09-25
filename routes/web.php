<?php

use App\Http\Controllers\Api\NewsCronController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CitizenSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsFetchLogController;
use App\Http\Controllers\NewsSettingsController;
use App\Http\Controllers\NewsSourceController;
use App\Http\Controllers\PublicNewsController;
use App\Http\Controllers\SubmissionModerationController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [PublicNewsController::class, 'home'])->name('home');
Route::get('/sitemap.xml', [PublicNewsController::class, 'sitemap'])->name('sitemap');
Route::get('/news', [PublicNewsController::class, 'news'])->name('news.index');
Route::get('/news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');
Route::get('/category/{slug}', [PublicNewsController::class, 'category'])->name('category.show');
Route::get('/local-news', [PublicNewsController::class, 'local'])->name('local-news');
Route::get('/photo-news', [PublicNewsController::class, 'photo'])->name('photo-news');
Route::get('/video-news', [PublicNewsController::class, 'video'])->name('video-news');
Route::get('/breaking-news', [PublicNewsController::class, 'breaking'])->name('breaking-news');
Route::get('/search', [PublicNewsController::class, 'search'])->name('search');
Route::get('/about', [PublicNewsController::class, 'about'])->name('about');
Route::get('/contact', [PublicNewsController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [PublicNewsController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms', [PublicNewsController::class, 'terms'])->name('terms');
Route::get('/disclaimer', [PublicNewsController::class, 'disclaimer'])->name('disclaimer');

// Secure Vercel / External News Fetch Cron
Route::match(['get', 'post'], '/api/cron/fetch-news', [NewsCronController::class, 'fetch'])->name('api.cron.fetch-news');

Route::get('/submit-news', [CitizenSubmissionController::class, 'createPublic'])->name('submit-news');
Route::post('/submit-news', [CitizenSubmissionController::class, 'storePublic'])->middleware('throttle:5,1')->name('submit-news.store');
Route::get('/submit-news/success/{submission}', [CitizenSubmissionController::class, 'success'])->name('submit-news.success');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin', [DashboardController::class, 'admin'])
        ->middleware('role:super_admin,admin')
        ->name('admin');
    Route::get('/editor', [DashboardController::class, 'editor'])
        ->middleware('role:super_admin,admin,editor')
        ->name('editor');

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin,admin')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');

        // News Sources
        Route::resource('sources', NewsSourceController::class)->except(['show']);
        Route::patch('sources/{source}/toggle-status', [NewsSourceController::class, 'toggleStatus'])->name('sources.toggle-status');
        Route::post('sources/{source}/fetch-now', [NewsSourceController::class, 'fetchNow'])->name('sources.fetch-now');

        // Fetch Logs
        Route::get('logs', [NewsFetchLogController::class, 'index'])->name('logs.index');
        Route::post('logs/clear', [NewsFetchLogController::class, 'clear'])->name('logs.clear');

        // News Settings
        Route::get('settings/news', [NewsSettingsController::class, 'index'])->name('settings.news');
        Route::post('settings/news', [NewsSettingsController::class, 'update'])->name('settings.news.update');
        Route::post('settings/news/fetch-all', [NewsSettingsController::class, 'fetchAllNow'])->name('settings.news.fetch-all');
    });

    Route::prefix('admin/news')->name('admin.news.')->middleware('role:super_admin,admin,editor')->group(function (): void {
        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::get('/create', [NewsController::class, 'create'])->name('create');
        Route::post('/', [NewsController::class, 'store'])->name('store');
        Route::get('/{news}', [NewsController::class, 'show'])->name('show');
        Route::get('/{news}/edit', [NewsController::class, 'edit'])->name('edit');
        Route::put('/{news}', [NewsController::class, 'update'])->name('update');
        Route::patch('/{news}/status', [NewsController::class, 'updateStatus'])->name('status');
        Route::patch('/{news}/toggle-breaking', [NewsController::class, 'toggleBreaking'])->name('toggle-breaking');
        Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('my-submissions')->name('my-submissions.')->middleware('role:citizen,contributor')->group(function (): void {
        Route::get('/', [CitizenSubmissionController::class, 'index'])->name('index');
        Route::get('/create', [CitizenSubmissionController::class, 'create'])->name('create');
        Route::post('/', [CitizenSubmissionController::class, 'store'])->middleware('throttle:5,1')->name('store');
        Route::get('/{submission}', [CitizenSubmissionController::class, 'show'])->name('show');
        Route::get('/{submission}/edit', [CitizenSubmissionController::class, 'edit'])->name('edit');
        Route::put('/{submission}', [CitizenSubmissionController::class, 'update'])->middleware('throttle:5,1')->name('update');
    });

    Route::prefix('admin/submissions')->name('admin.submissions.')->middleware('role:super_admin,admin,editor')->group(function (): void {
        Route::get('/', [SubmissionModerationController::class, 'index'])->name('index');
        Route::get('/{submission}', [SubmissionModerationController::class, 'show'])->name('show');
        Route::post('/{submission}/start-review', [SubmissionModerationController::class, 'startReview'])->name('start-review');
        Route::post('/{submission}/request-information', [SubmissionModerationController::class, 'requestInformation'])->name('request-information');
        Route::post('/{submission}/verify', [SubmissionModerationController::class, 'verify'])->name('verify');
        Route::post('/{submission}/approve', [SubmissionModerationController::class, 'approve'])->name('approve');
        Route::post('/{submission}/reject', [SubmissionModerationController::class, 'reject'])->name('reject');
        Route::post('/{submission}/publish', [SubmissionModerationController::class, 'publish'])->name('publish');
    });
});
