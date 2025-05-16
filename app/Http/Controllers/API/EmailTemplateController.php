<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
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
            if (!auth()->user()->hasPermission('email-template-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view email templates'
                ], 403);
            }

            $templates = EmailTemplate::all();

            return response()->json([
                'templates' => $templates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve email templates',
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
            if (!auth()->user()->hasPermission('email-template-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create email templates'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'subject' => 'required|string|max:255',
                'body' => 'required|string',
                'plain_text' => 'nullable|string',
                'placeholders' => 'nullable|array',
                'is_active' => 'boolean',
                'sender_name' => 'nullable|string|max:255',
                'sender_email' => 'nullable|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate slug from name
            $slug = Str::slug($request->name);

            // Check if slug already exists
            $slugCount = EmailTemplate::where('slug', 'like', $slug . '%')->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }

            // Create template
            $template = EmailTemplate::create([
                'name' => $request->name,
                'slug' => $slug,
                'subject' => $request->subject,
                'body' => $request->body,
                'plain_text' => $request->plain_text,
                'placeholders' => $request->placeholders ?? [],
                'is_active' => $request->is_active ?? true,
                'sender_name' => $request->sender_name,
                'sender_email' => $request->sender_email
            ]);

            return response()->json([
                'message' => 'Email template created successfully',
                'template' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create email template',
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
            if (!auth()->user()->hasPermission('email-template-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view email templates'
                ], 403);
            }

            $template = EmailTemplate::findOrFail($id);

            return response()->json([
                'template' => $template
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve email template',
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
            if (!auth()->user()->hasPermission('email-template-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update email templates'
                ], 403);
            }

            $template = EmailTemplate::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'subject' => 'sometimes|required|string|max:255',
                'body' => 'sometimes|required|string',
                'plain_text' => 'nullable|string',
                'placeholders' => 'nullable|array',
                'is_active' => 'boolean',
                'sender_name' => 'nullable|string|max:255',
                'sender_email' => 'nullable|email|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update slug if name is changed
            if ($request->has('name') && $request->name !== $template->name) {
                $slug = Str::slug($request->name);

                // Check if slug already exists
                $slugExists = EmailTemplate::where('slug', $slug)
                                        ->where('id', '!=', $id)
                                        ->exists();

                if ($slugExists) {
                    $slugCount = EmailTemplate::where('slug', 'like', $slug . '%')->count();
                    $slug = $slug . '-' . ($slugCount + 1);
                }

                $request->merge(['slug' => $slug]);
            }

            // Update template
            $template->update($request->only([
                'name', 'slug', 'subject', 'body', 'plain_text',
                'placeholders', 'is_active', 'sender_name', 'sender_email'
            ]));

            return response()->json([
                'message' => 'Email template updated successfully',
                'template' => $template->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update email template',
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
            if (!auth()->user()->hasPermission('email-template-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete email templates'
                ], 403);
            }

            $template = EmailTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'message' => 'Email template deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete email template',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
