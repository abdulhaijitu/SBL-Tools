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
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Presentations
    Route::get('/presentations', [PresentationController::class, 'index'])->name('presentations.index');
    Route::post('/presentations', [PresentationController::class, 'store'])->name('presentations.store');
    Route::delete('/presentations/{presentation}', [PresentationController::class, 'destroy'])->name('presentations.destroy');

    // Marketing & Content Calendar
    Route::get('/marketing/content-calendar', [ContentCalendarController::class, 'index'])->name('marketing.content-calendar');
    Route::post('/marketing/content-calendar', [ContentCalendarController::class, 'store'])->name('marketing.content-calendar.store');
    Route::put('/marketing/content-calendar/{contentItem}', [ContentCalendarController::class, 'update'])->name('marketing.content-calendar.update');
    Route::delete('/marketing/content-calendar/{contentItem}', [ContentCalendarController::class, 'destroy'])->name('marketing.content-calendar.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // SBL Plans & Toolkit (From PDF)
    Route::get('/toolkit', [\App\Http\Controllers\SblToolkitController::class, 'index'])->name('toolkit.index');

    // SBL Ecosystem Directory & Links CRUD
    Route::get('/ecosystem', [\App\Http\Controllers\EcosystemController::class, 'index'])->name('ecosystem.index');
    Route::post('/ecosystem', [\App\Http\Controllers\EcosystemController::class, 'store'])->name('ecosystem.store');
    Route::put('/ecosystem/{ecosystemLink}', [\App\Http\Controllers\EcosystemController::class, 'update'])->name('ecosystem.update');
    Route::delete('/ecosystem/{ecosystemLink}', [\App\Http\Controllers\EcosystemController::class, 'destroy'])->name('ecosystem.destroy');

    // SBL Official Contacts & WhatsApp Helpline CRUD
    Route::get('/contacts', [\App\Http\Controllers\SblContactController::class, 'index'])->name('contacts.index');
    Route::post('/contacts', [\App\Http\Controllers\SblContactController::class, 'store'])->name('contacts.store');
    Route::put('/contacts/{contact}', [\App\Http\Controllers\SblContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [\App\Http\Controllers\SblContactController::class, 'destroy'])->name('contacts.destroy');

    // SBL Binary Team Tree & Placement Engine
    Route::get('/binary', [\App\Http\Controllers\BinaryTeamController::class, 'index'])->name('binary.index');
    Route::post('/binary/place', [\App\Http\Controllers\BinaryTeamController::class, 'store'])->name('binary.store');
    Route::put('/binary/{node}', [\App\Http\Controllers\BinaryTeamController::class, 'update'])->name('binary.update');
    Route::delete('/binary/{node}', [\App\Http\Controllers\BinaryTeamController::class, 'destroy'])->name('binary.destroy');
    Route::get('/binary/search', [\App\Http\Controllers\BinaryTeamController::class, 'search'])->name('binary.search');
    Route::get('/binary/{node}/extreme/{direction}', [\App\Http\Controllers\BinaryTeamController::class, 'extreme'])->name('binary.extreme');

    // Team & User Management CRUD
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');

    // Roles & Permissions Management CRUD
    Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'index'])->name('roles.index');
    Route::post('/roles', [\App\Http\Controllers\RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [\App\Http\Controllers\RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [\App\Http\Controllers\RoleController::class, 'destroy'])->name('roles.destroy');

    // User Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
