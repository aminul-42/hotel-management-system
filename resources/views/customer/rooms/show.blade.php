@extends('layouts.customer.app')

@section('title', $roomType->name . ' — ' . config('app.name', 'Hotel'))

@php
    $images = collect($roomType->image_urls ?? []);
    $bannerImage = $images->first() ?? 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1920&auto=format&fit=crop';
@endphp

@section('hero')
<section class="page-banner" style="background-image:url('{{ $bannerImage }}')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Room Type</p>
        <h1 class="pb-title">{{ $roomType->name }}</h1>
        <p class="pb-subtitle">Capacity {{ $roomType->base_capacity }}–{{ $roomType->max_capacity }} guests</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="room-detail">
    <div class="rd-gallery">
        @if ($images->isNotEmpty())
            <div class="rd-gallery-main">
                <img src="{{ $images->first() }}" alt="{{ $roomType->name }}" id="rdMainImage">
            </div>
            @if ($images->count() > 1)
                <div class="rd-gallery-thumbs">
                    @foreach ($images as $i => $img)
                        <button type="button" class="rd-thumb {{ $i === 0 ? 'is-active' : '' }}" data-src="{{ $img }}">
                            <img src="{{ $img }}" alt="{{ $roomType->name }} view {{ $i + 1 }}" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        @else
            <div class="rd-gallery-placeholder"></div>
        @endif
    </div>

    <div class="rd-info">
        <p class="rd-description">{{ $roomType->description }}</p>

        <div class="rd-facts">
            <div class="rd-fact">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-4.13a4 4 0 11-4-4 4 4 0 014 4zm6 4a4 4 0 10-4-4"/></svg>
                {{ $roomType->base_capacity }}–{{ $roomType->max_capacity }} Guests
            </div>
            <div class="rd-fact">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Available: {{ $availableCount }} room(s)
            </div>
        </div>

        @if ($roomType->amenities)
            <div class="rd-amenities">
                <h3>Amenities</h3>
                <ul>
                    @foreach ((is_array($roomType->amenities) ? $roomType->amenities : json_decode($roomType->amenities, true)) ?? [] as $amenity)
                        <li>
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ $amenity }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rd-booking-card">
            @if ($pricing)
                <div class="rd-price-summary">
                    <span>Total for stay</span>
                    <strong>৳{{ number_format($pricing['total'], 2) }}</strong>
                    <small>{{ $pricing['nights'] }} nights</small>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.booking.start') }}" id="bookForm">
                @csrf
                <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                <div class="rd-form-row">
                    <label>Check-in <input type="date" name="check_in" id="bf_check_in" value="{{ $search['checkIn'] }}" required></label>
                    <label>Check-out <input type="date" name="check_out" id="bf_check_out" value="{{ $search['checkOut'] }}" required></label>
                </div>
                <div class="rd-form-row">
                    <label>Adults <input type="number" name="adults" min="1" value="{{ $search['adults'] }}" required></label>
                    <label>Children <input type="number" name="children" min="0" value="{{ $search['children'] }}"></label>
                </div>
                <button type="submit" class="btn btn-primary rd-submit">
                    Book This Room Type
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

</div>
@endsection

@push('styles')
<style>
    .page-banner {
        position: relative; height: 400px; background-size: cover; background-position: center;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .page-banner-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(30,31,34,0.4) 0%, rgba(30,31,34,0.75) 100%);
    }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow {
        font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500;
        letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem;
    }
    .pb-title { font-family: var(--font-display); font-size: clamp(2rem, 4vw, 2.8rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.9rem; color: #e7e3da; margin-top: 0.9rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 280px; } }

    .room-detail { display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 2.5rem; align-items: start; margin-top: 2.5rem; }

    .rd-gallery-main { border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow-sm); height: 420px; }
    .rd-gallery-main img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.2s ease; }
    .rd-gallery-placeholder { height: 420px; border-radius: var(--radius); background: linear-gradient(135deg, var(--primary-tint), var(--secondary-tint)); }
    .rd-gallery-thumbs { display: flex; gap: 0.6rem; margin-top: 0.75rem; flex-wrap: wrap; }
    .rd-thumb {
        width: 76px; height: 58px; border-radius: 8px; overflow: hidden; border: 2px solid transparent;
        cursor: pointer; padding: 0; opacity: 0.65; transition: opacity 0.15s, border-color 0.15s;
    }
    .rd-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .rd-thumb:hover, .rd-thumb.is-active { opacity: 1; border-color: var(--secondary); }

    .rd-description { font-size: 0.95rem; line-height: 1.7; color: var(--dark); margin-bottom: 1.5rem; }

    .rd-facts { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1.75rem; }
    .rd-fact { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: var(--text-muted); font-weight: 500; }
    .rd-fact svg { width: 17px; height: 17px; color: var(--secondary); flex-shrink: 0; }

    .rd-amenities { margin-bottom: 1.75rem; }
    .rd-amenities h3 { font-family: var(--font-display); font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem; }
    .rd-amenities ul { list-style: none; display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; }
    .rd-amenities li { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: var(--dark); }
    .rd-amenities li svg { width: 14px; height: 14px; color: var(--success); flex-shrink: 0; }

    .rd-booking-card {
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 1.5rem; box-shadow: var(--shadow-sm);
    }
    .rd-price-summary {
        display: flex; align-items: baseline; gap: 0.6rem; margin-bottom: 1.25rem;
        padding-bottom: 1.1rem; border-bottom: 1px solid var(--border);
    }
    .rd-price-summary span { font-size: 0.78rem; color: var(--text-muted); }
    .rd-price-summary strong { font-family: var(--font-display); font-size: 1.6rem; color: var(--primary); }
    .rd-price-summary small { font-size: 0.72rem; color: var(--text-muted); }

    .rd-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    .rd-form-row label { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #3a372f; }
    .rd-form-row input {
        padding: 0.6rem 0.75rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: 0.85rem; background: var(--light); outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .rd-form-row input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-tint); background: var(--white); }

    .rd-submit { width: 100%; justify-content: center; margin-top: 0.5rem; }
    .rd-submit svg { width: 15px; height: 15px; }

    @media (max-width: 900px) {
        .room-detail { grid-template-columns: 1fr; }
        .rd-gallery-main, .rd-gallery-placeholder { height: 300px; }
    }
    @media (max-width: 560px) {
        .rd-form-row { grid-template-columns: 1fr; }
        .rd-amenities ul { grid-template-columns: 1fr; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.rd-thumb').forEach(thumb => {
        thumb.addEventListener('click', function () {
            document.getElementById('rdMainImage').src = this.dataset.src;
            document.querySelectorAll('.rd-thumb').forEach(t => t.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });

    const bfIn = document.getElementById('bf_check_in');
    const bfOut = document.getElementById('bf_check_out');
    if (bfIn && bfOut) {
        const today = new Date().toISOString().split('T')[0];
        bfIn.min = today;
        bfIn.addEventListener('change', () => {
            const next = new Date(bfIn.value);
            next.setDate(next.getDate() + 1);
            bfOut.min = next.toISOString().split('T')[0];
            if (bfOut.value && bfOut.value <= bfIn.value) bfOut.value = bfOut.min;
        });
        document.getElementById('bookForm').addEventListener('submit', (e) => {
            if (bfOut.value <= bfIn.value) {
                e.preventDefault();
                alert('Check-out date must be after the check-in date.');
            }
        });
    }
</script>
@endpush