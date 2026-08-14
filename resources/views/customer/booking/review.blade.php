@extends('layouts.customer.app')

@section('title', 'Review Your Booking — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Step 3 of 4</p>
        <h1 class="pb-title">Review Your Booking</h1>
        <p class="pb-subtitle">{{ $breakdown['roomType']->name }} — {{ \Carbon\Carbon::parse($pending['check_in'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($pending['check_out'])->format('d M Y') }} — {{ $pending['guests_count'] }} guests</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="review-layout">
    <div class="invoice-card">
        <div class="invoice-row">
            <span>Room Total</span>
            <strong>৳{{ number_format($breakdown['roomPricing']['total'], 2) }}</strong>
        </div>

        @foreach ($breakdown['facilities']['lines'] as $line)
            <div class="invoice-row invoice-row-sub">
                <span>{{ $line['name'] }} <em>× {{ $line['quantity'] }}</em></span>
                <strong>৳{{ number_format($line['subtotal'], 2) }}</strong>
            </div>
        @endforeach

        <div class="invoice-divider"></div>

        <div class="invoice-row">
            <span>Subtotal</span>
            <strong>৳{{ number_format($breakdown['subtotal'], 2) }}</strong>
        </div>
        @if ($breakdown['discount'] > 0)
            <div class="invoice-row invoice-row-discount">
                <span>Discount</span>
                <strong>-৳{{ number_format($breakdown['discount'], 2) }}</strong>
            </div>
        @endif
        <div class="invoice-row">
            <span>Service Charge ({{ $breakdown['serviceChargePct'] }}%)</span>
            <strong>৳{{ number_format($breakdown['serviceCharge'], 2) }}</strong>
        </div>
        <div class="invoice-row">
            <span>VAT ({{ $breakdown['vatPct'] }}%)</span>
            <strong>৳{{ number_format($breakdown['vat'], 2) }}</strong>
        </div>

        <div class="invoice-divider invoice-divider-strong"></div>

        <div class="invoice-row invoice-row-total">
            <span>Total</span>
            <strong>৳{{ number_format($breakdown['totalAmount'], 2) }}</strong>
        </div>
        <div class="invoice-row invoice-row-deposit">
            <span>Deposit Due Now ({{ $breakdown['depositPct'] }}%)</span>
            <strong>৳{{ number_format($breakdown['depositAmount'], 2) }}</strong>
        </div>
        <div class="invoice-row">
            <span>Due at Hotel</span>
            <strong>৳{{ number_format($breakdown['due'], 2) }}</strong>
        </div>
    </div>

    <aside class="review-actions">
        <div class="ra-card">
            <p class="ra-label">Pay now to confirm</p>
            <p class="ra-amount">৳{{ number_format($breakdown['depositAmount'], 2) }}</p>
            <form method="POST" action="{{ route('customer.booking.confirm') }}">
                @csrf
                <button type="submit" class="btn btn-primary ra-submit">
                    Proceed to Payment
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
            <a href="{{ route('customer.booking.customize') }}" class="ra-edit">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-9.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 8.5-8.5z"/></svg>
                Edit Booking
            </a>
        </div>
    </aside>
</div>

</div>
@endsection

@push('styles')
<style>
    .page-banner { position: relative; height: 320px; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .page-banner-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(30,31,34,0.55) 0%, rgba(30,31,34,0.8) 100%); }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow { font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500; letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem; }
    .pb-title { font-family: var(--font-display); font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.85rem; color: #e7e3da; margin-top: 0.8rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 240px; } }

    .review-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; align-items: start; margin-top: 2.5rem; }

    .invoice-card { background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.75rem 1.9rem; box-shadow: var(--shadow-sm); }
    .invoice-row { display: flex; justify-content: space-between; font-size: 0.88rem; padding: 0.55rem 0; color: var(--dark); }
    .invoice-row-sub { padding-left: 1rem; font-size: 0.82rem; color: var(--text-muted); }
    .invoice-row-sub em { font-style: normal; color: var(--text-muted); }
    .invoice-row-discount strong { color: var(--success); }
    .invoice-divider { border-top: 1px solid var(--border); margin: 0.5rem 0; }
    .invoice-divider-strong { border-top: 2px solid var(--dark); margin: 0.75rem 0; }
    .invoice-row-total { font-size: 1.05rem; font-weight: 700; }
    .invoice-row-total strong { font-family: var(--font-display); font-size: 1.5rem; color: var(--primary); }
    .invoice-row-deposit { background: var(--secondary-tint); margin: 0.6rem -0.6rem; padding: 0.6rem; border-radius: var(--radius-sm); font-weight: 600; }

    .ra-card { position: sticky; top: calc(var(--header-h) + 1.5rem); background: var(--dark); color: var(--white); border-radius: var(--radius); padding: 1.75rem 1.5rem; text-align: center; }
    .ra-label { font-size: 0.78rem; color: #cfcdc8; margin-bottom: 0.4rem; }
    .ra-amount { font-family: var(--font-display); font-size: 2.1rem; font-weight: 700; color: var(--secondary-light); margin-bottom: 1.25rem; }
    .ra-submit { width: 100%; justify-content: center; }
    .ra-submit svg { width: 15px; height: 15px; }
    .ra-edit { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 1rem; font-size: 0.8rem; color: #cfcdc8; font-weight: 500; }
    .ra-edit svg { width: 14px; height: 14px; }
    .ra-edit:hover { color: var(--white); text-decoration: underline; }

    @media (max-width: 900px) {
        .review-layout { grid-template-columns: 1fr; }
        .ra-card { position: static; }
    }
</style>
@endpush