<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Grand Horizon Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2239 0%, #16324f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card { border: 0; border-radius: 18px; box-shadow: 0 24px 80px rgba(0,0,0,.4); width: 100%; max-width: 400px; }
        .brand-icon {
            width: 54px; height: 54px; border-radius: 14px; margin: 0 auto;
            background: linear-gradient(135deg, #c8a24b, #a8822f);
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 26px;
        }
        .btn-gold { background: #c8a24b; color: #fff; border: 0; }
        .btn-gold:hover { color: #fff; filter: brightness(.96); }
        .form-control:focus { border-color: #c8a24b; box-shadow: 0 0 0 .2rem rgba(200,162,75,.18); }
    </style>
</head>
<body>
    <div class="card login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon mb-3"><i class="bi bi-building"></i></div>
            <h1 class="h4 mb-1 fw-bold" style="color:#0f2239">Grand Horizon Hotel</h1>
            <p class="text-muted small mb-0">Sign in to the front desk console</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@hotel.local" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label small" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-gold w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Demo access<br>
            <code>admin@hotel.local</code> / <code>password</code>
        </p>
    </div>
</body>
</html>
