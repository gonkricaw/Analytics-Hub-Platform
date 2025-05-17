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
     * Track menu item click for analytics
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackMenuClick(Request $request)
    {
        try {
            $request->validate([
                'menu_id' => 'required|exists:menu_items,id'
            ]);

            $user = Auth::user();
            $menuId = $request->menu_id;

            // Log the activity
            \App\Models\UserActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'menu_access',
                'activity_details' => 'Accessed menu item',
                'related_model_type' => \App\Models\MenuItem::class,
                'related_model_id' => $menuId,
                'ip_address' => $request->ip()
            ]);

            // Track menu analytics
            \App\Models\MenuAnalytics::incrementAccessCount($menuId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Menu click tracked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track menu click',
                'error' => $e->getMessage()
            ], 500);
        }
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
