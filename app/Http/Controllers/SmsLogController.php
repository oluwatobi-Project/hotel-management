<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;

class SmsLogController extends Controller
{
    public function index()
    {
        $logs = SmsLog::with('booking')->orderByDesc('created_at')->paginate(20);

        return view('sms_logs.index', compact('logs'));
    }
}
