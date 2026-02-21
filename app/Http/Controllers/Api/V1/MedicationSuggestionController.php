<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MedicationSuggestionResource;
use App\Models\MedicationSuggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicationSuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MedicationSuggestion::where('user_id', $request->user()->id)
            ->with('glucoseReading')
            ->orderBy('evaluated_at', 'desc');

        if ($request->boolean('unacknowledged_only')) {
            $query->where('acknowledged', false);
        }
        if ($request->filled('from')) {
            $query->whereDate('evaluated_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('evaluated_at', '<=', $request->date('to'));
        }

        $suggestions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Medication suggestions retrieved.',
            'data' => [
                'suggestions' => MedicationSuggestionResource::collection($suggestions),
                'meta' => [
                    'current_page' => $suggestions->currentPage(),
                    'last_page' => $suggestions->lastPage(),
                    'per_page' => $suggestions->perPage(),
                    'total' => $suggestions->total(),
                ],
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $suggestion = MedicationSuggestion::where('user_id', $request->user()->id)
            ->with('glucoseReading')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Medication suggestion retrieved.',
            'data' => [
                'suggestion' => new MedicationSuggestionResource($suggestion),
            ],
        ]);
    }

    public function acknowledge(Request $request, int $id): JsonResponse
    {
        $suggestion = MedicationSuggestion::where('user_id', $request->user()->id)->findOrFail($id);
        $suggestion->update(['acknowledged' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Suggestion acknowledged.',
            'data' => [
                'suggestion' => new MedicationSuggestionResource($suggestion->fresh()),
            ],
        ]);
    }
}
