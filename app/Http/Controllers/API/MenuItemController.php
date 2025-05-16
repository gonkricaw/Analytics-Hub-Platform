<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view menu items'
                ], 403);
            }

            $menuItems = MenuItem::orderBy('order')->get();

            return response()->json([
                'menuItems' => $menuItems
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve menu items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create menu items'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'url' => 'nullable|string|max:255',
                'route_name' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:50',
                'order' => 'nullable|integer',
                'parent_id' => 'nullable|exists:menu_items,id',
                'permissions' => 'nullable|array',
                'is_active' => 'boolean',
                'is_external' => 'boolean',
                'target' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate slug from title
            $slug = Str::slug($request->title);

            // Check if slug already exists
            $slugCount = MenuItem::where('slug', 'like', $slug . '%')->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            // Determine the order if not provided
            $order = $request->order;
            if (!$order) {
                // Get the highest order of siblings
                $query = MenuItem::query();
                if ($request->has('parent_id')) {
                    $query->where('parent_id', $request->parent_id);
                } else {
                    $query->whereNull('parent_id');
                }
                $maxOrder = $query->max('order') ?? 0;
                $order = $maxOrder + 1;
            }

            // Create menu item
            $menuItem = MenuItem::create([
                'title' => $request->title,
                'slug' => $slug,
                'url' => $request->url,
                'route_name' => $request->route_name,
                'icon' => $request->icon,
                'order' => $order,
                'parent_id' => $request->parent_id,
                'permissions' => $request->permissions ?? [],
                'is_active' => $request->is_active ?? true,
                'is_external' => $request->is_external ?? false,
                'target' => $request->target
            ]);

            return response()->json([
                'message' => 'Menu item created successfully',
                'menuItem' => $menuItem
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create menu item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view menu items'
                ], 403);
            }

            $menuItem = MenuItem::with(['parent', 'children'])->findOrFail($id);

            return response()->json([
                'menuItem' => $menuItem
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve menu item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update menu items'
                ], 403);
            }

            $menuItem = MenuItem::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'url' => 'nullable|string|max:255',
                'route_name' => 'nullable|string|max:255',
                'icon' => 'nullable|string|max:50',
                'order' => 'nullable|integer',
                'parent_id' => 'nullable|exists:menu_items,id',
                'permissions' => 'nullable|array',
                'is_active' => 'boolean',
                'is_external' => 'boolean',
                'target' => 'nullable|string|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prevent setting parent to itself or its children
            if ($request->has('parent_id') && $request->parent_id == $id) {
                return response()->json([
                    'message' => 'A menu item cannot be its own parent'
                ], 400);
            }

            if ($request->has('parent_id') && $this->isChildOf($id, $request->parent_id)) {
                return response()->json([
                    'message' => 'A menu item cannot have one of its children as parent'
                ], 400);
            }

            // Update slug if title is changed
            if ($request->has('title') && $request->title !== $menuItem->title) {
                $slug = Str::slug($request->title);

                $slugExists = MenuItem::where('slug', $slug)
                                   ->where('id', '!=', $id)
                                   ->exists();

                if ($slugExists) {
                    $slugCount = MenuItem::where('slug', 'like', $slug . '%')->count();
                    $slug = $slug . '-' . ($slugCount + 1);
                }

                $request->merge(['slug' => $slug]);
            }

            // Update menu item
            $menuItem->update($request->only([
                'title', 'slug', 'url', 'route_name', 'icon', 'order', 'parent_id',
                'permissions', 'is_active', 'is_external', 'target'
            ]));

            return response()->json([
                'message' => 'Menu item updated successfully',
                'menuItem' => $menuItem->fresh()->load(['parent', 'children'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update menu item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete menu items'
                ], 403);
            }

            $menuItem = MenuItem::findOrFail($id);

            // Check if item has children
            if ($menuItem->children()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete menu item with children. Please remove or reassign children first.'
                ], 400);
            }

            $menuItem->delete();

            return response()->json([
                'message' => 'Menu item deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete menu item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the complete menu structure.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenuStructure()
    {
        try {
            $user = auth()->user();

            // Get root menu items with all their children
            $menuStructure = MenuItem::with('allChildren')
                                   ->active()
                                   ->root()
                                   ->orderBy('order')
                                   ->get();

            // Filter items based on user's permissions
            $filteredStructure = $this->filterMenuItemsByPermission($menuStructure, $user);

            return response()->json([
                'menu' => $filteredStructure
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve menu structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderItems(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('menu-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update menu items'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.id' => 'required|exists:menu_items,id',
                'items.*.order' => 'required|integer',
                'items.*.parent_id' => 'nullable|exists:menu_items,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the order of menu items
            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['id']);
                $menuItem->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null
                ]);
            }

            return response()->json([
                'message' => 'Menu items reordered successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reorder menu items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a menu item is a child of another menu item.
     *
     * @param  int  $parentId
     * @param  int  $childId
     * @return bool
     */
    private function isChildOf($parentId, $childId)
    {
        $child = MenuItem::find($childId);

        if (!$child) {
            return false;
        }

        $allChildren = MenuItem::where('parent_id', $parentId)->get();

        foreach ($allChildren as $childItem) {
            if ($childItem->id == $childId) {
                return true;
            }

            if ($this->isChildOf($childItem->id, $childId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter menu items recursively based on user permissions.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $items
     * @param  \App\Models\User  $user
     * @return array
     */
    private function filterMenuItemsByPermission($items, $user)
    {
        $result = [];

        foreach ($items as $item) {
            if ($item->canBeSeenBy($user)) {
                $filteredItem = $item->toArray();

                if (isset($filteredItem['all_children']) && !empty($filteredItem['all_children'])) {
                    $filteredItem['all_children'] = $this->filterMenuItemsByPermission($item->allChildren, $user);
                }

                $result[] = $filteredItem;
            }
        }

        return $result;
    }
}
