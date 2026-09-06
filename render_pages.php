<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    Illuminate\Support\Facades\Auth::login($user);
}

echo "Bootstrap successful. User: " . (Illuminate\Support\Facades\Auth::user()->email ?? 'None') . "\n";

$pages = [
    'dashboard' => function() {
        return app(App\Http\Controllers\DashboardController::class)->index(request())->render();
    },
    'leads' => function() {
        request()->merge(['view' => 'table']);
        return app(App\Http\Controllers\LeadController::class)->index(request())->render();
    },
    'kanban' => function() {
        request()->merge(['view' => 'kanban']);
        return app(App\Http\Controllers\LeadController::class)->index(request())->render();
    },
    'toolkit' => function() {
        return app(App\Http\Controllers\SblToolkitController::class)->index(request())->render();
    },
    'tasks' => function() {
        return app(App\Http\Controllers\TaskController::class)->index(request())->render();
    },
    'reports' => function() {
        return app(App\Http\Controllers\ReportController::class)->index(request())->render();
    },
    'calendar' => function() {
        return app(App\Http\Controllers\ContentCalendarController::class)->index(request())->render();
    },
    'users' => function() {
        return app(App\Http\Controllers\UserController::class)->index(request())->render();
    },
    'roles' => function() {
        return app(App\Http\Controllers\RoleController::class)->index()->render();
    },
    'ecosystem' => function() {
        return app(App\Http\Controllers\EcosystemController::class)->index(request())->render();
    },
    'contacts' => function() {
        return app(App\Http\Controllers\SblContactController::class)->index(request())->render();
    },
];

$rendered = [];
foreach ($pages as $key => $callback) {
    try {
        $html = $callback();
        $rendered[$key] = $html;
        echo "Rendered $key: " . strlen($html) . " bytes\n";
    } catch (Exception $e) {
        echo "Error rendering $key: " . $e->getMessage() . "\n";
    }
}

file_put_contents(__DIR__ . '/storage/rendered_pages.json', json_encode($rendered));
echo "Successfully saved storage/rendered_pages.json with " . count($rendered) . " pages!\n";
