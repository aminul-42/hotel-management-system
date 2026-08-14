@extends('layouts.customer.app')

@section('title', 'Customize Your Stay — ' . config('app.name', 'Hotel'))

@section('hero')
<section class="page-banner" style="background-image:url('https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?q=80&w=1920&auto=format&fit=crop')">
    <div class="page-banner-overlay"></div>
    <div class="page-banner-content">
        <p class="pb-eyebrow">Step 2 of 4</p>
        <h1 class="pb-title">Customize Your Stay</h1>
        <p class="pb-subtitle">{{ $roomType->name }} — {{ \Carbon\Carbon::parse($pending['check_in'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($pending['check_out'])->format('d M Y') }} — {{ $pending['guests_count'] }} guests</p>
    </div>
</section>
@endsection

@section('content')
<div class="content-container">

<div class="customize-layout">
    <form method="POST" action="{{ route('customer.booking.customize.save') }}" id="customizeForm">
        @csrf

        <div class="cz-card">
            <h3 class="cz-card-title">Add-on Facilities</h3>
            <p class="cz-card-hint">Select any facilities you'd like included with your stay.</p>

            <div class="facility-dropdown" id="facilityDropdown">
                <button type="button" class="fd-trigger" id="fdTrigger">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add a Facility
                </button>

                <div class="fd-panel" id="fdPanel">
                    @forelse ($facilities as $facility)
                        <button type="button" class="fd-option" data-id="{{ $facility->id }}" data-name="{{ $facility->name }}" data-price="{{ $facility->price }}">
                            @if ($facility->image_url)
                                <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" loading="lazy">
                            @else
                                <span class="fd-option-placeholder">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                </span>
                            @endif
                            <span class="fd-option-text">
                                <span class="fd-option-name">{{ $facility->name }}</span>
                                <span class="fd-option-price">৳{{ number_format($facility->price, 2) }} / unit</span>
                            </span>
                            <svg class="fd-option-add" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    @empty
                        <p class="fd-empty">No add-on facilities available right now.</p>
                    @endforelse
                </div>
            </div>

            <div class="fd-selected" id="fdSelected"></div>
        </div>

        <div class="cz-card">
            <h3 class="cz-card-title">Coupon Code</h3>
            <div class="coupon-row">
                <input type="text" id="couponInput" placeholder="Enter a coupon code" class="cz-coupon-input">
                <button type="button" id="couponApplyBtn" class="btn btn-secondary coupon-apply-btn">Apply</button>
            </div>
            <p class="coupon-message" id="couponMessage"></p>
            {{-- Hidden input actually submitted with the form on final "Continue" --}}
            <input type="hidden" name="coupon_code" id="couponHidden">
        </div>

        <div class="cz-card">
            <h3 class="cz-card-title">Special Requests</h3>
            <textarea name="special_requests" placeholder="Anything our team should know ahead of your arrival?"></textarea>
        </div>

        <div id="fdInputs"></div>

        <button type="submit" class="btn btn-primary cz-submit">
            Continue to Review
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </form>

    <aside class="cz-summary">
        <h3>Stay Summary</h3>
        <div class="cz-summary-row">
            <span>Room ({{ $pricingBreakdown['nights'] }} nights)</span>
            <strong>৳{{ number_format($pricingBreakdown['total'], 2) }}</strong>
        </div>
        <div class="cz-summary-row" id="czFacilitiesRow" style="display:none;">
            <span>Facilities (est.)</span>
            <strong id="czFacilitiesTotal">৳0.00</strong>
        </div>
        <div class="cz-summary-row" id="czDiscountRow" style="display:none;">
            <span>Discount</span>
            <strong id="czDiscountAmount" class="cz-discount-amount">-৳0.00</strong>
        </div>
        <div class="cz-summary-divider"></div>
        <div class="cz-summary-row cz-summary-total">
            <span>Estimated Total</span>
            <strong id="czGrandTotal">৳{{ number_format($pricingBreakdown['total'], 2) }}</strong>
        </div>
        <p class="cz-summary-note">Final total — including service charge and VAT — is calculated on the next step.</p>
    </aside>
</div>

</div>
@endsection

@push('styles')
<style>
    .page-banner {
        position: relative; height: 320px; background-size: cover; background-position: center;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .page-banner-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(30,31,34,0.55) 0%, rgba(30,31,34,0.8) 100%); }
    .page-banner-content { position: relative; z-index: 2; text-align: center; color: var(--white); padding: 0 1.5rem; max-width: 640px; }
    .pb-eyebrow { font-family: var(--font-accent); font-size: 0.74rem; font-weight: 500; letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-light); margin-bottom: 0.65rem; }
    .pb-title { font-family: var(--font-display); font-size: clamp(1.8rem, 3.5vw, 2.5rem); font-weight: 600; line-height: 1.15; }
    .pb-subtitle { font-size: 0.85rem; color: #e7e3da; margin-top: 0.8rem; line-height: 1.6; }
    @media (max-width: 700px) { .page-banner { height: 240px; } }

    .customize-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; align-items: start; margin-top: 2.5rem; }

    .cz-card {
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem;
    }
    .cz-card-title { font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; margin-bottom: 0.3rem; }
    .cz-card-hint { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.1rem; }

    .facility-dropdown { position: relative; }
    .fd-trigger {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--light); border: 1.5px dashed var(--border); color: var(--primary);
        font-size: 0.85rem; font-weight: 700; padding: 0.65rem 1.1rem; border-radius: var(--radius-sm);
        cursor: pointer; transition: background 0.15s, border-color 0.15s;
    }
    .fd-trigger svg { width: 15px; height: 15px; }
    .fd-trigger:hover { background: var(--primary-tint); border-color: var(--primary); }

    .fd-panel {
        position: absolute; z-index: 50; top: calc(100% + 0.5rem); left: 0; right: 0;
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        box-shadow: var(--shadow-md); padding: 0.5rem; max-height: 320px; overflow-y: auto;
        display: none;
    }
    .fd-panel.is-open { display: block; animation: fdIn 0.15s ease; }
    @keyframes fdIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

    .fd-option {
        display: flex; align-items: center; gap: 0.85rem; width: 100%;
        background: none; border: none; cursor: pointer; padding: 0.6rem 0.65rem;
        border-radius: var(--radius-sm); text-align: left; transition: background 0.15s;
    }
    .fd-option:hover { background: var(--light); }
    .fd-option.is-added { opacity: 0.4; pointer-events: none; }
    .fd-option img, .fd-option-placeholder { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .fd-option-placeholder { display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--primary-tint), var(--secondary-tint)); color: var(--secondary); }
    .fd-option-placeholder svg { width: 18px; height: 18px; }
    .fd-option-text { flex: 1; min-width: 0; }
    .fd-option-name { display: block; font-size: 0.85rem; font-weight: 600; color: var(--dark); }
    .fd-option-price { display: block; font-size: 0.74rem; color: var(--text-muted); margin-top: 0.1rem; }
    .fd-option-add { width: 16px; height: 16px; color: var(--secondary); flex-shrink: 0; }
    .fd-empty { font-size: 0.85rem; color: var(--text-muted); font-style: italic; padding: 0.6rem; }

    .fd-selected { display: flex; flex-direction: column; gap: 0.6rem; margin-top: 1rem; }
    .fd-selected:not(:empty) { margin-top: 1.1rem; }
    .fd-selected-item {
        display: flex; align-items: center; gap: 0.85rem;
        border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 0.6rem 0.8rem;
        background: var(--light);
    }
    .fd-selected-item img, .fd-selected-item .fd-option-placeholder { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .fd-selected-item-name { font-size: 0.85rem; font-weight: 600; color: var(--dark); }
    .fd-selected-item-price { font-size: 0.72rem; color: var(--text-muted); }
    .fd-selected-item-right { display: flex; align-items: center; gap: 0.6rem; margin-left: auto; }
    .fd-qty { width: 56px; padding: 0.4rem 0.5rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); font-size: 0.82rem; background: var(--white); text-align: center; }
    .fd-remove { background: none; border: none; cursor: pointer; color: var(--danger); padding: 0.3rem; display: flex; }
    .fd-remove svg { width: 16px; height: 16px; }

    /* ── Coupon apply row ─────────────────────────────────────── */
    .coupon-row { display: flex; gap: 0.6rem; }
    .cz-coupon-input {
        flex: 1; padding: 0.65rem 0.9rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: 0.85rem; background: var(--light); outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .cz-coupon-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-tint); background: var(--white); }
    .coupon-apply-btn { flex-shrink: 0; padding: 0.65rem 1.2rem; font-size: 0.85rem; }
    .coupon-apply-btn:disabled { opacity: 0.6; cursor: not-allowed; }
    .coupon-message { font-size: 0.78rem; margin-top: 0.6rem; min-height: 1.1em; }
    .coupon-message.is-success { color: var(--success); font-weight: 600; }
    .coupon-message.is-error { color: var(--danger); font-weight: 600; }
    .coupon-applied-badge {
        display: inline-flex; align-items: center; gap: 0.35rem; background: var(--success-bg); color: var(--success);
        font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.7rem; border-radius: 20px; margin-top: 0.7rem;
    }
    .coupon-applied-badge button {
        background: none; border: none; cursor: pointer; color: var(--success); display: flex; padding: 0; margin-left: 0.15rem;
    }
    .coupon-applied-badge svg { width: 13px; height: 13px; }

    .cz-submit { width: 100%; justify-content: center; }
    .cz-submit svg { width: 15px; height: 15px; }

    .cz-summary { position: sticky; top: calc(var(--header-h) + 1.5rem); background: var(--dark); color: var(--white); border-radius: var(--radius); padding: 1.6rem 1.5rem; }
    .cz-summary h3 { font-family: var(--font-display); font-size: 1.15rem; margin-bottom: 1.1rem; }
    .cz-summary-row { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.7rem; color: #cfcdc8; }
    .cz-summary-row strong { color: var(--white); font-weight: 600; }
    .cz-discount-amount { color: #7fdba0 !important; }
    .cz-summary-divider { border-top: 1px solid rgba(255,255,255,0.12); margin: 0.9rem 0; }
    .cz-summary-total { font-size: 0.95rem; }
    .cz-summary-total strong { font-family: var(--font-display); font-size: 1.3rem; color: var(--secondary-light); }
    .cz-summary-note { font-size: 0.72rem; color: #8f8b80; margin-top: 1rem; line-height: 1.5; }

    @media (max-width: 900px) {
        .customize-layout { grid-template-columns: 1fr; }
        .cz-summary { position: static; }
    }
    @media (max-width: 480px) {
        .coupon-row { flex-direction: column; }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const trigger = document.getElementById('fdTrigger');
    const panel = document.getElementById('fdPanel');
    const selectedWrap = document.getElementById('fdSelected');
    const inputsWrap = document.getElementById('fdInputs');
    const roomTotal = {{ (float) $pricingBreakdown['total'] }};

    const selected = new Map();
    let appliedDiscount = 0;
    let appliedCouponCode = null;

    /* ── Facility dropdown ────────────────────────────────────── */
    trigger.addEventListener('click', () => panel.classList.toggle('is-open'));
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#facilityDropdown')) panel.classList.remove('is-open');
    });

    panel.querySelectorAll('.fd-option').forEach(opt => {
        opt.addEventListener('click', () => {
            const id = opt.dataset.id;
            if (selected.has(id)) return;
            selected.set(id, {
                name: opt.dataset.name,
                price: parseFloat(opt.dataset.price),
                image: opt.querySelector('img') ? opt.querySelector('img').src : null,
                quantity: 1,
            });
            opt.classList.add('is-added');
            panel.classList.remove('is-open');
            render();
        });
    });

    function collectFacilitiesPayload() {
        const list = [];
        selected.forEach((item, id) => list.push({ facility_id: id, quantity: item.quantity }));
        return list;
    }

    function render(refreshCoupon = true) {
        selectedWrap.innerHTML = '';
        inputsWrap.innerHTML = '';
        let index = 0;
        let facilitiesTotal = 0;

        selected.forEach((item, id) => {
            facilitiesTotal += item.price * item.quantity;

            const row = document.createElement('div');
            row.className = 'fd-selected-item';
            row.innerHTML = `
                ${item.image
                    ? `<img src="${item.image}" alt="${item.name}">`
                    : `<span class="fd-option-placeholder"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></span>`}
                <div>
                    <div class="fd-selected-item-name">${item.name}</div>
                    <div class="fd-selected-item-price">৳${item.price.toFixed(2)} / unit</div>
                </div>
                <div class="fd-selected-item-right">
                    <input type="number" class="fd-qty" min="1" max="20" value="${item.quantity}" data-id="${id}">
                    <button type="button" class="fd-remove" data-id="${id}" aria-label="Remove">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>`;
            selectedWrap.appendChild(row);

            inputsWrap.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="facilities[${index}][facility_id]" value="${id}">
                <input type="hidden" name="facilities[${index}][quantity]" value="${item.quantity}">`);
            index++;
        });

        selectedWrap.querySelectorAll('.fd-qty').forEach(input => {
            input.addEventListener('input', () => {
                const id = input.dataset.id;
                const qty = Math.max(1, parseInt(input.value, 10) || 1);
                selected.get(id).quantity = qty;
                render();
            });
        });
        selectedWrap.querySelectorAll('.fd-remove').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                selected.delete(id);
                const opt = panel.querySelector(`.fd-option[data-id="${id}"]`);
                if (opt) opt.classList.remove('is-added');
                render();
            });
        });

        const facRow = document.getElementById('czFacilitiesRow');
        facRow.style.display = facilitiesTotal > 0 ? 'flex' : 'none';
        document.getElementById('czFacilitiesTotal').textContent = '৳' + facilitiesTotal.toFixed(2);
        updateGrandTotal(facilitiesTotal);

        // If a coupon is already applied and the facility mix changed, silently re-validate
        // so the discount never goes stale relative to the new subtotal.
        if (refreshCoupon && appliedCouponCode) {
            applyCoupon(appliedCouponCode, true);
        }
    }

    function updateGrandTotal(facilitiesTotal) {
        const grand = Math.max(0, roomTotal + facilitiesTotal - appliedDiscount);
        document.getElementById('czGrandTotal').textContent = '৳' + grand.toFixed(2);
    }

    function currentFacilitiesTotal() {
        let total = 0;
        selected.forEach(item => total += item.price * item.quantity);
        return total;
    }

    /* ── Coupon: instant AJAX apply, no form submit ──────────────── */
    const couponInput = document.getElementById('couponInput');
    const couponHidden = document.getElementById('couponHidden');
    const couponBtn = document.getElementById('couponApplyBtn');
    const couponMessage = document.getElementById('couponMessage');
    const discountRow = document.getElementById('czDiscountRow');
    const discountAmountEl = document.getElementById('czDiscountAmount');

    couponBtn.addEventListener('click', () => {
        const code = couponInput.value.trim();
        if (!code) {
            showCouponMessage('Please enter a coupon code.', false);
            return;
        }
        applyCoupon(code, false);
    });

    couponInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); couponBtn.click(); }
    });

    function applyCoupon(code, silent) {
        if (!silent) {
            couponBtn.disabled = true;
            couponBtn.textContent = 'Applying…';
        }

        fetch('{{ route("customer.booking.coupon.apply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                coupon_code: code,
                facilities: collectFacilitiesPayload(),
            }),
        })
            .then(async (res) => {
                const json = await res.json();
                if (!res.ok || !json.success) {
                    appliedDiscount = 0;
                    appliedCouponCode = null;
                    couponHidden.value = '';
                    discountRow.style.display = 'none';
                    updateGrandTotal(currentFacilitiesTotal());
                    if (!silent) showCouponMessage(json.message || 'That coupon code isn\'t valid.', false);
                    return;
                }

                appliedDiscount = parseFloat(json.discount) || 0;
                appliedCouponCode = code;
                couponHidden.value = code;

                if (appliedDiscount > 0) {
                    discountRow.style.display = 'flex';
                    discountAmountEl.textContent = '-৳' + appliedDiscount.toFixed(2);
                } else {
                    discountRow.style.display = 'none';
                }
                updateGrandTotal(currentFacilitiesTotal());
                if (!silent) showCouponMessage(json.message || 'Coupon applied successfully.', true);
            })
            .catch(() => {
                if (!silent) showCouponMessage('Something went wrong. Please try again.', false);
            })
            .finally(() => {
                if (!silent) {
                    couponBtn.disabled = false;
                    couponBtn.textContent = 'Apply';
                }
            });
    }

    function showCouponMessage(text, success) {
        couponMessage.textContent = text;
        couponMessage.classList.remove('is-success', 'is-error');
        couponMessage.classList.add(success ? 'is-success' : 'is-error');
    }
})();
</script>
@endpush