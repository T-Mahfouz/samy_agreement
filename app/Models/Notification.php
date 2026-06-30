<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    }

    /** إشعار لكل الأدمن */
    public static function notifyAdmins(string $title, ?string $body = null, ?string $link = null): void
    {
        User::where('role', 'admin')->pluck('id')->each(
            fn ($id) => static::notify($id, $title, $body, $link)
        );
    }
}
