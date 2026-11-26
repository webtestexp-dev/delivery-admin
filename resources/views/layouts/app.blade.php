<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Milk Delivery') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ Auth::check() ? 'admin-app' : 'admin-auth' }}">
    @auth
        <button class="admin-sidebar-backdrop" type="button" aria-label="Close navigation" data-sidebar-backdrop hidden></button>

        <aside class="admin-sidebar" id="adminSidebar" data-sidebar aria-label="Main navigation">
            <div class="admin-sidebar-brand">
                <a class="admin-brand" href="{{ route('home') }}">
                    <span class="admin-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M8 3h8l1 4 2 2v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9l2-2 1-4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M7 7h10M8 12h8M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span class="admin-brand-copy">
                        <span class="admin-brand-name">{{ config('app.name', 'Milk Delivery') }}</span>
                        <span class="admin-brand-caption">ADMIN PORTAL</span>
                    </span>
                </a>
                <button class="admin-sidebar-close" type="button" aria-label="Close navigation" data-sidebar-close>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>

            <nav class="admin-nav" aria-label="Admin menu">
                <span class="admin-nav-heading">WORKSPACE</span>
                <a class="admin-nav-link {{ request()->routeIs('home', 'dashboard') ? 'active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home', 'dashboard')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="3.5" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="11.5" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.7"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}" @if(request()->routeIs('customers.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="8" r="3.25" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19v-1.25A4.75 4.75 0 0 1 8.25 13h1.5a4.75 4.75 0 0 1 4.75 4.75V19h-11Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M15.5 5.2a3.25 3.25 0 0 1 0 6.1m2 2.2a4.75 4.75 0 0 1 3 4.45V19h-2.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>Customers</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}" @if(request()->routeIs('products.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m3.8 7.7 8.2 4.5 8.2-4.5M12 12.2V21m-4.1-16.8 8.2 4.4" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                    <span>Products</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}" href="{{ route('brands.index') }}" @if(request()->routeIs('brands.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5h16v13H4z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 9h8M8 12.5h5m-5 3.5h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>Brands</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('recurring-orders.*') ? 'active' : '' }}" href="{{ route('recurring-orders.index') }}" @if(request()->routeIs('recurring-orders.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.7 9A7 7 0 0 1 17.4 6.5L20 9M4 15l2.6 2.5A7 7 0 0 0 18.3 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Regular orders</span>
                </a>

                <span class="admin-nav-heading admin-nav-heading-spaced">OPERATIONS</span>
                <a class="admin-nav-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}" href="{{ route('deliveries.index') }}" @if(request()->routeIs('deliveries.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 6h11v11H3zM14 10h4l3 3v4h-7" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="7.5" cy="18" r="2" fill="#163a2d" stroke="currentColor" stroke-width="1.7"/><circle cx="17.5" cy="18" r="2" fill="#163a2d" stroke="currentColor" stroke-width="1.7"/></svg>
                    <span>Deliveries</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}" @if(request()->routeIs('payments.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M3 10h18m-13 5h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>Payments</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}" @if(request()->routeIs('invoices.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3.5h12v17l-3-1.8-3 1.8-3-1.8-3 1.8v-17Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 8h6m-6 4h6m-6 4h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>Invoices</span>
                </a>
                <a class="admin-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20V10m5 10V4m5 16v-7m5 7V7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M2.5 20.5h19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    <span>Reports</span>
                </a>
            </nav>

            <div class="admin-sidebar-account">
                <span class="admin-user-avatar">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
                <span class="admin-user-copy">
                    <span class="admin-user-name">{{ Auth::user()->name }}</span>
                    <span class="admin-user-role">Administrator</span>
                </span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="admin-logout-button" type="submit" aria-label="Log out" title="Log out">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10m4-4 4-4-4-4m4 4H9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="admin-menu-toggle" type="button" aria-label="Open navigation" aria-controls="adminSidebar" aria-expanded="false" data-sidebar-toggle>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <a class="admin-mobile-brand" href="{{ route('home') }}">
                    <span class="admin-brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M8 3h8l1 4 2 2v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9l2-2 1-4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M7 7h10M8 12h8M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                    </span>
                    <span>{{ config('app.name', 'Milk Delivery') }}</span>
                </a>
                <div class="admin-topbar-context">
                    <span class="admin-topbar-eyebrow">WORKSPACE</span>
                    <span class="admin-topbar-divider">/</span>
                    <span class="admin-topbar-page">@yield('title', 'Dashboard')</span>
                </div>
                <div class="admin-topbar-date">{{ now()->format('D, M j, Y') }}</div>
            </header>

            <main class="admin-content">
                <div class="admin-content-inner">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <div class="fw-semibold mb-1">Please check the following:</div>
                            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
            <footer class="admin-footer">
                <span>© {{ now()->year }} {{ config('app.name', 'Milk Delivery') }}</span>
                <span>Fresh deliveries, made simple.</span>
            </footer>
        </div>
    @else
        <div class="admin-auth-brand">
            <a class="admin-brand" href="{{ route('login') }}">
                <span class="admin-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M8 3h8l1 4 2 2v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V9l2-2 1-4Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M7 7h10M8 12h8M9 16h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <span class="admin-brand-copy">
                    <span class="admin-brand-name">{{ config('app.name', 'Milk Delivery') }}</span>
                    <span class="admin-brand-caption">ADMIN PORTAL</span>
                </span>
            </a>
        </div>
        <main class="admin-auth-content">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <div class="fw-semibold mb-1">Please check the following:</div>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @yield('content')
        </main>
    @endauth
</body>
</html>
