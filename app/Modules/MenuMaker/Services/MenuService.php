<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Modules\MenuMaker\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class MenuService
{
    protected string $cachePrefix = 'menu_';

    public function getMenu(string $slug, string $language = null): array
    {
        $lang = $language ?? app()->getLocale();
        $cacheKey = "{$this->cachePrefix}{$slug}_{$lang}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $menu = DB::table('menus')
            ->where('slug', $slug)
            ->where('language', $lang)
            ->where('is_active', true)
            ->first();

        if (!$menu) {
            return [];
        }

        $items = $this->getMenuItems($menu->id);

        $result = [
            'menu' => $menu,
            'items' => $this->buildTree($items),
        ];

        Cache::put($cacheKey, $result, now()->addHours(2));

        return $result;
    }

    public function renderMenu(string $slug, string $template = 'default'): string
    {
        $menuData = $this->getMenu($slug);

        if (empty($menuData)) {
            return '';
        }

        $view = "menu::templates.{$template}";

        if (!View::exists($view)) {
            $view = 'menu::templates.default';
        }

        return View::make($view, $menuData)->render();
    }

    public function createMenu(array $data): int
    {
        $id = DB::table('menus')->insertGetId([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            'position' => $data['position'] ?? 'header',
            'language' => $data['language'] ?? app()->getLocale(),
            'css_class' => $data['css_class'] ?? '',
            'css_id' => $data['css_id'] ?? '',
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->clearCache($data['slug'] ?? '');

        return $id;
    }

    public function addItem(int $menuId, array $data): int
    {
        // Calculate order
        $maxOrder = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('order') ?? 0;

        $id = DB::table('menu_items')->insertGetId([
            'menu_id' => $menuId,
            'parent_id' => $data['parent_id'] ?? null,
            'title' => $data['title'],
            'url' => $data['url'] ?? '#',
            'route' => $data['route'] ?? null,
            'route_params' => isset($data['route_params']) ? json_encode($data['route_params']) : null,
            'icon' => $data['icon'] ?? null,
            'target' => $data['target'] ?? '_self',
            'css_class' => $data['css_class'] ?? '',
            'order' => $maxOrder + 1,
            'is_active' => $data['is_active'] ?? true,
            'permissions' => isset($data['permissions']) ? json_encode($data['permissions']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->clearCacheByMenuId($menuId);

        return $id;
    }

    public function updateItem(int $itemId, array $data): bool
    {
        $item = DB::table('menu_items')->where('id', $itemId)->first();
        if (!$item) return false;

        DB::table('menu_items')->where('id', $itemId)->update([
            'title' => $data['title'] ?? $item->title,
            'url' => $data['url'] ?? $item->url,
            'route' => $data['route'] ?? $item->route,
            'icon' => $data['icon'] ?? $item->icon,
            'target' => $data['target'] ?? $item->target,
            'css_class' => $data['css_class'] ?? $item->css_class,
            'is_active' => $data['is_active'] ?? $item->is_active,
            'updated_at' => now(),
        ]);

        $this->clearCacheByMenuId($item->menu_id);

        return true;
    }

    public function deleteItem(int $itemId): bool
    {
        $item = DB::table('menu_items')->where('id', $itemId)->first();
        if (!$item) return false;

        // Delete children recursively
        $this->deleteChildren($itemId);

        DB::table('menu_items')->where('id', $itemId)->delete();

        $this->clearCacheByMenuId($item->menu_id);

        return true;
    }

    public function reorderItems(int $menuId, array $order): bool
    {
        foreach ($order as $index => $itemId) {
            DB::table('menu_items')
                ->where('id', $itemId)
                ->update(['order' => $index + 1, 'updated_at' => now()]);
        }

        $this->clearCacheByMenuId($menuId);

        return true;
    }

    public function getMenuTypes(): array
    {
        return [
            'horizontal' => 'منوی افقی',
            'vertical' => 'منوی عمودی',
            'dropdown' => 'منوی کشویی',
            'mega' => 'مگامنو',
            'sidebar' => 'سایدبار',
            'footer' => 'فوتر',
            'breadcrumb' => 'Breadcrumb',
            'tab' => 'تب منو',
            'accordion' => 'آکاردئون',
            'context' => 'منوی راست‌کلیک',
        ];
    }

    public function getPositions(): array
    {
        return [
            'header' => 'هدر',
            'sidebar' => 'سایدبار',
            'footer' => 'فوتر',
            'top-bar' => 'نوار بالا',
            'bottom-bar' => 'نوار پایین',
            'mobile' => 'موبایل',
        ];
    }

    protected function getMenuItems(int $menuId): array
    {
        return DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('is_active', true)
            ->orderBy('order')
            ->get()
            ->map(function ($item) {
                $item->children = [];
                $item->route_params = json_decode($item->route_params, true);
                $item->permissions = json_decode($item->permissions, true);
                return $item;
            })
            ->toArray();
    }

    protected function buildTree(array $items, int $parentId = null): array
    {
        $branch = [];

        foreach ($items as $item) {
            if ($item->parent_id === $parentId) {
                $children = $this->buildTree($items, $item->id);
                if ($children) {
                    $item->children = $children;
                }
                $branch[] = $item;
            }
        }

        return $branch;
    }

    protected function deleteChildren(int $parentId): void
    {
        $children = DB::table('menu_items')->where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $this->deleteChildren($childId);
            DB::table('menu_items')->where('id', $childId)->delete();
        }
    }

    protected function clearCache(string $slug): void
    {
        foreach (['fa', 'ar', 'en'] as $lang) {
            Cache::forget("{$this->cachePrefix}{$slug}_{$lang}");
        }
    }

    protected function clearCacheByMenuId(int $menuId): void
    {
        $menu = DB::table('menus')->where('id', $menuId)->first();
        if ($menu) {
            $this->clearCache($menu->slug);
        }
    }
}
