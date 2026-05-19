<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TaskFase;
use App\Models\TaskModulo;
use App\Models\TaskPrioridade;
use App\Models\TaskStatus;
use App\Models\TaskTipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TaskSupportSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $statuses = [
                'Fazer - Code',
                'Análise - Claude',
                'Executar - Code',
                'Revisão - Claude',
                'Aprovação - TwoClicks',
                'Concluído',
            ];
            foreach ($statuses as $i => $name) {
                TaskStatus::create([
                    'project_id' => $project->id,
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'order'      => $i + 1,
                    'status'     => true,
                ]);
            }

            $fases = [
                'Fase 1 (BD)',
                'Fase 2 (Infra backend)',
                'Fase 3 (Auth/permissões)',
                'Fase 4 (Pessoas)',
                'Fase 5 (Frontend base)',
                'Fase 6 (Segurança/admin)',
                'Fase 7 (Demais módulos)',
                'Fase 8 (Complementos)',
            ];
            foreach ($fases as $i => $name) {
                TaskFase::create([
                    'project_id' => $project->id,
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'order'      => $i + 1,
                    'status'     => true,
                ]);
            }

            $modulos = ['Pessoas', 'Produtos', 'Vendas', 'Compras', 'Financeiro', 'Infra/Deploy', 'Admin'];
            foreach ($modulos as $i => $name) {
                TaskModulo::create([
                    'project_id' => $project->id,
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'order'      => $i + 1,
                    'status'     => true,
                ]);
            }

            $tipos = ['Frontend', 'Backend', 'Banco de Dados', 'Infra/Deploy', 'Produto'];
            foreach ($tipos as $i => $name) {
                TaskTipo::create([
                    'project_id' => $project->id,
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'order'      => $i + 1,
                    'status'     => true,
                ]);
            }

            $prioridades = [
                ['name' => 'Alta',  'color' => '#EF4444'],
                ['name' => 'Média', 'color' => '#F59E0B'],
                ['name' => 'Baixa', 'color' => '#10B981'],
            ];
            foreach ($prioridades as $i => $data) {
                TaskPrioridade::create([
                    'project_id' => $project->id,
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'color'      => $data['color'],
                    'order'      => $i + 1,
                    'status'     => true,
                ]);
            }
        }

        $this->command->info('Tabelas de apoio criadas para ' . $projects->count() . ' projetos.');
    }
}
