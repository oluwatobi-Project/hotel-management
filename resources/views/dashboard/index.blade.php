@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="label">Occupancy Rate</div>
                        <div class="value">{{ $occupancyRate }}%</div>
                        <div class="sub">{{ $occupied }} of {{ $totalRooms }} rooms in use</div>
                    </div>
                    <span class="badge rounded-pill text-bg-light fs-6"><i class="bi bi-pie-chart text-gold"></i></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="label">Available Rooms</div>
                <div class="value">{{ $available }}</div>
                <div class="sub">{{ $cleaning }} cleaning · {{ $maintenance }} maintenance</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="label">Active Bookings</div>
                <div class="value">{{ $activeBookings }}</div>
                <div class="sub">{{ $todayCheckIns }} arrivals · {{ $todayCheckOuts }} departures today</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="label">Revenue</div>
                <div class="value">{{ $settings['currency'] }}{{ number_format($totalRevenue, 2) }}</div>
                <div class="sub">{{ $settings['currency'] }}{{ number_format($monthRevenue, 2) }} this month</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-lg-4">
        <div class="row g-3">
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div class="display-6 fw-bold" style="color:var(--gold)">{{ $totalGuests }}</div>
                        <div class="text-muted small">Total Guests</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        <div class="display-6 fw-bold" style="color:#2f6fed">{{ $pendingRequests }}</div>
                        <div class="text-muted small">Open Room Requests</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3" style="color:var(--navy)">Room Type Breakdown</h6>
                @foreach($roomTypeStats as $rt)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-semibold">{{ $rt->name }}</span>
                            <span class="text-muted">{{ (int) $rt->occupied }}/{{ (int) $rt->total }} occupied</span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $rt->total > 0 ? round(((int) $rt->occupied / (int) $rt->total) * 100) : 0 }}%; background: linear-gradient(90deg,#c8a24b,#d9b45e)"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-0" style="color:var(--navy)">Revenue Flow — Last 7 Days</h6>
                    <small class="text-muted">Collected payments, {{ \Carbon\Carbon::now()->subDays(6)->format('M j') }} – {{ \Carbon\Carbon::now()->format('M j, Y') }}</small>
                </div>
                <div class="text-end">
                    <div class="h5 fw-bold mb-0" style="color:var(--navy)">{{ $settings['currency'] }}{{ number_format($weeklyRevenue, 2) }}</div>
                    <small class="text-muted">this week
                        @if($revenueChange !== null)
                            <span class="fw-semibold {{ $revenueChange >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-{{ $revenueChange >= 0 ? 'arrow-up-right' : 'arrow-down-right' }}"></i>{{ $revenueChange >= 0 ? '+' : '' }}{{ $revenueChange }}%
                            </span>
                            <span class="text-muted">vs prior week</span>
                        @endif
                    </small>
                </div>
            </div>
            <div class="card-body">
                <div style="position:relative;height:250px">
                    <canvas id="revenueChart"></canvas>
                </div>
                @if($revenuePeak)
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top small text-muted">
                        <span><i class="bi bi-trophy text-gold me-1"></i>Best day: <strong>{{ $revenuePeak['date'] }}</strong> — <span class="money">{{ $settings['currency'] }}{{ number_format($revenuePeak['amount'], 2) }}</span></span>
                        <span class="d-none d-sm-inline">Previous week shown as the faint line below</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color:var(--navy)">Active & Upcoming Stays</h6>
                <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm"><i class="bi bi-plus-lg"></i> New Booking</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $booking)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar">{{ strtoupper(substr($booking->guest->name, 0, 1)) }}</span>
                                            <span class="fw-semibold">{{ $booking->guest->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $booking->room->room_number }} <small class="text-muted">· {{ $booking->room->roomType->name }}</small></td>
                                    <td>{{ $booking->check_in_date->format('d M Y') }}</td>
                                    <td>{{ $booking->check_out_date->format('d M Y') }}</td>
                                    <td class="money">{{ $settings['currency'] }}{{ number_format($booking->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $map = ['reserved' => ['secondary', 'Reserved'], 'checked_in' => ['success', 'Checked In']];
                                        @endphp
                                        <span class="badge text-bg-{{ $map[$booking->status][0] }} badge-status">{{ $map[$booking->status][1] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No active bookings</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0" style="color:var(--navy)">Latest Room Requests</h6>
                <a href="{{ route('requests.index') }}" class="small text-decoration-none" style="color:var(--gold)">View all</a>
            </div>
            <div class="card-body">
                @forelse($recentRequests as $req)
                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                        <div class="avatar" style="background:{{ match($req->priority) { 'high' => '#d64550', 'medium' => '#d98a2b', default => '#2f6fed' } }}">{{ strtoupper(substr($req->request_type, 0, 1)) }}</div>
                        <div>
                            <div class="fw-semibold">{{ ucfirst($req->request_type) }} · Room {{ $req->room->room_number }}</div>
                            <div class="small text-muted">{{ Str::limit($req->description, 70) }}</div>
                            <div class="small mt-1">
                                <span class="badge badge-status text-bg-light">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</span>
                                <span class="text-muted">{{ $req->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">No room requests</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const series = @json($revenueSeries);
    const currency = @json($settings['currency'] ?? '$');
    const fmt = (v) => currency + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const labels = series.map(d => d.label);
    const amounts = series.map(d => d.amount);
    const previous = series.map(d => d.previous);

    const gradient = (ctx, top, bottom) => {
        const g = ctx.createLinearGradient(0, top, 0, bottom);
        g.addColorStop(0, 'rgba(200,162,75,.42)');
        g.addColorStop(1, 'rgba(200,162,75,.02)');
        return g;
    };

    const chart = new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Previous week',
                    data: previous,
                    borderColor: 'rgba(140,155,175,.5)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    pointHitRadius: 12,
                    fill: false,
                    tension: .45,
                    spanGaps: true
                },
                {
                    label: 'Revenue',
                    data: amounts,
                    borderColor: '#c8a24b',
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#c8a24b',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: .45,
                    spanGaps: true,
                    backgroundColor: (ctx) => {
                        const { chart } = ctx;
                        const { ctx: c, chartArea } = chart;
                        if (!chartArea) return 'rgba(200,162,75,.1)';
                        return gradient(c, chartArea.top, chartArea.bottom);
                    }
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: {
                duration: 1400,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,34,57,.95)',
                    titleFont: { weight: '600' },
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        title: (items) => series[items[0].dataIndex]?.full || '',
                        label: (item) => {
                            if (item.datasetIndex === 0) return 'Previous week: ' + fmt(item.parsed.y);
                            return 'Revenue: ' + fmt(item.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grace: '10%',
                    grid: { color: '#eef1f5' },
                    border: { display: false },
                    ticks: {
                        maxTicksLimit: 5,
                        callback: (v) => fmt(v).replace(/\.[0-9]{2}$/, '')
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { weight: '600' } }
                }
            }
        }
    });

    // Re-draw with a slow "flowing" shimmer a moment after first paint.
    setTimeout(() => {
        if (chart) chart.update();
    }, 400);
});
</script>
@endpush
