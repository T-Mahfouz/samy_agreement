<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ErasesRecords;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    use ErasesRecords;

    public function index(Request $request): Response
    {
        $status = $request->query('status');

        $providers = ProviderProfile::query()
            ->with(['user:id,name,email,status', 'mainCategory:id,name'])
            ->withCount('documents')
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15, ['id', 'user_id', 'company_name', 'commercial_register_no', 'mobile', 'main_category_id', 'status', 'created_at'])->withQueryString();

        return Inertia::render('admin/providers/Index', [
            'providers' => $providers,
            'filter' => $status,
            'counts' => [
                'all' => ProviderProfile::count(),
                'pending' => ProviderProfile::where('status', 'pending')->count(),
                'approved' => ProviderProfile::where('status', 'approved')->count(),
                'rejected' => ProviderProfile::where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function show(ProviderProfile $provider): Response
    {
        $provider->load([
            'user:id,name,username,email,phone,status',
            'mainCategory:id,name',
            'documents:id,provider_id,doc_type,file_path,uploaded_at',
        ]);

        return Inertia::render('admin/providers/Show', [
            'provider' => $provider,
        ]);
    }

    public function update(Request $request, ProviderProfile $provider): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $provider->update($data);

        $userStatus = match ($data['status']) {
            'approved' => 'active',
            'rejected' => 'suspended',
            default => 'pending',
        };
        $provider->user?->update(['status' => $userStatus]);

        $label = match ($data['status']) {
            'approved' => 'تم اعتماد المورّد.',
            'rejected' => 'تم رفض المورّد.',
            default => 'تم تعليق المورّد.',
        };

        if (in_array($data['status'], ['approved', 'rejected'], true)) {
            Notification::notify(
                $provider->user_id,
                $data['status'] === 'approved' ? 'تم اعتماد حسابك' : 'تم رفض حسابك',
                $data['status'] === 'approved'
                    ? 'تم اعتماد حسابك كمورّد، يمكنك الآن تقديم العروض.'
                    : 'نأسف، تم رفض طلب اعتماد حسابك. يرجى التواصل مع الإدارة.',
                '/provider/dashboard'
            );
        }

        return back()->with('success', $label);
    }

    public function destroy(ProviderProfile $provider): RedirectResponse
    {
        $name = $provider->company_name;
        $this->eraseProvider($provider);

        return redirect('/admin/providers')->with('success', "تم حذف المورّد «{$name}» وحسابه نهائيًا.");
    }
}
