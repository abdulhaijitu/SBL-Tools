<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesWorkspaceRecords
{
    protected static function bootScopesWorkspaceRecords(): void
    {
        static::addGlobalScope('workspace', function (Builder $query): void {
            $user = auth()->user();
            if (! $user || $user->isSuperAdmin()) {
                return;
            }

            $model = $query->getModel();
            // Team trees are private even when a manager can assign CRM leads.
            if ($model instanceof \App\Models\BinaryNode) {
                $query->where($model->qualifyColumn('tree_owner_id'), $user->id);
                return;
            }

            if ($user->hasPermission('leads.assign')) {
                return;
            }

            $column = $model instanceof \App\Models\Lead ? 'owner_user_id' : 'user_id';
            $query->where($model->qualifyColumn($column), $user->id);
        });
    }
}
