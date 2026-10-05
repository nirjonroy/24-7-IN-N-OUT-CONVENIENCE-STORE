<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::withCount('allItems')
                ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query
                    ->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('key', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%')))
                ->when($request->filled('location'), fn ($query) => $query->where('location', $request->location))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only(['search', 'location', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.create', [
            'menu' => new Menu(['is_active' => true]),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        Menu::create($this->data($request, $request->validated()));

        return redirect()->route('admin.menus.index')->with('success', 'Menu created successfully.');
    }

    public function show(Menu $menu): RedirectResponse
    {
        return redirect()->route('admin.menus.edit', $menu);
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->data($request, $request->validated()));

        return redirect()->route('admin.menus.index')->with('success', 'Menu updated successfully.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }

    private function data(Request $request, array $data): array
    {
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
