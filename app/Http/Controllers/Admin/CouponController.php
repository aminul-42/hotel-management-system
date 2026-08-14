<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCoupon($request);

        Coupon::create($validated);

        return response()->json(['message' => 'Coupon created successfully.']);
    }

    public function edit(Coupon $coupon)
    {
        return response()->json(['coupon' => $coupon]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $this->validateCoupon($request, $coupon->id);

        $coupon->update($validated);

        return response()->json(['message' => 'Coupon updated successfully.']);
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'message' => 'Coupon ' . ($coupon->is_active ? 'activated' : 'deactivated') . '.',
            'is_active' => $coupon->is_active,
        ]);
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_count > 0) {
            return response()->json(['message' => 'This coupon has already been used and cannot be deleted. Deactivate it instead.'], 422);
        }

        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully.']);
    }

    protected function validateCoupon(Request $request, ?int $excludeId = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($excludeId)],
            'type' => ['required', Rule::in(['fixed', 'percentage'])],
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'min_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            throw ValidationException::withMessages(['value' => 'Percentage discount cannot exceed 100.']);
        }

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }
}