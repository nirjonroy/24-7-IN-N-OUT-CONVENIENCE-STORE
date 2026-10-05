<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqCategoryRequest;
use App\Http\Requests\UpdateFaqCategoryRequest;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.faq.categories.index', ['categories' => FaqCategory::withCount('faqs')->orderBy('sort_order')->orderBy('id')->paginate(15)]);
    }
    public function create(): View { return view('admin.faq.categories.create', ['category' => new FaqCategory(['is_active' => true])]); }
    public function store(StoreFaqCategoryRequest $request): RedirectResponse { FaqCategory::create($this->data($request, $request->validated())); return redirect()->route('admin.faq.categories.index')->with('success', 'FAQ category created successfully.'); }
    public function show(FaqCategory $category): RedirectResponse { return redirect()->route('admin.faq.categories.edit', $category); }
    public function edit(FaqCategory $category): View { return view('admin.faq.categories.edit', compact('category')); }
    public function update(UpdateFaqCategoryRequest $request, FaqCategory $category): RedirectResponse { $category->update($this->data($request, $request->validated())); return redirect()->route('admin.faq.categories.index')->with('success', 'FAQ category updated successfully.'); }
    public function destroy(FaqCategory $category): RedirectResponse { $category->faqs()->update(['faq_category_id' => null]); $category->delete(); return redirect()->route('admin.faq.categories.index')->with('success', 'FAQ category deleted successfully.'); }
    private function data(Request $request, array $data): array { $data['is_featured']=$request->boolean('is_featured'); $data['is_active']=$request->boolean('is_active'); $data['sort_order']=$data['sort_order']??0; return $data; }
}
