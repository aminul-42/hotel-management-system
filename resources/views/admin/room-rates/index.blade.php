@extends('layouts.admin.app')


@section('title', 'Room Rates')
@section('page-title', 'Room Rates')
@section('page-subtitle', 'Base, weekend, seasonal and occupancy pricing per room type')

@push('styles')
<style>
    .filter-select {
        padding: 0.5rem 0.9rem; border: 1.5px solid var(--border); border-radius: 20px;
        background: var(--light); font-size: 0.85rem; font-family: var(--font-body);
        color: var(--dark); outline: none; cursor: pointer;
    }
    .rate-type-fields { display: none; }
    .rate-type-fields.show { display: block; }
</style>
@endpush

@section('content')

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">All Rates</div>
        <div class="table-toolbar-actions">
            <form method="GET">
                <select name="room_type_id" class="filter-select" onchange="this.form.submit()">
                    <option value="">All room types</option>
                    @foreach ($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ ($filters['room_type_id'] ?? '') == $rt->id ? 'selected' : '' }}>{{ $rt->name }}</option>
                    @endforeach
                </select>
            </form>
            <button type="button" class="btn btn-primary btn-sm" onclick="RoomRatesPage.openCreate()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Rate
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Room Type</th><th>Name</th><th>Type</th><th>Price / Night</th>
                <th>Applies To</th><th>Priority</th><th>Active</th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rates as $rate)
                @php
                    $typeBadge = ['base' => 'badge-blue', 'weekend' => 'badge-gold', 'seasonal' => 'badge-green', 'occupancy' => 'badge-gray'][$rate->rate_type];
                    $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                    $appliesTo = match($rate->rate_type) {
                        'seasonal' => $rate->start_date->format('d M Y') . ' – ' . $rate->end_date->format('d M Y'),
                        'weekend' => $days[$rate->day_of_week] ?? '—',
                        'occupancy' => 'Guests beyond base capacity',
                        default => 'Every night (fallback rate)',
                    };
                @endphp
                <tr>
                    <td>{{ $rate->roomType->name }}</td>
                    <td>{{ $rate->name }}</td>
                    <td><span class="badge {{ $typeBadge }}">{{ ucfirst($rate->rate_type) }}</span></td>
                    <td>৳{{ number_format($rate->price, 2) }}</td>
                    <td>{{ $appliesTo }}</td>
                    <td>{{ $rate->priority }}</td>
                    <td>
                        <button type="button" class="toggle-switch {{ $rate->is_active ? 'is-on' : '' }}" role="switch"
                                data-endpoint="{{ route('admin.room-rates.toggle', $rate) }}" data-state-key="is_active"
                                aria-checked="{{ $rate->is_active ? 'true' : 'false' }}">
                            <span class="toggle-knob"></span>
                        </button>
                    </td>
                    <td>
                        <div style="display:flex; gap:0.4rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="RoomRatesPage.openEdit({{ $rate->id }})">Edit</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="RoomRatesPage.confirmDelete({{ $rate->id }}, '{{ addslashes($rate->name) }}')">Delete</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr class="table-empty-row"><td colspan="8">No rates configured yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:1.25rem;">{{ $rates->links() }}</div>

