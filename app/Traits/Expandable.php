<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait Expandable
{
    /**
     * Aplica `with()` no query builder com base no ?expand= da request.
     * Apenas relations whitelisted (constante EXPANDABLE do model) são aceitas.
     */
    public function scopeExpand($query, Request $request)
    {
        $expand = $request->query('expand');

        if (empty($expand)) {
            return $query;
        }

        $allowed = defined(static::class . '::EXPANDABLE')
            ? static::EXPANDABLE
            : [];

        if (empty($allowed)) {
            return $query;
        }

        $requested = array_map('trim', explode(',', $expand));

        // Permite nested via ponto (ex: task.project) desde que a raiz esteja na whitelist
        $valid = array_filter($requested, function ($rel) use ($allowed) {
            $root = explode('.', $rel)[0];
            return in_array($root, $allowed, true);
        });

        if (!empty($valid)) {
            $query->with($valid);
        }

        return $query;
    }
}
