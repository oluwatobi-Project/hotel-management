<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomRequest;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@hotel.local'],
            [
                'name' => 'Hotel Manager',
                'phone' => '+1 555 100 0001',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'staff@hotel.local'],
            [
                'name' => 'Receptionist Jane',
                'phone' => '+1 555 100 0002',
                'role' => 'staff',
                'password' => Hash::make('password'),
            ]
        );

        $settings = [
            'hotel_name' => 'Grand Horizon Hotel',
            'hotel_address' => '12 Ocean Drive, Marina Bay',
            'hotel_phone' => '+1 555 123 4567',
            'hotel_email' => 'reservations@grandhorizon.local',
            'currency' => '$',
            'smtp_host' => 'smtp.mailtrap.io',
            'smtp_port' => '587',
            'sms_provider' => 'Log (development)',
        ];
        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        if (RoomType::count() > 0) {
            return;
        }

        $standard = RoomType::create([
            'name' => 'Standard', 'price' => 88, 'capacity' => 2, 'bed_count' => 1,
            'amenities' => 'WiFi, TV, Air Conditioning, Work Desk',
            'description' => 'Comfortable room with a queen bed.',
        ]);
        $deluxe = RoomType::create([
            'name' => 'Deluxe', 'price' => 148, 'capacity' => 3, 'bed_count' => 2,
            'amenities' => 'WiFi, 55" Smart TV, Bathtub, Mini Bar, City View',
            'description' => 'Spacious room with a king bed and sofa area.',
        ]);
        $suite = RoomType::create([
            'name' => 'Suite', 'price' => 288, 'capacity' => 4, 'bed_count' => 2,
            'amenities' => 'WiFi, 65" TV, Living Room, Jacuzzi, Ocean View',
            'description' => 'Luxurious suite with separate living room.',
        ]);

        $roomPlan = [
            ['101', 1, $standard], ['102', 1, $standard], ['103', 1, $standard],
            ['201', 2, $standard], ['202', 2, $deluxe], ['203', 2, $deluxe],
            ['301', 3, $deluxe], ['302', 3, $deluxe], ['303', 3, $suite],
            ['401', 4, $suite], ['402', 4, $suite], ['501', 5, $suite],
        ];
        $rooms = [];
        foreach ($roomPlan as [$num, $floor, $type]) {
            $rooms[$num] = Room::create([
                'room_number' => $num, 'floor' => $floor, 'room_type_id' => $type->id, 'status' => 'available',
            ]);
        }

        $guests = [];
        $guestData = [
            ['Zhang Wei', 'zhangwei@example.com', '+86 138 0000 1234', '110101199001011234', 'China'],
            ['Li Na', 'lina@example.com', '+86 139 0000 5678', '310101199203026789', 'China'],
            ['Chen Hao', 'chenhao@example.com', '+86 137 0000 9876', '440101198805051111', 'China'],
            ['Emily Johnson', 'emily.j@example.com', '+1 415 555 0132', 'E1234567', 'USA'],
            ['Wang Fang', 'wangfang@example.com', '+86 136 0000 4567', '510101199512127777', 'China'],
            ['Akira Tanaka', 'akira.t@example.com', '+81 90 1234 5678', 'TK902211', 'Japan'],
        ];
        foreach ($guestData as [$name, $email, $phone, $idCard, $country]) {
            $guests[] = Guest::create([
                'name' => $name, 'email' => $email, 'phone' => $phone,
                'id_card' => $idCard, 'nationality' => $country,
                'address' => $country,
            ]);
        }

        $today = now();
        $bookings = [];

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-A1B2C3', 'guest_id' => $guests[0]->id, 'room_id' => $rooms['101']->id,
            'check_in_date' => $today->copy()->subDays(3), 'check_out_date' => $today->copy()->subDays(1),
            'status' => 'checked_out', 'total_amount' => 176, 'discount' => 0, 'notes' => 'Quiet floor requested',
            'created_by' => $admin->id,
        ]);

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-D4E5F6', 'guest_id' => $guests[1]->id, 'room_id' => $rooms['202']->id,
            'check_in_date' => $today->copy()->subDays(2), 'check_out_date' => $today->copy()->addDays(2),
            'status' => 'checked_in', 'total_amount' => 592, 'discount' => 0, 'notes' => 'Business trip',
            'created_by' => $staff->id,
        ]);

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-G7H8I9', 'guest_id' => $guests[2]->id, 'room_id' => $rooms['401']->id,
            'check_in_date' => $today->copy()->subDays(1), 'check_out_date' => $today->copy()->addDays(3),
            'status' => 'checked_in', 'total_amount' => 1152, 'discount' => 0, 'notes' => 'Anniversary celebration',
            'created_by' => $staff->id,
        ]);

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-J1K2L3', 'guest_id' => $guests[3]->id, 'room_id' => $rooms['103']->id,
            'check_in_date' => $today->copy()->addDays(1), 'check_out_date' => $today->copy()->addDays(4),
            'status' => 'reserved', 'total_amount' => 264, 'discount' => 0, 'notes' => 'Early check-in requested',
            'created_by' => $admin->id,
        ]);

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-M4N5O6', 'guest_id' => $guests[4]->id, 'room_id' => $rooms['302']->id,
            'check_in_date' => $today->copy()->addDays(4), 'check_out_date' => $today->copy()->addDays(7),
            'status' => 'reserved', 'total_amount' => 444, 'discount' => 10,
            'created_by' => $staff->id,
        ]);

        $bookings[] = Booking::create([
            'booking_ref' => 'GH-P7Q8R9', 'guest_id' => $guests[1]->id, 'room_id' => $rooms['102']->id,
            'check_in_date' => $today->copy()->subDays(5), 'check_out_date' => $today->copy()->subDays(2),
            'status' => 'cancelled', 'total_amount' => 264, 'discount' => 0, 'notes' => 'Cancelled by guest',
            'created_by' => $admin->id,
        ]);

        $rooms['202']->update(['status' => 'occupied']);
        $rooms['401']->update(['status' => 'occupied']);
        $rooms['302']->update(['status' => 'cleaning']);

        Payment::create([
            'receipt_no' => 'RCP-100001', 'booking_id' => $bookings[0]->id,
            'amount' => 176, 'method' => 'card', 'status' => 'paid', 'paid_at' => $today->copy()->subDay()->subHours(3),
        ]);
        Payment::create([
            'receipt_no' => 'RCP-100002', 'booking_id' => $bookings[1]->id,
            'amount' => 296, 'method' => 'mobile', 'status' => 'paid', 'paid_at' => $today->copy()->subDays(2),
        ]);
        Payment::create([
            'receipt_no' => 'RCP-100003', 'booking_id' => $bookings[2]->id,
            'amount' => 288, 'method' => 'cash', 'status' => 'paid', 'paid_at' => $today->copy()->subDay(),
        ]);

        RoomRequest::create([
            'booking_id' => $bookings[1]->id, 'room_id' => $rooms['202']->id, 'guest_id' => $guests[1]->id,
            'request_type' => 'cleaning', 'description' => 'Room needs a full clean and fresh towels.',
            'priority' => 'high', 'status' => 'in_progress', 'assigned_to' => $staff->id,
        ]);        RoomRequest::create([
            'booking_id' => $bookings[2]->id, 'room_id' => $rooms['401']->id, 'guest_id' => $guests[2]->id,
            'request_type' => 'food', 'description' => 'Requesting a fruit platter and two glasses of champagne.',
            'priority' => 'medium', 'status' => 'pending',
        ]);
        RoomRequest::create([
            'booking_id' => $bookings[3]->id, 'room_id' => $rooms['103']->id, 'guest_id' => $guests[3]->id,
            'request_type' => 'amenity', 'description' => 'Please add an extra pillow and a bathrobe.',
            'priority' => 'low', 'status' => 'pending',
        ]);

        AppNotification::create([
            'user_id' => null, 'title' => 'Welcome to Grand Horizon Hotel',
            'message' => 'The hotel management system is ready. Explore bookings, rooms, and guest services.',
            'type' => 'success', 'link' => '/dashboard',
        ]);

        SmsLog::create([
            'booking_id' => $bookings[1]->id, 'to_number' => $guests[1]->phone,
            'message' => 'Welcome, Li Na! You have checked in to room 202. Enjoy your stay.',
            'provider' => 'log', 'status' => 'sent',
        ]);
    }
}
