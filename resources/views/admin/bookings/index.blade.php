@extends('layouts.admin.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')
@section('page-subtitle', 'Create and manage reservations, check-ins and payments')

@push('styles')
<style>
    .filter-select {
        padding: 0.5rem 0.9rem; border: 1.5px solid var(--border); border-radius: 20px;
        background: var(--light); font-size: 0.85rem; font-family: var(--font-body);
        color: var(--dark); outline: none; cursor: pointer;
    }

    .customer-toggle { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
    .customer-toggle label {
        flex: 1; text-align: center; padding: 0.55rem; border: 1.5px solid var(--border);
        border-radius: var(--radius-sm); cursor: pointer; font-size: 0.82rem; font-weight: 600;
        color: var(--text-muted); transition: all 0.15s;
    }
    .customer-toggle input { display: none; }
    .customer-toggle input:checked + span { color: var(--white); }
    .customer-toggle label:has(input:checked) { background: var(--primary); border-color: var(--primary); color: var(--white); }

    .customer-search-wrap { position: relative; }
    .customer-results {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius-sm);
        box-shadow: var(--shadow-md); max-height: 220px; overflow-y: auto; margin-top: 4px; display: none;
    }
    .customer-results.open { display: block; }
    .customer-results button {
        display: block; width: 100%; text-align: left; padding: 0.6rem 0.85rem; border: none;
        background: none; cursor: pointer; border-bottom: 1px solid #f2efe9; font-family: var(--font-body);
    }
    .customer-results button:hover { background: var(--light); }
    .customer-results button strong { display: block; font-size: 0.85rem; color: var(--dark); }
    .customer-results button small { color: var(--text-muted); font-size: 0.75rem; }

    .selected-customer-card {
        display: none; align-items: center; justify-content: space-between;
        background: var(--primary-tint); border: 1px solid var(--primary); border-radius: var(--radius-sm);
        padding: 0.6rem 0.85rem; margin-top: 0.5rem;
    }
    .selected-customer-card.show { display: flex; }
    .selected-customer-card .info strong { display: block; font-size: 0.85rem; }
    .selected-customer-card .info small { color: var(--text-muted); font-size: 0.75rem; }
    .selected-customer-card button { background: none; border: none; color: var(--primary); font-size: 0.78rem; font-weight: 600; cursor: pointer; }

    .rate-box {
        background: var(--light); border: 1px dashed var(--border); border-radius: var(--radius-sm);
        padding: 0.85rem; margin: 0.75rem 0; font-size: 0.82rem;
    }
    .rate-box .rate-row { display: flex; justify-content: space-between; padding: 0.2rem 0; color: var(--text-muted); }
    .rate-box .rate-total { display: flex; justify-content: space-between; padding-top: 0.5rem; margin-top: 0.4rem; border-top: 1px solid var(--border); font-weight: 700; color: var(--dark); font-size: 0.95rem; }
    .rate-box .rate-placeholder { color: var(--text-muted); font-style: italic; }

    /* Facilities picker */
    .facility-list { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.4rem; }
    .facility-row {
        display: flex; align-items: center; gap: 0.7rem; padding: 0.55rem 0.75rem;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm); background: var(--light);
    }
    .facility-row input[type="checkbox"] { width: 16px; height: 16px; flex-shrink: 0; }
    .facility-row .fac-name { flex: 1; font-size: 0.85rem; font-weight: 600; color: var(--dark); }
    .facility-row .fac-price { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; }
    .facility-row .fac-qty { width: 60px; padding: 0.3rem 0.5rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 0.82rem; }
    .facility-row .fac-qty:disabled { opacity: 0.5; }
    .no-facilities-hint { font-size: 0.8rem; color: var(--text-muted); font-style: italic; }

    .due-display-box {
        padding: 0.65rem 0.9rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        background: var(--light); display: flex; align-items: center; min-height: 44px;
    }
    .due-display { font-family: var(--font-body); font-size: 1.05rem; font-weight: 700; color: var(--primary); line-height: 1; }

    .booking-ref { font-family: var(--font-accent); font-weight: 600; color: var(--primary); font-size: 0.8rem; letter-spacing: 0.03em; }
    .guest-cell strong { display: block; font-size: 0.85rem; }
    .guest-cell small { color: var(--text-muted); font-size: 0.72rem; }

    .info-btn {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.35rem 0.75rem; border-radius: 20px;
        background: var(--info-bg); color: var(--info); border: none;
        font-size: 0.75rem; font-weight: 600; cursor: pointer; font-family: var(--font-body);
    }
    .info-btn svg { width: 15px; height: 15px; }

    .info-popover {
        position: fixed; z-index: 3500; display: none;
        background: var(--white); border: 1px solid var(--border); border-radius: var(--radius);
        box-shadow: var(--shadow-lg); width: 400px; max-width: 92vw;
        max-height: 75vh; overflow-y: auto;
    }
    .info-popover.open { display: block; animation: popoverIn 0.12s ease; }

    .info-popover-header { padding: 0.9rem 1.1rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .info-popover-header h4 { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--dark); }
    .info-popover-close { background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 0.2rem; }
    .info-popover-close svg { width: 16px; height: 16px; }

    .info-lang-toggle { display: flex; gap: 0.4rem; padding: 0.7rem 1.1rem 0; }
    .info-lang-toggle button {
        flex: 1; padding: 0.4rem; border-radius: var(--radius-sm); border: 1.5px solid var(--border);
        background: var(--light); font-size: 0.78rem; font-weight: 600; color: var(--text-muted);
        cursor: pointer; font-family: var(--font-body);
    }
    .info-lang-toggle button.active { background: var(--primary); border-color: var(--primary); color: var(--white); }

    .info-popover-body { padding: 0.9rem 1.1rem 1.1rem; font-size: 0.82rem; color: var(--dark); line-height: 1.6; }
    .info-popover-body h5 { font-size: 0.78rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.03em; margin: 0.9rem 0 0.4rem; }
    .info-popover-body h5:first-child { margin-top: 0; }
    .info-popover-body ul { padding-left: 1.1rem; margin: 0; }
    .info-popover-body li { margin-bottom: 0.3rem; }
    .info-popover-body .flow-line { display: block; font-family: monospace; background: var(--light); padding: 0.4rem 0.6rem; border-radius: 6px; margin-bottom: 0.3rem; font-size: 0.78rem; }
    .info-lang-block { display: none; }
    .info-lang-block.active { display: block; }
