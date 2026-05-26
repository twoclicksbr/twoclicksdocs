<?php

namespace App\Services;

use App\Jobs\DispatchStatusWebhookJob;
use App\Models\Task;

class TaskWebhookService
{
    public function dispatchIfApplicable(Task $task): void
    {
        $task->loadMissing(['autoExecuteStatuses:id', 'project:id,slug', 'status']);
        $status = $task->getStatusRelation();

        if (! $status || ! $status->webhook_url) {
            return;
        }

        if (! $task->autoExecuteStatuses->contains('id', $status->id)) {
            return;
        }

        DispatchStatusWebhookJob::dispatch(
            $task->id,
            $status->id,
            $status->webhook_url,
            $status->slug,
            $task->project?->slug,
        );
    }
}
