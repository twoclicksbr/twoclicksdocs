<?php

namespace App\Http\Controllers\Admin;

use App\Models\Person;

class PessoaController extends CrudController
{
    protected string $model = Person::class;
    protected string $route = 'admin.pessoas';
    protected string $title = 'Pessoa';
    protected string $titlePlural = 'Pessoas';
    protected array $searchable = ['first_name', 'surname'];
    protected string $orderBy = 'first_name';
    protected array $fields = [
        ['name' => 'first_name', 'label' => 'Nome',      'type' => 'text'],
        ['name' => 'surname',    'label' => 'Sobrenome', 'type' => 'text'],
    ];

    protected function rules(?int $id = null): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'surname'    => 'required|string|max:255',
        ];
    }
}
