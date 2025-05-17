<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemConfigurationController extends Controller
{
    /**
     * Display a listing of the system configurations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view system configurations'
                ], 403);
            }

            $query = \App\Models\SystemConfiguration::query();

            // Filter by group if specified
            if ($request->has('group') && $request->group) {
                $query->where('group', $request->group);
            }

            // Filter by key if specified
            if ($request->has('key') && $request->key) {
                $query->where('key', 'like', '%' . $request->key . '%');
            }

            $configs = $query->orderBy('group')->orderBy('key')->get();

            return response()->json([
                'configurations' => $configs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get configuration groups for filtering
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroups()
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view system configurations'
                ], 403);
            }

            $groups = \App\Models\SystemConfiguration::select('group')
                ->distinct()
                ->orderBy('group')
                ->get()
                ->pluck('group');

            return response()->json([
                'groups' => $groups
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve configuration groups',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created system configuration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create system configurations'
                ], 403);
            }

            // Validate request
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'key' => 'required|string|max:255|unique:system_configurations',
                'value' => 'required|string',
                'type' => 'required|string|in:string,integer,boolean,json,array',
                'group' => 'required|string|max:255',
                'is_public' => 'boolean',
                'display_name' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create configuration
            $config = \App\Models\SystemConfiguration::create($request->all());

            return response()->json([
                'message' => 'System configuration created successfully',
                'configuration' => $config
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified system configuration.
     *
     * @param  string  $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $key)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view system configurations'
                ], 403);
            }

            $config = \App\Models\SystemConfiguration::where('key', $key)->first();

            if (!$config) {
                return response()->json([
                    'message' => 'System configuration not found'
                ], 404);
            }

            return response()->json([
                'configuration' => $config
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified system configuration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $key)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update system configurations'
                ], 403);
            }

            $config = \App\Models\SystemConfiguration::where('key', $key)->first();

            if (!$config) {
                return response()->json([
                    'message' => 'System configuration not found'
                ], 404);
            }

            // Validate request
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'value' => 'required|string',
                'type' => 'sometimes|string|in:string,integer,boolean,json,array',
                'group' => 'sometimes|string|max:255',
                'is_public' => 'boolean',
                'display_name' => 'nullable|string|max:255',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update configuration (excluding 'key' as it should not change)
            $config->update($request->except('key'));

            return response()->json([
                'message' => 'System configuration updated successfully',
                'configuration' => $config->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update multiple configurations at once
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpdate(Request $request)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update system configurations'
                ], 403);
            }

            // Validate request
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'configurations' => 'required|array',
                'configurations.*.key' => 'required|string|exists:system_configurations,key',
                'configurations.*.value' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update each configuration
            foreach ($request->configurations as $configData) {
                $config = \App\Models\SystemConfiguration::where('key', $configData['key'])->first();
                if ($config) {
                    $config->update(['value' => $configData['value']]);
                }
            }

            return response()->json([
                'message' => 'System configurations updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update system configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified system configuration.
     *
     * @param  string  $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $key)
    {
        try {
            // Check for permission
            if (!auth()->user()->hasPermission('system-config-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete system configurations'
                ], 403);
            }

            $config = \App\Models\SystemConfiguration::where('key', $key)->first();

            if (!$config) {
                return response()->json([
                    'message' => 'System configuration not found'
                ], 404);
            }

            $config->delete();

            return response()->json([
                'message' => 'System configuration deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete system configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
