@extends('layouts.admin.app')

@section('title', 'Facilities')
@section('page-title', 'Facilities')
@section('page-subtitle', 'Manage hotel facilities and services shown to guests')

@section('content')
<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">Facilities</div>
        <div class="table-toolbar-actions">
            <div class="search-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="facilitySearch" placeholder="Search facilities...">
            </div>
            <button type="button" class="btn btn-primary" onclick="FacilityPage.openCreate()">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Facility
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:56px;"></th>
                <th>Name</th>
                <th>Pricing</th>
                <th>Sort</th>
                <th>Status</th>
                <th style="width:160px;">Actions</th>
            </tr>
        </thead>
        <tbody id="facilitiesTbody">
            @forelse ($facilities as $facility)
                <tr data-id="{{ $facility->id }}">
                    <td>
                        @if ($facility->image)
                            <img src="{{ $facility->image_url }}" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                        @else
                            <div style="width:40px;height:40px;border-radius:8px;background:var(--light);border:1px solid var(--border);"></div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $facility->name }}</strong>
                        @if ($facility->description)
                            <div style="font-size:0.75rem;color:var(--text-muted);max-width:320px;">{{ Str::limit($facility->description, 80) }}</div>
                        @endif
                    </td>
                    <td>
                        @if ($facility->pricing_type === 'free')
                            <span class="badge badge-green">Free</span>
                        @elseif ($facility->pricing_type === 'fixed')
                            <span class="badge badge-blue">{{ number_format($facility->price, 2) }} BDT</span>
                        @else
                            <span class="badge badge-gold">On Request</span>
                        @endif
                    </td>
                    <td>{{ $facility->sort_order }}</td>
                    <td>
                        <button type="button" class="toggle-switch {{ $facility->is_active ? 'is-on' : '' }}"
                            role="switch" aria-checked="{{ $facility->is_active ? 'true' : 'false' }}"
                            data-endpoint="{{ route('admin.facilities.toggle', $facility) }}" data-state-key="is_active">
                            <span class="toggle-knob"></span>
                        </button>
                        <span class="toggle-label" data-on="Active" data-off="Inactive">{{ $facility->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="FacilityPage.openEdit({{ $facility->id }})">Edit</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="FacilityPage.confirmDelete({{ $facility->id }}, '{{ addslashes($facility->name) }}')">Delete</button>
                    </td>
                </tr>
            @empty
                {{-- handled by JS below if empty --}}
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@if ($facilities->isEmpty())
    <script>document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('facilitiesTbody').innerHTML = Loader.emptyRow(6, 'No facilities added yet.');
    });</script>
@endif

<script>
const FacilityPage = (() => {
    const facilities = @json($facilities->keyBy('id'));
    const routes = {
        store: '{{ route('admin.facilities.store') }}',
        update: (id) => `/admin/facilities/${id}`,
        destroy: (id) => `/admin/facilities/${id}`,
    };

    function pricingFieldsHtml(f = {}) {
        return `
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="facName" value="${f.name ?? ''}" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="facDescription" rows="3">${f.description ?? ''}</textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Pricing Type</label>
                    <select name="pricing_type" id="facPricingType" onchange="FacilityPage.togglePriceField()">
                        <option value="free" ${f.pricing_type === 'free' ? 'selected' : ''}>Free</option>
                        <option value="fixed" ${f.pricing_type === 'fixed' ? 'selected' : ''}>Fixed Price</option>
                        <option value="on_request" ${(!f.pricing_type || f.pricing_type === 'on_request') ? 'selected' : ''}>On Request</option>
                    </select>
                </div>
                <div class="form-group" id="facPriceGroup" style="display:${f.pricing_type === 'fixed' ? 'block' : 'none'};">
                    <label>Price (BDT)</label>
                    <input type="number" step="0.01" min="0" name="price" id="facPrice" value="${f.price ?? ''}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" min="0" name="sort_order" value="${f.sort_order ?? 0}">
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*">
                    ${f.image_url ? `
                        <div class="form-check" style="margin-top:0.5rem;">
                            <input type="checkbox" id="removeImageChk" name="remove_image" value="1">
                            <label for="removeImageChk">Remove current image</label>
                        </div>` : ''}
                </div>
            </div>
            <div class="form-check" style="margin-top:0.4rem;">
                <input type="checkbox" id="facIsActive" name="is_active" ${(f.is_active ?? true) ? 'checked' : ''}>
                <label for="facIsActive">Active</label>
            </div>`;
    }

    function togglePriceField() {
        const type = document.getElementById('facPricingType').value;
        document.getElementById('facPriceGroup').style.display = type === 'fixed' ? 'block' : 'none';
    }

    function openCreate() {
        const body = `<form id="facilityForm" enctype="multipart/form-data">${pricingFieldsHtml()}</form>`;
        const footer = `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="facSaveBtn" onclick="FacilityPage.submit()">Save Facility</button>`;
        Modal.open('Add Facility', body, footer);
    }

    function openEdit(id) {
        const f = facilities[id];
        const body = `<form id="facilityForm" enctype="multipart/form-data">${pricingFieldsHtml(f)}</form>`;
        const footer = `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="facSaveBtn" onclick="FacilityPage.submit(${id})">Update Facility</button>`;
        Modal.open('Edit Facility', body, footer);
    }

    async function submit(id = null) {
        const form = document.getElementById('facilityForm');
        const btn = document.getElementById('facSaveBtn');
        const formData = new FormData(form);
        if (!formData.get('is_active')) formData.set('is_active', '0');

        Modal.setSubmitting(btn, true, id ? 'Updating...' : 'Saving...');
        try {
            const res = await fetch(id ? routes.update(id) : routes.store, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || Object.values(json.errors || {})[0]?.[0] || 'Something went wrong.');
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
            title: 'Delete Facility?',
            message: `Are you sure you want to delete "${name}"? This cannot be undone.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(routes.destroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Delete failed.');
                Toast.show(json.message, 'success');
                setTimeout(() => window.location.reload(), 500);
            }
        });
    }

    return { openCreate, openEdit, submit, confirmDelete, togglePriceField };
})();

LiveSearch.attach(document.getElementById('facilitySearch'), '#facilitiesTbody', { emptyColCount: 6 });
</script>
@endpush