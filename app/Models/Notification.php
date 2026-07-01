<?php

namespace App\Models;

use App\Mail\SystemNotificationMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'body', 'link', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** إنشاء إشعار لمستخدم */
    public static function notify(?int $userId, string $title, ?string $body = null, ?string $link = null): void
    {
        if (! $userId) {
            return;
        }
        static::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);

        // إرسال بريد بنفس محتوى الإشعار — يجب ألا يُعطّل إنشاء الإشعار أبدًا
        try {
            $user = User::find($userId);
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new SystemNotificationMail(
                    recipientName: $user->name,
                    titleText: $title,
                    bodyText: $body,
                    relativeLink: $link,
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('SystemNotificationMail dispatch failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /** إشعار لكل الأدمن */
    public static function notifyAdmins(string $title, ?string $body = null, ?string $link = null): void
    {
        User::where('role', 'admin')->pluck('id')->each(
            fn ($id) => static::notify($id, $title, $body, $link)
        );
    }
}
