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

function setRenderRoute(string $uri, array $query = []) {
    $request = Illuminate\Http\Request::create($uri, 'GET', $query);
    $user = Illuminate\Support\Facades\Auth::user();
    if ($user) {
        $request->setUserResolver(fn() => $user);
    }
    $router = app('router');
    try {
        $route = $router->getRoutes()->match($request);
        $request->setRouteResolver(fn() => $route);
    } catch (\Throwable $e) {
        // Fallback
    }
    app()->instance('request', $request);
    Illuminate\Support\Facades\Request::swap($request);
    Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag);
    return $request;
}

echo "Bootstrap successful. User: " . (Illuminate\Support\Facades\Auth::user()->email ?? 'None') . "\n";

$pages = [
    'dashboard' => function () {
        $req = setRenderRoute('/dashboard');
        return app(App\Http\Controllers\DashboardController::class)->index($req)->render();
    },
    'leads' => function () {
        $req = setRenderRoute('/leads', ['view' => 'table']);
        return app(App\Http\Controllers\LeadController::class)->index($req)->render();
    },
    'kanban' => function () {
        $req = setRenderRoute('/leads', ['view' => 'kanban']);
        return app(App\Http\Controllers\LeadController::class)->index($req)->render();
    },
    'toolkit' => function () {
        $req = setRenderRoute('/packages');
        return app(App\Http\Controllers\SblToolkitController::class)->packages($req)->render();
    },
    'packages' => function () {
        $req = setRenderRoute('/packages');
        return app(App\Http\Controllers\SblToolkitController::class)->packages($req)->render();
    },
    'ranks' => function () {
        $req = setRenderRoute('/ranks');
        return app(App\Http\Controllers\SblToolkitController::class)->ranks($req)->render();
    },
    'counseling' => function () {
        $req = setRenderRoute('/counseling');
        return app(App\Http\Controllers\SblToolkitController::class)->counseling($req)->render();
    },
    'commission' => function () {
        $req = setRenderRoute('/commission');
        return app(App\Http\Controllers\SblToolkitController::class)->commission($req)->render();
    },
    'links' => function () {
        $req = setRenderRoute('/links');
        return app(App\Http\Controllers\SblToolkitController::class)->links($req)->render();
    },
    'resources' => function () {
        $req = setRenderRoute('/resources');
        return app(App\Http\Controllers\SblToolkitController::class)->resources($req)->render();
    },
    'tasks' => function () {
        $req = setRenderRoute('/tasks');
        return app(App\Http\Controllers\TaskController::class)->index($req)->render();
    },
    'reports' => function () {
        $req = setRenderRoute('/reports');
        return app(App\Http\Controllers\ReportController::class)->index($req)->render();
    },
    'calendar' => function () {
        $req = setRenderRoute('/marketing/content-calendar');
        return app(App\Http\Controllers\ContentCalendarController::class)->index($req)->render();
    },
    'users' => function () {
        $req = setRenderRoute('/users');
        return app(App\Http\Controllers\UserController::class)->index($req)->render();
    },
    'roles' => function () {
        $req = setRenderRoute('/roles');
        return app(App\Http\Controllers\RoleController::class)->index()->render();
    },
    'ecosystem' => function () {
        $req = setRenderRoute('/ecosystem');
        return app(App\Http\Controllers\EcosystemController::class)->index($req)->render();
    },
    'abbreviations' => function () {
        $req = setRenderRoute('/abbreviations');
        return app(App\Http\Controllers\AbbreviationController::class)->index($req)->render();
    },
    'contacts' => function () {
        $req = setRenderRoute('/contacts');
        return app(App\Http\Controllers\SblContactController::class)->index($req)->render();
    },
    'leads_create' => function () {
        $req = setRenderRoute('/leads/create');
        return app(App\Http\Controllers\LeadController::class)->create()->render();
    },
    'presentations' => function () {
        $req = setRenderRoute('/presentations');
        return app(App\Http\Controllers\PresentationController::class)->index($req)->render();
    },
    'binary' => function () {
        $req = setRenderRoute('/team', ['view' => 'builder']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index($req)->render();
    },
    'binary_mindmap' => function () {
        $req = setRenderRoute('/team', ['view' => 'mindmap']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index($req)->render();
    },
    'binary_table' => function () {
        $req = setRenderRoute('/team', ['view' => 'table']);
        return app(App\Http\Controllers\BinaryTeamController::class)->index($req)->render();
    },
    'leads_show' => function () {
        $lead = App\Models\Lead::first();
        if ($lead) {
            setRenderRoute('/leads/' . $lead->id);
            return app(App\Http\Controllers\LeadController::class)->show($lead)->render();
        }
        return '';
    },
    'leads_edit' => function () {
        $lead = App\Models\Lead::first();
        if ($lead) {
            setRenderRoute('/leads/' . $lead->id . '/edit');
            return app(App\Http\Controllers\LeadController::class)->edit($lead)->render();
        }
        return '';
    },
    'profile' => function () {
        $req = setRenderRoute('/profile');
        return app(App\Http\Controllers\ProfileController::class)->edit($req)->render();
    },
    'login' => function () {
        setRenderRoute('/login');
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
