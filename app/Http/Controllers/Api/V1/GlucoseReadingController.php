<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Glucose\GlucoseReadingCritical;
use App\Events\Glucose\GlucoseReadingRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Glucose\StoreGlucoseReadingRequest;
use App\Http\Requests\Api\V1\Glucose\UpdateGlucoseReadingRequest;
use App\Http\Resources\Api\V1\GlucoseReadingResource;
use App\Models\GlucoseReading;
use App\Services\Glucose\GlucoseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class GlucoseReadingController extends Controller
{
    public function __construct(
        private readonly GlucoseService $glucoseService
    ) {
    }

    /**
     * List glucose readings for the authenticated user (time-series ordered by measured_at)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from' => ['sometimes', 'date', 'before_or_equal:now'],
            'to' => ['sometimes', 'date', 'before_or_equal:now'],
        ]);

        if ($request->filled('from') && $request->filled('to') && $request->date('from')->gt($request->date('to'))) {
            throw ValidationException::withMessages(['from' => ['The from date must be before or equal to the to date.']]);
        }

        $user = $request->user();
        $from = $request->filled('from') ? $request->date('from')->startOfDay() : null;
        $to = $request->filled('to') ? $request->date('to')->endOfDay() : null;
        $readings = $this->glucoseService->getReadingsForUser($user, $from, $to);

        return response()->json([
            'success' => true,
            'message' => 'Glucose readings retrieved.',
            'data' => [
                'readings' => GlucoseReadingResource::collection($readings),
            ],
        ]);
    }

    /**
     * Store a new glucose reading
     */
    public function store(StoreGlucoseReadingRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = array_merge($request->validated(), [
            'measured_at' => $request->date('measured_at'),
        ]);

        $reading = $this->glucoseService->createReading($user, $data);

        Event::dispatch(new GlucoseReadingRecorded($reading));
        if ($reading->requires_alert) {
            Event::dispatch(new GlucoseReadingCritical($reading));
        }

        return response()->json([
            'success' => true,
            'message' => 'Glucose reading recorded.',
            'data' => [
                'reading' => new GlucoseReadingResource($reading),
            ],
        ], 201);
    }

    /**
     * Show a single reading (user-scoped)
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $reading = GlucoseReading::query()
            ->forUser($request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Glucose reading retrieved.',
            'data' => [
                'reading' => new GlucoseReadingResource($reading),
            ],
        ]);
    }

    /**
     * Update an existing reading (user-scoped)
     */
    public function update(UpdateGlucoseReadingRequest $request, int $id): JsonResponse
    {
        $reading = GlucoseReading::query()
            ->forUser($request->user()->id)
            ->findOrFail($id);

        $data = $request->validated();
        if (isset($data['measured_at'])) {
            $data['measured_at'] = $request->date('measured_at');
        }

        $updated = $this->glucoseService->updateReading($reading, $data);

        Event::dispatch(new GlucoseReadingRecorded($updated));
        if ($updated->requires_alert) {
            Event::dispatch(new GlucoseReadingCritical($updated));
        }

        return response()->json([
            'success' => true,
            'message' => 'Glucose reading updated.',
            'data' => [
                'reading' => new GlucoseReadingResource($updated),
            ],
        ]);
    }

    /**
     * Delete a reading (user-scoped)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $reading = GlucoseReading::query()
            ->forUser($request->user()->id)
            ->findOrFail($id);

        $reading->delete();

        return response()->json([
            'success' => true,
            'message' => 'Glucose reading deleted.',
            'data' => null,
        ]);
    }
}
