<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SystemNotificationMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TestEmailController extends Controller
{
    public function send(Request $request)
    {
        $email = $request->email;
        // $email = $request->user()->email;

        try {
            Mail::to($email)->sendNow(new SystemNotificationMail(
                recipientName: $request->name,
                // recipientName: $request->user()->name,
                titleText: 'بريد تجريبي من منصة اتفاق',
                bodyText: 'إذا وصلك هذا البريد فإعدادات إرسال البريد (SMTP) تعمل بنجاح.',
                relativeLink: '/admin/dashboard',
            ));

            return back()->with('success', "تم إرسال بريد تجريبي إلى {$email} — تحقّق من صندوق الوارد.");
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
            // return back()->with('error', 'فشل إرسال البريد: '.$e->getMessage());
        }
    }
}
