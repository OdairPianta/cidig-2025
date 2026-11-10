<?php

namespace App\GraphQL\Queries;

use App\Models\MapProject;
use Illuminate\Database\Eloquent\Builder;

class IntegrationContent
{
    public function mapProjects(): Builder
    {
        // Reuse the public map's approval rules and sanitized projection.
        return MapProject::onMap()->orderBy('id')->afterQuery(
            fn ($projects) => $projects->map(fn (MapProject $project) => array_merge(
                $project->toMapArray(),
                ['created_at' => $project->created_at, 'updated_at' => $project->updated_at],
            )),
        );
    }
}
