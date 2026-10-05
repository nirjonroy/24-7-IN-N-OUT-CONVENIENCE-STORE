<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Menu $menu): View
    {
        $menu->load(['items.page', 'items.children.page']);

        return view('admin.menu-items.index', compact('menu'));
    }

    public function create(Menu $menu): View
    {
        return view('admin.menu-items.create', [
            'menu' => $menu,
            'item' => new MenuItem(['link_type' => MenuItem::LINK_PAGE, 'target' => '_self', 'is_active' => true]),
            'pages' => Page::orderBy('name')->get(),
            'parents' => $this->parents($menu),
        ]);
    }

    public function store(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        DB::transaction(function () use ($request, $menu) {
            $data = $this->data($request, $request->validated());
            $data['menu_id'] = $menu->id;
            MenuItem::create($data);
        });

        return redirect()->route('admin.menus.items.index', $menu)->with('success', 'Menu item created successfully.');
    }

    public function show(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        return redirect()->route('admin.menus.items.edit', [$menu, $item]);
    }

    public function edit(Menu $menu, MenuItem $item): View
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        return view('admin.menu-items.edit', [
            'menu' => $menu,
            'item' => $item,
            'pages' => Page::orderBy('name')->get(),
            'parents' => $this->parents($menu, $item),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);

        DB::transaction(fn () => $item->update($this->data($request, $request->validated())));

        return redirect()->route('admin.menus.items.index', $menu)->with('success', 'Menu item updated successfully.');
    }

    public function destroy(Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->ensureItemBelongsToMenu($menu, $item);
        $item->delete();

        return redirect()->route('admin.menus.items.index', $menu)->with('success', 'Menu item deleted successfully.');
    }

    private function data(Request $request, array $data): array
    {
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if (($data['link_type'] ?? null) === MenuItem::LINK_PAGE) {
            $data['url'] = null;
        } else {
            $data['page_id'] = null;
        }

        return $data;
    }

    private function parents(Menu $menu, ?MenuItem $item = null)
    {
        return $menu->allItems()
            ->whereNull('parent_id')
            ->when($item, fn ($query) => $query->whereKeyNot($item->id))
            ->get();
    }

    private function ensureItemBelongsToMenu(Menu $menu, MenuItem $item): void
    {
        abort_unless((int) $item->menu_id === (int) $menu->id, 404);
    }
}
