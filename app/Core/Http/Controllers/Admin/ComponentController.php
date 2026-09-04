<?php

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\ComponentMaker\Models\Component;
use App\Modules\ComponentMaker\Services\ComponentMakerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ComponentController extends AdminController
{
    protected ComponentMakerService $service;

    public function __construct(ComponentMakerService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست کامپوننت‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'type', 'search', 'is_active']);
        $perPage = $request->get('per_page', 20);

        $components = $this->service->getList($filters, $perPage);
        $stats = $this->service->getStats();
        $categories = $this->service->getCategories();

        return view('core::admin.components.index', compact('components', 'stats', 'categories', 'filters'));
    }

    /**
     * فعال کردن کامپوننت
     */
    public function activate(string $slug)
    {
        try {
            $component = Component::where('slug', $slug)->first();
            
            if (!$component) {
                throw new \Exception("کامپوننت '{$slug}' یافت نشد.");
            }

            $component->is_active = true;
            $component->save();
            $component->clearCache();

            return redirect()->back()->with('success', "کامپوننت '{$component->name}' با موفقیت فعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * غیرفعال کردن کامپوننت
     */
    public function deactivate(string $slug)
    {
        try {
            $component = Component::where('slug', $slug)->first();
            
            if (!$component) {
                throw new \Exception("کامپوننت '{$slug}' یافت نشد.");
            }

            // بررسی وابستگی‌ها
            if ($component->is_core) {
                throw new \Exception("کامپوننت '{$component->name}' یک کامپوننت هسته است و نمی‌توان آن را غیرفعال کرد.");
            }

            $component->is_active = false;
            $component->save();
            $component->clearCache();

            return redirect()->back()->with('success', "کامپوننت '{$component->name}' با موفقیت غیرفعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * نصب کامپوننت
     */
    public function install(string $slug)
    {
        try {
            $this->service->install($slug);

            return redirect()->back()->with('success', "کامپوننت '{$slug}' با موفقیت نصب شد.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف نصب کامپوننت
     */
    public function uninstall(string $slug)
    {
        try {
            $this->service->uninstall($slug);

            return redirect()->back()->with('success', "کامپوننت '{$slug}' با موفقیت حذف شد.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف کامپوننت (قابل بازیابی)
     */
    public function destroy(string $slug)
    {
        try {
            $component = Component::where('slug', $slug)->first();
            
            if (!$component) {
                throw new \Exception("کامپوننت '{$slug}' یافت نشد.");
            }

            if ($component->is_core) {
                throw new \Exception("کامپوننت '{$component->name}' یک کامپوننت هسته است و نمی‌توان آن را حذف کرد.");
            }

            $this->service->delete($component);

            return redirect()->route('admin.components.index')->with('success', "کامپوننت '{$component->name}' با موفقیت حذف شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * بازیابی کامپوننت حذف شده
     */
    public function restore(string $slug)
    {
        try {
            $component = $this->service->restore($slug);
            
            if (!$component) {
                throw new \Exception("کامپوننت '{$slug}' یافت نشد.");
            }

            return redirect()->route('admin.components.index')->with('success', "کامپوننت '{$component->name}' با موفقیت بازیابی شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف دائمی کامپوننت
     */
    public function forceDestroy(string $slug)
    {
        try {
            $component = Component::withTrashed()->where('slug', $slug)->first();
            
            if (!$component) {
                throw new \Exception("کامپوننت '{$slug}' یافت نشد.");
            }

            if ($component->is_core) {
                throw new \Exception("کامپوننت '{$component->name}' یک کامپوننت هسته است و نمی‌توان آن را حذف کرد.");
            }

            $this->service->forceDelete($component);

            return redirect()->route('admin.components.index')->with('success', "کامپوننت '{$component->name}' با موفقیت به طور دائمی حذف شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * دریافت وضعیت کامپوننت (API)
     */
    public function status(string $slug)
    {
        $component = Component::where('slug', $slug)->first();
        
        if (!$component) {
            return response()->json(['error' => 'Component not found'], 404);
        }

        return response()->json([
            'slug' => $component->slug,
            'name' => $component->name,
            'is_active' => $component->is_active,
            'is_installed' => $component->isInstalled(),
            'version' => $component->version,
            'category' => $component->category,
            'type' => $component->type,
            'view_count' => $component->view_count,
            'download_count' => $component->download_count,
        ]);
    }
}

