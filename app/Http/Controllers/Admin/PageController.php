<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::withCount('sections')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page([
                'template' => 'default',
                'robots_index' => true,
                'robots_follow' => true,
                'status' => Page::STATUS_DRAFT,
            ]),
            'statuses' => Page::STATUSES,
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $this->pageData($request->validated());

            if ($data['is_home']) {
                Page::query()->update(['is_home' => false]);
            }

            Page::create($data);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function show(Page $page): RedirectResponse
    {
        return redirect()->route('admin.pages.edit', $page);
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
            'statuses' => Page::STATUSES,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        DB::transaction(function () use ($request, $page) {
            $data = $this->pageData($request->validated(), $page);

            if ($data['is_home']) {
                Page::whereKeyNot($page->id)->update(['is_home' => false]);
            }

            $page->update($data);
        });

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    private function pageData(array $data, ?Page $page = null): array
    {
        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['template'] = $data['template'] ?: 'default';
        $data['robots_index'] = request()->boolean('robots_index');
        $data['robots_follow'] = request()->boolean('robots_follow');
        $data['is_home'] = request()->boolean('is_home');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['status'] === Page::STATUS_PUBLISHED && empty($data['published_at']) && ! $page?->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
