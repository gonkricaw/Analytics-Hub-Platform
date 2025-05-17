<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    /**
     * Get menu structure based on user's role and permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get top-level menu items
        $menuItems = MenuItem::with('allChildren')
            ->active()
            ->root()
            ->orderBy('order')
            ->get();

        // Filter menu items based on user permissions
        $menuItems = $menuItems->filter(function ($menuItem) use ($user) {
            return $menuItem->canBeSeenBy($user);
        });

        // Filter children recursively
        $menuItems = $this->filterChildrenRecursively($menuItems, $user);

        return response()->json([
            'success' => true,
            'data' => $menuItems
        ]);
    }

    /**
     * Recursively filter child menu items based on permissions.
     *
     * @param  \Illuminate\Support\Collection  $menuItems
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection
     */
    private function filterChildrenRecursively($menuItems, $user)
    {
        return $menuItems->map(function ($menuItem) use ($user) {
            // If this menu item has children
            if ($menuItem->children && $menuItem->children->count() > 0) {
                // Filter the children
                $children = $menuItem->children->filter(function ($child) use ($user) {
                    return $child->canBeSeenBy($user);
                });

                // Recursively filter grandchildren
                $menuItem->children = $this->filterChildrenRecursively($children, $user);
            }

            return $menuItem;
        });
    }
}
