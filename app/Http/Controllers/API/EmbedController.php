<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmbeddedUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EmbedController extends Controller
{
    /**
     * Get embedded URL details by UUID.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($uuid)
    {
        $embeddedUrl = EmbeddedUrl::where('uuid', $uuid)
            ->active()
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'target_url' => $embeddedUrl->target_url,
                'description' => $embeddedUrl->description
            ]
        ]);
    }

    /**
     * Store a newly created embedded URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'target_url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $embeddedUrl = EmbeddedUrl::create([
            'target_url' => $request->target_url,
            'description' => $request->description,
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Embedded URL created successfully.',
            'data' => [
                'uuid' => $embeddedUrl->uuid,
                'target_url' => $embeddedUrl->target_url,
                'description' => $embeddedUrl->description,
                'created_at' => $embeddedUrl->created_at
            ]
        ], 201);
    }

    /**
     * Update the specified embedded URL.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $embeddedUrl = EmbeddedUrl::findOrFail($id);

        $request->validate([
            'target_url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $embeddedUrl->update($request->only(['target_url', 'description', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Embedded URL updated successfully.',
            'data' => $embeddedUrl
        ]);
    }

    /**
     * Remove the specified embedded URL.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $embeddedUrl = EmbeddedUrl::findOrFail($id);
        $embeddedUrl->delete();

        return response()->json([
            'success' => true,
            'message' => 'Embedded URL deleted successfully.'
        ]);
    }

    /**
     * List all embedded URLs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = EmbeddedUrl::with('creator:id,name');

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $embeddedUrls = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $embeddedUrls
        ]);
    }
}
