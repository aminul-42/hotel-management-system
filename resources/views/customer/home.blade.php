@extends('layouts.customer.app')

@section('title', config('app.name', 'Hotel') . ' — A Five-Star Residence')

{{-- ══════════════════════════════════════════════════════════════
     HERO — full-bleed slider, dark overlay, animated headline,
     mini booking widget. Layout skips the boxed plaque because
     this section is defined.
     ══════════════════════════════════════════════════════════════ --}}
@section('hero')
<section class="hero" id="hero">
    <div class="hero-slides" id="heroSlides">
        <div class="hero-slide is-active" style="background-image:url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1920&auto=format&fit=crop')"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1920&auto=format&fit=crop')"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1920&auto=format&fit=crop')"></div>
        <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?q=80&w=1920&auto=format&fit=crop')"></div>
    </div>
    <div class="hero-overlay"></div>

    <div class="hero-content">
        <p class="hero-eyebrow reveal" style="--d:0.1s">The Residence Awaits</p>
        <h1 class="hero-title">
            <span class="reveal" style="--d:0.25s">Welcome To</span>
            <span class="reveal" style="--d:0.4s">ADI INTERNATIONAL</span>
        </h1>
        <p class="hero-subtitle reveal" style="--d:0.55s">
            Curated suites, attentive service, and effortless booking — all in one place.
        </p>

        <div class="hero-booking reveal" style="--d:0.7s">
            <form action="{{ route('customer.booking.start') }}" method="POST" id="heroBookingForm">
                @csrf
                <div class="hb-field">
                    <label for="hb_room_type_id">Room Type</label>
                    <select name="room_type_id" id="hb_room_type_id" required>
                        <option value="" disabled selected>Choose a room</option>
                        @foreach ($roomTypes as $roomType)
                            <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="hb-field">
                    <label for="hb_check_in">Check In</label>
                    <input type="date" name="check_in" id="hb_check_in" required>
                </div>
                <div class="hb-field">
                    <label for="hb_check_out">Check Out</label>
                    <input type="date" name="check_out" id="hb_check_out" required>
                </div>
                <div class="hb-field hb-field-sm">
                    <label for="hb_adults">Adults</label>
                    <input type="number" name="adults" id="hb_adults" min="1" value="2" required>
                </div>
                <div class="hb-field hb-field-sm">
                    <label for="hb_children">Children</label>
                    <input type="number" name="children" id="hb_children" min="0" value="0">
                </div>
                <button type="submit" class="hb-submit">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    Check Availability
                </button>
            </form>
        </div>
    </div>

    <div class="hero-dots" id="heroDots"></div>
    <a href="#about" class="hero-scroll" aria-label="Scroll to explore">
        <svg fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
    </a>
</section>
@endsection

{{-- ══════════════════════════════════════════════════════════════
     CONTENT — since hero is defined, layout gives us the full
     width; we box each section ourselves as needed.
     ══════════════════════════════════════════════════════════════ --}}
@section('content')

{{-- ── About ───────────────────────────────────────────────────── --}}
<section class="about-section" id="about">
    <div class="about-image reveal-on-scroll" style="background-image:url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?q=80&w=1400&auto=format&fit=crop')"></div>
    <div class="about-copy reveal-on-scroll" style="--d:0.15s">
        <p class="section-eyebrow">About Us</p>
        <h2 class="section-title">Quiet Luxury, Considered Detail</h2>
        <p>
            Set apart from the noise of the city, {{ config('app.name', 'our residence') }} was built on a
            simple idea: that true hospitality is felt in the details. Every room is designed as a private
            sanctuary — warm materials, considered light, and a stillness that lets you actually rest.
        </p>
        <p>
            Our team works quietly in the background so your stay never feels managed — only welcomed.
            From the moment you arrive to the morning you leave, everything is arranged around one guest: you.
        </p>
        <div class="about-stats">
            <div class="about-stat"><strong>{{ $roomTypes->count() ?: '—' }}</strong><span>Room Types</span></div>
            <div class="about-stat"><strong>24/7</strong><span>Front Desk</span></div>
            <div class="about-stat"><strong>5★</strong><span>Guest Rated</span></div>
        </div>
        <a href="{{ route('customer.rooms.index') }}" class="btn btn-primary about-cta">Discover Our Rooms</a>
    </div>
</section>

