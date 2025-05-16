<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
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
            if (!auth()->user()->hasPermission('user-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view users'
                ], 403);
            }

            $users = User::with('roles')->get();

            return response()->json([
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('user-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create users'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'department' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'department' => $request->department,
                'position' => $request->position,
                'phone' => $request->phone,
                'force_password_change' => true, // Force new users to change password on first login
                'is_active' => $request->has('is_active') ? $request->is_active : true,
            ]);

            // Assign roles if provided
            if ($request->has('roles') && is_array($request->roles)) {
                $user->roles()->sync($request->roles);
            } else {
                // Assign default role (user)
                $defaultRole = Role::where('name', 'user')->first();
                if ($defaultRole) {
                    $user->roles()->attach($defaultRole->id);
                }
            }

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user->load('roles')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('user-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view users'
                ], 403);
            }

            $user = User::with('roles')->findOrFail($id);

            return response()->json([
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('user-edit')) {
                return response()->json([
                    'message' => 'You do not have permission to edit users'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'department' => 'nullable|string|max:255',
                'position' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
                'is_active' => 'nullable|boolean',
                'force_password_change' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($id);

            // Update user fields
            $user->fill($request->only([
                'name', 'email', 'department', 'position', 'phone', 'is_active', 'force_password_change'
            ]));

            $user->save();

            // Update roles if provided
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user->fresh()->load('roles')
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('user-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete users'
                ], 403);
            }

            $user = User::findOrFail($id);

            // Don't allow deletion of self
            if ($user->id === auth()->id()) {
                return response()->json([
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile (for authenticated user)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'avatar' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();

            // Update only allowed fields
            $user->fill($request->only([
                'name', 'phone', 'avatar'
            ]));

            $user->save();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
