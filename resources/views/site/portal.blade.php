@extends('layouts.site')

@section('title', 'My Booking ' . $booking->booking_ref . ' — ' . ($settings['hotel_name'] ?? 'Grand Horizon Hotel'))

@section('content')
<section class="py-5" style="background:var(--cream); min-height:60vh">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="font-display fw-bold mb-0">Booking {{ $booking->booking_ref }}</h4>
                <span class="text-muted small">Hi, {{ $booking->guest->name }}</span>
            </div>
            <a href="{{ route('site.booking.lookup') }}" class="btn btn-outline-gold btn-sm">Search another</a>
        </div>

        @php
            $badge = ['reserved' => 'text-bg-warning', 'checked_in' => 'text-bg-success', 'checked_out' => 'text-bg-secondary', 'cancelled' => 'text-bg-danger'][$booking->status] ?? 'text-bg-light';
        @endphp
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0">Booking Details</h6>
                        <span class="badge {{ $badge }} text-uppercase">{{ $booking->status }}</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6"><span class="text-muted small d-block">Room</span><span class="fw-semibold">Room {{ $booking->room->room_number }} — {{ $booking->room->roomType->name }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Check-in</span><span class="fw-semibold">{{ $booking->check_in_date }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Check-out</span><span class="fw-semibold">{{ $booking->check_out_date }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Nights</span><span class="fw-semibold">{{ $booking->nights() }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Total</span><span class="fw-semibold">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->total_amount, 2) }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Paid</span><span class="fw-semibold text-success">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->paidAmount(), 2) }}</span></div>
                        <div class="col-sm-6"><span class="text-muted small d-block">Balance</span><span class="fw-bold" style="color:var(--navy)">{{ $settings['currency'] ?? '$' }}{{ number_format($booking->outstandingAmount(), 2) }}</span></div>
                        @if($booking->status === 'reserved' && $booking->isUnpaid())
                            <div class="col-12">
                                <div class="alert alert-warning py-2 mb-0 small"><i class="bi bi-clock-history me-1"></i>Hold expires {{ $booking->releaseDueAt()->format('M d, Y H:i') }}. Pay now to secure your room.</div>
                            </div>
                        @endif
                    </div>

                    @if($booking->payments->isNotEmpty())
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3">Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light"><tr><th>Receipt</th><th>Date</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                                <tbody>
                                    @foreach($booking->payments as $payment)
                                    <tr>
                                        <td class="small">{{ $payment->receipt_no }}</td>
                                        <td class="small">{{ $payment->paid_at?->format('M d, Y H:i') }}</td>
                                        <td class="small text-uppercase">{{ $payment->method }}</td>
                                        <td class="text-end fw-semibold">{{ $settings['currency'] ?? '$' }}{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if($booking->status === 'checked_in')
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h6 class="fw-semibold mb-3">Request Front Desk Service</h6>
                    <form action="{{ route('site.booking.request', $booking->id) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Service Type</label>
                                <select name="request_type" class="form-select" required>
                                    @foreach(\App\Models\RoomRequest::TYPES as $type)
                                        <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Description</label>
                                <textarea name="description" rows="3" class="form-control" required placeholder="e.g. Extra towels, wake-up call at 7am..."></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-gold px-4" type="submit"><i class="bi bi-send me-2"></i>Send Request</button>
                            </div>
                        </div>
                    </form>

                    @if($booking->requests->isNotEmpty())
                        <hr class="my-4">
                        <h6 class="fw-semibold mb-3">Your Requests</h6>
                        <div class="list-group">
                            @foreach($booking->requests->sortByDesc('created_at') as $req)
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold small">{{ ucwords(str_replace('_', ' ', $req->request_type)) }} <span class="text-muted fw-normal">· {{ $req->created_at->format('M d, H:i') }}</span></div>
                                    <p class="text-muted small mb-0">{{ $req->description }}</p>
                                </div>
                                <span class="badge {{ ['pending' => 'text-bg-warning', 'in_progress' => 'text-bg-info', 'completed' => 'text-bg-success', 'cancelled' => 'text-bg-danger'][$req->status] ?? 'text-bg-light' }}">{{ str_replace('_', ' ', $req->status) }}</span>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                @if($booking->status === 'checked_in' && $menuItems->isNotEmpty())
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-cup-hot me-2 text-gold"></i>Order from the Restaurant</h6>
                    <form action="{{ route('site.booking.restaurant-order', $booking->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Choose your dishes</label>
                            @foreach($menuCategories as $category => $items)
                                <div class="fw-semibold small text-muted text-uppercase mt-2">{{ $category }}</div>
                                @foreach($items as $item)
                                    <label class="d-flex justify-content-between align-items-center border rounded-2 px-3 py-2 mt-1">
                                        <span class="small">{{ $item->name }} <span class="text-muted">— {{ $settings['currency'] ?? '$' }}{{ number_format($item->price, 2) }}</span></span>
                                        <input type="number" name="items[{{ $item->id }}][id]" value="{{ $item->id }}" class="d-none">
                                        <input type="number" name="items[{{ $item->id }}][quantity]" class="form-control form-control-sm" style="width:70px" min="0" max="50" value="0">
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes for the kitchen</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Allergies, preferences..."></textarea>
                        </div>
                        <button class="btn btn-gold px-4" type="submit"><i class="bi bi-bag-check me-2"></i>Place Order</button>
                    </form>
                </div>
                @endif

                @if($booking->status === 'checked_in')
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-water me-2 text-gold"></i>Laundry Service</h6>
                    <form action="{{ route('site.booking.laundry', $booking->id) }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Service Type</label>
                                <select name="service_type" class="form-select" required>
                                    @foreach(\App\Models\LaundryRequest::SERVICE_TYPES as $serviceType)
                                        <option value="{{ $serviceType }}">{{ ucwords(str_replace('_', ' ', $serviceType)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="1" min="1" max="200" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Items</label>
                                <input type="text" name="item_description" class="form-control" placeholder="e.g. 2 shirts, 1 pair of trousers" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Notes</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Stains, special care..."></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-gold px-4" type="submit"><i class="bi bi-send me-2"></i>Send Laundry Request</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <div class="col-lg-4">
                @if(in_array($booking->status, ['reserved', 'checked_in']) && $booking->outstandingAmount() > 0)
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-credit-card me-2 text-gold"></i>Make a Payment</h6>
                    <div class="alert alert-light border small py-2">Outstanding balance: <strong>{{ $settings['currency'] ?? '$' }}{{ number_format($booking->outstandingAmount(), 2) }}</strong></div>
                    <form action="{{ route('site.booking.pay', $booking->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Amount ({{ $settings['currency'] ?? '$' }})</label>
                            <input type="number" name="amount" step="0.01" min="1" max="{{ $booking->outstandingAmount() }}" class="form-control"
                                   value="{{ $booking->outstandingAmount() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Payment Method</label>
                            <select name="method" class="form-select">
                                <option value="card">Credit / Debit Card</option>
                                <option value="mobile">Mobile Money</option>
                                <option value="cash">Cash at Front Desk</option>
                            </select>
                        </div>
                        <div class="demo-card bg-navy text-white rounded-3 p-3 mb-3">
                            <div class="small fw-semibold mb-2">Demo Card Details</div>
                            <div class="row g-2 small">
                                <div class="col-8"><label class="form-label mb-0 text-white-50">Card Number</label><input type="text" name="card_number" class="form-control form-control-sm" placeholder="4242 4242 4242 4242"></div>
                                <div class="col-4"><label class="form-label mb-0 text-white-50">CVV</label><input type="text" name="card_cvv" class="form-control form-control-sm" maxlength="4" placeholder="123"></div>
                                <div class="col-8"><label class="form-label mb-0 text-white-50">Name on Card</label><input type="text" name="card_name" class="form-control form-control-sm" placeholder="John Doe"></div>
                                <div class="col-4"><label class="form-label mb-0 text-white-50">Expiry</label><input type="text" name="card_expiry" class="form-control form-control-sm" maxlength="7" placeholder="12/28"></div>
                            </div>
                            <div class="small text-white-50 mt-2"><i class="bi bi-info-circle me-1"></i>Demo gateway — no real charge is made.</div>
                        </div>
                        <button class="btn btn-gold w-100 py-2"><i class="bi bi-lock me-2"></i>Pay {{ $settings['currency'] ?? '$' }}{{ number_format($booking->outstandingAmount(), 2) }}</button>
                    </form>
                </div>
                @endif

                @if($booking->status === 'reserved')
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4 text-center">
                    <h6 class="fw-semibold mb-2">Cancel Reservation</h6>
                    <p class="text-muted small mb-3">Cancelling will release the room immediately.</p>
                    <form action="{{ route('site.booking.cancel', $booking->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                        @csrf
                        <button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-x-circle me-2"></i>Cancel Booking</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
