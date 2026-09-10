@extends('layouts.app')

@section('title', 'Email Log')
@section('page-title', 'Email Log')

@section('content')
<div id="emailAlert"></div>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <h6 class="fw-bold mb-0" style="color:var(--navy)">Outgoing Emails</h6>
                <span class="badge text-bg-success">{{ $counts['sent'] }} sent</span>
                <span class="badge text-bg-danger">{{ $counts['failed'] }} failed</span>
                <span class="badge text-bg-light">{{ $counts['total'] }} total</span>
            </div>
            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#testEmailModal">
                <i class="bi bi-envelope-arrow-up"></i> Send Test Email
            </button>
        </div>
        <div class="mt-2">
            <form class="d-flex flex-wrap gap-2" method="GET">
                <input type="text" name="search" class="form-control form-control-sm" style="width:240px" placeholder="Search recipient, subject or type…" value="{{ request('search') }}">
                <select name="status" class="form-select form-select-sm" style="width:150px" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                </select>
                @if(request()->has('status') || request()->has('search'))
                    <a href="/email-logs" class="btn btn-outline-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Booking</th>
                        <th>Sent At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted">#{{ $log->id }}</td>
                            <td class="small">{{ $log->to_email }}</td>
                            <td>
                                <span class="small d-inline-block" style="max-width:320px" title="{{ $log->subject }}">
                                    {{ Str::limit($log->subject, 70) }}
                                </span>
                                @if($log->status === 'failed' && $log->error)
                                    <i class="bi bi-exclamation-circle text-danger ms-1" title="{{ $log->error }}" data-bs-toggle="tooltip"></i>
                                @endif
                            </td>
                            <td><span class="badge text-bg-light">{{ $log->mailable }}</span></td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge text-bg-success">Sent</span>
                                @else
                                    <span class="badge text-bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>
                                @if($log->booking)
                                    <a href="{{ route('bookings.show', $log->booking_id) }}" class="small">{{ $log->booking->booking_ref }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="text-end">
                                <a href="/email-logs/{{ $log->id }}/preview" target="_blank" class="btn btn-outline-secondary btn-sm" title="View email body">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-outline-primary btn-sm" title="Re-send this email" data-bs-toggle="modal" data-bs-target="#resendEmailModal"
                                    onclick="openResend('{{ $log->id }}', '{{ $log->to_email }}', {{ json_encode($log->subject) }})">
                                    <i class="bi bi-send"></i> Re-send
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No emails have been sent yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="resendEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="resendEmailForm">
            @csrf
            <input type="hidden" name="log_id" id="resendLogId">
            <div class="modal-header">
                <h5 class="modal-title">Re-send Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2" id="resendSubjectLabel">Re-deliver this email to the guest using the original body.</p>
                <label class="form-label small fw-semibold">To</label>
                <input type="email" name="to_email" class="form-control" id="resendTo" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="resendSubmitBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="resendSpinner"></span>Re-send
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="testEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" id="testEmailForm">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Send Test Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-semibold">To</label>
                <input type="email" name="to_email" class="form-control" placeholder="guest@example.com" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="testSubmitBtn">
                    <span class="spinner-border spinner-border-sm d-none me-1" id="testSpinner"></span>Send
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showEmailAlert(type, message) {
    const el = document.getElementById('emailAlert');
    el.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    setTimeout(() => { el.innerHTML = ''; }, 5000);
}

const emailCsrf = () => document.querySelector('meta[name="csrf-token"]').content;

function openResend(id, to, subject) {
    document.getElementById('resendLogId').value = id;
    document.getElementById('resendTo').value = to;
    document.getElementById('resendSubjectLabel').textContent = subject ? 'Re-deliver to the guest using the original body: "' + subject + '"' : 'Re-deliver to the guest using the original body.';
}

document.getElementById('resendEmailForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('resendSubmitBtn');
    const spinner = document.getElementById('resendSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    const body = new URLSearchParams({
        _token: emailCsrf(),
        to_email: document.getElementById('resendTo').value
    });

    fetch('/email-logs/' + document.getElementById('resendLogId').value + '/resend', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': emailCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: body
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        bootstrap.Modal.getInstance(document.getElementById('resendEmailModal')).hide();
        showEmailAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Email re-sent.' : 'Could not re-send email.'));
        if (ok) setTimeout(() => window.location.reload(), 700);
    })
    .catch(() => {
        bootstrap.Modal.getInstance(document.getElementById('resendEmailModal')).hide();
        showEmailAlert('danger', 'Network error while re-sending.');
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('d-none');
    });
});

document.getElementById('testEmailForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const btn = document.getElementById('testSubmitBtn');
    const spinner = document.getElementById('testSpinner');
    btn.disabled = true;
    spinner.classList.remove('d-none');

    fetch('/emails/test', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': emailCsrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: new URLSearchParams(new FormData(this))
    })
    .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
    .then(({ ok, data }) => {
        bootstrap.Modal.getInstance(document.getElementById('testEmailModal')).hide();
        showEmailAlert(ok ? 'success' : 'danger', data.message || (ok ? 'Test email sent.' : 'Could not send test email.'));
        if (ok) setTimeout(() => window.location.reload(), 700);
    })
    .catch(() => {
        bootstrap.Modal.getInstance(document.getElementById('testEmailModal')).hide();
        showEmailAlert('danger', 'Network error while sending test email.');
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('d-none');
    });
});
</script>
@endpush
