<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdministrationLog\StoreAdministrationLogRequest;
use App\Http\Resources\Api\V1\AdministrationLogResource;
use App\Models\AdministrationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdministrationLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdministrationLog::where('user_id', $request->user()->id)
            ->with(['prescription.drug', 'glucoseReading'])
            ->orderBy('taken_at', 'desc');

        if ($request->filled('prescription_id')) {
            $query->where('prescription_id', $request->prescription_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('taken_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('taken_at', '<=', $request->date('to'));
        }

        $logs = $query->paginate($request->input('per_page', 30));

        return response()->json([
            'success' => true,
            'message' => 'Administration logs retrieved.',
            'data' => [
                'logs' => AdministrationLogResource::collection($logs),
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                ],
            ],
        ]);
    }

    public function store(StoreAdministrationLogRequest $request): JsonResponse
    {
        $log = $request->user()->administrationLogs()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Medication administration logged.',
            'data' => [
                'log' => new AdministrationLogResource($log->load(['prescription.drug', 'glucoseReading'])),
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $log = AdministrationLog::where('user_id', $request->user()->id)
            ->with(['prescription.drug', 'glucoseReading'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Administration log retrieved.',
            'data' => [
                'log' => new AdministrationLogResource($log),
            ],
        ]);
    }
}
