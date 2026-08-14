<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.rooms.index', compact('roomTypes'));
    }

    public function data(Request $request)
    {
        $query = Room::with('roomType:id,name')->orderBy('room_number');

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->input('room_type_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $rooms = $query->get()->map(function ($room) {
            return [
                'id'            => $room->id,
                'room_number'   => $room->room_number,
                'floor'         => $room->floor,
                'status'        => $room->status,
                'room_type_id'  => $room->room_type_id,
                'room_type'     => $room->roomType->name ?? '—',
                'images'        => $room->images ?? [],      // raw storage paths (needed to remove a specific image)
                'image_urls'    => $room->image_urls ?? [],  // display URLs, same order as 'images'
                'is_active'     => (bool) $room->is_active,
            ];
        });

        return response()->json(['data' => $rooms]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $room = new Room();
        $room->room_type_id = $validated['room_type_id'];
        $room->room_number  = $validated['room_number'];
        $room->floor        = $validated['floor'];
        $room->status       = $validated['status'];
        $room->is_active    = $request->boolean('is_active', true);
        $room->images       = $this->storeNewImages($request);
        $room->save();

        return response()->json([
            'message' => 'Room created successfully.',
            'data'    => $room->load('roomType:id,name'),
        ], 201);
    }

    public function update(Request $request, Room $room)
    {
        $validated = $this->validated($request, $room->id);

        $room->room_type_id = $validated['room_type_id'];
        $room->room_number  = $validated['room_number'];
        $room->floor        = $validated['floor'];
        $room->status       = $validated['status'];
        $room->is_active    = $request->boolean('is_active', true);

        $images = $room->images ?? [];

        foreach ((array) $request->input('removed_images', []) as $removed) {
            if (in_array($removed, $images, true)) {
                Storage::disk('public')->delete($removed);
                $images = array_values(array_diff($images, [$removed]));
            }
        }

        $images = array_merge($images, $this->storeNewImages($request));

        $room->images = $images;
        $room->save();

        return response()->json([
            'message' => 'Room updated successfully.',
            'data'    => $room->load('roomType:id,name'),
        ]);
    }

    public function destroy(Room $room)
    {
        if ($room->bookings()->whereIn('status', ['confirmed', 'checked_in'])->exists()) {
            return response()->json([
                'message' => 'Cannot delete: this room has active or upcoming bookings.',
            ], 422);
        }

        foreach ($room->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $room->delete();

        return response()->json(['message' => 'Room deleted successfully.']);
    }

    /**
     * Quick inline status change (clean/dirty/occupied/blocked/maintenance)
     * used by front desk & admin dashboards without opening the full edit form.
     */
    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['clean', 'dirty', 'occupied', 'blocked', 'maintenance'])],
        ]);

        $room->status = $validated['status'];
        $room->save();

        return response()->json([
            'message' => 'Room status updated.',
            'status'  => $room->status,
        ]);
    }

    private function validated(Request $request, ?int $ignoreRoomId = null): array
    {
        return $request->validate([
            'room_type_id'     => 'required|exists:room_types,id',
            'room_number'      => [
                'required', 'string', 'max:20',
                Rule::unique('rooms', 'room_number')->ignore($ignoreRoomId),
            ],
            'floor'            => 'required|integer|min:0',
            'status'           => ['required', Rule::in(['clean', 'dirty', 'occupied', 'blocked', 'maintenance'])],
            'is_active'        => 'nullable', // checkbox sends "on"/"1"/nothing; handled via $request->boolean() below
            'images.*'         => 'nullable|image|max:5120',
            'removed_images'   => 'nullable',
            'removed_images.*' => 'nullable|string',
        ]);
    }

    private function storeNewImages(Request $request): array
    {
        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('rooms', 'public');
                }
            }
        }

        return $paths;
    }
}