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

        $weeklyRevenue = Payment::where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as amount')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('amount', 'day');

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
            'occupancyRate', 'weeklyRevenue', 'roomTypeStats',
            'recentBookings', 'recentRequests'
        ));
    }
}
