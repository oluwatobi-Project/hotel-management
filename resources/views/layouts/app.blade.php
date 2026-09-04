<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ $settings['hotel_name'] ?? 'Grand Horizon Hotel' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --navy: #0f2239;
            --navy-2: #16324f;
            --gold: #c8a24b;
            --gold-soft: #f5ecd9;
            --bg: #f4f6fa;
        }
        body { background: var(--bg); font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .sidebar {
            width: 236px; min-height: 100vh; position: fixed; inset-block: 0; left: 0;
            background: linear-gradient(180deg, var(--navy), var(--navy-2)); z-index: 1030;
        }
        .main { margin-left: 236px; padding: 24px 28px; min-height: 100vh; }
        .brand { display:flex; align-items:center; gap:10px; padding:20px 18px; border-bottom:1px solid rgba(255,255,255,.08); }
        .brand-icon {
            width:38px; height:38px; border-radius:10px; flex-shrink:0;
            background: linear-gradient(135deg, var(--gold), #a8822f);
            display:flex; align-items:center; justify-content:center; color:#fff; font-size:19px;
        }
        .brand-name { color:#fff; font-weight:700; font-size:15px; letter-spacing:.3px; }
        .brand-sub { color:#8fa3bb; font-size:11px; }
        .nav-link { color:#b9c6d6; border-radius:9px; margin:2px 10px; padding:11px 14px; font-size:14px; display:flex; gap:11px; align-items:center; }
        .nav-link:hover { color:#fff; background:rgba(255,255,255,.06); }
        .nav-link.active { color:var(--gold); background:rgba(200,162,75,.16); font-weight:600; }
        .nav-link i { font-size:17px; width:20px; }
        .sidebar-foot { padding:16px 18px; border-top:1px solid rgba(255,255,255,.08); color:#7d91a9; font-size:11px; }
        .stat-card { border:0; border-radius:14px; box-shadow:0 1px 3px rgba(15,34,57,.07), 0 6px 18px rgba(15,34,57,.05); }
        .stat-card .label { color:#6b7a8c; font-size:13px; }
        .stat-card .value { font-size:26px; font-weight:700; color:var(--navy); }
        .stat-card .sub { font-size:12px; color:#6b7a8c; }
        .card { border-color:#e6ebf2; border-radius:14px; box-shadow:0 1px 2px rgba(15,34,57,.05); }
        .table > :not(caption) > * > * { padding:.7rem .8rem; }
        .table thead th { font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#6b7a8c; border-bottom-width:2px; }
        .avatar {
            width:32px; height:32px; border-radius:50%; flex-shrink:0;
            background: linear-gradient(135deg, var(--navy), var(--gold)); color:#fff;
            display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:700;
        }
        .badge-status { font-size:12px; font-weight:600; }
        .btn-gold { background:var(--gold); color:#fff; border:0; }
        .btn-gold:hover { color:#fff; filter:brightness(.96); }
        .room-card { border:1px solid #e6ebf2; border-radius:12px; }
        .room-dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
        .dot-available { background:#1f9d6d; }
        .dot-occupied { background:#d64550; }
        .dot-maintenance { background:#d98a2b; }
        .dot-cleaning { background:#2f6fed; }
        .notif-item { white-space:normal; border-bottom:1px solid #eef1f5; }
        .notif-item:last-child { border-bottom:0; }
        .dropdown-menu { box-shadow:0 10px 40px rgba(15,34,57,.15); border:0; }
        .text-gold { color: var(--gold); }
        .money { font-weight:600; color:var(--navy); }
        .pagination { margin-bottom:0; }
        .page-link { color: var(--navy); }
        .page-item.active .page-link { background: var(--navy); border-color: var(--navy); }
        @media (max-width: 992px) {
            .sidebar { position: static; width: 100%; min-height: auto; }
            .main { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="bi bi-building"></i></div>
        <div>
            <div class="brand-name">{{ $settings['hotel_name'] ?? 'Grand Horizon' }}</div>
            <div class="brand-sub">Hotel Management</div>
        </div>
    </div>
    <div class="mt-2">
        @php
            $user = auth()->user();
            $navLinks = [];
            foreach (config('rbac.modules', []) as $key => $definition) {
                if ($user->isAdmin() || $user->canModule($key)) {
                    $navLinks[] = ['key' => $key, 'label' => $definition['label'], 'icon' => $definition['icon'], 'route' => $key];
                }
            }
            $navAdminLinks = [];
            if ($user->isAdmin()) {
                foreach (config('rbac.admin_modules', []) as $key => $definition) {
                    $navAdminLinks[] = ['key' => $key, 'label' => $definition['label'], 'icon' => $definition['icon'], 'route' => $key];
                }
            }
        @endphp
        @foreach($navLinks as $link)
            @if($link['key'] === 'dashboard')
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @elseif($link['key'] === 'requests')
                <a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @elseif($link['key'] === 'restaurant')
                <a class="nav-link {{ request()->routeIs('restaurant.*') ? 'active' : '' }}" href="{{ route('restaurant.menu') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @elseif($link['key'] === 'notifications')
                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @elseif($link['key'] === 'sms-logs')
                <a class="nav-link {{ request()->routeIs('sms-logs.*') ? 'active' : '' }}" href="{{ route('sms-logs.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @else
                <a class="nav-link {{ request()->routeIs($link['key'].'.*') ? 'active' : '' }}" href="{{ route($link['key'].'.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
            @endif
        @endforeach
        @if($user->isAdmin())
            <hr style="border-color:rgba(255,255,255,.08);margin:8px 12px">
            @foreach($navAdminLinks as $link)
                @if($link['key'] === 'roles')
                    <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
                @elseif($link['key'] === 'staff')
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
                @else
                    <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}</a>
                @endif
            @endforeach
        @endif
    </div>
    <div class="sidebar-foot">
        @if($user->isAdmin())
            Administrator · Full access
        @else
            {{ $user->accessRole?->name ?? 'Staff' }} account
        @endif
    </div>
</div>

<div class="main">
    <nav class="navbar navbar-expand px-0 mb-3">
        <div class="container-fluid px-0">
            <div class="d-none d-sm-block">
                <h1 class="h4 mb-0" style="color:var(--navy)">@yield('page-title', 'Dashboard')</h1>
                <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:var(--gold)">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" style="width:340px">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2">
                            <strong>Notifications</strong>
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                            </form>
                        </div>
                        <div style="max-height:340px;overflow-y:auto">
                            @forelse($notifications as $notification)
                                <a href="{{ $notification->link ? route('notifications.read', $notification->id) : '#' }}" class="dropdown-item notif-item d-flex gap-2 {{ $notification->is_read ? 'text-muted' : '' }}">
                                    <i class="bi {{ match($notification->type) { 'success' => 'bi-check-circle text-success', 'danger' => 'bi-x-circle text-danger', 'warning' => 'bi-exclamation-triangle text-warning', default => 'bi-info-circle text-primary' } }}"></i>
                                    <span>
                                        <span class="d-block fw-semibold">{{ $notification->title }}</span>
                                        <span class="d-block small text-muted">{{ $notification->message }}</span>
                                        <span class="d-block small text-muted">{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                </a>
                            @empty
                                <div class="dropdown-item text-muted text-center py-3">No notifications</div>
                            @endforelse
                        </div>
                        <a href="{{ route('notifications.index') }}" class="dropdown-item text-center fw-semibold py-2" style="color:var(--gold)">View all</a>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">{{ ucfirst(auth()->user()->role) }} account</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
