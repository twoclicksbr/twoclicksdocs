<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class TokenSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'alex@twoclicks.com')->first();

        if (!$user) {
            $this->command->error('User alex@twoclicks.com não encontrado. Rode InitialSeeder antes.');
            return;
        }

        $tokenNames = ['alex', 'claude', 'code'];
        $projects = Project::orderBy('order')->get();

        $this->command->info('Tokens gerados (anote, são exibidos apenas uma vez):');
        $this->command->newLine();

        foreach ($projects as $project) {
            foreach ($tokenNames as $name) {
                $token = $user->createToken($name);
                $token->accessToken->update(['project_id' => $project->id]);

                $this->command->line(sprintf(
                    '  [%-12s] %-7s = %s',
                    $project->slug,
                    $name,
                    $token->plainTextToken
                ));
            }
            $this->command->newLine();
        }
    }
}
