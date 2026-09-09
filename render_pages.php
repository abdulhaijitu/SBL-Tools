<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
if ($user) {
    Illuminate\Support\Facades\Auth::login($user);
    request()->setUserResolver(fn() => $user);
}

Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);

echo "Bootstrap successful. User: " . (Illuminate\Support\Facades\Auth::user()->email ?? 'None') . "\n";

$pages = [
    'dashboard' => function () {
        return app(App\Http\Controllers\DashboardController::class)->index(request())->render();
    },
    'leads' => function () {
        request()->merge(['view' => 'table']);
        return app(App\Http\Controllers\LeadController::class)->index(request())->render();
    },
    'kanban' => function () {
        request()->merge(['view' => 'kanban']);
        return app(App\Http\Controllers\LeadController::class)->index(request())->render();
    },
    'toolkit' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->packages(request())->render();
    },
    'packages' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->packages(request())->render();
    },
    'ranks' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->ranks(request())->render();
    },
    'counseling' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->counseling(request())->render();
    },
    'commission' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->commission(request())->render();
    },
    'links' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->links(request())->render();
    },
    'resources' => function () {
        return app(App\Http\Controllers\SblToolkitController::class)->resources(request())->render();
    },
    'tasks' => function () {
        return app(App\Http\Controllers\TaskController::class)->index(request())->render();
    },
    'reports' => function () {
        return app(App\Http\Controllers\ReportController::class)->index(request())->render();
    },
    'calendar' => function () {
        return app(App\Http\Controllers\ContentCalendarController::class)->index(request())->render();
    },
    'users' => function () {
        return app(App\Http\Controllers\UserController::class)->index(request())->render();
    },
    'roles' => function () {
        return app(App\Http\Controllers\RoleController::class)->index()->render();
    },
    'ecosystem' => function () {
        return app(App\Http\Controllers\EcosystemController::class)->index(request())->render();
    },
    'abbreviations' => function () {
        return app(App\Http\Controllers\AbbreviationController::class)->index(request())->render();
    },
    'contacts' => function () {
        return app(App\Http\Controllers\SblContactController::class)->index(request())->render();
    },
    'leads_create' => function () {
        return app(App\Http\Controllers\LeadController::class)->create()->render();
    },
    'presentations' => function () {
        return app(App\Http\Controllers\PresentationController::class)->index(request())->render();
    },
    'binary' => function () {
        request()->merge(['view' => 'builder']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index(request())->render();
    },
    'binary_mindmap' => function () {
        request()->merge(['view' => 'mindmap']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index(request())->render();
    },
    'binary_table' => function () {
        request()->merge(['view' => 'table']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index(request())->render();
    },
    'leads_show' => function () {
        $lead = App\Models\Lead::first();
        return $lead ? app(App\Http\Controllers\LeadController::class)->show($lead)->render() : '';
    },
    'leads_edit' => function () {
        $lead = App\Models\Lead::first();
        return $lead ? app(App\Http\Controllers\LeadController::class)->edit($lead)->render() : '';
    },
    'profile' => function () {
        return app(App\Http\Controllers\ProfileController::class)->edit(request())->render();
    },
    'login' => function () {
        return view('auth.login')->render();
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
