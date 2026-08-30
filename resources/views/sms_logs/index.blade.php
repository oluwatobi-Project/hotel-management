@extends('layouts.app')

@section('title', 'SMS Log')
@section('page-title', 'SMS Log')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color:var(--navy)">Outgoing SMS Messages</h6>
            <form method="POST" action="{{ route('sms.test') }}" class="d-flex gap-2">
                @csrf
                <input type="text" name="to" class="form-control form-control-sm" style="width:180px" placeholder="+1 555 000 1234" required>
                <input type="text" name="message" class="form-control form-control-sm" style="width:220px" placeholder="Test message…">
                <button class="btn btn-outline-primary btn-sm"><i class="bi bi-send"></i> Send Test</button>
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
                        <th>Message</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Booking</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-muted">#{{ $log->id }}</td>
                            <td>{{ $log->to_number }}</td>
                            <td><small>{{ Str::limit($log->message, 90) }}</small></td>
                            <td><span class="badge text-bg-light">{{ $log->provider }}</span></td>
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
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No SMS messages sent yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
