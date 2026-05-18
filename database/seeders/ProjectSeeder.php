<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            ['name' => 'SmartClick360', 'slug' => 'smartclick360'],
            ['name' => 'Bethel360',     'slug' => 'bethel360'],
            ['name' => 'ApDireta',      'slug' => 'apdireta'],
            ['name' => 'ClickBank',     'slug' => 'clickbank'],
            ['name' => 'WhatsPanel',    'slug' => 'whatspanel'],
        ];

        foreach ($projects as $i => $data) {
            Project::create([
                'name'   => $data['name'],
                'slug'   => $data['slug'],
                'order'  => $i + 1,
                'status' => true,
            ]);
        }

        $this->command->info('5 projetos criados.');
    }
}
