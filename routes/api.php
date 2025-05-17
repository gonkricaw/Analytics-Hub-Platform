<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [\App\Http\Controllers\API\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\API\AuthController::class, 'register']);
    Route::post('/forgot-password', [\App\Http\Controllers\API\AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [\App\Http\Controllers\API\AuthController::class, 'resetPassword']);

    // Protected auth routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [\App\Http\Controllers\API\AuthController::class, 'logout']);
        Route::get('/me', [\App\Http\Controllers\API\AuthController::class, 'me']);
        Route::post('/refresh', [\App\Http\Controllers\API\AuthController::class, 'refresh']);
        Route::post('/change-password', [\App\Http\Controllers\API\AuthController::class, 'changePassword']);
        Route::post('/change-password-first-time', [\App\Http\Controllers\API\AuthController::class, 'changePasswordFirstTime']);
        Route::post('/accept-terms', [\App\Http\Controllers\API\AuthController::class, 'acceptTerms']);
    });
});

// Protected API routes
Route::middleware(['auth:sanctum'])->group(function () {
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
    Route::put('/user/profile', [\App\Http\Controllers\API\UserController::class, 'updateProfile']);
    Route::post('/user/avatar', [\App\Http\Controllers\API\ProfileController::class, 'updateAvatar']);
    Route::get('/user/avatar', [\App\Http\Controllers\API\ProfileController::class, 'getAvatar']);

    // User Management
    Route::apiResource('users', \App\Http\Controllers\API\UserController::class);
    Route::post('/users/invite', [\App\Http\Controllers\API\UserController::class, 'invite']);

    // Role Management
    Route::apiResource('roles', \App\Http\Controllers\API\RoleController::class);
    Route::post('/roles/{role}/permissions', [\App\Http\Controllers\API\RoleController::class, 'syncPermissions']);

    // Permissions
    Route::apiResource('permissions', \App\Http\Controllers\API\PermissionController::class)->only(['index', 'show']);
    Route::get('/permissions/by-module', [\App\Http\Controllers\API\PermissionController::class, 'getByModule']);

    // Terms and Conditions
    Route::apiResource('terms', \App\Http\Controllers\API\TermAndConditionController::class);
    Route::get('/terms/active', [\App\Http\Controllers\API\TermAndConditionController::class, 'getActive']);
    Route::get('/terms/user-acceptance', [\App\Http\Controllers\API\TermAndConditionController::class, 'getUserAcceptance']);

    // Notifications
    Route::apiResource('notifications', \App\Http\Controllers\API\NotificationController::class);
    Route::post('/notifications/{notification}/mark-read', [\App\Http\Controllers\API\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\API\NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\API\NotificationController::class, 'getUnreadCount']);

    // Email Templates
    Route::apiResource('email-templates', \App\Http\Controllers\API\EmailTemplateController::class);

    // Content Management
    Route::apiResource('content', \App\Http\Controllers\API\ContentManagementController::class);
    Route::get('/content/by-slug/{slug}', [\App\Http\Controllers\API\ContentManagementController::class, 'getBySlug']);
    Route::get('/content/by-type/{type}', [\App\Http\Controllers\API\ContentManagementController::class, 'getByType']);

    // Menu Management
    Route::apiResource('menu-items', \App\Http\Controllers\API\MenuItemController::class);
    Route::get('/menu/structure', [\App\Http\Controllers\API\MenuItemController::class, 'getMenuStructure']);
    Route::post('/menu-items/reorder', [\App\Http\Controllers\API\MenuItemController::class, 'reorderItems']);

    // Role & Permission Management
    Route::prefix('roles-permissions')->group(function () {
        Route::get('/permissions', [\App\Http\Controllers\API\RolePermissionController::class, 'getAllPermissions']);
        Route::get('/permissions/by-module', [\App\Http\Controllers\API\RolePermissionController::class, 'getPermissionsByModule']);
        Route::post('/roles/{roleId}/permissions', [\App\Http\Controllers\API\RolePermissionController::class, 'assignPermissionsToRole']);
    });

    // Audit Logs (Admin only)
    Route::middleware(['permission:audit-log-view'])->prefix('admin')->group(function () {
        Route::get('/audit-logs', [\App\Http\Controllers\API\Admin\AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [\App\Http\Controllers\API\Admin\AuditLogController::class, 'show']);
        Route::get('/audit-logs-actions', [\App\Http\Controllers\API\Admin\AuditLogController::class, 'getActions']);
        Route::get('/audit-logs-model-types', [\App\Http\Controllers\API\Admin\AuditLogController::class, 'getModelTypes']);
    });

    // Security management (Admin only)
    Route::middleware(['permission:security-view'])->prefix('admin/security')->group(function () {
        Route::get('/blocked-ips', [\App\Http\Controllers\API\Admin\SecurityController::class, 'getBlockedIps']);
        Route::get('/login-history', [\App\Http\Controllers\API\Admin\SecurityController::class, 'getLoginHistory']);
        Route::post('/unblock-ip/{id}', [\App\Http\Controllers\API\Admin\SecurityController::class, 'unblockIp'])
             ->middleware('permission:security-manage');
    });

    // Menu Management
    Route::get('/menu', [\App\Http\Controllers\API\MenuController::class, 'index']);

    // Content Management
    Route::get('/content/{slug}', [\App\Http\Controllers\API\ContentController::class, 'show']);
    Route::get('/content/id/{id}', [\App\Http\Controllers\API\ContentController::class, 'getById']);
    Route::get('/content/type/{type}', [\App\Http\Controllers\API\ContentController::class, 'getByType']);

    // Embedded URLs
    Route::get('/embed/{uuid}', [\App\Http\Controllers\API\EmbedController::class, 'show']);

    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\API\DashboardController::class, 'getData']);

    // Public system configurations
    Route::get('/public-configs', [\App\Http\Controllers\API\SystemConfigurationController::class, 'getPublicConfigs']);

    // Admin routes
    Route::middleware(['permission:admin-access'])->prefix('admin')->group(function () {
        // Content Management (Admin)
        Route::apiResource('content', \App\Http\Controllers\API\Admin\ContentController::class);

        // Menu Management (Admin)
        Route::apiResource('menu', \App\Http\Controllers\API\Admin\MenuController::class);
        Route::post('/menu/order', [\App\Http\Controllers\API\Admin\MenuController::class, 'updateOrder']);

        // Embedded URLs Management (Admin)
        Route::apiResource('embed', \App\Http\Controllers\API\EmbedController::class)->except('show');

        // Notification Management (Admin)
        Route::apiResource('notifications', \App\Http\Controllers\API\Admin\NotificationController::class);
        Route::get('/notification-statistics', [\App\Http\Controllers\API\Admin\NotificationController::class, 'getStatistics']);

        // System Configuration Management (Admin)
        Route::apiResource('system-config', \App\Http\Controllers\API\Admin\SystemConfigurationController::class);
        Route::get('/system-config/groups', [\App\Http\Controllers\API\Admin\SystemConfigurationController::class, 'getGroups']);
        Route::post('/system-config/bulk-update', [\App\Http\Controllers\API\Admin\SystemConfigurationController::class, 'bulkUpdate']);
    });
});
