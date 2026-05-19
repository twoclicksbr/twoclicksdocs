<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Session;

class ProjectContext
{
    public static function current(): ?Project
    {
        $id = Session::get('current_project_id');
        if (!$id) return null;
        return Project::find((int) $id);
    }

    public static function currentId(): ?int
    {
        $id = Session::get('current_project_id');
        return $id ? (int) $id : null;
    }
}
