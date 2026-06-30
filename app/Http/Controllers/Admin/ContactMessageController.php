<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(): Response
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(15, ['id', 'full_name', 'mobile', 'email', 'message', 'status', 'created_at'])->withQueryString();

        return Inertia::render('admin/messages/Index', [
            'messages' => $messages,
            'newCount' => ContactMessage::where('status', 'new')->count(),
        ]);
    }

    public function update(Request $request, ContactMessage $message): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied'],
        ]);

        $message->update($data);

        return back()->with('success', 'تم تحديث حالة الرسالة.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'تم حذف الرسالة.');
    }
}
