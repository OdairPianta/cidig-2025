<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Http\Resources\MenuResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::query();

        // Filtro para itens ativos
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $menus = $query->ordered()->get();

        return response()->json([
            'data' => MenuResource::collection($menus),
            'meta' => [
                'total' => $menus->count(),
                'active_count' => $menus->where('is_active', true)->count(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'link' => 'nullable|string|max:500',
            'slug' => 'required|string|max:255|unique:menu,slug',
            'is_active' => 'boolean',
        ]);

        $menu = Menu::create($validated);

        return response()->json(new MenuResource($menu), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Menu $menu): JsonResponse
    {
        return response()->json(new MenuResource($menu));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'link' => 'nullable|string|max:500',
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('menu')->ignore($menu->id)],
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return response()->json(new MenuResource($menu));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }

    /**
     * Get active menu items for navigation
     */
    public function navigation(): JsonResponse
    {
        $menus = Menu::active()->ordered()->get();

        return response()->json([
            'data' => MenuResource::collection($menus)
        ]);
    }
}