</style>
@endpush

@section('content')

    <div class="table-wrap">
        <div class="table-toolbar">
            <div class="table-toolbar-title" style="display:flex; align-items:center; gap:0.6rem;">
                All Bookings
                <button type="button" class="info-btn" id="bookingInfoBtn">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Booking Instruction
                </button>
            </div>

            <div class="info-popover" id="bookingInfoPopover">
                <div class="info-popover-header">
                    <h4>Booking Instructions</h4>
                    <button type="button" class="info-popover-close" id="bookingInfoClose">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-lang-toggle">
                    <button type="button" class="active" data-lang="en">English</button>
                    <button type="button" data-lang="bn">বাংলা</button>
                </div>
                <div class="info-popover-body">
                    <div class="info-lang-block active" data-lang-block="en">
                        <h5>Status Meaning</h5>
                        <ul>
                            <li><strong>Pending</strong> — Booking created, not confirmed yet.</li>
                            <li><strong>Confirmed</strong> — Booking finalized, guest expected.</li>
                            <li><strong>Checked In</strong> — Guest has arrived and is staying.</li>
                            <li><strong>Checked Out</strong> — Guest has left, stay finished.</li>
                            <li><strong>Cancelled</strong> — Booking called off before arrival.</li>
                            <li><strong>No Show</strong> — Guest never arrived as expected.</li>
                        </ul>
                        <h5>Allowed Status Changes</h5>
                        <span class="flow-line">Pending → Confirmed or Cancelled</span>
                        <span class="flow-line">Confirmed → Checked In, Cancelled, or No Show</span>
                        <span class="flow-line">Checked In → Checked Out</span>
                        <span class="flow-line">Checked Out / Cancelled / No Show → Final (locked)</span>
                        <h5>Payment Terms</h5>
                        <ul>
                            <li><strong>Subtotal</strong> — Room charge + selected facilities, before discount.</li>
                            <li><strong>Service Charge / VAT</strong> — Applied automatically per hotel policy.</li>
                            <li><strong>Total Amount</strong> — Full price for the whole stay, including tax.</li>
                            <li><strong>Deposit</strong> — Partial payment collected in advance.</li>
                            <li><strong>Due Amount</strong> — Remaining balance to collect later.</li>
                        </ul>
                        <h5>Facilities</h5>
                        <ul>
                            <li>Only fixed-price facilities can be added to a booking. On-request services (e.g. laundry) are handled separately by Front Desk.</li>
                        </ul>
                        <h5>Coupons</h5>
                        <ul>
                            <li>Enter a valid code and click Apply before saving. The system checks it automatically and shows the discount.</li>
                        </ul>
                    </div>
                    <div class="info-lang-block" data-lang-block="bn">
                        <h5>স্ট্যাটাসের অর্থ</h5>
                        <ul>
                            <li><strong>পেন্ডিং</strong> — বুকিং তৈরি হয়েছে, এখনও কনফার্ম হয়নি।</li>
                            <li><strong>কনফার্মড</strong> — বুকিং চূড়ান্ত হয়েছে, গেস্ট আসবে বলে ধরে নেওয়া হচ্ছে।</li>
                            <li><strong>চেক-ইন</strong> — গেস্ট হোটেলে পৌঁছেছে এবং অবস্থান করছে।</li>
                            <li><strong>চেক-আউট</strong> — গেস্ট চলে গেছে, থাকার মেয়াদ শেষ।</li>
                            <li><strong>বাতিল</strong> — আগমনের আগেই বুকিং বাতিল করা হয়েছে।</li>
                            <li><strong>নো-শো</strong> — গেস্ট আসার কথা থাকলেও আসেনি।</li>
                        </ul>
                        <h5>পরিবর্তনের নিয়ম</h5>
                        <span class="flow-line">পেন্ডিং → কনফার্মড অথবা বাতিল</span>
                        <span class="flow-line">কনফার্মড → চেক-ইন, বাতিল, অথবা নো-শো</span>
                        <span class="flow-line">চেক-ইন → চেক-আউট</span>
                        <span class="flow-line">চেক-আউট / বাতিল / নো-শো → চূড়ান্ত (পরিবর্তনযোগ্য নয়)</span>
                        <h5>পেমেন্ট সংক্রান্ত তথ্য</h5>
                        <ul>
                            <li><strong>সাবটোটাল</strong> — রুম ভাড়া + নির্বাচিত সুবিধা, ছাড়ের আগে।</li>
                            <li><strong>সার্ভিস চার্জ / ভ্যাট</strong> — হোটেল নীতি অনুযায়ী স্বয়ংক্রিয়ভাবে যুক্ত হয়।</li>
                            <li><strong>মোট মূল্য</strong> — কর সহ পুরো থাকার সম্পূর্ণ খরচ।</li>
                            <li><strong>ডিপোজিট</strong> — আগাম জমা রাখা আংশিক টাকা।</li>
                            <li><strong>বাকি টাকা</strong> — পরে আদায় করতে হবে এমন অবশিষ্ট টাকা।</li>
                        </ul>
                        <h5>সুবিধাসমূহ</h5>
                        <ul>
                            <li>শুধুমাত্র নির্দিষ্ট মূল্যের সুবিধা বুকিং-এ যোগ করা যাবে। অন-রিকোয়েস্ট সেবা (যেমন লন্ড্রি) ফ্রন্ট ডেস্ক আলাদাভাবে পরিচালনা করবে।</li>
                        </ul>
                        <h5>কুপন</h5>
                        <ul>
                            <li>সেভ করার আগে সঠিক কোড লিখে Apply চাপুন। সিস্টেম নিজে থেকেই কোড যাচাই করে ছাড় দেখাবে।</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="table-toolbar-actions">
                <form method="GET" style="display:flex; gap:0.6rem; align-items:center;">
                    <div class="search-box">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                        <input type="text" name="search" placeholder="Reference, guest, phone..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        @foreach (['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'] as $s)
                            <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                </form>
                <button type="button" class="btn btn-primary btn-sm" onclick="BookingsPage.openCreate()">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    New Booking
                </button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Dates</th>
                    <th>Guests</th>
                    <th>Total</th>
                    <th>Deposit / Due</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td><span class="booking-ref">{{ $booking->booking_reference }}</span></td>
                        <td class="guest-cell">
                            <strong>{{ $booking->user->name ?? '—' }}</strong>
                            <small>{{ $booking->user->phone ?? $booking->user->email ?? '' }}</small>
                        </td>
                        <td>{{ $booking->room->room_number ?? '—' }} <small style="color:var(--text-muted)">({{ $booking->roomType->name ?? '' }})</small></td>
                        <td>{{ $booking->check_in->format('d M') }} – {{ $booking->check_out->format('d M Y') }}<br><small style="color:var(--text-muted)">{{ $booking->nights }} night{{ $booking->nights > 1 ? 's' : '' }}</small></td>
                        <td>{{ $booking->guests_count }}</td>
                        <td>৳{{ number_format($booking->total_amount, 2) }}</td>
                        <td>৳{{ number_format($booking->deposit_amount, 2) }} / ৳{{ number_format($booking->due_amount, 2) }}</td>
                        <td>
                            @php
                                $colors = [
                                    'pending' => '#97711F', 'confirmed' => '#3A5A78', 'checked_in' => '#2F6F4F',
                                    'checked_out' => '#756F63', 'cancelled' => '#A5352C', 'no_show' => '#A5352C',
                                ];
                                $allowed = \App\Models\Booking::ALLOWED_TRANSITIONS[$booking->status] ?? [];
                                $options = collect($allowed)->push($booking->status)->unique()->map(fn($s) => [
                                    'value' => $s,
                                    'label' => ucwords(str_replace('_', ' ', $s)),
                                    'color' => $colors[$s] ?? '#756F63',
                                ]);
                            @endphp
                            <span class="status-chip" data-current="{{ $booking->status }}"
                                  data-endpoint="{{ route('admin.bookings.status', $booking) }}"
                                  data-payload-key="status"
                                  data-options='{{ $options->values()->toJson() }}'></span>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.4rem;">
                                @if (in_array($booking->status, ['pending', 'confirmed']))
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="BookingsPage.openEdit({{ $booking->id }})">Edit</button>
                                @endif
                                @if (in_array($booking->status, ['pending', 'cancelled']))
                                    <button type="button" class="btn btn-danger btn-sm" onclick="BookingsPage.confirmDelete({{ $booking->id }}, '{{ $booking->booking_reference }}')">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="table-empty-row"><td colspan="9">No bookings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1.25rem;">{{ $bookings->links() }}</div>

    {{-- ── Hidden form template, cloned into the global Modal ─────────── --}}
    <template id="bookingFormTemplate">
        <form id="bookingForm" onsubmit="return false;">
            <div class="customer-toggle">
                <label><input type="radio" name="customer_mode" value="existing" checked><span>Existing Customer</span></label>
                <label><input type="radio" name="customer_mode" value="new"><span>New / Walk-in Customer</span></label>
            </div>

            <div id="existingCustomerBlock">
                <div class="form-group customer-search-wrap">
                    <label>Search Customer</label>
                    <input type="text" id="customerSearchInput" placeholder="Name, email or phone...">
                    <div class="customer-results" id="customerResults"></div>
                    <div class="selected-customer-card" id="selectedCustomerCard">
                        <div class="info">
                            <strong id="selectedCustomerName"></strong>
                            <small id="selectedCustomerMeta"></small>
                        </div>
                        <button type="button" onclick="BookingsPage.clearCustomer()">Change</button>
                    </div>
                    <input type="hidden" id="userIdInput">
                </div>
            </div>

            <div id="newCustomerBlock" style="display:none;">
                <div class="form-row">
                    <div class="form-group"><label>Full Name</label><input type="text" id="newCustomerName"></div>
                    <div class="form-group"><label>Phone</label><input type="text" id="newCustomerPhone"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email</label><input type="email" id="newCustomerEmail"></div>
                    <div class="form-group"><label>NID / Passport No. (optional)</label><input type="text" id="newCustomerNid"></div>
                </div>
            </div>

            <hr style="border:none; border-top:1px solid var(--border); margin:1.1rem 0;">

            <div class="form-row">
                <div class="form-group">
                    <label>Room Type</label>
                    <select id="roomTypeSelect">
                        <option value="">Select room type...</option>
                        @foreach ($roomTypes as $rt)
                            <option value="{{ $rt->id }}" data-max="{{ $rt->max_capacity }}">{{ $rt->name }} (max {{ $rt->max_capacity }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Guests</label>
                    <input type="number" id="guestsInput" min="1" value="1">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group"><label>Check-in</label><input type="date" id="checkInInput"></div>
                <div class="form-group"><label>Check-out</label><input type="date" id="checkOutInput"></div>
            </div>

            <div class="form-group">
                <label>Room</label>
                <select id="roomSelect" disabled><option value="">Select room type & dates first</option></select>
                <div class="hint" id="roomAvailabilityHint"></div>
            </div>

            <div class="rate-box" id="rateBox">
                <span class="rate-placeholder">Pick a room type, dates and guest count to see pricing.</span>
            </div>

            <div class="form-group">
                <label>Facilities (optional add-ons)</label>
                <div class="facility-list" id="facilityList">
                    @forelse ($facilities as $f)
                        <label class="facility-row">
                            <input type="checkbox" class="fac-checkbox" data-facility-id="{{ $f->id }}" data-price="{{ $f->price }}">
                            <span class="fac-name">{{ $f->name }}</span>
                            <span class="fac-price">৳{{ number_format($f->price, 2) }}</span>
                            <input type="number" class="fac-qty" data-facility-id="{{ $f->id }}" min="1" max="20" value="1" disabled>
                        </label>
                    @empty
                        <span class="no-facilities-hint">No fixed-price facilities available to add.</span>
                    @endforelse
                </div>
            </div>

            <div class="form-group customer-search-wrap">
                <label>Coupon Code (optional)</label>
                <div style="display:flex; gap:0.5rem;">
                    <input type="text" id="couponCodeInput" placeholder="e.g. SUMMER20" style="text-transform:uppercase;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="BookingsPage.applyCoupon()"
                        style="flex-shrink:0;">Apply</button>
                </div>
                <div class="selected-customer-card" id="appliedCouponCard">
                    <div class="info"><strong id="appliedCouponCode"></strong><small id="appliedCouponDiscount"></small></div>
                    <button type="button" onclick="BookingsPage.removeCoupon()">Remove</button>
                </div>
                <input type="hidden" id="discountInput" value="0">
                <input type="hidden" id="couponIdInput">
            </div>

            <div class="rate-box" id="summaryBox">
                <div class="rate-row"><span>Room Total</span><span id="summaryRoomTotal">৳0.00</span></div>
                <div class="rate-row" id="summaryFacilitiesRow" style="display:none;"><span>Facilities</span><span id="summaryFacilities">৳0.00</span></div>
                <div class="rate-row" id="summaryDiscountRow" style="display:none;"><span>Discount</span><span id="summaryDiscount">-৳0.00</span></div>
                <div class="rate-row"><span>Service Charge</span><span id="summaryServiceCharge">৳0.00</span></div>
                <div class="rate-row"><span>VAT</span><span id="summaryVat">৳0.00</span></div>
                <div class="rate-total"><span>Total Amount</span><span id="summaryNetTotal">৳0.00</span></div>
            </div>

            <div class="form-group">
                <label>Deposit %</label>
                <input type="number" id="depositPercentInput" min="0" max="100" step="0.01" value="{{ $depositDefault }}">
            </div>
            <div class="form-row">
                <div class="form-group"><label>Deposit Amount</label><input type="number" id="depositAmountInput" min="0"
                        step="0.01"></div>
                <div class="form-group">
                    <label>Due Amount</label>
                    <div class="due-display-box"><span class="due-display" id="dueDisplay">৳0.00</span></div>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="statusInput">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Special Requests</label>
                <textarea id="specialRequestsInput" rows="2"></textarea>
            </div>
        </form>
    </template>

@endsection

@push('scripts')
<script>
const BookingsPage = (() => {
    let editingBookingId = null;
    let selectedCustomerId = null;
    let depositEditedManually = false;
    let latestPricing = null; // full response from /calculate-rate (room + facilities + tax breakdown)

    function wireInfoPopover() {
        const btn = document.getElementById('bookingInfoBtn');
        const popover = document.getElementById('bookingInfoPopover');
        const closeBtn = document.getElementById('bookingInfoClose');

        function open() {
            const rect = btn.getBoundingClientRect();
            popover.style.top = `${rect.bottom + 8}px`;
            popover.style.left = `${rect.left}px`;
            popover.classList.add('open');
            requestAnimationFrame(() => {
                const pRect = popover.getBoundingClientRect();
                if (pRect.right > window.innerWidth - 8) {
                    popover.style.left = `${Math.max(8, window.innerWidth - pRect.width - 8)}px`;
                }
            });
        }
        function close() { popover.classList.remove('open'); }

        btn.addEventListener('click', (e) => { e.stopPropagation(); popover.classList.contains('open') ? close() : open(); });
        closeBtn.addEventListener('click', close);
        document.addEventListener('click', (e) => {
            if (popover.classList.contains('open') && !popover.contains(e.target) && e.target !== btn) close();
        });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

        popover.querySelectorAll('.info-lang-toggle button').forEach(langBtn => {
            langBtn.addEventListener('click', () => {
                popover.querySelectorAll('.info-lang-toggle button').forEach(b => b.classList.remove('active'));
                langBtn.classList.add('active');
                const lang = langBtn.dataset.lang;
                popover.querySelectorAll('.info-lang-block').forEach(block => {
                    block.classList.toggle('active', block.dataset.langBlock === lang);
                });
            });
        });
    }

    wireInfoPopover();

    // ── Reload after a booking status change ────────────────────────
    // The shared StatusChip toolkit only re-renders the clicked chip locally
    // after a successful transition — it doesn't know that a booking's *next*
    // allowed transitions differ depending on its *new* status (e.g. confirmed →
    // checked_in unlocks "Checked Out", which wasn't in the original popover
    // options computed at page load). So after any status-chip inside this
    // table is used, we reload the page once the AJAX call + toast have had
    // time to finish, so Blade recomputes fresh $options for the new status.
    function wireStatusChipReload() {
        let bookingChipClicked = false;

        document.querySelectorAll('.table-wrap .status-chip').forEach(chip => {
            chip.addEventListener('click', () => { bookingChipClicked = true; });
        });

        document.addEventListener('click', (e) => {
            const popoverBtn = e.target.closest('.status-popover button');
            if (popoverBtn && bookingChipClicked) {
                bookingChipClicked = false;
                setTimeout(() => window.location.reload(), 600);
            }
        });
    }

    wireStatusChipReload();

    function debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    function footerHtml() {
        return `
            <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancel</button>
            <button type="button" class="btn btn-primary" id="bookingSaveBtn">Save Booking</button>`;
    }

    function resetState() {
        editingBookingId = null;
        selectedCustomerId = null;
        depositEditedManually = false;
        latestPricing = null;
    }

    function openCreate() {
        resetState();
        Modal.open('New Booking', document.getElementById('bookingFormTemplate').innerHTML, footerHtml());
        wireForm();
    }

    async function openEdit(id) {
        resetState();
        editingBookingId = id;
        Modal.open('Loading booking...', '<div class="inline-spinner"><span class="dot-spinner"></span> Loading...</div>', '');
        try {
            const res = await fetch(`/admin/bookings/${id}/edit`, { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Failed to load booking.');

            Modal.open(`Edit Booking — ${json.booking.booking_reference}`, document.getElementById('bookingFormTemplate').innerHTML, footerHtml());
            wireForm();
            prefillForm(json.booking, json.editable);
        } catch (err) {
            Modal.close();
            Toast.show(err.message, 'error');
        }
    }

    function prefillForm(b, editable) {
        if (b.user) {
            selectCustomerObject(b.user);
        }
        document.getElementById('roomTypeSelect').value = b.room_type_id;
        document.getElementById('checkInInput').value = b.check_in;
        document.getElementById('checkOutInput').value = b.check_out;
        document.getElementById('guestsInput').value = b.guests_count;
        document.getElementById('discountInput').value = b.discount_amount;
        if (b.coupon_id) document.getElementById('couponIdInput').value = b.coupon_id;
        document.getElementById('depositPercentInput').value = b.deposit_percentage;
        document.getElementById('depositAmountInput').value = b.deposit_amount;
        document.getElementById('statusInput').value = b.status;
        document.getElementById('specialRequestsInput').value = b.special_requests || '';
        depositEditedManually = true;

        // Preselect facilities already attached to this booking
        if (b.facilities && b.facilities.length) {
            b.facilities.forEach(f => {
                const checkbox = document.querySelector(`.fac-checkbox[data-facility-id="${f.id}"]`);
                const qtyInput = document.querySelector(`.fac-qty[data-facility-id="${f.id}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    if (qtyInput) {
                        qtyInput.disabled = false;
                        qtyInput.value = f.pivot?.quantity || 1;
                    }
                }
            });
        }

        loadRoomsAndPreselect(b.room_type_id, b.check_in, b.check_out, b.room_id);
        fetchRate();

        if (!editable) {
            document.getElementById('bookingSaveBtn').disabled = true;
            document.getElementById('bookingSaveBtn').textContent = 'Locked (in progress / completed)';
        }
    }

    function wireForm() {
        document.querySelectorAll('input[name="customer_mode"]').forEach(r => r.addEventListener('change', toggleCustomerMode));
        toggleCustomerMode();

        document.getElementById('customerSearchInput').addEventListener('input', debounce(searchCustomers, 300));

        document.getElementById('roomTypeSelect').addEventListener('change', onStayInputsChanged);
        document.getElementById('checkInInput').addEventListener('change', onStayInputsChanged);
        document.getElementById('checkOutInput').addEventListener('change', onStayInputsChanged);
        document.getElementById('guestsInput').addEventListener('input', debounce(onStayInputsChanged, 400));

        document.querySelectorAll('.fac-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const qtyInput = document.querySelector(`.fac-qty[data-facility-id="${cb.dataset.facilityId}"]`);
                if (qtyInput) qtyInput.disabled = !cb.checked;
                fetchRate();
            });
        });
        document.querySelectorAll('.fac-qty').forEach(input => {
            input.addEventListener('input', debounce(fetchRate, 400));
        });

        document.getElementById('depositPercentInput').addEventListener('input', () => { depositEditedManually = false; recalculateDepositFromTotal(); });
        document.getElementById('depositAmountInput').addEventListener('input', () => { depositEditedManually = true; recomputeDue(); });

        document.getElementById('bookingSaveBtn').addEventListener('click', submitForm);

        document.addEventListener('click', (e) => {
            const results = document.getElementById('customerResults');
            if (results && !results.contains(e.target) && e.target.id !== 'customerSearchInput') {
                results.classList.remove('open');
            }
        });
    }

    function toggleCustomerMode() {
        const mode = document.querySelector('input[name="customer_mode"]:checked').value;
        document.getElementById('existingCustomerBlock').style.display = mode === 'existing' ? 'block' : 'none';
        document.getElementById('newCustomerBlock').style.display = mode === 'new' ? 'block' : 'none';
    }

    async function searchCustomers() {
        const q = document.getElementById('customerSearchInput').value.trim();
        const results = document.getElementById('customerResults');
        if (q.length < 2) { results.classList.remove('open'); return; }

        const res = await fetch(`/admin/bookings/search-customers?q=${encodeURIComponent(q)}`);
        const json = await res.json();

        if (!json.customers.length) {
            results.innerHTML = `<div style="padding:0.7rem; font-size:0.8rem; color:var(--text-muted);">No matches.</div>`;
        } else {
            results.innerHTML = json.customers.map(c => `
                <button type="button" data-id="${c.id}" data-name="${c.name}" data-email="${c.email || ''}" data-phone="${c.phone || ''}">
                    <strong>${c.name}</strong><small>${c.phone || c.email || ''}</small>
                </button>`).join('');
            results.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => selectCustomerObject({
                    id: btn.dataset.id, name: btn.dataset.name, email: btn.dataset.email, phone: btn.dataset.phone
                }));
            });
        }
        results.classList.add('open');
    }

    function selectCustomerObject(c) {
        selectedCustomerId = c.id;
        document.getElementById('userIdInput').value = c.id;
        document.getElementById('selectedCustomerName').textContent = c.name;
        document.getElementById('selectedCustomerMeta').textContent = [c.phone, c.email].filter(Boolean).join(' · ');
        document.getElementById('selectedCustomerCard').classList.add('show');
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('customerResults').classList.remove('open');
    }

    function collectSelectedFacilities() {
        const facilities = [];
        document.querySelectorAll('.fac-checkbox:checked').forEach(cb => {
            const qtyInput = document.querySelector(`.fac-qty[data-facility-id="${cb.dataset.facilityId}"]`);
            facilities.push({
                facility_id: parseInt(cb.dataset.facilityId, 10),
                quantity: parseInt(qtyInput?.value || '1', 10),
            });
        });
        return facilities;
    }

    async function applyCoupon() {
        if (!latestPricing) {
            Toast.show('Calculate the room total first (pick room type, dates, guests).', 'warning');
            return;
        }
        const code = document.getElementById('couponCodeInput').value.trim();
        if (!code) return;

        // Coupon discount applies to subtotal (room + facilities), not room total alone.
        const res = await fetch('/admin/bookings/apply-coupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: JSON.stringify({ code, amount: latestPricing.subtotal })
        });
        const json = await res.json();
        if (!res.ok) { Toast.show(json.message, 'error'); return; }

        document.getElementById('couponIdInput').value = json.coupon_id;
        document.getElementById('discountInput').value = json.discount_amount;
        document.getElementById('appliedCouponCode').textContent = json.code;
        document.getElementById('appliedCouponDiscount').textContent = `-৳${json.discount_amount.toFixed(2)} discount`;
        document.getElementById('appliedCouponCard').classList.add('show');
        document.getElementById('couponCodeInput').value = '';
        Toast.show('Coupon applied.', 'success');

        // Re-fetch full breakdown so service charge/VAT recompute on the discounted subtotal.
        await fetchRate();
    }

    async function removeCoupon() {
        document.getElementById('couponIdInput').value = '';
        document.getElementById('discountInput').value = 0;
        document.getElementById('appliedCouponCard').classList.remove('show');
        await fetchRate();
    }

    function clearCustomer() {
        selectedCustomerId = null;
        document.getElementById('userIdInput').value = '';
        document.getElementById('selectedCustomerCard').classList.remove('show');
    }

    async function onStayInputsChanged() {
        await loadRooms();
        await fetchRate();
    }

    async function loadRooms() {
        const roomTypeId = document.getElementById('roomTypeSelect').value;
        const checkIn = document.getElementById('checkInInput').value;
        const checkOut = document.getElementById('checkOutInput').value;
        const roomSelect = document.getElementById('roomSelect');
        const hint = document.getElementById('roomAvailabilityHint');

        if (!roomTypeId || !checkIn || !checkOut || checkOut <= checkIn) {
            roomSelect.innerHTML = '<option value="">Select room type & dates first</option>';
            roomSelect.disabled = true;
            hint.textContent = '';
            return;
        }

        roomSelect.disabled = true;
        roomSelect.innerHTML = '<option>Checking availability...</option>';

        const body = { room_type_id: roomTypeId, check_in: checkIn, check_out: checkOut };
        if (editingBookingId) body.exclude_booking_id = editingBookingId;

        try {
            const res = await fetch('/admin/bookings/check-availability', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify(body)
            });

            if (!res.ok) {
                let msg = `Availability check failed (${res.status}).`;
                try { const errJson = await res.json(); msg = errJson.message || msg; } catch (_) { }
                roomSelect.innerHTML = '<option value="">Error checking availability</option>';
                hint.textContent = msg;
                Toast.show(msg, 'error');
                return;
            }

            const json = await res.json();

            if (!json.rooms || !json.rooms.length) {
                roomSelect.innerHTML = '<option value="">No rooms available for these dates</option>';
                hint.textContent = 'Try different dates or another room type.';
                roomSelect.disabled = true;
            } else {
                roomSelect.innerHTML = json.rooms.map(r => `<option value="${r.id}">Room ${r.room_number} (Floor ${r.floor})</option>`).join('');
                roomSelect.disabled = false;
                hint.textContent = `${json.rooms.length} room(s) available.`;
            }
        } catch (err) {
            roomSelect.innerHTML = '<option value="">Error checking availability</option>';
            hint.textContent = 'Network or server error.';
            Toast.show('Could not check room availability.', 'error');
        }
    }

    async function loadRoomsAndPreselect(roomTypeId, checkIn, checkOut, roomId) {
        await loadRooms();
        const roomSelect = document.getElementById('roomSelect');
        if ([...roomSelect.options].some(o => o.value == roomId)) {
            roomSelect.value = roomId;
        } else {
            roomSelect.insertAdjacentHTML('afterbegin', `<option value="${roomId}" selected>Current room</option>`);
        }
    }

    async function fetchRate() {
        const roomTypeId = document.getElementById('roomTypeSelect').value;
        const checkIn = document.getElementById('checkInInput').value;
        const checkOut = document.getElementById('checkOutInput').value;
        const guests = parseInt(document.getElementById('guestsInput').value || '1', 10);
        const rateBox = document.getElementById('rateBox');

        if (!roomTypeId || !checkIn || !checkOut || checkOut <= checkIn) {
            rateBox.innerHTML = '<span class="rate-placeholder">Pick a room type, dates and guest count to see pricing.</span>';
            latestPricing = null;
            updateSummary();
            return;
        }

        const payload = {
            room_type_id: roomTypeId,
            check_in: checkIn,
            check_out: checkOut,
            guests_count: guests,
            facilities: collectSelectedFacilities(),
        };

        const couponId = document.getElementById('couponIdInput').value;
        if (couponId) {
            payload.coupon_id = couponId;
        } else {
            const manualDiscount = parseFloat(document.getElementById('discountInput').value || '0');
            if (manualDiscount > 0) payload.discount_amount = manualDiscount;
        }

        const res = await fetch('/admin/bookings/calculate-rate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!res.ok) {
            rateBox.innerHTML = `<span class="rate-placeholder">${json.message}</span>`;
            latestPricing = null;
            updateSummary();
            return;
        }

        latestPricing = json;

        let breakdownHtml = json.breakdown.map(n => `
            <div class="rate-row"><span>${n.date} (${n.rate_name})</span><span>৳${n.rate.toFixed(2)}</span></div>
        `).join('');

        if (json.facility_lines && json.facility_lines.length) {
            breakdownHtml += json.facility_lines.map(f => {
                const facName = document.querySelector(`.fac-checkbox[data-facility-id="${f.facility_id}"]`)?.closest('.facility-row')?.querySelector('.fac-name')?.textContent || 'Facility';
                return `<div class="rate-row"><span>${facName} × ${f.quantity}</span><span>৳${parseFloat(f.subtotal).toFixed(2)}</span></div>`;
            }).join('');
        }

        breakdownHtml += `<div class="rate-total"><span>${json.nights} night(s) — room + facilities</span><span>৳${json.subtotal.toFixed(2)}</span></div>`;

        rateBox.innerHTML = breakdownHtml;

        // Sync the discount hidden input with server-confirmed value (coupon or manual)
        document.getElementById('discountInput').value = json.discount_amount || 0;

        updateSummary();
    }

    function updateSummary() {
        const p = latestPricing;
        const roomTotal = p ? p.room_total : 0;
        const facilitiesSubtotal = p ? p.facilities_subtotal : 0;
        const discount = p ? p.discount_amount : 0;
        const serviceCharge = p ? p.service_charge_amount : 0;
        const vat = p ? p.vat_amount : 0;
        const grandTotal = p ? p.total_amount : 0;

        document.getElementById('summaryRoomTotal').textContent = `৳${roomTotal.toFixed(2)}`;

        const facRow = document.getElementById('summaryFacilitiesRow');
        if (facilitiesSubtotal > 0) {
            facRow.style.display = 'flex';
            document.getElementById('summaryFacilities').textContent = `৳${facilitiesSubtotal.toFixed(2)}`;
        } else {
            facRow.style.display = 'none';
        }

        const discountRow = document.getElementById('summaryDiscountRow');
        if (discount > 0) {
            discountRow.style.display = 'flex';
            document.getElementById('summaryDiscount').textContent = `-৳${discount.toFixed(2)}`;
        } else {
            discountRow.style.display = 'none';
        }

        document.getElementById('summaryServiceCharge').textContent = `৳${serviceCharge.toFixed(2)}`;
        document.getElementById('summaryVat').textContent = `৳${vat.toFixed(2)}`;
        document.getElementById('summaryNetTotal').textContent = `৳${grandTotal.toFixed(2)}`;

        recalculateDepositFromTotal();
    }

    function grandTotal() {
        return latestPricing ? latestPricing.total_amount : 0;
    }

    function recalculateDepositFromTotal() {
        if (!depositEditedManually) {
            const total = grandTotal();
            const pct = parseFloat(document.getElementById('depositPercentInput').value || '0');
            document.getElementById('depositAmountInput').value = (total * pct / 100).toFixed(2);
        }
        recomputeDue();
    }

    function recomputeDue() {
        const total = grandTotal();
        const deposit = parseFloat(document.getElementById('depositAmountInput').value || '0');
        const due = Math.max(0, total - deposit);
        document.getElementById('dueDisplay').textContent = `৳${due.toFixed(2)}`;
    }

    async function submitForm() {
        const btn = document.getElementById('bookingSaveBtn');
        const mode = document.querySelector('input[name="customer_mode"]:checked').value;

        const payload = {
            customer_mode: mode,
            user_id: mode === 'existing' ? (document.getElementById('userIdInput').value || null) : null,
            new_customer: mode === 'new' ? {
                name: document.getElementById('newCustomerName').value,
                email: document.getElementById('newCustomerEmail').value,
                phone: document.getElementById('newCustomerPhone').value,
                nid_passport_number: document.getElementById('newCustomerNid').value,
            } : null,
            room_type_id: document.getElementById('roomTypeSelect').value,
            room_id: document.getElementById('roomSelect').value,
            check_in: document.getElementById('checkInInput').value,
            check_out: document.getElementById('checkOutInput').value,
            guests_count: document.getElementById('guestsInput').value,
            status: document.getElementById('statusInput').value,
            coupon_id: document.getElementById('couponIdInput').value || null,
            discount_amount: document.getElementById('discountInput').value || 0,
            deposit_percentage: document.getElementById('depositPercentInput').value,
            deposit_amount: document.getElementById('depositAmountInput').value,
            special_requests: document.getElementById('specialRequestsInput').value,
            facilities: collectSelectedFacilities(),
        };

        Modal.setSubmitting(btn, true, 'Saving...');
        try {
            const url = editingBookingId ? `/admin/bookings/${editingBookingId}` : '/admin/bookings';
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || (json.errors ? Object.values(json.errors)[0][0] : 'Something went wrong.'));

            Toast.show(json.message, 'success');
            Modal.close();
            setTimeout(() => window.location.reload(), 500);
        } catch (err) {
            Modal.setSubmitting(btn, false);
            Toast.show(err.message, 'error');
        }
    }

    function confirmDelete(id, ref) {
        Modal.confirm({
            title: 'Delete booking?',
            message: `This will permanently delete booking <strong>${ref}</strong>. This cannot be undone.`,
            confirmLabel: 'Delete',
            danger: true,
            onConfirm: async () => {
                const res = await fetch(`/admin/bookings/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                });
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Delete failed.');
                Toast.show(json.message, 'success');
                setTimeout(() => window.location.reload(), 500);
            }
        });
    }

    return { openCreate, openEdit, clearCustomer, confirmDelete, applyCoupon, removeCoupon };
})();
</script>
@endpush