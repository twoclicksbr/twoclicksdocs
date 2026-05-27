<?php

namespace App\Http\Controllers\Api;

use App\Jobs\ProcessCodeTaskJob;
use App\Models\TaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Bus;

class WebhookCodeController extends Controller
{
    public function receive(Request $request): JsonResponse
    {
        $secret = config('services.webhook.code_secret');

        if ($request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'task_id'        => 'required|integer',
            'task_status_id' => 'required|integer',
            'project_slug'   => 'nullable|string|max:64',
        ]);

        $taskId       = $validated['task_id'];
        $taskStatusId = $validated['task_status_id'];
        $projectSlug  = $validated['project_slug'] ?? null;

        $status = TaskStatus::find($taskStatusId);

        $isCodeVps = $status?->executor_type === 'code' && $status?->runtime_location === 'vps';
        $isShell   = $status?->executor_type === 'shell';

        if (! $status || (! $isCodeVps && ! $isShell)) {
            return response()->json(['message' => 'Status não requer execução VPS'], 422);
        }

        $jobId = Bus::dispatch(new ProcessCodeTaskJob($taskId, $projectSlug));

        return response()->json([
            'queued'  => true,
            'job_id'  => $jobId,
            'task_id' => $taskId,
            'message' => 'Job enfileirado',
        ]);
    }
}
