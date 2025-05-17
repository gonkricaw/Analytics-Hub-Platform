<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get all dashboard data for the home page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData()
    {
        try {
            // Get system configurations
            $configs = \App\Models\SystemConfiguration::where('is_public', true)->get();
            $publicConfigs = [];
            foreach ($configs as $config) {
                $publicConfigs[$config->key] = $config->typed_value;
            }

            // Prepare response data
            $data = [
                'configs' => $publicConfigs,
                'widgets' => $this->getWidgetData()
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all widget data for the dashboard.
     *
     * @return array
     */
    protected function getWidgetData()
    {
        return [
            'online_users' => $this->getOnlineUsers(),
            'login_history' => $this->getLoginHistory(),
            'login_chart' => $this->getLoginChart(),
            'latest_notifications' => $this->getLatestNotifications(),
            'popular_menus' => $this->getPopularMenus()
        ];
    }

    /**
     * Get top 5 online users (active in last 15 minutes).
     *
     * @return array
     */
    protected function getOnlineUsers()
    {
        // Add cache here for performance
        $cacheKey = 'dashboard_online_users';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $onlineUsers = \App\Models\User::where('last_activity_at', '>=', now()->subMinutes(15))
            ->with('roles:id,name,display_name')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->profile_picture_url,
                    'role' => $user->roles->first() ? $user->roles->first()->display_name : null,
                    'last_active' => $user->last_activity_at
                ];
            });

        // Cache for 2 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $onlineUsers, 120);

        return $onlineUsers;
    }

    /**
     * Get top 5 frequently logged-in users.
     *
     * @return array
     */
    protected function getLoginHistory()
    {
        // Add cache here for performance
        $cacheKey = 'dashboard_login_history';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $topUsers = \App\Models\UserActivityLog::where('activity_type', 'login')
            ->where('created_at', '>=', now()->subDays(30))
            ->select('user_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as login_count'))
            ->groupBy('user_id')
            ->orderBy('login_count', 'desc')
            ->take(5)
            ->get();

        $result = \App\Models\User::whereIn('id', $topUsers->pluck('user_id'))
            ->with('roles:id,name,display_name')
            ->get()
            ->map(function ($user) use ($topUsers) {
                $loginData = $topUsers->where('user_id', $user->id)->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->profile_picture_url,
                    'role' => $user->roles->first() ? $user->roles->first()->display_name : null,
                    'login_count' => $loginData ? $loginData->login_count : 0
                ];
            })
            ->sortByDesc('login_count')
            ->values();

        // Cache for 30 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 1800);

        return $result;
    }

    /**
     * Get login data for chart (last 15 days).
     *
     * @return array
     */
    protected function getLoginChart()
    {
        // Add cache here for performance
        $cacheKey = 'dashboard_login_chart';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $startDate = now()->subDays(14)->startOfDay();
        $endDate = now()->endOfDay();

        $loginData = \App\Models\UserActivityLog::where('activity_type', 'login')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->select(
                \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [
            'labels' => [],
            'data' => []
        ];

        // Fill in all dates, including those with no logins
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $result['labels'][] = $date->format('M d');
            $result['data'][] = $loginData->has($dateStr) ? $loginData[$dateStr]->count : 0;
        }

        // Cache for 6 hours
        \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 360);

        return $result;
    }

    /**
     * Get top 5 latest notifications.
     *
     * @return array
     */
    protected function getLatestNotifications()
    {
        // Add cache here for performance
        $cacheKey = 'dashboard_latest_notifications';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $user = auth()->user();
        $notifications = \App\Models\SystemNotification::with(['userNotifications' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => \Illuminate\Support\Str::limit($notification->body, 100),
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                    'is_read' => $notification->userNotifications->isNotEmpty()
                        ? $notification->userNotifications->first()->is_read
                        : false
                ];
            });

        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $notifications, 300);

        return $notifications;
    }

    /**
     * Get top 5 popular menu items.
     *
     * @return array
     */
    protected function getPopularMenus()
    {
        // Add cache here for performance
        $cacheKey = 'dashboard_popular_menus';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        $popularMenus = \App\Models\UserActivityLog::where('activity_type', 'menu_access')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                'related_model_id as menu_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as access_count')
            )
            ->where('related_model_type', \App\Models\MenuItem::class)
            ->groupBy('related_model_id')
            ->orderBy('access_count', 'desc')
            ->take(5)
            ->get();

        $result = [];

        if ($popularMenus->isNotEmpty()) {
            $menuItems = \App\Models\MenuItem::whereIn('id', $popularMenus->pluck('menu_id'))->get();

            foreach ($popularMenus as $popularMenu) {
                $menu = $menuItems->where('id', $popularMenu->menu_id)->first();
                if ($menu) {
                    $result[] = [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'icon' => $menu->icon,
                        'access_count' => $popularMenu->access_count
                    ];
                }
            }
        }

        // If we don't have enough menu items from activity logs, get the most recent ones
        if (count($result) < 5) {
            $recentMenus = \App\Models\MenuItem::latest()->take(5 - count($result))->get();
            foreach ($recentMenus as $menu) {
                if (!collect($result)->pluck('id')->contains($menu->id)) {
                    $result[] = [
                        'id' => $menu->id,
                        'name' => $menu->name,
                        'icon' => $menu->icon,
                        'access_count' => 0
                    ];
                }

                if (count($result) >= 5) {
                    break;
                }
            }
        }

        // Cache for 30 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 1800);

        return $result;
    }
}
