<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaskTransitionApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.tc_doc' => [
                'driver'                  => 'sqlite',
                'database'                => ':memory:',
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        DB::purge('tc_doc');

        $this->artisan('migrate', ['--path' => 'database/migrations', '--realpath' => false]);
    }

    private function makeScenario(): array
    {
        $project = Project::create([
            'name'     => 'Docs',
            'slug'     => 'docstwoclicks',
            'order'    => 1,
            'status'   => true,
            'is_admin' => true,
        ]);

        $otherProject = Project::create([
            'name'     => 'Other',
            'slug'     => 'other',
            'order'    => 2,
            'status'   => true,
            'is_admin' => false,
        ]);

        $person = Person::create(['first_name' => 'Code', 'surname' => 'VPS']);
        $user = User::create([
            'person_id' => $person->id,
            'email'     => 'code@test.com',
            'password'  => bcrypt('password'),
        ]);

        $fazer = TaskStatus::create([
            'project_id' => $project->id, 'name' => 'Fazer', 'slug' => 'fazer-code', 'order' => 1, 'status' => true,
        ]);
        $analise = TaskStatus::create([
            'project_id' => $project->id, 'name' => 'Analise', 'slug' => 'analise-code', 'order' => 2, 'status' => true,
        ]);
        $otherAnalise = TaskStatus::create([
            'project_id' => $otherProject->id, 'name' => 'Analise', 'slug' => 'analise-code', 'order' => 1, 'status' => true,
        ]);

        $fase = DB::connection('tc_doc')->table('task_fases')->insertGetId([
            'project_id' => $project->id, 'name' => 'F', 'slug' => 'f', 'order' => 1, 'status' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $modulo = DB::connection('tc_doc')->table('task_modulos')->insertGetId([
            'project_id' => $project->id, 'name' => 'M', 'slug' => 'm', 'order' => 1, 'status' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $tipo = DB::connection('tc_doc')->table('task_tipos')->insertGetId([
            'project_id' => $project->id, 'name' => 'T', 'slug' => 't', 'order' => 1, 'status' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $prio = DB::connection('tc_doc')->table('task_prioridades')->insertGetId([
            'project_id' => $project->id, 'name' => 'P', 'slug' => 'p', 'order' => 1, 'status' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $task = Task::create([
            'project_id'         => $project->id,
            'title'              => 'Task X',
            'task_status_id'     => $fazer->id,
            'task_fase_id'       => $fase,
            'task_modulo_id'     => $modulo,
            'task_tipo_id'       => $tipo,
            'task_prioridade_id' => $prio,
        ]);

        $tokenResult = $user->createToken('test');
        $tokenResult->accessToken->update(['project_id' => $project->id]);

        return [$token = $tokenResult->plainTextToken, $task, $fazer, $analise, $otherAnalise];
    }

    public function test_transition_accepts_task_status_slug(): void
    {
        [$token, $task, $fazer, $analise] = $this->makeScenario();

        $response = $this->withToken($token)->postJson("/api/doc/tasks/{$task->id}/transition", [
            'task_status_slug' => 'analise-code',
        ]);

        $response->assertStatus(200);
        $this->assertSame($analise->id, $task->fresh()->task_status_id);
    }

    public function test_transition_accepts_task_status_id(): void
    {
        [$token, $task, $fazer, $analise] = $this->makeScenario();

        $response = $this->withToken($token)->postJson("/api/doc/tasks/{$task->id}/transition", [
            'task_status_id' => $analise->id,
        ]);

        $response->assertStatus(200);
        $this->assertSame($analise->id, $task->fresh()->task_status_id);
    }

    public function test_transition_without_either_field_returns_422(): void
    {
        [$token, $task] = $this->makeScenario();

        $response = $this->withToken($token)->postJson("/api/doc/tasks/{$task->id}/transition", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['task_status_id', 'task_status_slug']);
    }

    public function test_transition_with_slug_from_other_project_returns_422(): void
    {
        [$token, $task, , , $otherAnalise] = $this->makeScenario();

        // slug 'analise-code' exists in $otherProject too, but token is scoped to $project;
        // the Rule::exists scopes by project_id so this should still validate via the in-scope one.
        // To prove project scoping, attempt a slug that only exists in another project.
        TaskStatus::where('id', $otherAnalise->id)->update(['slug' => 'only-in-other']);

        $response = $this->withToken($token)->postJson("/api/doc/tasks/{$task->id}/transition", [
            'task_status_slug' => 'only-in-other',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['task_status_slug']);
    }
}
