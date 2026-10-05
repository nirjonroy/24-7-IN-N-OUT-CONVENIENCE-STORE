<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRedirectRequest;
use App\Http\Requests\UpdateRedirectRequest;
use App\Models\Redirect;
use App\Services\RedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.seo.redirects.index', [
            'redirects' => Redirect::with('createdBy')
                ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                    ->where('source_path', 'like', '%'.$request->search.'%')
                    ->orWhere('target_url', 'like', '%'.$request->search.'%')
                    ->orWhere('note', 'like', '%'.$request->search.'%')))
                ->when($request->filled('status_code'), fn ($query) => $query->where('status_code', $request->status_code))
                ->when($request->filled('match_type'), fn ($query) => $query->where('match_type', $request->match_type))
                ->when($request->filled('active'), fn ($query) => $query->where('is_active', $request->active === '1'))
                ->orderByDesc('updated_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only(['search', 'status_code', 'match_type', 'active']),
        ]);
    }

    public function create(): View
    {
        return view('admin.seo.redirects.create', [
            'redirect' => new Redirect(['match_type' => Redirect::MATCH_EXACT, 'status_code' => 301, 'preserve_query_string' => true, 'is_active' => true]),
        ]);
    }

    public function store(StoreRedirectRequest $request, RedirectService $redirectService): RedirectResponse
    {
        $data = $this->data($request, $request->validated());
        $data['created_by'] = $request->user()?->id;
        Redirect::create($data);
        $redirectService->clearCache();

        return redirect()->route('admin.seo.redirects.index')->with('success', 'Redirect created successfully.');
    }

    public function show(Redirect $redirect): RedirectResponse
    {
        return redirect()->route('admin.seo.redirects.edit', $redirect);
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.seo.redirects.edit', compact('redirect'));
    }

    public function update(UpdateRedirectRequest $request, Redirect $redirect, RedirectService $redirectService): RedirectResponse
    {
        $redirect->update($this->data($request, $request->validated()));
        $redirectService->clearCache();

        return redirect()->route('admin.seo.redirects.index')->with('success', 'Redirect updated successfully.');
    }

    public function destroy(Redirect $redirect, RedirectService $redirectService): RedirectResponse
    {
        $redirect->delete();
        $redirectService->clearCache();

        return redirect()->route('admin.seo.redirects.index')->with('success', 'Redirect deleted successfully.');
    }

    private function data(Request $request, array $data): array
    {
        $data['preserve_query_string'] = $request->boolean('preserve_query_string');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
