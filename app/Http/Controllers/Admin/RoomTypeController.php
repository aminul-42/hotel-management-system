<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    /**
     * Render the Room Types management page (self-contained blade, AJAX-driven).
     */
    public function index()
    {
        return view('admin.room-types.index');
    }

    /**
     * JSON data feed for the room types table.
     */
    public function data()
    {
        $roomTypes = RoomType::withCount('rooms')
            ->orderBy('name')
            ->get()
            ->map(function ($rt) {
                return [
                    'id'             => $rt->id,
                    'name'           => $rt->name,
                    'slug'           => $rt->slug,
                    'description'    => $rt->description,
                    'base_capacity'  => $rt->base_capacity,
                    'max_capacity'   => $rt->max_capacity,
                    'amenities'      => $rt->amenities ?? [],
                    'images'         => $rt->images ?? [],      // raw storage paths (needed to remove a specific image)
                    'image_urls'     => $rt->image_urls ?? [],  // display URLs, same order as 'images'
                    'is_active'      => (bool) $rt->is_active,
                    'rooms_count'    => $rt->rooms_count,
                ];
            });

        return response()->json(['data' => $roomTypes]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $roomType = new RoomType();
        $roomType->name          = $validated['name'];
        $roomType->slug          = Str::slug($validated['name']) . '-' . Str::lower(Str::random(5));
        $roomType->description   = $validated['description'] ?? null;
        $roomType->base_capacity = $validated['base_capacity'];
        $roomType->max_capacity  = $validated['max_capacity'];
        $roomType->amenities     = $this->parseAmenities($request->input('amenities'));
        $roomType->is_active     = $request->boolean('is_active', true);
        $roomType->images        = $this->storeNewImages($request);
        $roomType->save();

        return response()->json([
            'message' => 'Room type created successfully.',
            'data'    => $roomType,
        ], 201);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $validated = $this->validated($request);

        $roomType->name          = $validated['name'];
        $roomType->description   = $validated['description'] ?? null;
        $roomType->base_capacity = $validated['base_capacity'];
        $roomType->max_capacity  = $validated['max_capacity'];
        $roomType->amenities     = $this->parseAmenities($request->input('amenities'));
        $roomType->is_active     = $request->boolean('is_active', true);

        $images = $roomType->images ?? [];

        // Remove images the admin explicitly deleted in the edit form.
        foreach ((array) $request->input('removed_images', []) as $removed) {
            if (in_array($removed, $images, true)) {
                Storage::disk('public')->delete($removed);
                $images = array_values(array_diff($images, [$removed]));
            }
        }

        // Append any newly uploaded images.
        $images = array_merge($images, $this->storeNewImages($request));

        $roomType->images = $images;
        $roomType->save();

        return response()->json([
            'message' => 'Room type updated successfully.',
            'data'    => $roomType,
        ]);
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return response()->json([
                'message' => 'Cannot delete: rooms are still assigned to this room type. Reassign or remove them first.',
            ], 422);
        }

        foreach ($roomType->images ?? [] as $image) {
            Storage::disk('public')->delete($image);
        }

        $roomType->delete();

        return response()->json(['message' => 'Room type deleted successfully.']);
    }

    public function toggleActive(RoomType $roomType)
    {
        // Only block the deactivate direction — reactivating is always allowed.
        if ($roomType->is_active && $roomType->rooms()->exists()) {
            return response()->json([
                'message' => 'Cannot deactivate: rooms are still assigned to this room type. Reassign or remove them first.',
            ], 422);
        }

        $roomType->is_active = ! $roomType->is_active;
        $roomType->save();

        return response()->json([
            'message'   => 'Status updated.',
            'is_active' => $roomType->is_active,
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'base_capacity'    => 'required|integer|min:1',
            'max_capacity'     => 'required|integer|min:1|gte:base_capacity',
            'amenities'        => 'nullable',
            'images.*'         => 'nullable|image|max:5120',
            'is_active'        => 'nullable', // checkbox sends "on"/"1"/nothing; handled via $request->boolean() below
            'removed_images'   => 'nullable',
            'removed_images.*' => 'nullable|string',
        ]);
    }

    /**
     * Amenities arrive from the AJAX form as a comma-separated string.
     */
    private function parseAmenities($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw)));
        }

        if (is_string($raw) && $raw !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $raw))));
        }

        return [];
    }

    private function storeNewImages(Request $request): array
    {
        $paths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('room-types', 'public');
                }
            }
        }

        return $paths;
    }
}