<?php

namespace App\Http\Controllers\Admin;

use App\Models\PersonalAccessToken;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjetoController extends CrudController
{
    protected string $model = Project::class;
    protected string $route = 'admin.projetos';
    protected string $title = 'Projeto';
    protected string $titlePlural = 'Projetos';
    protected array $searchable = ['name', 'slug'];
    protected string $orderBy = 'order';
    protected array $fields = [
        ['name' => 'name',   'label' => 'Nome',  'type' => 'text'],
        ['name' => 'slug',   'label' => 'Slug',  'type' => 'text'],
        ['name' => 'order',  'label' => 'Ordem', 'type' => 'number'],
        ['name' => 'status', 'label' => 'Ativo', 'type' => 'boolean'],
    ];

    protected function rules(?int $id = null): array
    {
        return [
            'name'   => 'required|string|max:100',
            'slug'   => "required|string|max:100|unique:tc_doc.projects,slug,{$id}",
            'order'  => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Project::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%");
            });
        }
        $items = $query->orderBy('order')->paginate(50)->withQueryString();

        return view('admin.projetos.index', compact('items', 'search'));
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        $tokens = PersonalAccessToken::where('project_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.projetos.show', compact('project', 'tokens'));
    }

    public function createToken(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $user = $request->user();
        $token = $user->createToken($data['name']);
        $token->accessToken->update(['project_id' => $project->id]);

        return redirect()
            ->route('admin.projetos.show', $project->id)
            ->with('new_token', $token->plainTextToken)
            ->with('success', 'Token criado. Copie agora — não será mostrado de novo.');
    }

    public function revokeToken($projectId, $tokenId)
    {
        PersonalAccessToken::where('project_id', $projectId)
            ->where('id', $tokenId)
            ->delete();

        return redirect()
            ->route('admin.projetos.show', $projectId)
            ->with('success', 'Token revogado.');
    }
}
