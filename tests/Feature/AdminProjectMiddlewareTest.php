<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminProjectMiddlewareTest extends TestCase
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

    private function createUserWithToken(bool $isAdmin): array
    {
        $project = Project::create([
            'name'     => $isAdmin ? 'Docs TwoClicks' : 'Regular Project',
            'slug'     => $isAdmin ? 'docstwoclicks' : 'regular',
            'order'    => 1,
            'status'   => true,
            'is_admin' => $isAdmin,
        ]);

        $person = Person::create([
            'first_name' => 'Test',
            'surname'    => 'User',
        ]);

        $user = User::create([
            'person_id' => $person->id,
            'email'     => $isAdmin ? 'admin@test.com' : 'regular@test.com',
            'password'  => bcrypt('password'),
        ]);

        $tokenResult = $user->createToken('test');
        $tokenResult->accessToken->update(['project_id' => $project->id]);

        return [$user, $tokenResult->plainTextToken];
    }

    public function test_non_admin_token_receives_403_on_projects(): void
    {
        [, $token] = $this->createUserWithToken(false);

        $response = $this->withToken($token)->getJson('/api/projects');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Acesso restrito a projetos administradores.']);
    }

    public function test_admin_token_receives_200_on_projects(): void
    {
        [, $token] = $this->createUserWithToken(true);

        $response = $this->withToken($token)->getJson('/api/projects');

        $response->assertStatus(200);
    }
}
