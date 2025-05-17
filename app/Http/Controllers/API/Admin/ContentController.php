<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContentManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * Display a listing of the contents.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = ContentManagement::with('creator')
            ->orderBy('created_at', 'desc');

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by publication status if provided
        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        // Search by title or content if search term provided
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%$searchTerm%")
                  ->orWhere('content', 'like', "%$searchTerm%");
            });
        }

        $contents = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $contents
        ]);
    }

    /**
     * Store a newly created content.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|max:50',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'additional_data' => 'nullable|json',
        ]);

        // Generate slug from title
        $slug = Str::slug($request->title);

        // Check if slug already exists, if so, append a number
        $count = 1;
        $originalSlug = $slug;
        while (ContentManagement::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        // Handle file upload if present
        $additionalData = json_decode($request->additional_data ?? '{}', true);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('content_files', $filename, 'public');
            $additionalData['file_path'] = Storage::url($path);
        }

        $content = ContentManagement::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'type' => $request->type,
            'is_published' => $request->boolean('is_published', false),
            'published_at' => $request->is_published ? ($request->published_at ?? now()) : null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
            'meta_title' => $request->meta_title ?? $request->title,
            'meta_description' => $request->meta_description,
            'additional_data' => $additionalData,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content created successfully.',
            'data' => $content
        ], 201);
    }

    /**
     * Display the specified content.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $content = ContentManagement::with(['creator', 'updater'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $content
        ]);
    }

    /**
     * Update the specified content.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $content = ContentManagement::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|max:50',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'additional_data' => 'nullable|json',
        ]);

        // Handle slug update if title changed
        if ($content->title != $request->title) {
            $slug = Str::slug($request->title);

            // Check if slug already exists, if so, append a number
            $count = 1;
            $originalSlug = $slug;
            while (ContentManagement::where('slug', $slug)
                    ->where('id', '!=', $id)
                    ->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $content->slug = $slug;
        }

        // Handle file upload if present
        $additionalData = json_decode($request->additional_data ?? '{}', true);
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if (isset($content->additional_data['file_path'])) {
                $oldPath = str_replace('/storage/', '', $content->additional_data['file_path']);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Upload new file
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('content_files', $filename, 'public');
            $additionalData['file_path'] = Storage::url($path);
        } else if (isset($content->additional_data['file_path'])) {
            // Keep the existing file path
            $additionalData['file_path'] = $content->additional_data['file_path'];
        }

        $content->title = $request->title;
        $content->content = $request->content;
        $content->type = $request->type;
        $content->is_published = $request->boolean('is_published', $content->is_published);

        // Update published_at based on publish status
        if ($request->boolean('is_published') && !$content->published_at) {
            $content->published_at = $request->published_at ?? now();
        } else if (!$request->boolean('is_published')) {
            $content->published_at = null;
        } else if ($request->has('published_at')) {
            $content->published_at = $request->published_at;
        }

        $content->updated_by = Auth::id();
        $content->meta_title = $request->meta_title ?? $request->title;
        $content->meta_description = $request->meta_description;
        $content->additional_data = $additionalData;

        $content->save();

        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully.',
            'data' => $content
        ]);
    }

    /**
     * Remove the specified content.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $content = ContentManagement::findOrFail($id);

        // Delete associated file if exists
        if (isset($content->additional_data['file_path'])) {
            $path = str_replace('/storage/', '', $content->additional_data['file_path']);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $content->delete();

        return response()->json([
            'success' => true,
            'message' => 'Content deleted successfully.'
        ]);
    }
}
