<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        $person = Person::create([
            'first_name' => 'Alex',
            'surname' => 'Alves de Almeida',
        ]);

        $user = User::create([
            'person_id' => $person->id,
            'email' => 'alex@twoclicks.com',
            'password' => Hash::make('Alex1985@'),
        ]);

        // Tokens ainda NÃO criados aqui — os tokens dependem de projects existir.
        // Os tokens serão criados em um seeder separado depois de criarmos os projetos.

        $this->command->info('Person + User criados: alex@twoclicks.com');
    }
}
