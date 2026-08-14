@extends('layouts.customer.app')

@section('page-eyebrow', 'Secure Payment')
@section('page-title', 'Complete Your Payment')
@section('page-subtitle', 'Transaction ' . $txn->tran_id)

@section('content')

<div class="fc-dev-banner">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.93 19h14.14c1.4 0 2.27-1.52 1.58-2.75L13.58 4.75a1.8 1.8 0 00-3.16 0L3.35 16.25C2.66 17.48 3.53 19 4.93 19z"/></svg>
    Development Mode — this is a simulated gateway, not a real payment processor.
</div>

<div class="fc-card">
    <div class="fc-amount-row">
        <span>Amount Payable</span>
        <strong>৳{{ number_format($txn->amount, 2) }}</strong>
    </div>

    <div class="fc-actions">
        <form method="POST" action="{{ route('customer.payment.fake.callback', $txn->tran_id) }}">
            @csrf
            <input type="hidden" name="status" value="success">
            <button type="submit" class="btn btn-primary fc-btn">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Simulate Success
            </button>
        </form>

        <form method="POST" action="{{ route('customer.payment.fake.callback', $txn->tran_id) }}">
            @csrf
            <input type="hidden" name="status" value="failed">
            <button type="submit" class="btn btn-danger fc-btn">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Simulate Failure
            </button>
        </form>

        <form method="POST" action="{{ route('customer.payment.fake.callback', $txn->tran_id) }}">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="btn btn-secondary fc-btn">Simulate Cancel</button>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .fc-dev-banner {
        max-width: 480px; margin: 0 auto 1.75rem; display: flex; align-items: center; gap: 0.6rem;
        background: var(--warning-bg, #F6EEDD); color: var(--warning, #97711F);
        font-size: 0.8rem; font-weight: 600; padding: 0.75rem 1rem; border-radius: var(--radius-sm);
    }
    .fc-dev-banner svg { width: 17px; height: 17px; flex-shrink: 0; }

    .fc-card {
        max-width: 480px; margin: 0 auto; background: var(--white); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 1.75rem 1.9rem; box-shadow: var(--shadow-sm);
    }
    .fc-amount-row {
        display: flex; justify-content: space-between; align-items: baseline;
        padding-bottom: 1.25rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border);
    }
    .fc-amount-row span { font-size: 0.82rem; color: var(--text-muted); }
    .fc-amount-row strong { font-family: var(--font-display); font-size: 1.7rem; color: var(--primary); }

    .fc-actions { display: flex; flex-direction: column; gap: 0.75rem; }
    .fc-btn { width: 100%; justify-content: center; }
    .fc-btn svg { width: 15px; height: 15px; }
    .btn-danger { background: var(--danger-bg); color: var(--danger); }
    .btn-danger:hover { background: #f1d8d5; }
    .btn-secondary { background: var(--light); color: var(--dark); border: 1px solid var(--border); }
    .btn-secondary:hover { background: #f1efe9; }
</style>
@endpush