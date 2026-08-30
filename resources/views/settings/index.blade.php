@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<form method="POST" action="{{ route('settings.update') }}">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-building me-2"></i>Hotel Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Hotel Name</label>
                            <input type="text" name="hotel_name" class="form-control" value="{{ $settings['hotel_name'] }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Address</label>
                            <input type="text" name="hotel_address" class="form-control" value="{{ $settings['hotel_address'] }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Phone</label>
                            <input type="text" name="hotel_phone" class="form-control" value="{{ $settings['hotel_phone'] }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="hotel_email" class="form-control" value="{{ $settings['hotel_email'] }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Currency Symbol</label>
                            <input type="text" name="currency" class="form-control" value="{{ $settings['currency'] }}" maxlength="10">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-3">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-envelope me-2"></i>Email (SMTP)</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="{{ $settings['smtp_host'] }}" placeholder="smtp.example.com">
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold">Port</label>
                            <input type="text" name="smtp_port" class="form-control" value="{{ $settings['smtp_port'] }}" placeholder="587">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="smtp_username" class="form-control" value="{{ $settings['smtp_username'] }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" name="smtp_password" class="form-control" value="{{ $settings['smtp_password'] }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-white"><h6 class="fw-bold mb-0" style="color:var(--navy)"><i class="bi bi-chat-dots me-2"></i>SMS Provider</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Provider</label>
                            <select name="sms_provider" class="form-select">
                                @foreach(['Log (development)', 'Twilio', 'Vonage', 'Africa\'s Talking', 'Custom API'] as $provider)
                                    <option value="{{ $provider }}" @selected($settings['sms_provider'] === $provider)>{{ $provider }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Emails are currently sent to the log mailer in development. Configure SMTP above for real delivery.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">API Key</label>
                            <input type="password" name="sms_api_key" class="form-control" value="{{ $settings['sms_api_key'] }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-gold px-4"><i class="bi bi-check-lg"></i> Save Settings</button>
    </div>
</form>
@endsection
