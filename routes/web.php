<?php

use App\Http\Controllers\ContentCalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads CRM
    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
    Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::patch('/leads/{lead}/stage', [LeadController::class, 'updateStage'])->name('leads.update-stage');
    Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity'])->name('leads.add-activity');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    // Tasks & Follow-ups
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Presentations
    Route::get('/presentations', [PresentationController::class, 'index'])->name('presentations.index');
    Route::post('/presentations', [PresentationController::class, 'store'])->name('presentations.store');

    // Marketing & Content Calendar
    Route::get('/marketing/content-calendar', [ContentCalendarController::class, 'index'])->name('marketing.content-calendar');
    Route::post('/marketing/content-calendar', [ContentCalendarController::class, 'store'])->name('marketing.content-calendar.store');
    Route::put('/marketing/content-calendar/{contentItem}', [ContentCalendarController::class, 'update'])->name('marketing.content-calendar.update');
    Route::delete('/marketing/content-calendar/{contentItem}', [ContentCalendarController::class, 'destroy'])->name('marketing.content-calendar.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // SBL Plans & Toolkit (From PDF)
    Route::get('/toolkit', [\App\Http\Controllers\SblToolkitController::class, 'index'])->name('toolkit.index');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
