<?php

namespace App\Http\Controllers\Admin;

use App\Services\ProjectContext;
use Illuminate\Http\Request;

abstract class ProjectScopedCrudController extends CrudController
{
    protected ?int $currentProjectId = null;

    public function index(Request $request)
    {
        $projectId = ProjectContext::currentId();
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
        ]);
    }

    public function create(?Request $request = null)
    {
        return view('admin.crud.form', [
            'item'    => new $this->model,
            'route'   => $this->route,
            'title'   => $this->title,
            'fields'  => $this->fields,
            'mode'    => 'create',
            'options' => $this->options(),
        ]);
    }

    public function store(Request $request)
    {
        $projectId = ProjectContext::currentId();
        $this->currentProjectId = $projectId;

        $data = $request->validate($this->rules());
        $data = $this->prepareData($request, $data);
        $data['project_id'] = $projectId;

        $this->model::create($data);

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} criado(a).");
    }

    public function edit($id)
    {
        $item      = $this->model::findOrFail($id);
        $projectId = ProjectContext::currentId();

        if ($item->project_id !== $projectId) {
            abort(404);
        }

        return view('admin.crud.form', [
            'item'    => $item,
            'route'   => $this->route,
            'title'   => $this->title,
            'fields'  => $this->fields,
            'mode'    => 'edit',
            'options' => $this->options(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $item      = $this->model::findOrFail($id);
        $projectId = ProjectContext::currentId();

        if ($item->project_id !== $projectId) {
            abort(404);
        }

        $this->currentProjectId = $item->project_id;

        $data = $request->validate($this->rules($id));
        $data = $this->prepareData($request, $data);
        $item->update($data);

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} atualizado(a).");
    }

    public function destroy($id)
    {
        $item      = $this->model::findOrFail($id);
        $projectId = ProjectContext::currentId();

        if ($item->project_id !== $projectId) {
            abort(404);
        }

        $item->delete();

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} removido(a).");
    }
}
