<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;

/**
 * Extension of CrudController that scopes CRUD operations by project_id.
 * Children must still define $model, $route, $title, $titlePlural, $fields, rules().
 * rules() receives $id and also uses $this->currentProjectId.
 */
abstract class ProjectScopedCrudController extends CrudController
{
    protected ?int $currentProjectId = null;

    protected function resolveProjectId(Request $request): ?int
    {
        // input() checks both query string and request body — works for GET and POST
        $projectId = (int) $request->input('project_id');
        if (!$projectId) {
            $projectId = (int) Project::orderBy('name')->value('id');
        }
        return $projectId ?: null;
    }

    public function index(Request $request)
    {
        $projects  = Project::orderBy('name')->get();
        $projectId = (int) $request->query('project_id', $projects->first()?->id) ?: null;
        $search    = $request->query('search');

        $query = $this->model::query()->where('project_id', $projectId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchable as $col) {
                    $q->orWhere($col, 'ilike', "%{$search}%");
                }
            });
        }

        $items = $query->orderBy($this->orderBy, $this->orderDir)
                       ->paginate(50)
                       ->withQueryString();

        return view('admin.crud.index', [
            'items'       => $items,
            'route'       => $this->route,
            'title'       => $this->title,
            'titlePlural' => $this->titlePlural,
            'fields'      => $this->fields,
            'search'      => $search,
            'projects'    => $projects,
            'projectId'   => $projectId,
        ]);
    }

    public function create(?Request $request = null)
    {
        $projects  = Project::orderBy('name')->get();
        $projectId = $request ? $this->resolveProjectId($request) : $projects->first()?->id;

        return view('admin.crud.form', [
            'item'      => new $this->model,
            'route'     => $this->route,
            'title'     => $this->title,
            'fields'    => $this->fields,
            'mode'      => 'create',
            'options'   => $this->options(),
            'projects'  => $projects,
            'projectId' => $projectId,
        ]);
    }

    public function store(Request $request)
    {
        $projectId = $this->resolveProjectId($request);
        $this->currentProjectId = $projectId;

        $data = $request->validate($this->rules());
        $data = $this->prepareData($request, $data);
        $data['project_id'] = $projectId;

        $this->model::create($data);

        return redirect()
            ->route("{$this->route}.index", ['project_id' => $projectId])
            ->with('success', "{$this->title} criado(a).");
    }

    public function edit(int $id)
    {
        $item      = $this->model::findOrFail($id);
        $projects  = Project::orderBy('name')->get();
        $projectId = $item->project_id;

        return view('admin.crud.form', [
            'item'      => $item,
            'route'     => $this->route,
            'title'     => $this->title,
            'fields'    => $this->fields,
            'mode'      => 'edit',
            'options'   => $this->options(),
            'projects'  => $projects,
            'projectId' => $projectId,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $item = $this->model::findOrFail($id);
        $this->currentProjectId = $item->project_id;

        $data = $request->validate($this->rules($id));
        $data = $this->prepareData($request, $data);
        $item->update($data);

        return redirect()
            ->route("{$this->route}.index", ['project_id' => $item->project_id])
            ->with('success', "{$this->title} atualizado(a).");
    }

    public function destroy(int $id)
    {
        $item = $this->model::findOrFail($id);
        $projectId = $item->project_id;
        $item->delete();

        return redirect()
            ->route("{$this->route}.index", ['project_id' => $projectId])
            ->with('success', "{$this->title} removido(a).");
    }
}
