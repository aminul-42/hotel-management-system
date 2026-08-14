@extends('layouts.customer.app')

@section('title', 'Rooms & Suites — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Find Your Stay</p>
        <h1 class="pb-title">Rooms &amp; Suites</h1>
        <p class="pb-subtitle">Search by date to see live pricing and availability across every room type.</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<form method="GET" action="{{ route('customer.rooms.index') }}" class="search-bar">
    <div class="sb-field">
        <label for="s_check_in">Check-in</label>
        <input type="date" name="check_in" id="s_check_in" value="{{ $search['checkIn'] }}">
    </div>
    <div class="sb-field">
        <label for="s_check_out">Check-out</label>
        <input type="date" name="check_out" id="s_check_out" value="{{ $search['checkOut'] }}">
    </div>
    <div class="sb-field sb-field-sm">
        <label for="s_adults">Adults</label>
        <input type="number" name="adults" id="s_adults" min="1" value="{{ $search['adults'] }}">
    </div>
    <div class="sb-field sb-field-sm">
        <label for="s_children">Children</label>
        <input type="number" name="children" id="s_children" min="0" value="{{ $search['children'] }}">
    </div>
    <button type="submit" class="sb-submit">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Search
    </button>
</form>

@if (empty($results))
    <p class="empty-note">No room types found. Please adjust your search.</p>
@else
    <div class="room-result-grid">
        @foreach ($results as $r)
            @php
                $rt = $r['room_type'];
                $img = collect($rt->image_urls ?? [])->first();
                $qs = http_build_query([
                    'check_in' => $search['checkIn'], 'check_out' => $search['checkOut'],
                    'adults' => $search['adults'], 'children' => $search['children'],
                ]);
            @endphp
            <div class="room-result-card {{ !$r['fits_guests'] || $r['available_rooms'] < 1 ? 'is-unavailable' : '' }}">
                <div class="rr-image">
                    @if ($img)
                        <img src="{{ $img }}" alt="{{ $rt->name }}" loading="lazy">
                    @else
                        <div class="rr-placeholder"></div>
                    @endif
                    @if ($r['available_rooms'] > 0 && $r['fits_guests'])
                        <span class="rr-badge rr-badge-green">{{ $r['available_rooms'] }} Available</span>
                    @elseif (!$r['fits_guests'])
                        <span class="rr-badge rr-badge-amber">Exceeds Capacity</span>
                    @else
                        <span class="rr-badge rr-badge-red">Sold Out</span>
                    @endif
                </div>
                <div class="rr-body">
                    <h3>{{ $rt->name }}</h3>
                    <p class="rr-meta">Up to {{ $rt->max_capacity }} guests</p>
                    <div class="rr-price-row">
                        @if (!$r['fits_guests'])
                            <span class="rr-price-note">Doesn't fit your guest count</span>
                        @elseif ($r['total_price'] !== null)
                            <div>
                                <strong>৳{{ number_format($r['total_price'], 2) }}</strong>
                                <span class="rr-nights">/ {{ $r['nights'] }} nights</span>
                            </div>
                        @else
                            <span class="rr-price-note">Not available for these dates</span>
                        @endif
                        <a href="{{ route('customer.rooms.show', $rt->slug) }}?{{ $qs }}" class="btn btn-primary btn-sm">View</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

</div>
@endsection

@push('styles')
<style>
    /* ── Hero banner ──────────────────────────────────────────── */
    .page-banner {
        position: relative; height: 360px; background-size: cover; background-position: center;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .page-banner-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(30,31,34,0.5) 0%, rgba(30,31,34,0.72) 100%);
    }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow {
        font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500;
        letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem;
    }
    .pb-title { font-family: var(--font-display); font-size: clamp(2rem, 4vw, 2.8rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.9rem; color: #e7e3da; margin-top: 0.9rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 260px; } }

    /* ── Search bar ───────────────────────────────────────────── */
    .search-bar {
        display: flex; flex-wrap: wrap; align-items: end; gap: 1rem;
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 1.4rem 1.5rem; box-shadow: var(--shadow-md);
        margin: -3.5rem auto 2.5rem; position: relative; z-index: 3; max-width: 960px;
    }
    .sb-field { display: flex; flex-direction: column; gap: 0.35rem; flex: 1 1 160px; min-width: 130px; }
    .sb-field-sm { flex: 0 1 100px; min-width: 85px; }
    .sb-field label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
    .sb-field input {
        padding: 0.6rem 0.75rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: 0.85rem; font-family: var(--font-body); color: var(--dark); background: var(--light);
        outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .sb-field input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-tint); background: var(--white); }
    .sb-submit {
        display: inline-flex; align-items: center; gap: 0.45rem; background: var(--primary); color: var(--white);
        border: none; cursor: pointer; padding: 0.68rem 1.3rem; border-radius: var(--radius-sm);
        font-size: 0.85rem; font-weight: 700; white-space: nowrap; transition: background 0.15s;
    }
    .sb-submit svg { width: 15px; height: 15px; }
    .sb-submit:hover { background: var(--primary-dark); }

    .empty-note { text-align: center; color: var(--text-muted); padding: 2.5rem 0; }

    .room-result-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
    .room-result-card {
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow 0.2s, transform 0.2s;
    }
    .room-result-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .room-result-card.is-unavailable { opacity: 0.7; }

    .rr-image { position: relative; height: 190px; background: var(--dark); }
    .rr-image img { width: 100%; height: 100%; object-fit: cover; }
    .rr-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-tint), var(--secondary-tint)); }
    .rr-badge {
        position: absolute; top: 0.75rem; right: 0.75rem;
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
        padding: 0.3rem 0.65rem; border-radius: 20px; color: var(--white);
    }
    .rr-badge-green { background: var(--success); }
    .rr-badge-amber { background: var(--warning, #97711F); }
    .rr-badge-red { background: var(--danger); }

    .rr-body { padding: 1.15rem 1.25rem 1.3rem; }
    .rr-body h3 { font-family: var(--font-display); font-size: 1.3rem; font-weight: 600; color: var(--dark); margin-bottom: 0.2rem; }
    .rr-meta { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 1rem; }
    .rr-price-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; }
    .rr-price-row strong { font-family: var(--font-display); font-size: 1.2rem; color: var(--primary); }
    .rr-nights { font-size: 0.72rem; color: var(--text-muted); }
    .rr-price-note { font-size: 0.78rem; color: var(--text-muted); font-style: italic; }

    .btn-sm { padding: 0.5rem 0.95rem; font-size: 0.78rem; border-radius: var(--radius-sm); }

    @media (max-width: 700px) {
        .search-bar { margin-top: -2rem; flex-direction: column; align-items: stretch; }
        .sb-field, .sb-field-sm { flex: 1 1 auto; }
    }
</style>
@endpush