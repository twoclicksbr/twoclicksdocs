<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Paginação padrão: 100 itens por página, máximo 500.
     */
    protected function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 100);
        return min(max($perPage, 1), 500);
    }

    /**
     * Aplica ordenação ?order=field,direction (ex: ?order=created_at,desc).
     */
    protected function applyOrder(Builder $query, Request $request, string $default = 'order,asc'): Builder
    {
        $order = $request->query('order', $default);
        $parts = array_map('trim', explode(',', $order));

        $field = $parts[0] ?? 'id';
        $direction = strtolower($parts[1] ?? 'asc');

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return $query->orderBy($field, $direction);
    }

    /**
     * Aplica filtro ?search=texto em colunas específicas (definidas em cada controller).
     */
    protected function applySearch(Builder $query, Request $request, array $columns): Builder
    {
        $search = $request->query('search');

        if (empty($search) || empty($columns)) {
            return $query;
        }

        return $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $col) {
                $q->orWhere($col, 'ilike', "%{$search}%");
            }
        });
    }

    /**
     * Retorna o project_id injetado pelo middleware EnsureProjectToken.
     */
    protected function projectId(Request $request): int
    {
        return (int) $request->attributes->get('project_id');
    }

    /**
     * Filtra por colunas exatas via query string (?status=true&order=5).
     * Whitelist obrigatória.
     */
    protected function applyFilters(Builder $query, Request $request, array $allowed): Builder
    {
        foreach ($allowed as $field) {
            if ($request->has($field)) {
                $value = $request->query($field);

                // Tratamento básico: bool/null/integer/string
                if ($value === 'true')  $value = true;
                elseif ($value === 'false') $value = false;
                elseif ($value === 'null')  $value = null;

                if (is_null($value)) {
                    $query->whereNull($field);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }
}
