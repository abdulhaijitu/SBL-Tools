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

            if ($model instanceof \App\Models\Lead) {
                $query->where(function (Builder $q) use ($model, $user) {
                    $q->where($model->qualifyColumn('owner_user_id'), $user->id)
                        ->orWhere($model->qualifyColumn('assigned_to'), $user->id);
                });
                return;
            }

            $query->where($model->qualifyColumn('user_id'), $user->id);
        });
    }
}
