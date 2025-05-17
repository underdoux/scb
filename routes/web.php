 <?php

use App\Http\Controllers\{
    AnalyticsController,
    DashboardController,
    LogController,
    PostController,
    ProfileController,
    SocialAccountController
};
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard with statistics
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Posts routes
    Route::prefix('posts')->group(function () {
        // Main CRUD routes
        Route::get('/', [PostController::class, 'index'])->name('posts.index');
        Route::get('/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('/', [PostController::class, 'store'])->name('posts.store');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::patch('/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

        // AI Content Generation
        Route::post('/generate-content', [PostController::class, 'generateContent'])
            ->name('posts.generate-content');
        Route::post('/generate-variations', [PostController::class, 'generateVariations'])
            ->name('posts.generate-variations');

        // Schedule Management
        Route::post('/{post}/schedule', [PostController::class, 'schedule'])
            ->name('posts.schedule');
        Route::post('/{post}/cancel-schedule', [PostController::class, 'cancelSchedule'])
            ->name('posts.cancel-schedule');
    });

    // Social Media Account Management
    Route::prefix('social-accounts')->group(function () {
});
        Route::delete('/{platform}', [SocialAccountController::class, 'disconnect'])->name('social-accounts.disconnect');
        Route::get('/connect/{platform}', [SocialAccountController::class, 'redirect'])->name('social-accounts.connect');
        Route::get('/callback/{platform}', [SocialAccountController::class, 'callback'])->name('social-accounts.callback');
    });

    // Analytics and Reports
    Route::prefix('analytics')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    });

    // Activity Logs
    Route::prefix('logs')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('logs.index');
        Route::delete('/clear', [LogController::class, 'clear'])->name('logs.clear');
        Route::get('/export', [LogController::class, 'export'])->name('logs.export');
    });

require __DIR__.'/auth.php';
