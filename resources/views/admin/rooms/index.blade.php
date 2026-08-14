@extends('layouts.admin.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')
@section('page-subtitle', 'Manage individual rooms and their housekeeping status')

@section('content')

<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">All Rooms</div>
        <div class="table-toolbar-actions">
            <select id="filterRoomType">
                <option value="">All Room Types</option>
                @foreach($roomTypes as $rt)
                    <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                @endforeach
            </select>
            <select id="filterStatus">
                <option value="">All Statuses</option>
                <option value="clean">Clean</option>
                <option value="dirty">Dirty</option>
                <option value="occupied">Occupied</option>
                <option value="blocked">Blocked</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <div class="search-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="rmSearchInput" placeholder="Search rooms...">
            </div>
            <button type="button" class="btn btn-primary" id="btnAddRoom">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Room
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Room #</th>
                <th>Floor</th>
                <th>Room Type</th>
                <th>Status</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="rmTableBody"></tbody>
    </table>
</div>

@endsection

@push('styles')
<style>
    .rm-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: var(--radius-sm); display: block; }
    .rm-thumb-placeholder { width: 52px; height: 52px; border-radius: var(--radius-sm); background: var(--light); border: 1px dashed var(--border); }

    .table-toolbar-actions select {
        padding: 0.5rem 0.75rem; border: 1.5px solid var(--border); border-radius: 20px;
        font-size: 0.83rem; font-family: var(--font-body); color: var(--dark); background: var(--light);
    }

    .rm-existing-images { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 4px; }
    .rm-existing-img-wrap { position: relative; width: 62px; height: 62px; }
    .rm-existing-img-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border); }
    .rm-existing-img-wrap .rm-remove-img {
        position: absolute; top: -6px; right: -6px; background: var(--danger); color: #fff; border: none;
        border-radius: 50%; width: 19px; height: 19px; font-size: 12px; cursor: pointer; line-height: 1;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    let allRooms = [];
    let currentImages = [];
    let removedPaths = [];

    const tbody = document.getElementById('rmTableBody');
    const roomTypeOptions = @json($roomTypes->map(fn($rt) => ['id' => $rt->id, 'name' => $rt->name]));

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    const statusColors = {
        clean: '#2F6F4F', dirty: '#97711F', occupied: '#A5352C', blocked: '#756F63', maintenance: '#7D1A34',
    };
    const statusOptions = ['clean', 'dirty', 'occupied', 'blocked', 'maintenance']
        .map(v => ({ value: v, label: v.charAt(0).toUpperCase() + v.slice(1), color: statusColors[v] }));

    // ── Load / render table ─────────────────────────────────────────
    function loadTable() {
        tbody.innerHTML = Loader.skeletonRows(7, 4);

        const params = new URLSearchParams();
        const typeVal = document.getElementById('filterRoomType').value;
        const statusVal = document.getElementById('filterStatus').value;
        if (typeVal) params.append('room_type_id', typeVal);
        if (statusVal) params.append('status', statusVal);

        fetch(`{{ route('admin.rooms.data') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(json => { allRooms = json.data; renderTable(allRooms); })
            .catch(() => { tbody.innerHTML = Loader.emptyRow(7, 'Failed to load rooms. Please refresh.'); });
    }

    function renderTable(rows) {
        if (!rows.length) {
            tbody.innerHTML = Loader.emptyRow(7, 'No rooms match these filters.');
            return;
        }

        tbody.innerHTML = rows.map((room) => {
            const thumb = room.image_urls && room.image_urls.length
                ? `<img src="${room.image_urls[0]}" class="rm-thumb">`
                : `<div class="rm-thumb-placeholder"></div>`;
            const activeBadge = room.is_active
                ? '<span class="badge badge-green">Active</span>'
                : '<span class="badge badge-gray">Inactive</span>';

            return `
                <tr data-id="${room.id}">
                    <td>${thumb}</td>
                    <td><strong>${escapeHtml(room.room_number)}</strong></td>
                    <td>${room.floor}</td>
                    <td>${escapeHtml(room.room_type)}</td>
                    <td>
                        <span class="status-chip"
                              data-current="${room.status}"
                              data-endpoint="/admin/rooms/${room.id}/status"
                              data-payload-key="status"
                              data-options='${JSON.stringify(statusOptions)}'>
                        </span>
                    </td>
                    <td>${activeBadge}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm rm-edit-btn" data-id="${room.id}">Edit</button>
                        <button class="btn btn-danger btn-sm rm-delete-btn" data-id="${room.id}">Delete</button>
                    </td>
                </tr>`;
        }).join('');

        tbody.querySelectorAll('.rm-edit-btn').forEach(btn => btn.addEventListener('click', () => openEditModal(btn.dataset.id)));
        tbody.querySelectorAll('.rm-delete-btn').forEach(btn => btn.addEventListener('click', () => confirmDelete(btn.dataset.id)));
        StatusChip.init(tbody);
    }

    document.getElementById('filterRoomType').addEventListener('change', loadTable);
    document.getElementById('filterStatus').addEventListener('change', loadTable);

    // ── Modal: add / edit form ──────────────────────────────────────
    function roomTypeSelectHtml(selectedId) {
        return roomTypeOptions.map(rt =>
            `<option value="${rt.id}" ${String(rt.id) === String(selectedId) ? 'selected' : ''}>${escapeHtml(rt.name)}</option>`
        ).join('');
    }

    function statusSelectHtml(selected) {
        return ['clean', 'dirty', 'occupied', 'blocked', 'maintenance'].map(s =>
            `<option value="${s}" ${s === selected ? 'selected' : ''}>${s.charAt(0).toUpperCase() + s.slice(1)}</option>`
        ).join('');
    }

    function formHtml(room = null) {
        return `
            <form id="rmForm">
                <div class="form-group">
                    <label for="rm_room_type_id">Room Type</label>
                    <select id="rm_room_type_id" name="room_type_id" required>
                        <option value="">Select room type</option>
                        ${roomTypeSelectHtml(room ? room.room_type_id : null)}
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rm_room_number">Room Number</label>
                        <input type="text" id="rm_room_number" name="room_number" required maxlength="20" value="${room ? escapeHtml(room.room_number) : ''}">
                    </div>
                    <div class="form-group">
                        <label for="rm_floor">Floor</label>
                        <input type="number" id="rm_floor" name="floor" min="0" required value="${room ? room.floor : ''}">
                    </div>
                </div>
                <div class="form-group">
                    <label for="rm_status">Status</label>
                    <select id="rm_status" name="status" required>
                        ${statusSelectHtml(room ? room.status : 'clean')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Existing Images</label>
                    <div class="rm-existing-images" id="rmExistingImages"></div>
                </div>
                <div class="form-group">
                    <label for="rm_images">Upload Images</label>
                    <input type="file" id="rm_images" name="images[]" multiple accept="image/*">
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" id="rm_is_active" name="is_active" ${(!room || room.is_active) ? 'checked' : ''}>
                    <label for="rm_is_active" style="margin-bottom:0">Active (bookable)</label>
                </div>
            </form>`;
    }

    function footerHtml() {
        return `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="rmSaveBtn">Save Room</button>`;
    }

    function renderExistingImages() {
        const wrap = document.getElementById('rmExistingImages');
        if (!wrap) return;
        wrap.innerHTML = currentImages.map(img => `
            <div class="rm-existing-img-wrap">
                <img src="${img.url}">
                <button type="button" class="rm-remove-img" data-path="${img.path}">&times;</button>
            </div>`).join('') || '<span class="hint">No images uploaded yet.</span>';

        wrap.querySelectorAll('.rm-remove-img').forEach(btn => {
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
        Modal.open('Add Room', formHtml(), footerHtml());
        renderExistingImages();
        bindSave(null);
    }

    function openEditModal(id) {
        const room = allRooms.find(r => String(r.id) === String(id));
        if (!room) return;

        currentImages = (room.images || []).map((path, i) => ({ path, url: room.image_urls[i] }));
        removedPaths = [];
        Modal.open('Edit Room', formHtml(room), footerHtml());
        renderExistingImages();
        bindSave(room.id);
    }

    function bindSave(id) {
        const saveBtn = document.getElementById('rmSaveBtn');
        saveBtn.addEventListener('click', function () {
            const form = document.getElementById('rmForm');
            if (!form.reportValidity()) return;

            const formData = new FormData(form);
            removedPaths.forEach(path => formData.append('removed_images[]', path));
            if (!document.getElementById('rm_is_active').checked) {
                formData.set('is_active', '0');
            }

            const url = id ? `/admin/rooms/${id}` : '{{ route('admin.rooms.store') }}';

            Modal.setSubmitting(saveBtn, true, 'Saving...');
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
                .then(async (res) => {
                    const json = await res.json();
                    if (!res.ok) throw new Error(json.message || 'Failed to save room.');
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
        const room = allRooms.find(r => String(r.id) === String(id));
        Modal.confirm({
            title: 'Delete room?',
            message: `This will permanently remove room <strong>${escapeHtml(room ? room.room_number : '')}</strong>. This cannot be undone.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(`/admin/rooms/${id}`, {
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

    document.getElementById('btnAddRoom').addEventListener('click', openAddModal);
    LiveSearch.attach(document.getElementById('rmSearchInput'), '#rmTableBody', { emptyColCount: 7 });

    loadTable();
})();
</script>
@endpush