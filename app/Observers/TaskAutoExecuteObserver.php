<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\TaskAutoExecuteService;

class TaskAutoExecuteObserver
{
    public function __construct(private readonly TaskAutoExecuteService $service) {}

    public function created(Task $task): void
    {
        $this->service->applyDefaultsToTasks([$task->id], (int) $task->project_id);
    }
}
