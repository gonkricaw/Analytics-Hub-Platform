<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TermAndCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermAndConditionController extends Controller
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
            if (!auth()->user()->hasPermission('terms-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view terms and conditions'
                ], 403);
            }

            $terms = TermAndCondition::orderBy('effective_date', 'desc')
                                     ->orderBy('version', 'desc')
                                     ->get();

            return response()->json([
                'terms' => $terms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve terms and conditions',
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
            if (!auth()->user()->hasPermission('terms-create')) {
                return response()->json([
                    'message' => 'You do not have permission to create terms and conditions'
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'is_active' => 'boolean',
                'effective_date' => 'required|date',
                'version' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // If this new terms is active, deactivate all other terms
            if ($request->is_active) {
                TermAndCondition::where('is_active', true)
                              ->update(['is_active' => false]);
            }

            $terms = TermAndCondition::create([
                'title' => $request->title,
                'content' => $request->content,
                'is_active' => $request->is_active ?? false,
                'effective_date' => $request->effective_date,
                'version' => $request->version
            ]);

            return response()->json([
                'message' => 'Terms and conditions created successfully',
                'terms' => $terms
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create terms and conditions',
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
            if (!auth()->user()->hasPermission('terms-view')) {
                return response()->json([
                    'message' => 'You do not have permission to view terms and conditions'
                ], 403);
            }

            $terms = TermAndCondition::findOrFail($id);

            return response()->json([
                'terms' => $terms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve terms and conditions',
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
            if (!auth()->user()->hasPermission('terms-update')) {
                return response()->json([
                    'message' => 'You do not have permission to update terms and conditions'
                ], 403);
            }

            $terms = TermAndCondition::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'content' => 'sometimes|required|string',
                'is_active' => 'boolean',
                'effective_date' => 'sometimes|required|date',
                'version' => 'sometimes|required|string|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // If this terms is being set to active, deactivate all other terms
            if ($request->has('is_active') && $request->is_active) {
                TermAndCondition::where('id', '!=', $id)
                              ->where('is_active', true)
                              ->update(['is_active' => false]);
            }

            $terms->update($request->only([
                'title', 'content', 'is_active', 'effective_date', 'version'
            ]));

            return response()->json([
                'message' => 'Terms and conditions updated successfully',
                'terms' => $terms->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update terms and conditions',
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
            if (!auth()->user()->hasPermission('terms-delete')) {
                return response()->json([
                    'message' => 'You do not have permission to delete terms and conditions'
                ], 403);
            }

            $terms = TermAndCondition::findOrFail($id);

            // Cannot delete active terms
            if ($terms->is_active) {
                return response()->json([
                    'message' => 'Cannot delete active terms and conditions'
                ], 400);
            }

            $terms->delete();

            return response()->json([
                'message' => 'Terms and conditions deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete terms and conditions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the currently active terms and conditions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActive()
    {
        try {
            $activeTerms = TermAndCondition::active()->first();

            if (!$activeTerms) {
                return response()->json([
                    'message' => 'No active terms and conditions found'
                ], 404);
            }

            return response()->json([
                'terms' => $activeTerms
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active terms and conditions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the terms acceptance status for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAcceptance()
    {
        try {
            $user = auth()->user();
            $activeTerms = TermAndCondition::active()->first();

            if (!$activeTerms) {
                return response()->json([
                    'message' => 'No active terms and conditions found',
                    'hasAcceptedTerms' => true // No terms to accept
                ], 200);
            }

            $hasAccepted = $activeTerms->isAcceptedBy($user);

            return response()->json([
                'terms' => $activeTerms,
                'hasAcceptedTerms' => $hasAccepted,
                'acceptedAt' => $hasAccepted ?
                    $activeTerms->acceptedByUsers()->where('user_id', $user->id)->first()->pivot->accepted_at : null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve terms acceptance status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
