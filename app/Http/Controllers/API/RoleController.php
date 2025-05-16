<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
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
            if (!auth()->user()->hasPermission('role-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view roles'
                ], 403);
            }

            $roles = Role::with('permissions')->get();

            return response()->json([
                'roles' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve roles',
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
            if (!auth()->user()->hasPermission('role-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create roles'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create role
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description
            ]);

            // Assign permissions if provided
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'message' => 'Role created successfully',
                'role' => $role->load('permissions')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create role',
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
            if (!auth()->user()->hasPermission('role-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view roles'
                ], 403);
            }

            $role = Role::with('permissions')->findOrFail($id);

            return response()->json([
                'role' => $role
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve role',
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
            if (!auth()->user()->hasPermission('role-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update roles'
                ], 403);
            }

            // Find role
            $role = Role::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
                'display_name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update role
            $role->update($request->only(['name', 'display_name', 'description']));

            // Sync permissions if provided
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'message' => 'Role updated successfully',
                'role' => $role->fresh()->load('permissions')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role',
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
            if (!auth()->user()->hasPermission('role-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete roles'
                ], 403);
            }

            $role = Role::findOrFail($id);

            // Prevent deletion of essential roles
            if (in_array($role->name, ['admin', 'superadmin'])) {
                return response()->json([
                    'message' => 'Cannot delete system roles'
                ], 400);
            }

            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete role',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync permissions for a role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncPermissions(Request $request, $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('role-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update role permissions'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::findOrFail($id);

            // Sync permissions
            $role->syncPermissions($request->permissions);

            return response()->json([
                'message' => 'Role permissions updated successfully',
                'role' => $role->fresh()->load('permissions')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update role permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
