<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Get all permissions
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPermissions()
    {
        try {
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
     * Get permissions grouped by module
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionsByModule()
    {
        try {
            if (!auth()->user()->hasPermission('permission-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view permissions'
                ], 403);
            }

            $permissions = Permission::all();
            $groupedPermissions = [];

            foreach ($permissions as $permission) {
                $module = explode('-', $permission->name)[0];

                if (!isset($groupedPermissions[$module])) {
                    $groupedPermissions[$module] = [];
                }

                $groupedPermissions[$module][] = $permission;
            }

            return response()->json([
                'permissions' => $groupedPermissions
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to a role
     *
     * @param Request $request
     * @param int $roleId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPermissionsToRole(Request $request, $roleId)
    {
        try {
            if (!auth()->user()->hasPermission('role-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update roles'
                ], 403);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($roleId);

            // Don't allow editing system roles unless the user is admin
            if (in_array($role->name, ['admin', 'superadmin']) && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'You do not have permission to update system roles'
                ], 403);
            }

            // Sync permissions
            $role->permissions()->sync($request->permissions);

            return response()->json([
                'message' => 'Permissions updated successfully',
                'role' => $role->load('permissions')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
