<?php

namespace Database\Seeders;

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
        // Status
        $statuses = [
            'Fazer-Claude',
            'Análise-Code',
            'Validado-Claude',
            'Execução-Code',
            'Revisão-Claude',
            'Aprovação-TwoClicks',
            'Concluído-TwoClicks',
            'Refazer-TwoClicks',
        ];
        foreach ($statuses as $i => $name) {
            TaskStatus::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        // Fases
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
                'name'   => $name,
                'slug'   => Str::slug($name),
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        // Módulos
        $modulos = ['Pessoas', 'Produtos', 'Vendas', 'Compras', 'Financeiro'];
        foreach ($modulos as $i => $name) {
            TaskModulo::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        // Tipos
        $tipos = ['Frontend', 'Backend', 'Banco de Dados', 'Infra/Deploy', 'Produto'];
        foreach ($tipos as $i => $name) {
            TaskTipo::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        // Prioridades
        $prioridades = [
            ['name' => 'Alta',  'color' => '#EF4444'],
            ['name' => 'Média', 'color' => '#F59E0B'],
            ['name' => 'Baixa', 'color' => '#10B981'],
        ];
        foreach ($prioridades as $i => $data) {
            TaskPrioridade::create([
                'name'   => $data['name'],
                'slug'   => Str::slug($data['name']),
                'color'  => $data['color'],
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        $this->command->info('Tabelas de apoio (status, fases, módulos, tipos, prioridades) populadas.');
    }
}
