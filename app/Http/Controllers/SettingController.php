<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'hotel_name' => Setting::get('hotel_name', 'Grand Horizon Hotel'),
            'hotel_address' => Setting::get('hotel_address', ''),
            'hotel_phone' => Setting::get('hotel_phone', ''),
            'hotel_email' => Setting::get('hotel_email', ''),
            'currency' => Setting::get('currency', '$'),
            'smtp_host' => Setting::get('smtp_host', ''),
            'smtp_port' => Setting::get('smtp_port', '587'),
            'smtp_username' => Setting::get('smtp_username', ''),
            'smtp_password' => Setting::get('smtp_password', ''),
            'sms_provider' => Setting::get('sms_provider', 'Log (development)'),
            'sms_api_key' => Setting::get('sms_api_key', ''),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hotel_name' => ['required', 'string', 'max:150'],
            'hotel_address' => ['nullable', 'string', 'max:255'],
            'hotel_phone' => ['nullable', 'string', 'max:30'],
            'hotel_email' => ['nullable', 'email', 'max:150'],
            'currency' => ['nullable', 'string', 'max:10'],
            'smtp_host' => ['nullable', 'string', 'max:150'],
            'smtp_port' => ['nullable', 'string', 'max:10'],
            'smtp_username' => ['nullable', 'string', 'max:150'],
            'smtp_password' => ['nullable', 'string', 'max:150'],
            'sms_provider' => ['nullable', 'string', 'max:150'],
            'sms_api_key' => ['nullable', 'string', 'max:150'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        AppNotification::sendToAll('Settings updated', 'Hotel settings were updated.', 'info', route('settings.index'));

        return back()->with('success', 'Settings saved.');
    }
}
