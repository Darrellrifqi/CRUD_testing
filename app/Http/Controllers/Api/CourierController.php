<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CourierController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Courier::query();

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        if ($levelParam = $request->query('level')) {
            $levels = array_filter(
                array_map('intval', explode(',', $levelParam)),
                fn ($l) => in_array($l, Courier::LEVELS, true),
            );

            if (! empty($levels)) {
                $query->byLevels($levels);
            }
        }

        $sortBy = $request->query('sort_by', 'name');
        $column = $sortBy === 'registered_at' ? 'registered_at' : 'name';
        $query->orderBy($column);

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        return CourierResource::collection($query->paginate($perPage));
    }

    public function store(StoreCourierRequest $request): CourierResource
    {
        $courier = Courier::create($request->validated());

        return new CourierResource($courier);
    }

    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->fresh());
    }

    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }
}
