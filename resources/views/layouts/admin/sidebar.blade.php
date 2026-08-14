<div class="nav-section-label">Main</div>
<a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2
               2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0
               011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    Dashboard
</a>

<div class="nav-section-label">Inventory</div>
<a href="{{ route('admin.room-types.index') }}" class="nav-item {{ request()->routeIs('admin.room-types*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2
               0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1
               1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
    </svg>
    Room Types
</a>
<a href="{{ route('admin.rooms.index') }}" class="nav-item {{ request()->routeIs('admin.rooms*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M4 6h16M4 6v14a1 1 0 001 1h3m12-15v14a1 1 0
               01-1 1h-3m-8 0h8m-8 0V6m8 15V6"/>
    </svg>
    Rooms
</a>


<a href="{{ route('admin.room-rates.index') }}" class="nav-item {{ request()->routeIs('admin.room-rates*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343
               2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m9-8a9 9
               0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    Room Rates
</a>



<div class="nav-section-label">Sales</div>


<a href="{{route('admin.coupons.index')}}" class="nav-item {{ request()->routeIs('admin.coupons*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 5H7a2 2 0 00-2 2v3.586a1 1 0 00.293.707l7.414
               7.414a2 2 0 002.828 0l3.586-3.586a2 2 0
               000-2.828l-7.414-7.414A1 1 0 0011 5H9zm-1 4h.01"/>
    </svg>
    Coupons
</a>
<a href="{{ route('admin.bookings.index')}}" class="nav-item {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2
               2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    Bookings
</a>

<a href="{{ Route::has('admin.customers.index') ? route('admin.customers.index') : '#' }}" class="nav-item {{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0
               013-3.87m9-4.13a4 4 0 11-8 0 4 4 0 018 0zm-6
               8a4 4 0 004 4h0a4 4 0 004-4"/>
    </svg>
    Guests
</a>


<div class="nav-section-label">Content</div>
<a href="{{ route('admin.facilities.index') }}" class="nav-item {{ request()->routeIs('admin.facilities*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M19 21V9a2 2 0 00-2-2h-3V5a2 2 0 00-2-2H8a2 2 0
               00-2 2v2H5a2 2 0 00-2 2v12h16zM9 21v-4a1 1 0
               011-1h4a1 1 0 011 1v4M9 9h1m4 0h1m-6 4h1m4 0h1"/>
    </svg>
    Facilities
</a>
<a href="{{  route('admin.settings.index')  }}" class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724
               1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37
               2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756
               2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94
               1.543-.826 3.31-2.37 2.37a1.724 1.724 0
               00-2.572 1.065c-.426 1.756-2.924 1.756-3.35
               0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724
               1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924
               0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31
               2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    Settings
</a>