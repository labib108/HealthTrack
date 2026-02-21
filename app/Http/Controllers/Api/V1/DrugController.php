<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DrugResource;
use App\Models\Drug;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DrugController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Drug::where('is_active', true)->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('insulin_type')) {
            $query->where('insulin_type', $request->insulin_type);
        }
        if ($request->filled('therapeutic_class')) {
            $query->where('therapeutic_class', $request->therapeutic_class);
        }

        $drugs = $query->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'message' => 'Drugs retrieved.',
            'data' => [
                'drugs' => DrugResource::collection($drugs),
                'meta' => [
                    'current_page' => $drugs->currentPage(),
                    'last_page' => $drugs->lastPage(),
                    'per_page' => $drugs->perPage(),
                    'total' => $drugs->total(),
                ],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $drug = Drug::where('is_active', true)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Drug retrieved.',
            'data' => [
                'drug' => new DrugResource($drug),
            ],
        ]);
    }
}
