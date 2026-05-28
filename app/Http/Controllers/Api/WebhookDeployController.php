<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\TaskStatus;
use App\Services\TaskWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookDeployController extends Controller
{
    private const EXPECTED_SLUGS = [
        'sandbox' => 'aguardando-deploy-sandbox',
        'prod'    => 'aguardando-deploy-prod',
    ];

    private const SUCCESS_SLUGS = [
        'sandbox' => 'aprovacao-twoclicks',
        // Prod vai direto para concluido: bypass de aprovacao-prod-twoclicks evita que
        // ProcessCodeTaskJob seja morto por SIGTERM do restart do Horizon pós-deploy.
        'prod'    => 'concluido',
    ];

    public function receive(Request $request): JsonResponse
    {
        $token = config('services.deploy_webhook.token');

        if (! $token || ! hash_equals($token, (string) $request->header('X-Deploy-Webhook-Token', ''))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'task_id'    => 'required|integer|exists:tc_doc.tasks,id',
            'environment' => 'required|in:sandbox,prod',
            'status'     => 'required|in:success,failure',
            'ci_run_url' => 'nullable|url|max:500',
            'http_status' => 'nullable|integer',
        ]);

        $task = Task::query()
            ->with(['status', 'project:id,slug', 'autoExecuteStatuses:id'])
            ->findOrFail($data['task_id']);

        $expectedSlug = self::EXPECTED_SLUGS[$data['environment']];
        $currentSlug  = $task->status?->slug;

        if ($currentSlug !== $expectedSlug) {
            return response()->json([
                'message'  => "Task não está no status esperado '{$expectedSlug}' (atual: '{$currentSlug}'). Nenhuma ação tomada.",
                'expected' => $expectedSlug,
                'current'  => $currentSlug,
            ], 422);
        }

        $targetSlug = $data['status'] === 'failure'
            ? 'erro-code'
            : self::SUCCESS_SLUGS[$data['environment']];

        $targetStatus = TaskStatus::query()
            ->where('slug', $targetSlug)
            ->where('project_id', $task->project_id)
            ->firstOrFail();

        $ciRunUrl   = $data['ci_run_url'] ?? null;
        $httpStatus = $data['http_status'] ?? null;

        $resumo = $data['status'] === 'success'
            ? "Deploy {$data['environment']} concluído com sucesso. HTTP status: {$httpStatus}. CI: {$ciRunUrl}. Transitando para '{$targetSlug}'."
            : "Deploy {$data['environment']} falhou. HTTP status: {$httpStatus}. CI: {$ciRunUrl}. Transitando para 'erro-code'.";

        TaskDetail::create([
            'task_id'        => $task->id,
            'task_status_id' => $task->task_status_id,
            'person_id'      => null,
            'prompt'         => null,
            'resumo'         => $resumo,
        ]);

        // Vestigial: registra passagem pelo status aprovacao-prod-twoclicks sem entrar nele,
        // preservando rastreabilidade no histórico da task.
        if ($data['environment'] === 'prod' && $data['status'] === 'success') {
            $aprovacaoProdStatus = TaskStatus::query()
                ->where('slug', 'aprovacao-prod-twoclicks')
                ->where('project_id', $task->project_id)
                ->first();

            if ($aprovacaoProdStatus) {
                TaskDetail::create([
                    'task_id'        => $task->id,
                    'task_status_id' => $aprovacaoProdStatus->id,
                    'person_id'      => null,
                    'prompt'         => null,
                    'resumo'         => 'Aprovação prod inlined (vestigial): bypassed para evitar SIGTERM do restart do Horizon pós-deploy.',
                ]);
            }
        }

        $task->update(['task_status_id' => $targetStatus->id]);

        app(TaskWebhookService::class)->dispatchIfApplicable(
            $task->fresh(['autoExecuteStatuses', 'project', 'status'])
        );

        return response()->json([
            'success'        => true,
            'task_id'        => $task->id,
            'transitioned_to' => $targetSlug,
        ]);
    }
}
