@extends('layouts.customer.app')

@section('title', 'My Bookings — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Your Account</p>
        <h1 class="pb-title">My Bookings</h1>
        <p class="pb-subtitle">A record of every stay you've made with us.</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

@if ($bookings->isEmpty())
    <div class="mb-empty">
        <p>You don't have any bookings yet.</p>
        <a href="{{ route('customer.rooms.index') }}" class="btn btn-primary">Browse Rooms</a>
    </div>
@else
    <div class="mb-table-wrap">
        <table class="mb-table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Room Type</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $b)
                    <tr>
                        <td class="mb-ref">{{ $b->booking_reference }}</td>
                        <td>{{ $b->roomType->name }}</td>
                        <td>{{ $b->check_in->format('d M Y') }} → {{ $b->check_out->format('d M Y') }}</td>
                        <td><span class="mb-status mb-status-{{ $b->status }}">{{ ucfirst(str_replace('_', ' ', $b->status)) }}</span></td>
                        <td>৳{{ number_format($b->total_amount, 2) }}</td>
                        <td><a href="{{ route('customer.bookings.show', $b->id) }}" class="btn btn-secondary btn-sm">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mb-pagination">
        {{ $bookings->links('pagination::bootstrap-4') }}
    </div>
@endif

</div>
@endsection

@push('styles')
<style>
    .page-banner { position: relative; height: 320px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .page-banner-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(30,31,34,0.5) 0%, rgba(30,31,34,0.78) 100%); }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow { font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500; letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem; }
    .pb-title { font-family: var(--font-display); font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.85rem; color: #e7e3da; margin-top: 0.8rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 240px; } }

    .mb-empty { text-align: center; padding: 4rem 1rem; background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-sm); margin-top: 2.5rem; }
    .mb-empty p { color: var(--text-muted); margin-bottom: 1.25rem; }

    .mb-table-wrap { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); overflow-x: auto; box-shadow: var(--shadow-sm); margin-top: 2.5rem; }
    .mb-table { width: 100%; border-collapse: collapse; min-width: 640px; }
    .mb-table th { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); padding: 0.85rem 1.2rem; background: #fbfaf7; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
    .mb-table td { padding: 0.9rem 1.2rem; font-size: 0.85rem; border-bottom: 1px solid #f2efe9; white-space: nowrap; }
    .mb-table tr:last-child td { border-bottom: none; }
    .mb-table tr:hover td { background: #fdfcfa; }
    .mb-ref { font-weight: 700; color: var(--primary); }

    .mb-status { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 20px; text-transform: capitalize; }
    .mb-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .mb-status-pending { background: var(--secondary-tint); color: var(--secondary); }
    .mb-status-confirmed { background: var(--info-bg, #E8EEF3); color: var(--info, #3A5A78); }
    .mb-status-checked_in { background: var(--success-bg); color: var(--success); }
    .mb-status-checked_out { background: #eeece6; color: var(--text-muted); }
    .mb-status-cancelled { background: var(--danger-bg); color: var(--danger); }
    .mb-status-no_show { background: var(--danger-bg); color: var(--danger); }

    .btn-sm { padding: 0.45rem 0.9rem; font-size: 0.78rem; border-radius: var(--radius-sm); }
    .btn-secondary { background: var(--light); color: var(--dark); border: 1px solid var(--border); }
    .btn-secondary:hover { background: #f1efe9; }

    .mb-pagination { display: flex; justify-content: center; margin-top: 1.75rem; }
    .mb-pagination .pagination { display: flex; list-style: none; gap: 0.35rem; }
    .mb-pagination .page-link { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--border); background: var(--white); color: var(--dark); font-size: 0.82rem; text-decoration: none; }
    .mb-pagination .page-item.active .page-link { background: var(--primary); border-color: var(--primary); color: var(--white); }
    .mb-pagination .page-item.disabled .page-link { color: var(--text-muted); opacity: 0.5; }
    .mb-pagination .page-link:hover:not(.active) { background: var(--light); }

    @media (max-width: 700px) {
        .mb-table th, .mb-table td { padding: 0.75rem 0.9rem; }
    }
</style>
@endpush