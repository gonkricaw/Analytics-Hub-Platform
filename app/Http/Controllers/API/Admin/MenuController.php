<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    /**
     * Display a listing of the menu items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $menuItems = MenuItem::with('parent')
            ->orderBy('parent_id', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menuItems
        ]);
    }

    /**
     * Store a newly created menu item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'parent_id' => 'nullable|exists:menu_items,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'is_active' => 'boolean',
            'is_external' => 'boolean',
            'target' => 'nullable|string|in:_self,_blank,_parent,_top',
        ]);

        // Generate slug from title
        $slug = Str::slug($request->title);

        // Check if slug already exists, if so, append a number
        $count = 1;
        $originalSlug = $slug;
        while (MenuItem::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $menuItem = MenuItem::create([
            'title' => $request->title,
            'slug' => $slug,
            'url' => $request->url,
            'route_name' => $request->route_name,
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'parent_id' => $request->parent_id,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->is_active ?? true,
            'is_external' => $request->is_external ?? false,
            'target' => $request->target ?? '_self',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully.',
            'data' => $menuItem
        ], 201);
    }

    /**
     * Display the specified menu item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $menuItem = MenuItem::with(['parent', 'children'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $menuItem
        ]);
    }

    /**
     * Update the specified menu item.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $menuItem = MenuItem::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'route_name' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'order' => 'integer|min:0',
            'parent_id' => 'nullable|exists:menu_items,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
            'is_active' => 'boolean',
            'is_external' => 'boolean',
            'target' => 'nullable|string|in:_self,_blank,_parent,_top',
        ]);

        // Prevent setting parent as itself or any of its children
        if ($request->parent_id && $request->parent_id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Menu item cannot be its own parent.'
            ], 422);
        }

        // Check if the new parent is one of the children of this item
        if ($request->parent_id) {
            $childIds = $this->getAllChildIds($menuItem);
            if (in_array($request->parent_id, $childIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu item cannot have a child as its parent.'
                ], 422);
            }
        }

        $menuItem->update([
            'title' => $request->title,
            'url' => $request->url,
            'route_name' => $request->route_name,
            'icon' => $request->icon,
            'order' => $request->order ?? $menuItem->order,
            'parent_id' => $request->parent_id,
            'permissions' => $request->permissions ?? $menuItem->permissions,
            'is_active' => $request->boolean('is_active'),
            'is_external' => $request->boolean('is_external'),
            'target' => $request->target ?? $menuItem->target,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully.',
            'data' => $menuItem
        ]);
    }

    /**
     * Remove the specified menu item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);

        // Check if it has children
        if ($menuItem->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a menu item with children. Please delete or reassign children first.'
            ], 422);
        }

        $menuItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.'
        ]);
    }

    /**
     * Update the order of multiple menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        foreach ($request->items as $item) {
            MenuItem::where('id', $item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu order updated successfully.'
        ]);
    }

    /**
     * Get all child IDs of a menu item recursively.
     *
     * @param  \App\Models\MenuItem  $menuItem
     * @return array
     */
    private function getAllChildIds(MenuItem $menuItem)
    {
        $ids = [];

        foreach ($menuItem->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildIds($child));
        }

        return $ids;
    }
}
