<?php

use App\Mail\SystemNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('queues a system email when a notification is created', function () {
    Mail::fake();

    $user = User::factory()->create(['role' => 'client']);

    Notification::notify($user->id, 'عرض جديد', 'وصف الإشعار', '/client/tenders/1/offers');

    expect(Notification::where('user_id', $user->id)->count())->toBe(1);

    Mail::assertQueued(SystemNotificationMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email)
            && $mail->titleText === 'عرض جديد'
            && $mail->actionUrl === rtrim(config('app.url'), '/').'/client/tenders/1/offers';
    });
});

it('does not queue mail when the user id is null', function () {
    Mail::fake();

    Notification::notify(null, 'لا شيء');

    Mail::assertNothingQueued();
});

it('emails every admin when notifying admins', function () {
    Mail::fake();

    User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'admin']);

    Notification::notifyAdmins('رسالة تواصل جديدة', 'رسالة من زائر', '/admin/messages');

    Mail::assertQueued(SystemNotificationMail::class, 2);
});

it('lets an admin send a test email to verify SMTP', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post('/admin/test-email')->assertRedirect();

    Mail::assertSent(SystemNotificationMail::class, fn ($m) => $m->hasTo($admin->email));
});
