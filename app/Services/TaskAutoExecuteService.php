<?php

namespace App\Services;

use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;

class TaskAutoExecuteService
{
    /**
     * IDs dos statuses do projeto que devem ser auto-executados em toda task
     * (auto_execute_default=true). `show_on_task` controla APENAS a exibição
     * no formulário e não interfere mais no pivot — quem decide auto-execução
     * é exclusivamente `auto_execute_default`.
     *
     * @return array<int>
     */
    public function defaultStatusIdsFor(int $projectId): array
    {
        return TaskStatus::where('project_id', $projectId)
            ->where('auto_execute_default', true)
            ->pluck('id')
            ->all();
    }

    /**
     * Garante que cada task em $taskIds tenha entrada em
     * task_auto_execute_statuses para todos os statuses "alwaysAuto" do
     * projeto. Idempotente via ON CONFLICT DO NOTHING.
     */
    public function applyDefaultsToTasks(array $taskIds, int $projectId): void
    {
        if (empty($taskIds)) {
            return;
        }

        $statusIds = $this->defaultStatusIdsFor($projectId);
        if (empty($statusIds)) {
            return;
        }

        $rows = [];
        foreach ($taskIds as $taskId) {
            foreach ($statusIds as $statusId) {
                $rows[] = ['task_id' => (int) $taskId, 'task_status_id' => (int) $statusId];
            }
        }

        DB::connection('tc_doc')
            ->table('task_auto_execute_statuses')
            ->insertOrIgnore($rows);
    }
}
