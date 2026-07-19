<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ErasesRecords;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    use ErasesRecords;

    public function index(): Response
    {
        $clients = ClientProfile::query()
            ->with('user:id,name,email,status')
            ->withCount('tenders')
            ->latest()
            ->paginate(15, ['id', 'user_id', 'company_name', 'mobile', 'bank_name', 'created_at'])->withQueryString();

        return Inertia::render('admin/clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function show(ClientProfile $client): Response
    {
        $client->load([
            'user:id,name,username,email,phone,status',
            'tenders' => fn ($q) => $q->latest()->select('id', 'client_id', 'tender_no', 'name', 'status', 'created_at'),
        ]);

        return Inertia::render('admin/clients/Show', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, ClientProfile $client): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        $client->user?->update(['status' => $data['status']]);

        return back()->with('success', $data['status'] === 'active' ? 'تم تفعيل الحساب.' : 'تم تعليق الحساب.');
    }

    public function destroy(ClientProfile $client): RedirectResponse
    {
        $name = $client->company_name;
        $this->eraseClient($client);

        return redirect('/admin/clients')->with('success', "تم حذف المستفيد «{$name}» وحسابه نهائيًا.");
    }
}
