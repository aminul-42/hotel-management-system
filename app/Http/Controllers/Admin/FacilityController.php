<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.facilities.index', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateData($request);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('facilities', 'public');
        }

        $facility = Facility::create($validated);

        return response()->json([
            'message' => 'Facility created successfully.',
            'facility' => $facility,
        ]);
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $this->validateData($request, $facility->id);

        if ($validated['name'] !== $facility->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $facility->id);
        }

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            if ($facility->image) {
                Storage::disk('public')->delete($facility->image);
            }
            $validated['image'] = $request->file('image')->store('facilities', 'public');
        } elseif ($request->boolean('remove_image') && $facility->image) {
            Storage::disk('public')->delete($facility->image);
            $validated['image'] = null;
        }

        $facility->update($validated);

        return response()->json([
            'message' => 'Facility updated successfully.',
            'facility' => $facility->fresh(),
        ]);
    }

    public function toggleActive(Facility $facility)
    {
        $facility->update(['is_active' => ! $facility->is_active]);

        return response()->json([
            'message' => 'Facility status updated.',
            'is_active' => $facility->is_active,
        ]);
    }

    public function destroy(Facility $facility)
    {
        if ($facility->image) {
            Storage::disk('public')->delete($facility->image);
        }

        $facility->delete();

        return response()->json(['message' => 'Facility deleted successfully.']);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:2000',
            'pricing_type' => ['required', Rule::in(['free', 'fixed', 'on_request'])],
            'price' => 'required_if:pricing_type,fixed|nullable|numeric|min:0|max:9999999.99',
            'image' => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Facility::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-" . $i++;
        }

        return $slug;
    }
}