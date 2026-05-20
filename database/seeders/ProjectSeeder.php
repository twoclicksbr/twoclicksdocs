<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            ['name' => 'SmartClick360',  'slug' => 'smartclick360',  'is_admin' => false],
            ['name' => 'Bethel360',      'slug' => 'bethel360',      'is_admin' => false],
            ['name' => 'ApDireta',       'slug' => 'apdireta',       'is_admin' => false],
            ['name' => 'ClickBank',      'slug' => 'clickbank',      'is_admin' => false],
            ['name' => 'WhatsPanel',     'slug' => 'whatspanel',     'is_admin' => false],
            ['name' => 'Docs TwoClicks', 'slug' => 'docs-twoclicks', 'is_admin' => true],
        ];

        foreach ($projects as $i => $data) {
            Project::create([
                'name'     => $data['name'],
                'slug'     => $data['slug'],
                'order'    => $i + 1,
                'status'   => true,
                'is_admin' => $data['is_admin'],
            ]);
        }

        $this->command->info('6 projetos criados.');
    }
}
