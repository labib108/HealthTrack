<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Condition\StoreConditionRequest;
use App\Http\Requests\Api\V1\Condition\UpdateConditionRequest;
use App\Http\Resources\Api\V1\UserConditionResource;
use App\Models\UserCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserConditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $conditions = $request->user()->userConditions()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Conditions retrieved.',
            'data' => [
                'conditions' => UserConditionResource::collection($conditions),
            ],
        ]);
    }

    public function store(StoreConditionRequest $request): JsonResponse
    {
        $condition = $request->user()->userConditions()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Condition added.',
            'data' => [
                'condition' => new UserConditionResource($condition),
            ],
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $condition = UserCondition::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Condition retrieved.',
            'data' => [
                'condition' => new UserConditionResource($condition),
            ],
        ]);
    }

    public function update(UpdateConditionRequest $request, int $id): JsonResponse
    {
        $condition = UserCondition::where('user_id', $request->user()->id)->findOrFail($id);
        $condition->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Condition updated.',
            'data' => [
                'condition' => new UserConditionResource($condition->fresh()),
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $condition = UserCondition::where('user_id', $request->user()->id)->findOrFail($id);
        $condition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Condition removed.',
            'data' => null,
        ]);
    }
}