<template id="rateFormTemplate">
    <form id="rateForm" onsubmit="return false;">
        <div class="form-group">
            <label>Room Type</label>
            <select id="rateRoomType">
                @foreach ($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Rate Name</label>
            <input type="text" id="rateName" placeholder="e.g. Standard Base Rate">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rate Type</label>
                <select id="rateType">
                    <option value="base">Base (fallback, every night)</option>
                    <option value="weekend">Weekend (specific day)</option>
                    <option value="seasonal">Seasonal (date range)</option>
                    <option value="occupancy">Occupancy (extra guests)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price / Night</label>
                <input type="number" id="ratePrice" min="0" step="0.01">
            </div>
        </div>

        <div class="rate-type-fields" id="weekendFields">
            <div class="form-group">
                <label>Day of Week</label>
                <select id="rateDayOfWeek">
                    <option value="0">Sunday</option><option value="1">Monday</option>
                    <option value="2">Tuesday</option><option value="3">Wednesday</option>
                    <option value="4">Thursday</option><option value="5">Friday</option>
                    <option value="6">Saturday</option>
                </select>
            </div>
        </div>

        <div class="rate-type-fields" id="seasonalFields">
            <div class="form-row">
                <div class="form-group"><label>Start Date</label><input type="date" id="rateStartDate"></div>
                <div class="form-group"><label>End Date</label><input type="date" id="rateEndDate"></div>
            </div>
        </div>

        <div class="form-group">
            <label>Priority</label>
            <input type="number" id="ratePriority" min="0" value="0">
            <div class="hint">Higher priority wins when two rates match the same night.</div>
        </div>

        <div class="form-check">
            <input type="checkbox" id="rateIsActive" checked>
            <label for="rateIsActive">Active</label>
        </div>
    </form>
</template>

@endsection

@push('scripts')
<script>
const RoomRatesPage = (() => {
    let editingId = null;

    function footerHtml() {
        return `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="rateSaveBtn">Save Rate</button>`;
    }

    function wireTypeToggle() {
        const select = document.getElementById('rateType');
        const update = () => {
            document.getElementById('weekendFields').classList.toggle('show', select.value === 'weekend');
            document.getElementById('seasonalFields').classList.toggle('show', select.value === 'seasonal');
        };
        select.addEventListener('change', update);
        update();
    }

    function openCreate() {
        editingId = null;
        Modal.open('New Room Rate', document.getElementById('rateFormTemplate').innerHTML, footerHtml());
        wireTypeToggle();
        document.getElementById('rateSaveBtn').addEventListener('click', submitForm);
    }

    async function openEdit(id) {
        editingId = id;
        Modal.open('Loading...', '<div class="inline-spinner"><span class="dot-spinner"></span> Loading...</div>', '');
        try {
            const res = await fetch(`/admin/room-rates/${id}/edit`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to load rate.');

            Modal.open('Edit Room Rate', document.getElementById('rateFormTemplate').innerHTML, footerHtml());
            wireTypeToggle();

            const r = json.rate;
            document.getElementById('rateRoomType').value = r.room_type_id;
            document.getElementById('rateName').value = r.name;
            document.getElementById('rateType').value = r.rate_type;
            document.getElementById('ratePrice').value = r.price;
            document.getElementById('ratePriority').value = r.priority;
            document.getElementById('rateIsActive').checked = !!r.is_active;
            if (r.day_of_week !== null) document.getElementById('rateDayOfWeek').value = r.day_of_week;
            if (r.start_date) document.getElementById('rateStartDate').value = r.start_date;
            if (r.end_date) document.getElementById('rateEndDate').value = r.end_date;
            document.getElementById('rateType').dispatchEvent(new Event('change'));

            document.getElementById('rateSaveBtn').addEventListener('click', submitForm);
        } catch (err) {
            Modal.close();
            Toast.show(err.message, 'error');
        }
    }

    async function submitForm() {
        const btn = document.getElementById('rateSaveBtn');
        const payload = {
            room_type_id: document.getElementById('rateRoomType').value,
            name: document.getElementById('rateName').value,
            rate_type: document.getElementById('rateType').value,
            price: document.getElementById('ratePrice').value,
            day_of_week: document.getElementById('rateDayOfWeek').value,
            start_date: document.getElementById('rateStartDate').value,
            end_date: document.getElementById('rateEndDate').value,
            priority: document.getElementById('ratePriority').value,
            is_active: document.getElementById('rateIsActive').checked,
        };

        Modal.setSubmitting(btn, true, 'Saving...');
        try {
            const url = editingId ? `/admin/room-rates/${editingId}` : '/admin/room-rates';
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

    function confirmDelete(id, name) {
        Modal.confirm({
            title: 'Delete rate?',
            message: `Delete rate <strong>${name}</strong>? Bookings already priced won't be affected, only future calculations.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(`/admin/room-rates/${id}`, {
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

    return { openCreate, openEdit, confirmDelete };
})();
</script>
@endpush