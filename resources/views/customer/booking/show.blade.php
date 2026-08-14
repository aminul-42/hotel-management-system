@extends('layouts.customer.app')

@section('title', 'Booking ' . $booking->booking_reference . ' — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Your Account</p>
        <h1 class="pb-title">Booking {{ $booking->booking_reference }}</h1>
        <p class="pb-subtitle">{{ $booking->roomType->name }} — Room {{ $booking->room->room_number ?? 'N/A' }}</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="bs-card">
    <div class="bs-header">
        <span class="mb-status mb-status-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
    </div>

    <div class="bs-row">
        <span>Room Type</span>
        <strong>{{ $booking->roomType->name }} — Room {{ $booking->room->room_number ?? 'N/A' }}</strong>
    </div>
    <div class="bs-row">
        <span>Stay Dates</span>
        <strong>{{ $booking->check_in->toDateString() }} → {{ $booking->check_out->toDateString() }}</strong>
    </div>
    <div class="bs-divider"></div>
    <div class="bs-row">
        <span>Total Amount</span>
        <strong>৳{{ number_format($booking->total_amount, 2) }}</strong>
    </div>
    <div class="bs-row">
        <span>Deposit Paid</span>
        <strong>৳{{ number_format($booking->deposit_amount, 2) }}</strong>
    </div>
    <div class="bs-row">
        <span>Due at Hotel</span>
        <strong>৳{{ number_format($booking->due_amount, 2) }}</strong>
    </div>
</div>

<a href="{{ route('customer.bookings.index') }}" class="bs-back">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back to My Bookings
</a>

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

    .bs-card { max-width: 560px; margin: 2.5rem auto 1.5rem; background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.75rem 1.9rem; box-shadow: var(--shadow-sm); }
    .bs-header { margin-bottom: 1.1rem; }
    .bs-row { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.87rem; padding: 0.6rem 0; color: var(--text-muted); }
    .bs-row strong { color: var(--dark); font-weight: 600; text-align: right; }
    .bs-divider { border-top: 1px solid var(--border); margin: 0.5rem 0; }

    .mb-status { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 20px; text-transform: capitalize; }
    .mb-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .mb-status-pending { background: var(--secondary-tint); color: var(--secondary); }
    .mb-status-confirmed { background: var(--info-bg, #E8EEF3); color: var(--info, #3A5A78); }
    .mb-status-checked_in { background: var(--success-bg); color: var(--success); }
    .mb-status-checked_out { background: #eeece6; color: var(--text-muted); }
    .mb-status-cancelled { background: var(--danger-bg); color: var(--danger); }
    .mb-status-no_show { background: var(--danger-bg); color: var(--danger); }

    .bs-back { display: inline-flex; align-items: center; gap: 0.4rem; max-width: 560px; margin: 0 auto; font-size: 0.83rem; font-weight: 600; color: var(--primary); }
    .bs-back svg { width: 14px; height: 14px; }
    .bs-back:hover { color: var(--primary-dark); }
</style>
@endpush