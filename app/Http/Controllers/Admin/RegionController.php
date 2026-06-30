<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegionController extends Controller
{
    public function index(): Response
    {
        $regions = Region::query()
            ->withCount('cities')
            ->with('cities:id,region_id,name,is_active')
            ->orderBy('name')
            ->paginate(12, ['id', 'name', 'is_active'])->withQueryString();

        return Inertia::render('admin/locations/Index', [
            'regions' => $regions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Region::create($this->validateData($request));

        return back()->with('success', 'تم إضافة المنطقة.');
    }

    public function update(Request $request, Region $region): RedirectResponse
    {
        $region->update($this->validateData($request));

        return back()->with('success', 'تم تحديث المنطقة.');
    }

    public function destroy(Region $region): RedirectResponse
    {
        $region->delete();

        return back()->with('success', 'تم حذف المنطقة ومدنها.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ], [], ['name' => 'اسم المنطقة']);
    }
}
