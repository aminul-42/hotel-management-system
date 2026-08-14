<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomRate;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoomRateController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomRate::with('roomType');

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $rates = $query->orderBy('room_type_id')->orderByDesc('priority')->paginate(15)->withQueryString();
        $roomTypes = RoomType::orderBy('name')->get();

        return view('admin.room-rates.index', [
            'rates' => $rates,
            'roomTypes' => $roomTypes,
            'filters' => $request->only('room_type_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRate($request);
        $this->assertNoDuplicateBase($validated);

        RoomRate::create($this->normalizeForType($validated));

        return response()->json(['message' => 'Rate created successfully.']);
    }

    public function edit(RoomRate $roomRate)
    {
        return response()->json(['rate' => $roomRate]);
    }

    public function update(Request $request, RoomRate $roomRate)
    {
        $validated = $this->validateRate($request);
        $this->assertNoDuplicateBase($validated, $roomRate->id);

        $roomRate->update($this->normalizeForType($validated));

        return response()->json(['message' => 'Rate updated successfully.']);
    }

    public function toggle(RoomRate $roomRate)
    {
        $roomRate->update(['is_active' => !$roomRate->is_active]);

        return response()->json([
            'message' => 'Rate ' . ($roomRate->is_active ? 'activated' : 'deactivated') . '.',
            'is_active' => $roomRate->is_active,
        ]);
    }

    public function destroy(RoomRate $roomRate)
    {
        $roomRate->delete();

        return response()->json(['message' => 'Rate deleted successfully.']);
    }

    protected function validateRate(Request $request): array
    {
        return $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'name' => 'required|string|max:255',
            'rate_type' => ['required', Rule::in(['base', 'weekend', 'seasonal', 'occupancy'])],
            'price' => 'required|numeric|min:0',
            'start_date' => 'required_if:rate_type,seasonal|nullable|date',
            'end_date' => 'required_if:rate_type,seasonal|nullable|date|after_or_equal:start_date',
            'day_of_week' => 'required_if:rate_type,weekend|nullable|integer|min:0|max:6',
            'priority' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }

    protected function normalizeForType(array $validated): array
    {
        // Clear irrelevant fields based on rate_type so stale data never lingers
        $validated['start_date'] = $validated['rate_type'] === 'seasonal' ? $validated['start_date'] : null;
        $validated['end_date'] = $validated['rate_type'] === 'seasonal' ? $validated['end_date'] : null;
        $validated['day_of_week'] = $validated['rate_type'] === 'weekend' ? $validated['day_of_week'] : null;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }

    protected function assertNoDuplicateBase(array $validated, ?int $excludeId = null): void
    {
        if ($validated['rate_type'] !== 'base' || !($validated['is_active'] ?? true)) {
            return;
        }

        $exists = RoomRate::where('room_type_id', $validated['room_type_id'])
            ->where('rate_type', 'base')
            ->where('is_active', true)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'rate_type' => 'This room type already has an active base rate. Deactivate it first or edit it instead.',
            ]);
        }
    }
}