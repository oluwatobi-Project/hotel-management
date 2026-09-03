@extends('layouts.app')

@section('title', 'Restaurant Orders')
@section('page-title', 'Restaurant Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
        <a href="{{ route('restaurant.menu') }}" class="btn {{ request()->routeIs('restaurant.menu') ? 'btn-gold' : 'btn-outline-gold' }} btn-sm">Menu Items</a>
        <a href="{{ route('restaurant.orders.index') }}" class="btn {{ request()->routeIs('restaurant.orders.*') ? 'btn-gold' : 'btn-outline-gold' }} btn-sm">Orders</a>
    </div>
    <button class="btn btn-gold btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal">
        <i class="bi bi-plus-lg"></i> New Order
    </button>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0" style="color:var(--navy)">Order Queue</h6>
            <form method="GET" class="d-flex gap-1">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach(\App\Models\RestaurantOrder::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Room</th>
                        <th>Guest</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>When</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->order_no }}</td>
                            <td>{{ $order->room?->room_number ?? $order->booking?->room?->room_number ?? '—' }}</td>
                            <td>{{ $order->guest?->name ?? $order->booking?->guest?->name ?? 'Walk-in' }}</td>
                            <td>
                                <small>
                                    @foreach($order->items as $item)
                                        <div>{{ $item->quantity }}× {{ $item->menuItem?->name }}</div>
                                    @endforeach
                                </small>
                            </td>
                            <td class="money">{{ $settings['currency'] }}{{ number_format($order->total, 2) }}</td>
                            <td>
                                <span class="badge badge-status {{ match($order->status) { 'pending' => 'text-bg-warning', 'preparing' => 'text-bg-info', 'served' => 'text-bg-success', 'cancelled' => 'text-bg-danger', default => 'text-bg-light' } }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $order->created_at->diffForHumans() }}</small></td>
                            <td class="text-end">
                                <div class="dropdown d-inline">
                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Update</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach(\App\Models\RestaurantOrder::STATUSES as $status)
                                            @if($status !== $order->status)
                                                <li>
                                                    <form method="POST" action="{{ route('restaurant.orders.status', $order->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button class="dropdown-item small">{{ ucfirst($status) }}</button>
                                                    </form>
                                                </li>
                                            @endif
                                        @endforeach
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('restaurant.orders.destroy', $order->id) }}" onsubmit="return confirm('Delete this order?')">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item small text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No orders found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $orders->links() }}</div>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('restaurant.orders.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">New Restaurant Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Room</label>
                        <select name="room_id" class="form-select" id="orderRoom">
                            <option value="">— Walk-in / pick later —</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">Room {{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Booking (optional)</label>
                        <select name="booking_id" class="form-select">
                            <option value="">None</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}">{{ $booking->booking_ref }} · {{ $booking->guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Guest (optional)</label>
                        <select name="guest_id" class="form-select">
                            <option value="">None</option>
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}">{{ $guest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <label class="form-label small fw-semibold">Menu Items</label>
                <div id="orderItems">
                    <div class="row g-2 order-line mb-2">
                        <div class="col-7">
                            <select name="items[0][id]" class="form-select form-select-sm">
                                <option value="">Select item...</option>
                                @foreach($menuItems as $item)
                                    <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->name }} — {{ $settings['currency'] }}{{ number_format($item->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm" value="1" min="1">
                        </div>
                        <div class="col-2 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.order-line').remove(); updateOrderTotal();"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addOrderLine()"><i class="bi bi-plus"></i> Add item</button>

                <div class="alert alert-light border small mt-3 mb-0">Total: <strong id="orderTotal">{{ $settings['currency'] }}0.00</strong></div>

                <div class="mt-3">
                    <label class="form-label small fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Allergies, table, delivery instructions..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Place Order</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let orderLineIndex = 1;

function addOrderLine() {
    const html = `
        <div class="row g-2 order-line mb-2">
            <div class="col-7">
                <select name="items[${orderLineIndex}][id]" class="form-select form-select-sm">
                    <option value="">Select item...</option>
                    @foreach($menuItems as $item)
                        <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->name }} — {{ $settings['currency'] }}{{ number_format($item->price, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="items[${orderLineIndex}][quantity]" class="form-control form-control-sm" value="1" min="1">
            </div>
            <div class="col-2 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.order-line').remove(); updateOrderTotal();"><i class="bi bi-trash"></i></button>
            </div>
        </div>`;
    document.getElementById('orderItems').insertAdjacentHTML('beforeend', html);
    orderLineIndex++;
}

function updateOrderTotal() {
    let total = 0;
    document.querySelectorAll('.order-line').forEach(line => {
        const sel = line.querySelector('select');
        const qty = parseInt(line.querySelector('input[type=number]').value, 10) || 0;
        if (sel.value && qty > 0) total += parseFloat(sel.selectedOptions[0].dataset.price) * qty;
    });
    document.getElementById('orderTotal').textContent = '{{ $settings["currency"] }}' + total.toFixed(2);
}

document.getElementById('orderItems').addEventListener('change', updateOrderTotal);
document.getElementById('orderItems').addEventListener('input', updateOrderTotal);

document.getElementById('orderModal').addEventListener('show.bs.modal', () => {
    orderLineIndex = 1;
});
</script>
@endpush
