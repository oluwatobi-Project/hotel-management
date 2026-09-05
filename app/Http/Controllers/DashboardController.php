<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomRequest;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRooms = Room::count();
        $occupied = Room::where('status', 'occupied')->count();
        $available = Room::where('status', 'available')->count();
        $maintenance = Room::where('status', 'maintenance')->count();
        $cleaning = Room::where('status', 'cleaning')->count();

        $today = now()->toDateString();
        $todayCheckIns = Booking::whereIn('status', ['reserved', 'checked_in'])
            ->whereDate('check_in_date', $today)->count();
        $todayCheckOuts = Booking::where('status', 'checked_in')
            ->whereDate('check_out_date', $today)->count();

        $activeBookings = Booking::whereIn('status', ['reserved', 'checked_in'])->count();
        $totalRevenue = Payment::where('status', 'paid')->sum('amount');
        $monthRevenue = Payment::where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');
        $totalGuests = \App\Models\Guest::count();
        $pendingRequests = RoomRequest::whereIn('status', ['pending', 'in_progress'])->count();

        $occupancyRate = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100) : 0;

        // Daily revenue totals keyed by date (YYYY-MM-DD).
        $dailyRevenue = Payment::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as amount')
            ->groupBy('day')
            ->pluck('amount', 'day');

        // Build a complete, zero-filled 7-day series ending today, plus the
        // matching week before it for a dynamic comparison "flow".
        $revenueSeries = collect(range(6, 0))->map(function ($offset) use ($dailyRevenue) {
            $date = now()->subDays($offset);
            $prior = $date->copy()->subDays(7);
            $key = $date->toDateString();
            $priorKey = $prior->toDateString();

            return [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'full' => $date->format('l, M j'),
                'amount' => (float) ($dailyRevenue[$key] ?? 0),
                'previous' => (float) ($dailyRevenue[$priorKey] ?? 0),
            ];
        })->values();

        $weeklyRevenue = $revenueSeries->sum('amount');
        $previousRevenue = $revenueSeries->sum('previous');
        $revenueChange = $previousRevenue > 0
            ? round((($weeklyRevenue - $previousRevenue) / $previousRevenue) * 100)
            : null;
        $bestDay = $revenueSeries->sortByDesc('amount')->first();
        $revenuePeak = $bestDay && $bestDay['amount'] > 0
            ? ['date' => $bestDay['date'], 'amount' => $bestDay['amount']]
            : null;

        $roomTypeStats = DB::table('room_types as rt')
            ->leftJoin('rooms as r', 'r.room_type_id', '=', 'rt.id')
            ->selectRaw('rt.id, rt.name, COUNT(r.id) as total, SUM(CASE WHEN r.status = "occupied" THEN 1 ELSE 0 END) as occupied')
            ->groupBy('rt.id', 'rt.name')
            ->orderByDesc('rt.price')
            ->get();

        $recentBookings = Booking::with(['guest', 'room.roomType'])
            ->whereIn('status', ['reserved', 'checked_in'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $recentRequests = RoomRequest::with(['room', 'guest', 'booking'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('dashboard.index', compact(
            'totalRooms', 'occupied', 'available', 'maintenance', 'cleaning',
            'todayCheckIns', 'todayCheckOuts', 'activeBookings',
            'totalRevenue', 'monthRevenue', 'totalGuests', 'pendingRequests',
            'occupancyRate', 'weeklyRevenue', 'previousRevenue', 'revenueChange',
            'revenuePeak', 'revenueSeries', 'roomTypeStats',
            'recentBookings', 'recentRequests'
        ));
    }
}
