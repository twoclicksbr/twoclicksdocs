<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Base CRUD controller.
 * Filhos definem: $model, $route, $title, $titlePlural, $fields, $searchable, rules()
 */
abstract class CrudController extends Controller
{
    protected string $model;
    protected string $route;
    protected string $title;
    protected string $titlePlural;
    protected array $fields = [];
    protected array $searchable = [];
    protected string $orderBy = 'id';
    protected string $orderDir = 'asc';

    abstract protected function rules(?int $id = null): array;

    public function index(Request $request)
    {
        $query = $this->model::query();

        if ($search = $request->query('search')) {
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

    public function create()
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
        $data = $request->validate($this->rules());
        $this->model::create($data);

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} criado(a).");
    }

    public function edit($id)
    {
        $item = $this->model::findOrFail($id);
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
        $item = $this->model::findOrFail($id);
        $data = $request->validate($this->rules($id));
        $item->update($data);

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} atualizado(a).");
    }

    public function destroy($id)
    {
        $item = $this->model::findOrFail($id);
        $item->delete();

        return redirect()
            ->route("{$this->route}.index")
            ->with('success', "{$this->title} removido(a).");
    }

    protected function options(): array
    {
        return [];
    }
}
