<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceApplicationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->status !== 'inactive', 403, 'This account is inactive. Contact your administrator.');

        [$module, $action] = array_pad(explode('.', $request->route()->getName() ?? '', 2), 2, 'index');
        $read = $request->isMethod('GET');
        $permission = match ($module) {
            'leads', 'members' => match ($action) {
                'create', 'store' => 'leads.create',
                'edit', 'update', 'update-stage', 'add-activity' => 'leads.edit',
                'destroy' => 'leads.delete',
                'convert' => 'leads.convert',
                default => 'leads.view',
            },
            'tasks' => $read ? 'tasks.view' : ($action === 'destroy' ? 'tasks.delete' : 'tasks.manage'),
            'presentations' => $read ? 'presentations.view' : 'presentations.manage',
            'users' => $read ? 'users.view' : 'users.manage',
            'roles' => $read && $action === 'index' ? 'roles.view' : 'roles.manage',
            'toolkit', 'ecosystem', 'contacts' => $read ? 'toolkit.view' : 'users.manage',
            'marketing' => $read ? 'marketing.view' : 'marketing.manage',
            'reports' => 'reports.view',
            'abbreviations' => $read ? null : 'users.manage',
            default => null,
        };

        if ($permission) {
            abort_unless($user->hasPermission($permission), 403, 'You do not have access to this action. Ask your administrator for permission.');
        }

        return $next($request);
    }
}
