<?php

namespace App\Http\Controllers;

use App\Models\MapProject;
use App\Models\Menu;

class MapController extends Controller
{
    public function index()
    {
        $projects = MapProject::onMap()->orderBy('name')->get()->map->toMapArray()->values();

        return view('v2.map.index', [
            'projects' => $projects,
            'menuItems' => Menu::active()->ordered()->get(),
        ]);
    }
}
