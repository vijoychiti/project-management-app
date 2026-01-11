<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskUpdateController;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('tasks.index');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        
        // Activity Logs
        Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity_logs.index');
    });

    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Task Routes
    Route::get('tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    // Task Update Routes
    Route::post('tasks/{task}/updates', [TaskUpdateController::class, 'store'])->name('tasks.updates.store');
    
    // Comments & Attachments
    Route::post('tasks/{task}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('tasks.comments.store');

    // Credential Routes
    Route::get('credentials', [App\Http\Controllers\CredentialController::class, 'index'])->name('credentials.index');
    Route::post('credentials', [App\Http\Controllers\CredentialController::class, 'store'])->name('credentials.store');
    Route::post('credentials/share', [App\Http\Controllers\CredentialController::class, 'share'])->name('credentials.share'); // Bulk share
    Route::delete('credentials/{credential}', [App\Http\Controllers\CredentialController::class, 'destroy'])->name('credentials.destroy');
    Route::post('credentials/bulk-delete', [App\Http\Controllers\CredentialController::class, 'bulkDestroy'])->name('credentials.bulkDestroy');
});

// php artisan migrate:refresh --seed along with cache clearing route
Route::get('/migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:refresh', ['--seed' => true]);
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return redirect()->back()->with('success', 'Database migrated successfully!');
});