{{-- ── Room Types — flip cards ─────────────────────────────────── --}}
<section class="rooms-section" id="rooms">
    <div class="section-heading">
        <p class="section-eyebrow">Accommodations</p>
        <h2 class="section-title">Rooms &amp; Suites</h2>
        <p class="section-lede">Every category is built around comfort, space, and a view worth waking up to.</p>
    </div>

    @if ($roomTypes->isEmpty())
        <p class="empty-note">Room types will appear here shortly — please check back soon.</p>
    @else
        <div class="room-grid">
            @foreach ($roomTypes as $roomType)
                @php $img = collect($roomType->image_urls ?? [])->first(); @endphp
                <a href="{{ route('customer.rooms.show', $roomType->slug) }}" class="flip-card" tabindex="0">
                    <div class="flip-card-inner">
                        <div class="flip-face flip-front">
                            @if ($img)
                                <img src="{{ $img }}" alt="{{ $roomType->name }}" loading="lazy">
                            @else
                                <div class="flip-placeholder"></div>
                            @endif
                            <span class="flip-front-label">{{ $roomType->name }}</span>
                        </div>
                        <div class="flip-face flip-back">
                            <p class="flip-back-name">{{ $roomType->name }}</p>
                            <p class="flip-back-meta">Sleeps up to {{ $roomType->max_capacity }} guests</p>
                            <span class="flip-back-cta">
                                View Rooms
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- ── Facilities ──────────────────────────────────────────────── --}}
<section class="facilities-section" id="facilities">
    <div class="section-heading light">
        <p class="section-eyebrow">On The Grounds</p>
        <h2 class="section-title">Facilities &amp; Services</h2>
        <p class="section-lede">A residence built for convenience, wherever your stay takes you.</p>
    </div>

    @if ($facilities->isEmpty())
        <p class="empty-note light">Facility details will appear here shortly.</p>
    @else
        <div class="facility-grid">
            @foreach ($facilities as $facility)
                <div class="facility-card">
                    @if ($facility->image_url)
                        <img src="{{ $facility->image_url }}" alt="{{ $facility->name }}" loading="lazy">
                    @else
                        <div class="facility-placeholder">
                            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </div>
                    @endif
                    <div class="facility-overlay">
                        <p class="facility-name">{{ $facility->name }}</p>
                        @if ($facility->pricing_type === 'free')
                            <span class="facility-tag">Complimentary</span>
                        @elseif ($facility->pricing_type === 'on_request')
                            <span class="facility-tag">On Request</span>
                        @else
                            <span class="facility-tag">Available to Book</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ── Closing marquee — auto-scrolling real room photos ──────── --}}
@if ($roomGalleryImages->isNotEmpty())
<section class="marquee-section">
    <div class="marquee-track" id="marqueeTrack">
        @foreach ($roomGalleryImages as $img)
            <div class="marquee-item">
                <img src="{{ $img['url'] }}" alt="{{ $img['label'] }}" loading="lazy">
                <span>{{ $img['label'] }}</span>
            </div>
        @endforeach
        {{-- duplicate once for a seamless CSS loop --}}
        @foreach ($roomGalleryImages as $img)
            <div class="marquee-item" aria-hidden="true">
                <img src="{{ $img['url'] }}" alt="" loading="lazy">
                <span>{{ $img['label'] }}</span>
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="final-cta">
    <p class="section-eyebrow">Ready When You Are</p>
    <h2 class="section-title">Reserve Your Stay Today</h2>
    <a href="{{ route('customer.rooms.index') }}" class="btn btn-primary">Browse Available Rooms</a>
</section>

@endsection

