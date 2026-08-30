@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0" style="color:var(--navy)">{{ $notifications->total() }} Notifications</h6>
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all"></i> Mark all read</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <a href="{{ $notification->link ? route('notifications.read', $notification->id) : '#' }}"
                   class="list-group-item list-group-item-action d-flex gap-3 {{ $notification->is_read ? 'opacity-75' : 'bg-light' }}">
                    <i class="bi fs-4 {{ match($notification->type) { 'success' => 'bi-check-circle text-success', 'danger' => 'bi-x-circle text-danger', 'warning' => 'bi-exclamation-triangle text-warning', default => 'bi-info-circle text-primary' } }}"></i>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $notification->title }}</span>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-muted">{{ $notification->message }}</div>
                    </div>
                    @if(! $notification->is_read)
                        <span class="badge rounded-pill align-self-center" style="background:var(--gold)">New</span>
                    @endif
                </a>
            @empty
                <div class="text-center text-muted py-5">No notifications</div>
            @endforelse
        </div>
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
