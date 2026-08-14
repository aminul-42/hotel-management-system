@extends('layouts.admin.app')

@section('title', 'Customers')
@section('page-title', 'Guests')
@section('page-subtitle', 'View registered guests and their booking history')

@section('content')
<div class="table-wrap">
    <div class="table-toolbar">
        <div class="table-toolbar-title">Guests</div>
        <div class="table-toolbar-actions">
            <div class="search-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="customerSearch" placeholder="Search by name, email, phone...">
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>NID/Passport</th>
                <th>Bookings</th>
                <th>Joined</th>
                <th>Status</th>
                <th style="width:110px;">Actions</th>
            </tr>
        </thead>
        <tbody id="customersTbody">
            @forelse ($customers as $customer)
                <tr data-id="{{ $customer->id }}">
                    <td><strong>{{ $customer->name }}</strong></td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone ?? '—' }}</td>
                    <td>{{ $customer->nid_passport_number ?? '—' }}</td>
                    <td><span class="badge badge-blue">{{ $customer->bookings_count }}</span></td>
                    <td>{{ $customer->created_at->format('d M Y') }}</td>
                    <td>
                        <button type="button" class="toggle-switch {{ $customer->is_active ? 'is-on' : '' }}"
                            role="switch" aria-checked="{{ $customer->is_active ? 'true' : 'false' }}"
                            data-endpoint="{{ route('admin.customers.toggle', $customer) }}" data-state-key="is_active">
                            <span class="toggle-knob"></span>
                        </button>
                        <span class="toggle-label" data-on="Active" data-off="Blocked">{{ $customer->is_active ? 'Active' : 'Blocked' }}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="CustomerPage.viewHistory({{ $customer->id }})">View</button>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
@if ($customers->isEmpty())
<script>document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('customersTbody').innerHTML = Loader.emptyRow(8, 'No registered guests yet.');
});</script>
@endif

<script>
const CustomerPage = (() => {
    async function viewHistory(id) {
        Modal.open('Guest Details', `<div class="inline-spinner"><span class="dot-spinner"></span> Loading...</div>`, '');

        try {
            const res = await fetch(`/admin/customers/${id}`, {
                headers: { 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to load guest details.');

            const c = json.customer;
            const bookings = json.bookings;

            let bookingsHtml = '';
            if (bookings.length === 0) {
                bookingsHtml = `<p class="hint">No bookings yet.</p>`;
            } else {
                bookingsHtml = `
                    <table style="width:100%;">
                        <thead>
                            <tr>
                                <th style="text-align:left;font-size:0.7rem;color:var(--text-muted);padding:0.4rem 0.5rem;">Ref</th>
                                <th style="text-align:left;font-size:0.7rem;color:var(--text-muted);padding:0.4rem 0.5rem;">Room</th>
                                <th style="text-align:left;font-size:0.7rem;color:var(--text-muted);padding:0.4rem 0.5rem;">Dates</th>
                                <th style="text-align:left;font-size:0.7rem;color:var(--text-muted);padding:0.4rem 0.5rem;">Status</th>
                                <th style="text-align:right;font-size:0.7rem;color:var(--text-muted);padding:0.4rem 0.5rem;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${bookings.map(b => `
                                <tr style="border-top:1px solid var(--border);">
                                    <td style="padding:0.5rem;font-size:0.82rem;">${b.booking_reference}</td>
                                    <td style="padding:0.5rem;font-size:0.82rem;">${b.room ?? '—'} <span class="hint">(${b.room_type ?? ''})</span></td>
                                    <td style="padding:0.5rem;font-size:0.82rem;">${b.check_in} → ${b.check_out}</td>
                                    <td style="padding:0.5rem;"><span class="badge badge-gold">${b.status}</span></td>
                                    <td style="padding:0.5rem;font-size:0.82rem;text-align:right;">${Number(b.total_amount).toFixed(2)}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>`;
            }

            const body = `
                <div style="margin-bottom:1.2rem;">
                    <div style="font-family:var(--font-display);font-size:1.15rem;font-weight:600;">${c.name}</div>
                    <div class="hint">${c.email} &nbsp;•&nbsp; ${c.phone ?? 'No phone'}</div>
                    <div class="hint">NID/Passport: ${c.nid_passport_number ?? '—'}</div>
                </div>
                <div style="font-weight:600;font-size:0.85rem;margin-bottom:0.5rem;">Booking History</div>
                ${bookingsHtml}`;

            Modal.open('Guest Details', body, `<button type="button" class="btn btn-secondary" onclick="Modal.close()">Close</button>`);
        } catch (err) {
            Modal.close();
            Toast.show(err.message, 'error');
        }
    }

    return { viewHistory };
})();

LiveSearch.attach(document.getElementById('customerSearch'), '#customersTbody', { emptyColCount: 8 });
</script>
@endpush