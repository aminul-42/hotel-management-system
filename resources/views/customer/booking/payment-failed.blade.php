@extends('layouts.customer.app')

@section('title', 'Payment ' . ucfirst($txn->status) . ' — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner page-banner-muted" style="background-image:url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Payment Status</p>
        <h1 class="pb-title">Payment {{ ucfirst($txn->status) }}</h1>
        <p class="pb-subtitle">Don't worry — your booking details are still saved.</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="pf-wrap">
    <div class="pf-icon">
        <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.93 19h14.14c1.4 0 2.27-1.52 1.58-2.75L13.58 4.75a1.8 1.8 0 00-3.16 0L3.35 16.25C2.66 17.48 3.53 19 4.93 19z"/>
        </svg>
    </div>
    <p class="pf-message">Your payment was {{ $txn->status }}. No charge has been completed, and your selected room and dates are still held in your session.</p>
    <a href="{{ route('customer.booking.review') }}" class="btn btn-primary pf-retry">
        Try Again
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

</div>
@endsection

@push('styles')
<style>
    .page-banner { position: relative; height: 260px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .page-banner-muted .page-banner-overlay { background: linear-gradient(180deg, rgba(30,31,34,0.72) 0%, rgba(30,31,34,0.88) 100%); }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow { font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500; letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem; }
    .pb-title { font-family: var(--font-display); font-size: clamp(1.7rem, 3.2vw, 2.2rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.85rem; color: #e7e3da; margin-top: 0.8rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 200px; } }

    .pf-wrap { max-width: 460px; margin: 2.5rem auto 0; text-align: center; }
    .pf-icon { width: 68px; height: 68px; margin: 0 auto 1.5rem; color: var(--danger); }
    .pf-icon svg { width: 100%; height: 100%; }
    .pf-message { font-size: 0.9rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 2rem; }
    .pf-retry svg { width: 15px; height: 15px; }
</style>
@endpush