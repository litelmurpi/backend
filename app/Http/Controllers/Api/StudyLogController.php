<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudyLogController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $studyLogs = StudyLog::with(['user'])
                ->where('user_id', auth()->id()) // Hanya log milik user yang login
                ->orderBy('created_at', 'desc')
                ->paginate(10);
    
            return response()->json([
                'success' => true,
                'message' => 'Study logs retrieved successfully',
                'data' => $studyLogs
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve study logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'topic' => 'required|string|max:255',
                'duration_minutes' => 'required|integer|min:1',
                'log_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Gunakan authenticated user
            $data = $request->all();
            $data['user_id'] = auth()->id();
            
            $studyLog = StudyLog::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Study log created successfully',
                'data' => $studyLog->load(['user'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create study log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $studyLog = StudyLog::with(['user'])->find($id);

            if (!$studyLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study log not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Study log retrieved successfully',
                'data' => $studyLog
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve study log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $studyLog = StudyLog::find($id);

            if (!$studyLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study log not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|required|exists:users,id',
                'topic' => 'sometimes|required|string|max:255',
                'duration_minutes' => 'sometimes|required|integer|min:1',
                'log_date' => 'sometimes|required|date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $studyLog->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Study log updated successfully',
                'data' => $studyLog->load(['user'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update study log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $studyLog = StudyLog::find($id);

            if (!$studyLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study log not found'
                ], 404);
            }

            $studyLog->delete();

            return response()->json([
                'success' => true,
                'message' => 'Study log deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete study log',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
