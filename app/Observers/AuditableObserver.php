<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'create', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log(
            $model,
            'update',
            $model->getOriginal(),
            $model->getChanges()
        );
    }

    public function deleted(Model $model): void
    {
        $action = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
            ? 'force_delete'
            : 'delete';

        $this->log($model, $action, $model->getOriginal(), null);
    }

    public function restored(Model $model): void
    {
        $this->log($model, 'restore', null, $model->getAttributes());
    }

    /**
     * Persiste o log de auditoria.
     */
    private function log(Model $model, string $action, ?array $old, ?array $new): void
    {
        $request = request();
        $user    = $request?->user();
        $token   = $user?->currentAccessToken();

        AuditLog::create([
            'person_id'   => $user?->person_id,
            'project_id'  => $token?->project_id,
            'token_name'  => $token?->name,
            'action'      => $action,
            'table_name'  => $model->getTable(),
            'record_id'   => $model->getKey(),
            'old_values'  => $this->sanitize($old),
            'new_values'  => $this->sanitize($new),
            'ip_address'  => $request?->ip(),
        ]);
    }

    /**
     * Remove campos sensíveis dos valores logados.
     */
    private function sanitize(?array $values): ?array
    {
        if (empty($values)) {
            return null;
        }

        $blacklist = ['password', 'remember_token', 'token'];

        foreach ($blacklist as $field) {
            if (array_key_exists($field, $values)) {
                $values[$field] = '***';
            }
        }

        return $values;
    }
}
