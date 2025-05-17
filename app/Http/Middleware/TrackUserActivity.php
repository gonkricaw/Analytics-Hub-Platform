<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track activity for authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // Update last activity timestamp
            $user->last_activity_at = now();
            $user->save();

            // Log specific activities
            $this->logSpecificActivities($request, $user);
        }

        return $response;
    }

    /**
     * Log specific user activities based on the request
     *
     * @param Request $request
     * @param User $user
     */
    protected function logSpecificActivities(Request $request, User $user)
    {
        $path = $request->path();
        $method = $request->method();

        // Don't log certain endpoints (e.g., frequent polling endpoints)
        $ignorePaths = [
            'api/notifications/unread-count',
            'api/dashboard', // Don't log dashboard views as they're frequent
        ];

        if (in_array($path, $ignorePaths)) {
            return;
        }

        // Log menu accesses
        if (preg_match('#^api/menu/(\d+)$#', $path, $matches) && $method === 'GET') {
            $menuId = $matches[1];
            UserActivityLog::logActivity(
                $user->id,
                'menu_access',
                "Accessed menu item #$menuId",
                $request->ip(),
                $request->userAgent(),
                \App\Models\MenuItem::find($menuId)
            );
            return;
        }

        // Log content views
        if (preg_match('#^api/content/([^/]+)$#', $path, $matches) && $method === 'GET') {
            $slug = $matches[1];
            $content = \App\Models\ContentManagement::where('slug', $slug)->first();
            if ($content) {
                UserActivityLog::logActivity(
                    $user->id,
                    'content_view',
                    "Viewed content: {$content->title}",
                    $request->ip(),
                    $request->userAgent(),
                    $content
                );
            }
            return;
        }
    }
}
