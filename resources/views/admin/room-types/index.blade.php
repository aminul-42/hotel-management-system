@extends('layouts.admin.app')

@section('title', 'Room Types')
@section('page-title', 'Room Types')
@section('page-subtitle', 'Manage the room categories guests can browse and book')

@section('content')

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">All Room Types</div>
        <div class="table-toolbar-actions">
            <div class="search-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="rtSearchInput" placeholder="Search room types...">
            </div>
            <button type="button" class="btn btn-primary" id="btnAddRoomType">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Room Type
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Capacity</th>
                <th>Amenities</th>
                <th>Rooms</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="rtTableBody"></tbody>
    </table>
</div>

@endsection

@push('styles')
<style>
    .rt-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: var(--radius-sm); background: var(--light); display: block; }
    .rt-thumb-placeholder { width: 52px; height: 52px; border-radius: var(--radius-sm); background: var(--light); border: 1px dashed var(--border); }
    .rt-amenity-tag { display: inline-block; background: var(--secondary-tint); color: var(--secondary); padding: 2px 8px; border-radius: 5px; font-size: 11px; font-weight: 600; margin: 1px; }
    .rt-capacity { font-variant-numeric: tabular-nums; color: var(--text-muted); font-size: 0.82rem; }

    /* Modal-only styles (form layout inside the smart Modal) */
    .rt-existing-images { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
    .rt-existing-img-wrap { position: relative; width: 62px; height: 62px; }
    .rt-existing-img-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border); }
    .rt-existing-img-wrap .rt-remove-img {
        position: absolute; top: -6px; right: -6px; background: var(--danger); color: #fff; border: none;
        border-radius: 50%; width: 19px; height: 19px; font-size: 12px; cursor: pointer; line-height: 1;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    let allRoomTypes = [];
    let currentImages = [];   // [{path, url}] for the room type currently open in the modal
    let removedPaths = [];

    const tbody = document.getElementById('rtTableBody');

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // ── Load / render table ─────────────────────────────────────────
    function loadTable() {
        tbody.innerHTML = Loader.skeletonRows(7, 4);

        fetch('{{ route('admin.room-types.data') }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(json => {
                allRoomTypes = json.data;
                renderTable(allRoomTypes);
            })
            .catch(() => {
                tbody.innerHTML = Loader.emptyRow(7, 'Failed to load room types. Please refresh.');
            });
    }

    function renderTable(rows) {
        if (!rows.length) {
            tbody.innerHTML = Loader.emptyRow(7, 'No room types yet — add your first one.');
            return;
        }

        tbody.innerHTML = rows.map((rt) => {
            const thumb = rt.image_urls && rt.image_urls.length
                ? `<img src="${rt.image_urls[0]}" class="rt-thumb">`
                : `<div class="rt-thumb-placeholder"></div>`;
            const amenities = (rt.amenities || []).map(a => `<span class="rt-amenity-tag">${escapeHtml(a)}</span>`).join(' ') || '<span style="color:var(--text-muted)">—</span>';

            return `
                <tr data-id="${rt.id}">
                    <td>${thumb}</td>
                    <td><strong>${escapeHtml(rt.name)}</strong></td>
                    <td class="rt-capacity">${rt.base_capacity}–${rt.max_capacity} guests</td>
                    <td>${amenities}</td>
                    <td>${rt.rooms_count}</td>
                    <td>
                        <button type="button" class="toggle-switch ${rt.is_active ? 'is-on' : ''}" role="switch"
                                aria-checked="${rt.is_active ? 'true' : 'false'}"
                                data-endpoint="/admin/room-types/${rt.id}/toggle" data-state-key="is_active">
                            <span class="toggle-knob"></span>
                        </button>
                        <span class="toggle-label" data-on="Active" data-off="Inactive">${rt.is_active ? 'Active' : 'Inactive'}</span>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm rt-edit-btn" data-id="${rt.id}">Edit</button>
                        <button class="btn btn-danger btn-sm rt-delete-btn" data-id="${rt.id}">Delete</button>
                    </td>
                </tr>`;
        }).join('');

        tbody.querySelectorAll('.rt-edit-btn').forEach(btn => btn.addEventListener('click', () => openEditModal(btn.dataset.id)));
        tbody.querySelectorAll('.rt-delete-btn').forEach(btn => btn.addEventListener('click', () => confirmDelete(btn.dataset.id)));
        ToggleSwitch.init(tbody);
    }

    // ── Modal: add / edit form ──────────────────────────────────────
    function formHtml(rt = null) {
        const amenitiesValue = rt ? (rt.amenities || []).join(', ') : '';
        return `
            <form id="rtForm">
                <div class="form-group">
                    <label for="rt_name">Name</label>
                    <input type="text" id="rt_name" name="name" required maxlength="255" value="${rt ? escapeHtml(rt.name) : ''}">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rt_base_capacity">Base Capacity</label>
                        <input type="number" id="rt_base_capacity" name="base_capacity" min="1" required value="${rt ? rt.base_capacity : ''}">
                    </div>
                    <div class="form-group">
                        <label for="rt_max_capacity">Max Capacity</label>
                        <input type="number" id="rt_max_capacity" name="max_capacity" min="1" required value="${rt ? rt.max_capacity : ''}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="rt_description">Description</label>
                    <textarea id="rt_description" name="description" rows="3">${rt ? escapeHtml(rt.description) : ''}</textarea>
                </div>
                <div class="form-group">
                    <label for="rt_amenities">Amenities</label>
                    <input type="text" id="rt_amenities" name="amenities" placeholder="AC, Wi-Fi, Mini Bar, City View" value="${escapeHtml(amenitiesValue)}">
                    <div class="hint">Comma separated</div>
                </div>
                <div class="form-group">
                    <label>Existing Images</label>
                    <div class="rt-existing-images" id="rtExistingImages"></div>
                </div>
                <div class="form-group">
                    <label for="rt_images">Upload Images</label>
                    <input type="file" id="rt_images" name="images[]" multiple accept="image/*">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="rt_is_active" name="is_active" ${(!rt || rt.is_active) ? 'checked' : ''}>
                    <label for="rt_is_active" style="margin-bottom:0">Active (visible to customers)</label>
                </div>
            </form>`;
    }

    function footerHtml() {
        return `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="rtSaveBtn">Save Room Type</button>`;
    }

    function renderExistingImages() {
        const wrap = document.getElementById('rtExistingImages');
        if (!wrap) return;
        wrap.innerHTML = currentImages.map(img => `
            <div class="rt-existing-img-wrap">
                <img src="${img.url}">
                <button type="button" class="rt-remove-img" data-path="${img.path}">&times;</button>
            </div>`).join('') || '<span class="hint">No images uploaded yet.</span>';

        wrap.querySelectorAll('.rt-remove-img').forEach(btn => {
            btn.addEventListener('click', function () {
                removedPaths.push(this.dataset.path);
                currentImages = currentImages.filter(img => img.path !== this.dataset.path);
                renderExistingImages();
            });
        });
    }

    function openAddModal() {
        currentImages = [];
        removedPaths = [];
        Modal.open('Add Room Type', formHtml(), footerHtml());
        renderExistingImages();
        bindSave(null);
    }

    function openEditModal(id) {
        const rt = allRoomTypes.find(r => String(r.id) === String(id));
        if (!rt) return;

        currentImages = (rt.images || []).map((path, i) => ({ path, url: rt.image_urls[i] }));
        removedPaths = [];
        Modal.open('Edit Room Type', formHtml(rt), footerHtml());
        renderExistingImages();
        bindSave(rt.id);
    }

    function bindSave(id) {
        const saveBtn = document.getElementById('rtSaveBtn');
        saveBtn.addEventListener('click', function () {
            const form = document.getElementById('rtForm');
            if (!form.reportValidity()) return;

            const formData = new FormData(form);
            removedPaths.forEach(path => formData.append('removed_images[]', path));
            if (!document.getElementById('rt_is_active').checked) {
                formData.set('is_active', '0');
            }

            const url = id ? `/admin/room-types/${id}` : '{{ route('admin.room-types.store') }}';

            Modal.setSubmitting(saveBtn, true, 'Saving...');
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
                .then(async (res) => {
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Failed to save room type.');
                    Toast.show(json.message, 'success');
                    Modal.close();
                    loadTable();
                })
                .catch(err => {
                    Modal.setSubmitting(saveBtn, false);
                    Toast.show(err.message, 'error');
                });
        });
    }

    function confirmDelete(id) {
        const rt = allRoomTypes.find(r => String(r.id) === String(id));
        Modal.confirm({
            title: 'Delete room type?',
            message: `This will permanently remove <strong>${escapeHtml(rt ? rt.name : 'this room type')}</strong>. This cannot be undone.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(`/admin/room-types/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Delete failed.');
                Toast.show(json.message, 'success');
                loadTable();
            }
        });
    }

    document.getElementById('btnAddRoomType').addEventListener('click', openAddModal);
    LiveSearch.attach(document.getElementById('rtSearchInput'), '#rtTableBody', { emptyColCount: 7 });

    loadTable();
})();
</script>
@endpush