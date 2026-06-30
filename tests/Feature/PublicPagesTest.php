<?php

use App\Models\ContactMessage;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\SystemSetting;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the about page with content from the database', function () {
    ContentPage::create(['slug' => 'about', 'section_key' => 'who_we_are', 'title' => 'من نحن', 'body' => 'نص من نحن']);

    $this->get('/about')->assertOk();
});

it('shows the faqs page', function () {
    Faq::create(['question' => 'سؤال؟', 'answer' => 'جواب', 'is_active' => true, 'sort_order' => 0]);

    $this->get('/faqs')->assertOk();
});

it('shows the contact page and stores a message', function () {
    $this->get('/contact')->assertOk();

    $this->post('/contact', [
        'full_name' => 'زائر',
        'mobile' => '0500000000',
        'email' => 'v@example.com',
        'message' => 'استفسار عن المنصة',
    ])->assertRedirect();

    $msg = ContactMessage::where('email', 'v@example.com')->first();
    expect($msg)->not->toBeNull();
    expect($msg->status)->toBe('new');
    expect($msg->full_name)->toBe('زائر');
});

it('shows contact info from the database (system settings)', function () {
    SystemSetting::set('contact_phone', '0112005500');
    SystemSetting::set('contact_whatsapp', '0552005500');
    SystemSetting::set('contact_email', 'info@agreement.com');
    SystemSetting::set('contact_support_email', 'support@agreement.com');

    $this->get('/contact')->assertInertia(fn (Assert $page) => $page
        ->component('public/Contact')
        ->where('info.phone', '0112005500')
        ->where('info.whatsapp', '0552005500')
        ->where('info.email', 'info@agreement.com')
        ->where('info.support_email', 'support@agreement.com')
    );
});

it('validates the contact form', function () {
    $this->post('/contact', ['full_name' => '', 'message' => ''])
        ->assertSessionHasErrors(['full_name', 'message']);
});
