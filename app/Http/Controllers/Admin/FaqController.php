<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.faq.questions.index', [
            'faqs' => Faq::with('category')->withCount('pages')
                ->when($request->filled('search'), fn ($q) => $q->where(fn ($q) => $q->where('question','like','%'.$request->search.'%')->orWhere('answer','like','%'.$request->search.'%')))
                ->when($request->filled('category_id'), fn ($q) => $q->where('faq_category_id', $request->category_id))
                ->when($request->filled('page_id'), fn ($q) => $q->whereHas('pages', fn ($q) => $q->where('pages.id', $request->page_id)))
                ->when($request->filled('featured'), fn ($q) => $q->where('is_featured', $request->featured === '1'))
                ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
                ->orderBy('sort_order')->orderBy('id')->paginate(15)->withQueryString(),
            'categories' => FaqCategory::orderBy('name')->get(), 'pages' => Page::orderBy('name')->get(), 'filters' => $request->only(['search','category_id','page_id','featured','status']),
        ]);
    }
    public function create(): View { return view('admin.faq.questions.create', ['faq'=>new Faq(['is_active'=>true]), 'categories'=>FaqCategory::orderBy('name')->get(), 'pages'=>Page::orderBy('name')->get()]); }
    public function store(StoreFaqRequest $request): RedirectResponse { DB::transaction(fn()=> $this->saveFaq(new Faq(), $request)); return redirect()->route('admin.faq.questions.index')->with('success','FAQ created successfully.'); }
    public function show(Faq $question): RedirectResponse { return redirect()->route('admin.faq.questions.edit', $question); }
    public function edit(Faq $question): View { $question->load('pages'); return view('admin.faq.questions.edit', ['faq'=>$question, 'categories'=>FaqCategory::orderBy('name')->get(), 'pages'=>Page::orderBy('name')->get()]); }
    public function update(UpdateFaqRequest $request, Faq $question): RedirectResponse { DB::transaction(fn()=> $this->saveFaq($question, $request)); return redirect()->route('admin.faq.questions.index')->with('success','FAQ updated successfully.'); }
    public function destroy(Faq $question): RedirectResponse { $question->pages()->detach(); $question->delete(); return redirect()->route('admin.faq.questions.index')->with('success','FAQ deleted successfully.'); }
    private function saveFaq(Faq $faq, Request $request): void { $data=$request->validated(); $pages=$data['pages']??[]; unset($data['pages']); $data['is_featured']=$request->boolean('is_featured'); $data['is_active']=$request->boolean('is_active'); $data['sort_order']=$data['sort_order']??0; $faq->fill($data)->save(); $faq->pages()->sync(collect($pages)->mapWithKeys(fn($id)=>[$id=>['sort_order'=>$faq->sort_order]])->all()); }
}