@push('styles')
<style>
    /* ══════════════════════════════════════════════
       HERO
       ══════════════════════════════════════════════ */
    .hero {
        position: relative;
        min-height: 100vh;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
        background: var(--dark);
    }
    .hero-slides { position: absolute; inset: 0; }
    .hero-slide {
        position: absolute; inset: 0;
        background-size: cover; background-position: center;
        opacity: 0; transition: opacity 1.6s ease;
    }
    .hero-slide.is-active { opacity: 1; }
    .hero-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(180deg, rgba(30,31,34,0.65) 0%, rgba(30,31,34,0.55) 45%, rgba(30,31,34,0.85) 100%);
        z-index: 1;
    }

    .hero-content {
        position: relative; z-index: 2;
        max-width: 780px; margin: 0 auto; padding: 7rem 1.75rem 4rem;
        text-align: center; color: var(--white);
    }
    .hero-eyebrow {
        font-family: var(--font-accent); font-size: 0.78rem; font-weight: 500;
        letter-spacing: 0.22em; text-transform: uppercase; color: var(--secondary-light);
        margin-bottom: 1rem;
    }
    .hero-title {
        font-family: var(--font-display); font-weight: 600; line-height: 1.08;
        font-size: clamp(2.4rem, 5.5vw, 4.2rem);
    }
    .hero-title span { display: block; }
    .hero-subtitle {
        font-size: 1rem; color: #e7e3da; margin: 1.4rem auto 0; max-width: 480px; line-height: 1.7;
    }

    .reveal {
        opacity: 0; transform: translateY(18px);
        animation: revealIn 0.8s ease forwards;
        animation-delay: var(--d, 0s);
    }
    @keyframes revealIn { to { opacity: 1; transform: translateY(0); } }

    /* Booking widget */
    .hero-booking { margin-top: 2.75rem; }
    #heroBookingForm {
        display: flex; flex-wrap: wrap; align-items: end; gap: 0.85rem;
        background: rgba(255,255,255,0.97);
        border-radius: 16px; padding: 1.25rem 1.4rem;
        box-shadow: var(--shadow-md);
    }
    .hb-field { display: flex; flex-direction: column; gap: 0.35rem; text-align: left; flex: 1 1 150px; min-width: 130px; }
    .hb-field-sm { flex: 0 1 90px; min-width: 80px; }
    .hb-field label {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        color: var(--text-muted);
    }
    .hb-field input, .hb-field select {
        padding: 0.6rem 0.7rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
        font-size: 0.85rem; font-family: var(--font-body); color: var(--dark);
        background: var(--light); outline: none; transition: border-color 0.2s, box-shadow 0.2s;
    }
    .hb-field input:focus, .hb-field select:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-tint); background: var(--white);
    }
    .hb-submit {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--primary); color: var(--white); border: none; cursor: pointer;
        padding: 0.7rem 1.4rem; border-radius: var(--radius-sm);
        font-family: var(--font-body); font-size: 0.85rem; font-weight: 700;
        white-space: nowrap; transition: background 0.15s; flex-shrink: 0;
        box-shadow: 0 2px 10px rgba(125,26,52,0.3);
    }
    .hb-submit svg { width: 16px; height: 16px; }
    .hb-submit:hover { background: var(--primary-dark); }

    .hero-dots {
        position: absolute; bottom: 6.5rem; left: 50%; transform: translateX(-50%);
        z-index: 2; display: flex; gap: 0.5rem;
    }
    .hero-dots button {
        width: 8px; height: 8px; border-radius: 50%; border: none; cursor: pointer;
        background: rgba(255,255,255,0.4); transition: background 0.2s, transform 0.2s;
        padding: 0;
    }
    .hero-dots button.is-active { background: var(--secondary-light); transform: scale(1.3); }

    .hero-scroll {
        position: absolute; bottom: 1.75rem; left: 50%; transform: translateX(-50%);
        z-index: 2; color: rgba(255,255,255,0.75);
        animation: scrollBounce 2s ease-in-out infinite;
    }
    .hero-scroll svg { width: 22px; height: 22px; }
    @keyframes scrollBounce { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, 8px); } }

    @media (max-width: 700px) {
        .hero-content { padding: 6.5rem 1.25rem 3rem; }
        #heroBookingForm { flex-direction: column; align-items: stretch; }
        .hb-field, .hb-field-sm { flex: 1 1 auto; }
        .hb-submit { justify-content: center; }
        .hero-dots { bottom: auto; top: 1.25rem; right: 1.25rem; left: auto; transform: none; }
    }

    /* ══════════════════════════════════════════════
       SHARED SECTION HELPERS
       ══════════════════════════════════════════════ */
    .section-heading { max-width: 640px; margin: 0 auto 3rem; text-align: center; padding: 0 1.75rem; }
    .section-eyebrow {
        font-family: var(--font-accent); font-size: 0.7rem; font-weight: 500;
        letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary);
        margin-bottom: 0.65rem;
    }
    .section-heading.light .section-eyebrow { color: var(--secondary-light); }
    .section-title {
        font-family: var(--font-display); font-size: clamp(1.9rem, 3.5vw, 2.5rem);
        font-weight: 600; color: var(--dark); line-height: 1.15;
    }
    .section-heading.light .section-title, .facilities-section .section-title { color: var(--white); }
    .section-lede { font-size: 0.9rem; color: var(--text-muted); margin-top: 0.9rem; line-height: 1.65; }
    .section-heading.light .section-lede { color: #cfcdc8; }
    .empty-note { text-align: center; color: var(--text-muted); padding: 0 1.75rem 2rem; }
    .empty-note.light { color: #cfcdc8; }

    .reveal-on-scroll { opacity: 0; transform: translateY(24px); transition: opacity 0.7s ease, transform 0.7s ease; transition-delay: var(--d, 0s); }
    .reveal-on-scroll.is-visible { opacity: 1; transform: translateY(0); }

    /* ══════════════════════════════════════════════
       ABOUT
       ══════════════════════════════════════════════ */
    .about-section {
        max-width: 1180px; margin: 0 auto; padding: 5.5rem 1.75rem;
        display: grid; grid-template-columns: 0.9fr 1.1fr; gap: 3.5rem; align-items: center;
    }
    .about-image {
        min-height: 420px; border-radius: var(--radius);
        background-size: cover; background-position: center; background-attachment: fixed;
        box-shadow: var(--shadow-md);
    }
    .about-copy p { color: var(--dark); line-height: 1.75; margin-bottom: 1rem; font-size: 0.94rem; }
    .about-stats { display: flex; gap: 2.25rem; margin: 1.75rem 0; }
    .about-stat strong { display: block; font-family: var(--font-display); font-size: 1.9rem; color: var(--primary); font-weight: 700; }
    .about-stat span { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); }
    .about-cta { margin-top: 0.5rem; }

    @media (max-width: 900px) {
        .about-section { grid-template-columns: 1fr; padding: 3.5rem 1.25rem; gap: 2rem; }
        .about-image { min-height: 260px; background-attachment: scroll; }
    }

    /* ══════════════════════════════════════════════
       ROOM TYPES — FLIP CARDS
       ══════════════════════════════════════════════ */
    .rooms-section { max-width: 1180px; margin: 0 auto; padding: 2rem 1.75rem 5.5rem; }
    .room-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(460px, 1fr)); gap: 1.5rem;
    }
    .flip-card {
        display: block; height: 320px; perspective: 1200px; border-radius: var(--radius);
        outline: none;
    }
    .flip-card-inner {
        position: relative; width: 100%; height: 100%;
        transition: transform 0.6s cubic-bezier(.4,.2,.2,1);
        transform-style: preserve-3d; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    }
    .flip-card:hover .flip-card-inner,
    .flip-card:focus-visible .flip-card-inner,
    .flip-card.is-flipped .flip-card-inner { transform: rotateY(180deg); }

    .flip-face {
        position: absolute; inset: 0; backface-visibility: hidden; border-radius: var(--radius);
        overflow: hidden;
    }
    .flip-front { background: var(--dark); }
    .flip-front img { width: 100%; height: 100%; object-fit: cover; opacity: 0.88; }
    .flip-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-tint), var(--secondary-tint)); }
    .flip-front-label {
        position: absolute; left: 1rem; bottom: 1rem; right: 1rem;
        font-family: var(--font-display); font-size: 1.25rem; font-weight: 600; color: var(--white);
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }
    .flip-back {
        background: linear-gradient(150deg, var(--primary), var(--primary-dark));
        transform: rotateY(180deg);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; padding: 1.5rem; color: var(--white);
    }
    .flip-back-name { font-family: var(--font-display); font-size: 1.4rem; font-weight: 600; margin-bottom: 0.4rem; }
    .flip-back-meta { font-size: 0.8rem; color: var(--secondary-tint); margin-bottom: 1.4rem; }
    .flip-back-cta {
        display: inline-flex; align-items: center; gap: 0.4rem;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
        color: var(--secondary-light); border-bottom: 1px solid var(--secondary-light); padding-bottom: 2px;
    }
    .flip-back-cta svg { width: 14px; height: 14px; }

    /* ══════════════════════════════════════════════
       FACILITIES
       ══════════════════════════════════════════════ */
    .facilities-section { background: var(--dark); padding: 5.5rem 1.75rem; }
    .facility-grid {
        max-width: 1180px; margin: 0 auto;
        display: grid; grid-template-columns: repeat(auto-fill, minmax(430px, 1fr)); gap: 1.4rem;
    }
    .facility-card {
        position: relative; height: 230px; border-radius: var(--radius); overflow: hidden;
        cursor: default;
    }
    .facility-card img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 0.5s ease, filter 0.5s ease;
    }
    .facility-card:hover img { transform: scale(1.08); filter: brightness(0.75); }
    .facility-placeholder {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(125,26,52,0.35), rgba(131,108,49,0.35));
        display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.5);
    }
    .facility-placeholder svg { width: 34px; height: 34px; }
    .facility-overlay {
        position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: flex-end;
        padding: 1.1rem; background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.75) 100%);
    }
    .facility-name {
        font-family: var(--font-display); font-size: 1.15rem; font-weight: 600; color: var(--white);
        transform: translateY(6px); transition: transform 0.35s ease;
    }
    .facility-card:hover .facility-name { transform: translateY(0); }
    .facility-tag {
        display: inline-block; margin-top: 0.35rem; font-size: 0.66rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.06em; color: var(--secondary-light);
        opacity: 0; transition: opacity 0.35s ease 0.05s;
    }
    .facility-card:hover .facility-tag { opacity: 1; }

    /* ══════════════════════════════════════════════
       MARQUEE
       ══════════════════════════════════════════════ */
    .marquee-section { overflow: hidden; padding: 3.5rem 0; background: var(--light); }
    .marquee-track {
        display: flex; gap: 1.1rem; width: max-content;
        animation: marqueeScroll 38s linear infinite;
    }
    .marquee-section:hover .marquee-track { animation-play-state: paused; }
    .marquee-item {
        position: relative; width: 360px; height: 270px; border-radius: var(--radius-sm);
        overflow: hidden; flex-shrink: 0; box-shadow: var(--shadow-sm);
    }
    .marquee-item img { width: 100%; height: 100%; object-fit: cover; }
    .marquee-item span {
        position: absolute; left: 0.75rem; bottom: 0.6rem; color: var(--white);
        font-size: 0.75rem; font-weight: 600; text-shadow: 0 1px 6px rgba(0,0,0,0.6);
    }
    @keyframes marqueeScroll { from { transform: translateX(0); } to { transform: translateX(-50%); } }

    /* ══════════════════════════════════════════════
       FINAL CTA
       ══════════════════════════════════════════════ */
    .final-cta {
        text-align: center; padding: 5.5rem 1.75rem 6rem;
        max-width: 640px; margin: 0 auto;
    }
    .final-cta .section-title { margin-bottom: 1.75rem; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    /* ── Hero slider: pure CSS/JS crossfade ──────────────────────── */
    const slides = document.querySelectorAll('#heroSlides .hero-slide');
    const dotsWrap = document.getElementById('heroDots');
    let current = 0;

    slides.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
        if (i === 0) dot.classList.add('is-active');
        dot.addEventListener('click', () => goTo(i));
        dotsWrap.appendChild(dot);
    });
    const dots = dotsWrap.querySelectorAll('button');

    function goTo(index) {
        slides[current].classList.remove('is-active');
        dots[current].classList.remove('is-active');
        current = index;
        slides[current].classList.add('is-active');
        dots[current].classList.add('is-active');
    }

    let timer = setInterval(() => goTo((current + 1) % slides.length), 5500);
    const hero = document.getElementById('hero');
    hero.addEventListener('mouseenter', () => clearInterval(timer));
    hero.addEventListener('mouseleave', () => {
        timer = setInterval(() => goTo((current + 1) % slides.length), 5500);
    });

    /* ── Booking widget: keep check_out after check_in, client-side ── */
    const checkIn = document.getElementById('hb_check_in');
    const checkOut = document.getElementById('hb_check_out');
    const today = new Date().toISOString().split('T')[0];
    checkIn.min = today;

    checkIn.addEventListener('change', () => {
        const inDate = new Date(checkIn.value);
        const nextDay = new Date(inDate);
        nextDay.setDate(nextDay.getDate() + 1);
        const minOut = nextDay.toISOString().split('T')[0];
        checkOut.min = minOut;
        if (checkOut.value && checkOut.value <= checkIn.value) {
            checkOut.value = minOut;
        }
    });

    document.getElementById('heroBookingForm').addEventListener('submit', (e) => {
        if (checkOut.value <= checkIn.value) {
            e.preventDefault();
            alert('Check-out date must be after the check-in date.');
        }
    });

    /* ── Flip cards: tap-to-flip on touch devices ────────────────── */
    if (matchMedia('(hover: none)').matches) {
        document.querySelectorAll('.flip-card').forEach(card => {
            card.addEventListener('click', function (e) {
                if (!this.classList.contains('is-flipped')) {
                    e.preventDefault();
                    document.querySelectorAll('.flip-card.is-flipped').forEach(c => {
                        if (c !== this) c.classList.remove('is-flipped');
                    });
                    this.classList.add('is-flipped');
                }
            });
        });
    }

    /* ── Reveal-on-scroll for About section ──────────────────────── */
    const revealEls = document.querySelectorAll('.reveal-on-scroll');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    revealEls.forEach(el => io.observe(el));
})();
</script>
@endpush