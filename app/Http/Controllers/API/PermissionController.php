<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
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
            if (!auth()->user()->hasPermission('permission-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view permissions'
                ], 403);
            }

            $permissions = Permission::all();

            return response()->json([
                'permissions' => $permissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve permissions',
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
            if (!auth()->user()->hasPermission('permission-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view permissions'
                ], 403);
            }

            $permission = Permission::findOrFail($id);

            return response()->json([
                'permission' => $permission
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get permissions grouped by module.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByModule()
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('permission-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view permissions'
                ], 403);
            }

            $permissions = Permission::all();
            $groupedPermissions = $permissions->groupBy('module');

            return response()->json([
                'permissions' => $groupedPermissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve permissions by module',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
