<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentManagement;

class ContentController extends Controller
{
    /**
     * Display the specified content.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($slug)
    {
        $content = ContentManagement::where('slug', $slug)
            ->published()
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $content
        ]);
    }

    /**
     * Get content by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        $content = ContentManagement::published()
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $content
        ]);
    }

    /**
     * List content of a specific type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType(Request $request, $type)
    {
        $content = ContentManagement::published()
            ->ofType($type)
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $content
        ]);
    }
}
