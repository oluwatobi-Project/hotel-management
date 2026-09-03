<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\RestaurantMenuItem;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RestaurantOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = RestaurantOrder::with(['booking.guest', 'guest', 'room', 'items.menuItem']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderByRaw("FIELD(status, 'pending', 'preparing', 'served', 'cancelled')")->orderByDesc('created_at')->paginate(20)->withQueryString();

        $menuItems = RestaurantMenuItem::where('is_available', true)->orderBy('name')->get();
        $rooms = Room::orderBy('room_number')->get();
        $bookings = Booking::with('guest')->whereIn('status', ['reserved', 'checked_in'])->orderByDesc('created_at')->get();
        $guests = Guest::orderBy('name')->get();

        return view('restaurant.orders', compact('orders', 'menuItems', 'rooms', 'bookings', 'guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:restaurant_menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $items = collect($data['items'])->filter(fn ($i) => ($i['quantity'] ?? 0) > 0);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'Add at least one menu item.']);
        }

        $menuItemIds = $items->pluck('id');
        $prices = RestaurantMenuItem::whereIn('id', $menuItemIds)->pluck('price', 'id');

        $total = 0;
        foreach ($items as $line) {
            $total += (float) $prices[$line['id']] * $line['quantity'];
        }

        $order = RestaurantOrder::create([
            'order_no' => RestaurantOrder::generateOrderNo(),
            'booking_id' => $data['booking_id'] ?? null,
            'guest_id' => $data['guest_id'] ?? null,
            'room_id' => $data['room_id'] ?? null,
            'status' => 'pending',
            'total' => round($total, 2),
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($items as $line) {
            RestaurantOrderItem::create([
                'restaurant_order_id' => $order->id,
                'restaurant_menu_item_id' => $line['id'],
                'quantity' => $line['quantity'],
                'unit_price' => $prices[$line['id']],
            ]);
        }

        $roomLabel = $order->room?->room_number ?? ($order->booking?->room?->room_number ?? '—');
        $guestLabel = $order->guest?->name ?? ($order->booking?->guest?->name ?? 'Walk-in');

        AppNotification::sendToAll(
            'New restaurant order',
            sprintf('Order %s — Room %s (%s) — %s', $order->order_no, $roomLabel, $guestLabel, $order->itemsCountLabel()),
            'info',
            route('restaurant.orders.index', ['status' => 'pending'])
        );

        return back()->with('success', "Order {$order->order_no} created.");
    }

    public function setStatus(Request $request, RestaurantOrder $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', RestaurantOrder::STATUSES)],
        ]);

        $order->update(['status' => $data['status']]);

        return back()->with('success', "Order {$order->order_no} marked as {$data['status']}.");
    }

    public function destroy(Request $request, RestaurantOrder $order)
    {
        $order->delete();

        return back()->with('success', 'Order deleted.');
    }
}
