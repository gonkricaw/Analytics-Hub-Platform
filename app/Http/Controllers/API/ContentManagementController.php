<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ContentManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContentManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Check authorization
            if (!auth()->user()->hasPermission('content-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view content'
                ], 403);
            }

            $query = ContentManagement::query();

            // Filter by type if provided
            if ($request->has('type')) {
                $query->ofType($request->type);
            }

            // Filter by published status if provided
            if ($request->has('is_published')) {
                $query->where('is_published', filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN));
            }

            // Order by published date (desc) by default
            $query->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $content = $query->paginate($perPage);

            return response()->json($content, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve content',
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
            if (!auth()->user()->hasPermission('content-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create content'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'type' => 'required|string|max:50',
                'is_published' => 'boolean',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'additional_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate slug from title
            $slug = Str::slug($request->title);

            // Check if slug already exists for this content type
            $slugCount = ContentManagement::where('slug', 'like', $slug . '%')
                                       ->where('type', $request->type)
                                       ->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            // Set published date to now if content is published
            $publishedAt = null;
            if ($request->is_published) {
                $publishedAt = $request->has('published_at') ? $request->published_at : now();
            }

            // Create content
            $content = ContentManagement::create([
                'title' => $request->title,
                'slug' => $slug,
                'content' => $request->content,
                'type' => $request->type,
                'is_published' => $request->is_published ?? false,
                'published_at' => $publishedAt,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'meta_title' => $request->meta_title ?? $request->title,
                'meta_description' => $request->meta_description,
                'additional_data' => $request->additional_data
            ]);

            return response()->json([
                'message' => 'Content created successfully',
                'content' => $content
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create content',
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
            if (!auth()->user()->hasPermission('content-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view content'
                ], 403);
            }

            $content = ContentManagement::with(['creator:id,name', 'updater:id,name'])->findOrFail($id);

            return response()->json([
                'content' => $content
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve content',
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
            if (!auth()->user()->hasPermission('content-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update content'
                ], 403);
            }

            $content = ContentManagement::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'type' => 'sometimes|required|string|max:50',
                'is_published' => 'boolean',
                'published_at' => 'nullable|date',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:255',
                'additional_data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update slug if title is changed
            if ($request->has('title') && $request->title !== $content->title) {
                $slug = Str::slug($request->title);

                // Check if slug already exists for this content type
                $contentType = $request->has('type') ? $request->type : $content->type;
                $slugExists = ContentManagement::where('slug', $slug)
                                           ->where('id', '!=', $id)
                                           ->where('type', $contentType)
                                           ->exists();

                if ($slugExists) {
                    $slugCount = ContentManagement::where('slug', 'like', $slug . '%')
                                               ->where('type', $contentType)
                                               ->count();
                    $slug = $slug . '-' . ($slugCount + 1);
                }

                $request->merge(['slug' => $slug]);
            }

            // Set published date if publishing for the first time
            if ($request->has('is_published') && $request->is_published && !$content->is_published) {
                $publishedAt = $request->published_at ?? now();
                $request->merge(['published_at' => $publishedAt]);
            }

            // Always update the updater
            $request->merge(['updated_by' => Auth::id()]);

            // Update content
            $content->update($request->only([
                'title', 'slug', 'content', 'type', 'is_published',
                'published_at', 'updated_by', 'meta_title',
                'meta_description', 'additional_data'
            ]));

            return response()->json([
                'message' => 'Content updated successfully',
                'content' => $content->fresh()->load(['creator:id,name', 'updater:id,name'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update content',
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
            if (!auth()->user()->hasPermission('content-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete content'
                ], 403);
            }

            $content = ContentManagement::findOrFail($id);
            $content->delete();

            return response()->json([
                'message' => 'Content deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content by slug.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySlug($slug)
    {
        try {
            $content = ContentManagement::where('slug', $slug)->first();

            if (!$content) {
                return response()->json([
                    'message' => 'Content not found'
                ], 404);
            }

            // If content is not published and user doesn't have permission to view unpublished content
            if (!$content->is_published &&
                (!Auth::check() || !auth()->user()->hasPermission('content-view'))) {
                return response()->json([
                    'message' => 'Content not found'
                ], 404);
            }

            return response()->json([
                'content' => $content->load(['creator:id,name', 'updater:id,name'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content by type.
     *
     * @param  string  $type
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByType($type, Request $request)
    {
        try {
            $query = ContentManagement::ofType($type);

            // If user is not authenticated or doesn't have permission, only show published content
            if (!Auth::check() || !auth()->user()->hasPermission('content-view')) {
                $query->published();
            } else if ($request->has('is_published')) {
                $query->where('is_published', filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN));
            }

            // Order by published date (desc)
            $query->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $content = $query->paginate($perPage);

            return response()->json($content, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve content',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
