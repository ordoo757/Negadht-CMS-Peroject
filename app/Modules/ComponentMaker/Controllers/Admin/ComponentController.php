<?php

namespace App\Modules\ComponentMaker\Controllers\Admin;

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
     * نمایش لیست کامپوننت‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'type', 'search', 'is_active']);
        $perPage = $request->get('per_page', 20);

        $components = $this->service->getList($filters, $perPage);
        $categories = $this->service->getCategories();
        $stats = $this->service->getStats();

        return view('component-maker::admin.index', compact('components', 'categories', 'stats', 'filters'));
    }

    /**
     * نمایش فرم ایجاد کامپوننت
     */
    public function create()
    {
        $categories = $this->service->getCategories();
        $types = ['custom', 'layout', 'widget', 'module', 'plugin', 'theme', 'template'];
        $statuses = ['draft', 'stable', 'beta', 'alpha', 'deprecated', 'archived'];

        return view('component-maker::admin.create', compact('categories', 'types', 'statuses'));
    }

    /**
     * ذخیره کامپوننت جدید
     */
    public function store(Request $request)
    {
        try {
            $component = $this->service->create($request->all());

            return redirect()
                ->route('admin.component-maker.index')
                ->with('success', "Component '{$component->name}' created successfully.");
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to create component: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * نمایش جزئیات کامپوننت
     */
    public function show(string $slug)
    {
        $component = $this->service->getDetails($slug);

        if (!$component) {
            abort(404, 'Component not found.');
        }

        $similar = $this->service->getSimilar($component, 5);

        return view('component-maker::admin.show', compact('component', 'similar'));
    }

    /**
     * نمایش فرم ویرایش کامپوننت
     */
    public function edit(string $slug)
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            abort(404, 'Component not found.');
        }

        $categories = $this->service->getCategories();
        $types = ['custom', 'layout', 'widget', 'module', 'plugin', 'theme', 'template'];
        $statuses = ['draft', 'stable', 'beta', 'alpha', 'deprecated', 'archived'];

        return view('component-maker::admin.edit', compact('component', 'categories', 'types', 'statuses'));
    }

    /**
     * بروزرسانی کامپوننت
     */
    public function update(Request $request, string $slug)
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            abort(404, 'Component not found.');
        }

        try {
            $component = $this->service->update($component, $request->all());

            return redirect()
                ->route('admin.component-maker.index')
                ->with('success', "Component '{$component->name}' updated successfully.");
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update component: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف کامپوننت
     */
    public function destroy(string $slug)
    {
        $component = Component::where('slug', $slug)->first();

        if (!$component) {
            abort(404, 'Component not found.');
        }

        try {
            $this->service->delete($component);

            return redirect()
                ->route('admin.component-maker.index')
                ->with('success', "Component '{$component->name}' deleted successfully.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to delete component: ' . $e->getMessage());
        }
    }

    /**
     * حذف دائمی کامپوننت
     */
    public function forceDestroy(string $slug)
    {
        $component = Component::withTrashed()->where('slug', $slug)->first();

        if (!$component) {
            abort(404, 'Component not found.');
        }

        try {
            $this->service->forceDelete($component);

            return redirect()
                ->route('admin.component-maker.index')
                ->with('success', "Component '{$component->name}' permanently deleted.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to permanently delete component: ' . $e->getMessage());
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
                abort(404, 'Component not found.');
            }

            return redirect()
                ->route('admin.component-maker.index')
                ->with('success', "Component '{$component->name}' restored successfully.");
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to restore component: ' . $e->getMessage());
        }
    }

    /**
     * نصب کامپوننت
     */
    public function install(string $slug)
    {
        try {
            $this->service->install($slug);

            return redirect()
                ->back()
                ->with('success', "Component '{$slug}' installed successfully.");
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to install component: ' . $e->getMessage());
        }
    }

    /**
     * حذف نصب کامپوننت
     */
    public function uninstall(string $slug)
    {
        try {
            $this->service->uninstall($slug);

            return redirect()
                ->back()
                ->with('success', "Component '{$slug}' uninstalled successfully.");
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to uninstall component: ' . $e->getMessage());
        }
    }

    /**
     * صادر کردن کامپوننت
     */
    public function export(string $slug)
    {
        try {
            $zipPath = $this->service->export($slug);

            return response()->download($zipPath);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to export component: ' . $e->getMessage());
        }
    }
}
