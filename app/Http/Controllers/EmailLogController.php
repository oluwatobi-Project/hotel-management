<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Services\EmailService;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('booking')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('to_email', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhere('mailable', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $logs = $query->paginate(20)->withQueryString();

        $counts = [
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'total' => EmailLog::count(),
        ];

        return view('email_logs.index', compact('logs', 'counts'));
    }

    public function preview(EmailLog $log)
    {
        return response($log->body ?? '<p>No body captured for this email.</p>', 200, [
            'Content-Type' => 'text/html',
        ]);
    }

    public function resend(Request $request, EmailLog $log, EmailService $emails)
    {
        $data = $request->validate([
            'to_email' => ['nullable', 'email', 'max:191'],
        ]);

        $ok = $emails->resend($log, $data['to_email'] ?? null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $ok
                    ? 'Email re-sent successfully.'
                    : 'Failed to re-send email.',
            ], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Email re-sent successfully.'
            : 'Failed to re-send email.');
    }

    public function test(Request $request, EmailService $emails)
    {
        $data = $request->validate([
            'to_email' => ['required', 'email', 'max:191'],
        ]);

        $ok = $emails->sendTest($data['to_email']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $ok,
                'message' => $ok
                    ? 'Test email sent successfully.'
                    : 'Failed to send test email. Check the mail transport.',
            ], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $ok
            ? 'Test email sent successfully.'
            : 'Failed to send test email. Check the mail transport.');
    }
}
