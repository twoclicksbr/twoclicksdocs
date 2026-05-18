<?php

namespace App\Http\Controllers\Admin;

use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends CrudController
{
    protected string $model = User::class;
    protected string $route = 'admin.usuarios';
    protected string $title = 'Usuário';
    protected string $titlePlural = 'Usuários';
    protected array $searchable = ['email'];
    protected string $orderBy = 'email';
    protected array $fields = [
        ['name' => 'person_id', 'label' => 'Pessoa', 'type' => 'select'],
        ['name' => 'email',     'label' => 'Email',  'type' => 'email'],
        ['name' => 'password',  'label' => 'Senha',  'type' => 'password', 'in_table' => false],
    ];

    protected function rules(?int $id = null): array
    {
        return [
            'person_id' => 'required|integer|exists:tc_doc.people,id',
            'email'     => "required|email|max:255|unique:tc_doc.users,email,{$id}",
            'password'  => $id ? 'nullable|string|min:6' : 'required|string|min:6',
        ];
    }

    protected function options(): array
    {
        return [
            'person_id' => Person::orderBy('first_name')->get()
                ->mapWithKeys(fn($p) => [$p->id => "{$p->first_name} {$p->surname}"])
                ->toArray(),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route("{$this->route}.index")->with('success', 'Usuário criado.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->validate($this->rules($id));
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);
        return redirect()->route("{$this->route}.index")->with('success', 'Usuário atualizado.');
    }
}
