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

namespace App\Modules\ContentManager\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\ContentManager\Models\Category;
use App\Modules\ContentManager\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends AdminController
{
    protected ContentService $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست دسته‌بندی‌ها
     */
    public function index()
    {
        $categories = $this->service->getCategories();
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
        ];

        return view('content-manager::admin.categories.index', compact('categories', 'stats'));
    }

    /**
     * نمایش فرم ایجاد دسته‌بندی جدید
     */
    public function create()
    {
        $parents = Category::whereNull('parent_id')->get();
        return view('content-manager::admin.categories.create', compact('parents'));
    }

    /**
     * ذخیره دسته‌بندی جدید
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'slug' => 'nullable|string|max:255|unique:categories,slug',
                'description' => 'nullable|string|max:500',
                'parent_id' => 'nullable|exists:categories,id',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            $category = $this->service->createCategory($validated);

            return redirect()->route('admin.content-manager.categories.index')
                ->with('success', "دسته‌بندی '{$category->name}' با موفقیت ایجاد شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد دسته‌بندی: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * نمایش فرم ویرایش دسته‌بندی
     */
    public function edit(int $id)
    {
        $category = Category::findOrFail($id);
        $parents = Category::whereNull('parent_id')->where('id', '!=', $id)->get();

        return view('content-manager::admin.categories.edit', compact('category', 'parents'));
    }

    /**
     * بروزرسانی دسته‌بندی
     */
    public function update(Request $request, int $id)
    {
        try {
            $category = Category::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
                'description' => 'nullable|string|max:500',
                'parent_id' => 'nullable|exists:categories,id',
                'order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            $category = $this->service->updateCategory($category, $validated);

            return redirect()->route('admin.content-manager.categories.index')
                ->with('success', "دسته‌بندی '{$category->name}' با موفقیت بروزرسانی شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی دسته‌بندی: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * حذف دسته‌بندی
     */
    public function destroy(int $id)
    {
        try {
            $category = Category::findOrFail($id);

            // بررسی وجود صفحات مرتبط
            if ($category->pages()->count() > 0) {
                return back()->with('error', "دسته‌بندی '{$category->name}' دارای صفحات مرتبط است. ابتدا صفحات را حذف یا انتقال دهید.");
            }

            // بررسی وجود زیردسته‌ها
            if ($category->children()->count() > 0) {
                return back()->with('error', "دسته‌بندی '{$category->name}' دارای زیردسته است. ابتدا زیردسته‌ها را حذف یا انتقال دهید.");
            }

            $this->service->deleteCategory($category);

            return redirect()->route('admin.content-manager.categories.index')
                ->with('success', "دسته‌بندی '{$category->name}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف دسته‌بندی: ' . $e->getMessage());
        }
    }

    /**
     * فعال/غیرفعال کردن دسته‌بندی
     */
    public function toggle(int $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->is_active = !$category->is_active;
            $category->save();

            $status = $category->is_active ? 'فعال' : 'غیرفعال';

            return redirect()->route('admin.content-manager.categories.index')
                ->with('success', "دسته‌بندی '{$category->name}' با موفقیت {$status} شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در تغییر وضعیت دسته‌بندی: ' . $e->getMessage());
        }
    }
}
