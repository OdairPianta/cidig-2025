<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->with('items')->firstOrFail();

        return view('v2.pages.custom', [
            'page' => $page,
            'menuItems' => Menu::active()->ordered()->get(),
        ]);
    }
}
