@extends('layouts.customer.app')

@section('title', 'Booking Confirmed — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Step 4 of 4</p>
        <h1 class="pb-title">Booking Confirmed</h1>
        <p class="pb-subtitle">A reservation has been arranged for your arrival — we look forward to hosting you.</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="confirm-wrap">
    <div class="confirm-check">
        <svg viewBox="0 0 52 52">
            <circle cx="26" cy="26" r="24" fill="none"/>
            <path fill="none" d="M14 27l7 7 16-16"/>
        </svg>
    </div>

    <p class="confirm-ref-label">Booking Reference</p>
    <p class="confirm-ref">{{ $booking->booking_reference }}</p>

    <div class="confirm-card">
        <div class="confirm-row">
            <span>Room Type</span>
            <strong>{{ $booking->roomType->name }} — Room {{ $booking->room->room_number }}</strong>
        </div>
        <div class="confirm-row">
            <span>Stay Dates</span>
            <strong>{{ $booking->check_in->toDateString() }} → {{ $booking->check_out->toDateString() }}</strong>
        </div>
        <div class="confirm-divider"></div>
        <div class="confirm-row">
            <span>Total Amount</span>
            <strong>৳{{ number_format($booking->total_amount, 2) }}</strong>
        </div>
        <div class="confirm-row confirm-row-highlight">
            <span>Deposit Paid</span>
            <strong>৳{{ number_format($booking->deposit_amount, 2) }}</strong>
        </div>
        <div class="confirm-row">
            <span>Due at Hotel</span>
            <strong>৳{{ number_format($booking->due_amount, 2) }}</strong>
        </div>
    </div>

    <a href="{{ route('customer.bookings.index') }}" class="btn btn-primary confirm-cta">View My Bookings</a>
</div>

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

    .confirm-wrap { max-width: 500px; margin: 2.5rem auto 0; text-align: center; }

    .confirm-check { width: 80px; height: 80px; margin: 0 auto 1.5rem; }
    .confirm-check svg { width: 100%; height: 100%; }
    .confirm-check circle { stroke: var(--success); stroke-width: 3; stroke-dasharray: 151; stroke-dashoffset: 151; animation: circleDraw 0.6s ease forwards; }
    .confirm-check path { stroke: var(--success); stroke-width: 3.5; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 34; stroke-dashoffset: 34; animation: checkDraw 0.35s ease 0.55s forwards; }
    @keyframes circleDraw { to { stroke-dashoffset: 0; } }
    @keyframes checkDraw { to { stroke-dashoffset: 0; } }

    .confirm-ref-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); }
    .confirm-ref { font-family: var(--font-display); font-size: 1.9rem; font-weight: 700; color: var(--primary); margin-bottom: 2rem; }

    .confirm-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.6rem 1.8rem; text-align: left; box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
    .confirm-row { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.85rem; padding: 0.6rem 0; color: var(--text-muted); }
    .confirm-row strong { color: var(--dark); font-weight: 600; text-align: right; }
    .confirm-divider { border-top: 1px solid var(--border); margin: 0.4rem 0; }
    .confirm-row-highlight { background: var(--success-bg); margin: 0.3rem -0.6rem; padding: 0.6rem; border-radius: var(--radius-sm); }
    .confirm-row-highlight strong { color: var(--success); }

    .confirm-cta { display: inline-flex; }
</style>
@endpush