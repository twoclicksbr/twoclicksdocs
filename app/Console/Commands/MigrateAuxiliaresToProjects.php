<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateAuxiliaresToProjects extends Command
{
    protected $signature = 'auxiliares:migrate-to-projects';
    protected $description = 'Duplica registros globais das 5 auxiliares para cada projeto existente e atualiza FKs.';

    private array $auxTables = [
        'task_statuses'    => 'task_status_id',
        'task_fases'       => 'task_fase_id',
        'task_modulos'     => 'task_modulo_id',
        'task_tipos'       => 'task_tipo_id',
        'task_prioridades' => 'task_prioridade_id',
    ];

    public function handle(): int
    {
        $db = DB::connection('tc_doc');

        // Idempotency check
        foreach (array_keys($this->auxTables) as $table) {
            if ($db->table($table)->whereNotNull('project_id')->exists()) {
                $this->error("Tabela {$table} já possui registros com project_id. Abortar — migração já foi executada.");
                return Command::FAILURE;
            }
        }

        $projects = $db->table('projects')->whereNull('deleted_at')->orderBy('id')->get();

        if ($projects->isEmpty()) {
            $this->error('Nenhum projeto encontrado. Nada a fazer.');
            return Command::FAILURE;
        }

        $this->info("Projetos encontrados: {$projects->count()}");

        $db->transaction(function () use ($db, $projects) {
            foreach ($this->auxTables as $table => $taskFk) {
                $this->line("\nProcessando {$table}...");

                $originals = $db->table($table)->whereNull('project_id')->orderBy('id')->get();
                $this->info("  Registros globais: {$originals->count()}");

                // old_id → [project_id => new_id]
                $idMap = [];

                foreach ($projects as $project) {
                    foreach ($originals as $row) {
                        $data = (array) $row;
                        unset($data['id']);
                        $data['project_id'] = $project->id;
                        $data['updated_at'] = now();
                        $data['deleted_at'] = null;

                        $newId = $db->table($table)->insertGetId($data);
                        $idMap[$row->id][$project->id] = $newId;
                    }
                    $this->info("  Projeto {$project->id} ({$project->name}): {$originals->count()} registros criados.");
                }

                // Update tasks.{$taskFk} (all tasks, including soft-deleted)
                $updatedTasks = $this->updateFks($db, 'tasks', $taskFk, 'project_id', $idMap, $table);
                $this->info("  tasks.{$taskFk} atualizados: {$updatedTasks}");

                // task_statuses is also referenced by task_details.task_status_id
                if ($table === 'task_statuses') {
                    $updatedDetails = $this->updateTaskDetailsFk($db, $idMap, $table);
                    $this->info("  task_details.task_status_id atualizados: {$updatedDetails}");
                }

                // Hard-delete original global records
                $originalIds = $originals->pluck('id');
                $db->table($table)->whereIn('id', $originalIds)->delete();
                $this->info("  Registros globais removidos: {$originalIds->count()}");
            }
        });

        $this->newLine();
        $this->info('Migração concluída com sucesso!');

        foreach (array_keys($this->auxTables) as $table) {
            $count = $db->table($table)->whereNull('deleted_at')->count();
            $this->line("  {$table}: {$count} registros ativos com project_id");
        }

        return Command::SUCCESS;
    }

    private function updateFks($db, string $refTable, string $column, string $projectCol, array $idMap, string $srcTable): int
    {
        $rows = $db->table($refTable)
            ->whereNotNull($column)
            ->get(['id', $projectCol, $column]);

        $updated = 0;
        foreach ($rows as $row) {
            $oldId     = $row->{$column};
            $projectId = $row->{$projectCol};

            if (!isset($idMap[$oldId][$projectId])) {
                throw new \RuntimeException(
                    "{$refTable}.id={$row->id}: sem mapeamento para {$srcTable}.{$oldId} no projeto {$projectId}"
                );
            }

            $db->table($refTable)->where('id', $row->id)->update([$column => $idMap[$oldId][$projectId]]);
            $updated++;
        }

        return $updated;
    }

    private function updateTaskDetailsFk($db, array $idMap, string $srcTable): int
    {
        $rows = $db->table('task_details')
            ->join('tasks', 'tasks.id', '=', 'task_details.task_id')
            ->whereNotNull('task_details.task_status_id')
            ->get(['task_details.id', 'tasks.project_id', 'task_details.task_status_id']);

        $updated = 0;
        foreach ($rows as $row) {
            $oldId     = $row->task_status_id;
            $projectId = $row->project_id;

            if (!isset($idMap[$oldId][$projectId])) {
                throw new \RuntimeException(
                    "task_details.id={$row->id}: sem mapeamento para {$srcTable}.{$oldId} no projeto {$projectId}"
                );
            }

            $db->table('task_details')->where('id', $row->id)->update(['task_status_id' => $idMap[$oldId][$projectId]]);
            $updated++;
        }

        return $updated;
    }
}
