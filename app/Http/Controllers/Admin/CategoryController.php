<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->with('parent:id,name')
            ->withCount('children')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->paginate(30, ['id', 'parent_id', 'name', 'is_active', 'sort_order'])->withQueryString();

        // القطاعات الرئيسية فقط (لاختيارها كأب)
        $parents = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'parents' => $parents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Category::create($data);

        return back()->with('success', 'تم إضافة التصنيف بنجاح.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateData($request, $category);
        $category->update($data);

        return back()->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }

    private function validateData(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], [
            'name' => 'اسم التصنيف',
            'parent_id' => 'القطاع الرئيسي',
        ]);
    }
}
