<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryItemRequest;
use App\Http\Requests\UpdateGalleryItemRequest;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Services\MediaAttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GalleryItemController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.gallery.items.index', [
            'items'=>GalleryItem::with(['category','mediaAttachments.media.variants'])
                ->when($request->filled('search'), fn($q)=>$q->where(fn($q)=>$q->where('title','like','%'.$request->search.'%')->orWhere('caption','like','%'.$request->search.'%')->orWhere('description','like','%'.$request->search.'%')->orWhere('photographer','like','%'.$request->search.'%')))
                ->when($request->filled('category_id'), fn($q)=>$q->where('gallery_category_id',$request->category_id))
                ->when($request->filled('featured'), fn($q)=>$q->where('is_featured',$request->featured==='1'))
                ->when($request->filled('status'), fn($q)=>$q->where('is_active',$request->status==='active'))
                ->orderBy('sort_order')->orderBy('id')->paginate(24)->withQueryString(),
            'categories'=>GalleryCategory::orderBy('name')->get(), 'filters'=>$request->only(['search','category_id','featured','status']),
        ]);
    }
    public function create(): View { return view('admin.gallery.items.create', ['item'=>new GalleryItem(['is_active'=>true]), 'categories'=>GalleryCategory::orderBy('name')->get()]); }
    public function store(StoreGalleryItemRequest $request, MediaAttachmentService $media): RedirectResponse { DB::transaction(fn()=> $this->saveItem(new GalleryItem(), $request, $media)); return redirect()->route('admin.gallery.items.index')->with('success','Gallery item created successfully.'); }
    public function show(GalleryItem $item): RedirectResponse { return redirect()->route('admin.gallery.items.edit',$item); }
    public function edit(GalleryItem $item): View { $item->load('mediaAttachments.media.variants'); return view('admin.gallery.items.edit', ['item'=>$item,'categories'=>GalleryCategory::orderBy('name')->get()]); }
    public function update(UpdateGalleryItemRequest $request, GalleryItem $item, MediaAttachmentService $media): RedirectResponse { DB::transaction(fn()=> $this->saveItem($item, $request, $media)); return redirect()->route('admin.gallery.items.index')->with('success','Gallery item updated successfully.'); }
    public function destroy(GalleryItem $item): RedirectResponse { $item->mediaAttachments()->delete(); $item->delete(); return redirect()->route('admin.gallery.items.index')->with('success','Gallery item deleted successfully.'); }
    private function saveItem(GalleryItem $item, Request $request, MediaAttachmentService $media): void { $data=$request->validated(); $mediaData=['image'=>[$data['image_media_id']??null,$data['image_alt_override']??null],'meta_image'=>[$data['meta_image_media_id']??null,$data['meta_image_alt_override']??null]]; unset($data['image_media_id'],$data['image_alt_override'],$data['meta_image_media_id'],$data['meta_image_alt_override']); $data['is_featured']=$request->boolean('is_featured'); $data['is_active']=$request->boolean('is_active'); $data['sort_order']=$data['sort_order']??0; $item->fill($data)->save(); foreach($mediaData as $collection=>[$id,$alt]) $media->syncSingle($item,$collection,$id?(int)$id:null,['alt_text_override'=>$alt]); }
}
