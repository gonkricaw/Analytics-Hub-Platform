<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemConfigurationController extends Controller
{
    /**
     * Get all publicly accessible system configurations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicConfigs()
    {
        try {
            // Try to get from cache first
            $cacheKey = 'public_system_configs';
            if (Cache::has($cacheKey)) {
                return response()->json([
                    'configurations' => Cache::get($cacheKey)
                ], 200);
            }

            // Get all public configs
            $configs = SystemConfiguration::where('is_public', true)->get();

            // Format response
            $formattedConfigs = [];
            foreach ($configs as $config) {
                $formattedConfigs[$config->key] = $config->typed_value;
            }

            // Cache for 30 minutes
            Cache::put($cacheKey, $formattedConfigs, 1800);

            return response()->json([
                'configurations' => $formattedConfigs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve public configurations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
