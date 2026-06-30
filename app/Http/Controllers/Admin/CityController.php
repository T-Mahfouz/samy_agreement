<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        City::create($this->validateData($request));

        return back()->with('success', 'تم إضافة المدينة.');
    }

    public function update(Request $request, City $city): RedirectResponse
    {
        $city->update($this->validateData($request));

        return back()->with('success', 'تم تحديث المدينة.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return back()->with('success', 'تم حذف المدينة.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ], [], [
            'region_id' => 'المنطقة',
            'name' => 'اسم المدينة',
        ]);
    }
}
