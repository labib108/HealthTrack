<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Prescription\StorePrescriptionRequest;
use App\Http\Requests\Api\V1\Prescription\UpdatePrescriptionRequest;
use App\Http\Resources\Api\V1\PrescriptionResource;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Prescription::where('user_id', $request->user()->id)
            ->with(['drug', 'userCondition'])
            ->orderBy('started_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('condition_id')) {
            $query->where('user_condition_id', $request->condition_id);
        }

        $prescriptions = $query->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Prescriptions retrieved.',
            'data' => [
                'prescriptions' => PrescriptionResource::collection($prescriptions),
                'meta' => [
                    'current_page' => $prescriptions->currentPage(),
                    'last_page' => $prescriptions->lastPage(),
                    'per_page' => $prescriptions->perPage(),
                    'total' => $prescriptions->total(),
                ],
            ],
        ]);
    }

    public function store(StorePrescriptionRequest $request): JsonResponse
    {
        $prescription = $request->user()->prescriptions()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Prescription added.',
            'data' => [
                'prescription' => new PrescriptionResource($prescription->load(['drug', 'userCondition'])),
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $prescription = Prescription::where('user_id', $request->user()->id)
            ->with(['drug', 'userCondition'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Prescription retrieved.',
            'data' => [
                'prescription' => new PrescriptionResource($prescription),
            ],
        ]);
    }

    public function update(UpdatePrescriptionRequest $request, int $id): JsonResponse
    {
        $prescription = Prescription::where('user_id', $request->user()->id)->findOrFail($id);
        $prescription->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Prescription updated.',
            'data' => [
                'prescription' => new PrescriptionResource($prescription->fresh(['drug', 'userCondition'])),
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $prescription = Prescription::where('user_id', $request->user()->id)->findOrFail($id);
        $prescription->delete();

        return response()->json([
            'success' => true,
            'message' => 'Prescription removed.',
            'data' => null,
        ]);
    }
}
