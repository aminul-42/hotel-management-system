@extends('layouts.admin.app')


@section('title', 'Coupons')
@section('page-title', 'Coupons')
@section('page-subtitle', 'Discount codes for bookings')

@section('content')

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">All Coupons</div>
        <div class="table-toolbar-actions">
            <div class="search-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" id="couponSearchInput" placeholder="Search code...">
            </div>
            <button type="button" class="btn btn-primary btn-sm" onclick="CouponsPage.openCreate()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Coupon
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th><th>Type</th><th>Value</th><th>Min. Amount</th>
                <th>Usage</th><th>Valid Period</th><th>Active</th><th></th>
            </tr>
        </thead>
        <tbody id="couponsTbody">
            @forelse ($coupons as $c)
                <tr>
                    <td><strong>{{ $c->code }}</strong></td>
                    <td><span class="badge {{ $c->type === 'percentage' ? 'badge-gold' : 'badge-blue' }}">{{ ucfirst($c->type) }}</span></td>
                    <td>{{ $c->type === 'percentage' ? $c->value.'%' : '৳'.number_format($c->value, 2) }}</td>
                    <td>{{ $c->min_amount ? '৳'.number_format($c->min_amount, 2) : '—' }}</td>
                    <td>{{ $c->used_count }}{{ $c->max_uses ? ' / '.$c->max_uses : ' / ∞' }}</td>
                    <td>
                        @if ($c->valid_from || $c->valid_until)
                            {{ $c->valid_from?->format('d M Y') ?? 'Any' }} – {{ $c->valid_until?->format('d M Y') ?? 'No end' }}
                        @else
                            Always valid
                        @endif
                    </td>
                    <td>
                        <button type="button" class="toggle-switch {{ $c->is_active ? 'is-on' : '' }}" role="switch"
                                data-endpoint="{{ route('admin.coupons.toggle', $c) }}" data-state-key="is_active"
                                aria-checked="{{ $c->is_active ? 'true' : 'false' }}">
                            <span class="toggle-knob"></span>
                        </button>
                    </td>
                    <td>
                        <div style="display:flex; gap:0.4rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="CouponsPage.openEdit({{ $c->id }})">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="CouponsPage.confirmDelete({{ $c->id }}, '{{ $c->code }}')">Delete</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="table-empty-row"><td colspan="8">No coupons yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1.25rem;">{{ $coupons->links() }}</div>

<template id="couponFormTemplate">
    <form id="couponForm" onsubmit="return false;">
        <div class="form-row">
            <div class="form-group"><label>Code</label><input type="text" id="couponCode" style="text-transform:uppercase;" placeholder="e.g. SUMMER20"></div>
            <div class="form-group">
                <label>Type</label>
                <select id="couponType">
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Value</label><input type="number" id="couponValue" min="0" step="0.01"></div>
            <div class="form-group"><label>Min. Booking Amount (optional)</label><input type="number" id="couponMinAmount" min="0" step="0.01"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Max Uses (optional)</label><input type="number" id="couponMaxUses" min="1" placeholder="Unlimited"></div>
            <div class="form-group" style="display:flex; align-items:flex-end;">
                <div class="form-check"><input type="checkbox" id="couponIsActive" checked><label for="couponIsActive">Active</label></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Valid From (optional)</label><input type="date" id="couponValidFrom"></div>
            <div class="form-group"><label>Valid Until (optional)</label><input type="date" id="couponValidUntil"></div>
        </div>
    </form>
</template>

@endsection

@push('scripts')
<script>
const CouponsPage = (() => {
    let editingId = null;

    function footerHtml() {
        return `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="couponSaveBtn">Save Coupon</button>`;
    }

    function openCreate() {
        editingId = null;
        Modal.open('New Coupon', document.getElementById('couponFormTemplate').innerHTML, footerHtml());
        document.getElementById('couponSaveBtn').addEventListener('click', submitForm);
    }

    async function openEdit(id) {
        editingId = id;
        Modal.open('Loading...', '<div class="inline-spinner"><span class="dot-spinner"></span> Loading...</div>', '');
        try {
            const res = await fetch(`/admin/coupons/${id}/edit`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to load coupon.');

            Modal.open('Edit Coupon', document.getElementById('couponFormTemplate').innerHTML, footerHtml());

            const c = json.coupon;
            document.getElementById('couponCode').value = c.code;
            document.getElementById('couponType').value = c.type;
            document.getElementById('couponValue').value = c.value;
            document.getElementById('couponMinAmount').value = c.min_amount ?? '';
            document.getElementById('couponMaxUses').value = c.max_uses ?? '';
            document.getElementById('couponIsActive').checked = !!c.is_active;
            document.getElementById('couponValidFrom').value = c.valid_from ?? '';
            document.getElementById('couponValidUntil').value = c.valid_until ?? '';

            document.getElementById('couponSaveBtn').addEventListener('click', submitForm);
        } catch (err) {
            Modal.close();
            Toast.show(err.message, 'error');
        }
    }

    async function submitForm() {
        const btn = document.getElementById('couponSaveBtn');
        const payload = {
            code: document.getElementById('couponCode').value,
            type: document.getElementById('couponType').value,
            value: document.getElementById('couponValue').value,
            min_amount: document.getElementById('couponMinAmount').value || null,
            max_uses: document.getElementById('couponMaxUses').value || null,
            is_active: document.getElementById('couponIsActive').checked,
            valid_from: document.getElementById('couponValidFrom').value || null,
            valid_until: document.getElementById('couponValidUntil').value || null,
        };

        Modal.setSubmitting(btn, true, 'Saving...');
        try {
            const url = editingId ? `/admin/coupons/${editingId}` : '/admin/coupons';
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || (json.errors ? Object.values(json.errors)[0][0] : 'Something went wrong.'));

            Toast.show(json.message, 'success');
            Modal.close();
            setTimeout(() => window.location.reload(), 500);
        } catch (err) {
            Modal.setSubmitting(btn, false);
            Toast.show(err.message, 'error');
        }
    }

    function confirmDelete(id, code) {
        Modal.confirm({
            title: 'Delete coupon?',
            message: `Delete coupon <strong>${code}</strong>? This cannot be undone.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(`/admin/coupons/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Delete failed.');
                Toast.show(json.message, 'success');
                setTimeout(() => window.location.reload(), 500);
            }
        });
    }

    document.getElementById('couponSearchInput')?.addEventListener('input', function () {
        LiveSearch._filter(this.value, '#couponsTbody', { emptyColCount: 8 });
    });

    return { openCreate, openEdit, confirmDelete };
})();
</script>
@endpush