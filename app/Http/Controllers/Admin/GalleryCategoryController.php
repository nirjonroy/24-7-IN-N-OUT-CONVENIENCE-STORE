<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryCategoryRequest;
use App\Http\Requests\UpdateGalleryCategoryRequest;
use App\Models\GalleryCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryCategoryController extends Controller
{
    public function index(): View { return view('admin.gallery.categories.index', ['categories'=>GalleryCategory::withCount('items')->orderBy('sort_order')->orderBy('id')->paginate(15)]); }
    public function create(): View { return view('admin.gallery.categories.create', ['category'=>new GalleryCategory(['is_active'=>true])]); }
    public function store(StoreGalleryCategoryRequest $request): RedirectResponse { GalleryCategory::create($this->data($request,$request->validated())); return redirect()->route('admin.gallery.categories.index')->with('success','Gallery category created successfully.'); }
    public function show(GalleryCategory $category): RedirectResponse { return redirect()->route('admin.gallery.categories.edit',$category); }
    public function edit(GalleryCategory $category): View { return view('admin.gallery.categories.edit', compact('category')); }
    public function update(UpdateGalleryCategoryRequest $request, GalleryCategory $category): RedirectResponse { $category->update($this->data($request,$request->validated())); return redirect()->route('admin.gallery.categories.index')->with('success','Gallery category updated successfully.'); }
    public function destroy(GalleryCategory $category): RedirectResponse { $category->items()->update(['gallery_category_id' => null]); $category->delete(); return redirect()->route('admin.gallery.categories.index')->with('success','Gallery category deleted successfully.'); }
    private function data(Request $request,array $data): array { $data['is_featured']=$request->boolean('is_featured'); $data['is_active']=$request->boolean('is_active'); $data['sort_order']=$data['sort_order']??0; return $data; }
}
