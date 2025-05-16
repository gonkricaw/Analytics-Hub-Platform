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
Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\API'], function () {
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');
    Route::post('/forgot-password', 'AuthController@forgotPassword');
    Route::post('/reset-password', 'AuthController@resetPassword');

    // Protected auth routes
    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('/logout', 'AuthController@logout');
        Route::post('/change-password', 'AuthController@changePassword');
        Route::post('/accept-terms', 'AuthController@acceptTerms');
    });
});

// Protected API routes
Route::group(['middleware' => ['auth:sanctum'], 'namespace' => 'App\Http\Controllers\API'], function () {
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
    Route::put('/user/profile', 'UserController@updateProfile');

    // User Management
    Route::apiResource('users', 'UserController');

    // Role Management
    Route::apiResource('roles', 'RoleController');
    Route::post('/roles/{role}/permissions', 'RoleController@syncPermissions');

    // Permissions
    Route::apiResource('permissions', 'PermissionController')->only(['index', 'show']);
    Route::get('/permissions/by-module', 'PermissionController@getByModule');

    // Terms and Conditions
    Route::apiResource('terms', 'TermAndConditionController');
    Route::get('/terms/active', 'TermAndConditionController@getActive');
    Route::get('/terms/user-acceptance', 'TermAndConditionController@getUserAcceptance');

    // Notifications
    Route::apiResource('notifications', 'NotificationController');
    Route::post('/notifications/{notification}/mark-read', 'NotificationController@markAsRead');
    Route::post('/notifications/mark-all-read', 'NotificationController@markAllAsRead');
    Route::get('/notifications/unread-count', 'NotificationController@getUnreadCount');

    // Email Templates
    Route::apiResource('email-templates', 'EmailTemplateController');

    // Content Management
    Route::apiResource('content', 'ContentManagementController');
    Route::get('/content/by-slug/{slug}', 'ContentManagementController@getBySlug');
    Route::get('/content/by-type/{type}', 'ContentManagementController@getByType');

    // Menu Management
    Route::apiResource('menu-items', 'MenuItemController');
    Route::get('/menu/structure', 'MenuItemController@getMenuStructure');
    Route::post('/menu-items/reorder', 'MenuItemController@reorderItems');
});
