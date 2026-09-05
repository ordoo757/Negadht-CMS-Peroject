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

namespace App\Core\Http\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\PluginMaker\Models\Plugin;
use App\Modules\PluginMaker\Services\PluginMakerService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PluginController extends AdminController
{
    protected PluginMakerService $service;

    public function __construct(PluginMakerService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست پلاگین‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'type', 'search', 'is_active', 'is_free']);
        $perPage = $request->get('per_page', 20);

        $plugins = $this->service->getList($filters, $perPage);
        $stats = $this->service->getStats();

        return view('core::admin.plugins.index', compact('plugins', 'stats', 'filters'));
    }

    /**
     * فعال کردن پلاگین
     */
    public function activate(string $slug)
    {
        try {
            $plugin = Plugin::where('slug', $slug)->first();
            
            if (!$plugin) {
                throw new \Exception("پلاگین '{$slug}' یافت نشد.");
            }

            $plugin->activate();

            return redirect()->back()->with('success', "پلاگین '{$plugin->name}' با موفقیت فعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * غیرفعال کردن پلاگین
     */
    public function deactivate(string $slug)
    {
        try {
            $plugin = Plugin::where('slug', $slug)->first();
            
            if (!$plugin) {
                throw new \Exception("پلاگین '{$slug}' یافت نشد.");
            }

            if ($plugin->is_core) {
                throw new \Exception("پلاگین '{$plugin->name}' یک پلاگین هسته است و نمی‌توان آن را غیرفعال کرد.");
            }

            $plugin->deactivate();

            return redirect()->back()->with('success', "پلاگین '{$plugin->name}' با موفقیت غیرفعال شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * نصب پلاگین
     */
    public function install(string $slug)
    {
        try {
            $this->service->install($slug);

            return redirect()->back()->with('success', "پلاگین '{$slug}' با موفقیت نصب شد.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف نصب پلاگین
     */
    public function uninstall(string $slug)
    {
        try {
            $this->service->uninstall($slug);

            return redirect()->back()->with('success', "پلاگین '{$slug}' با موفقیت حذف شد.");
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف پلاگین (قابل بازیابی)
     */
    public function destroy(string $slug)
    {
        try {
            $plugin = Plugin::where('slug', $slug)->first();
            
            if (!$plugin) {
                throw new \Exception("پلاگین '{$slug}' یافت نشد.");
            }

            if ($plugin->is_core) {
                throw new \Exception("پلاگین '{$plugin->name}' یک پلاگین هسته است و نمی‌توان آن را حذف کرد.");
            }

            $this->service->delete($plugin);

            return redirect()->route('admin.plugins.index')->with('success', "پلاگین '{$plugin->name}' با موفقیت حذف شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * بازیابی پلاگین حذف شده
     */
    public function restore(string $slug)
    {
        try {
            $plugin = $this->service->restore($slug);
            
            if (!$plugin) {
                throw new \Exception("پلاگین '{$slug}' یافت نشد.");
            }

            return redirect()->route('admin.plugins.index')->with('success', "پلاگین '{$plugin->name}' با موفقیت بازیابی شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * حذف دائمی پلاگین
     */
    public function forceDestroy(string $slug)
    {
        try {
            $plugin = Plugin::withTrashed()->where('slug', $slug)->first();
            
            if (!$plugin) {
                throw new \Exception("پلاگین '{$slug}' یافت نشد.");
            }

            if ($plugin->is_core) {
                throw new \Exception("پلاگین '{$plugin->name}' یک پلاگین هسته است و نمی‌توان آن را حذف کرد.");
            }

            $this->service->forceDelete($plugin);

            return redirect()->route('admin.plugins.index')->with('success', "پلاگین '{$plugin->name}' با موفقیت به طور دائمی حذف شد.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * دریافت وضعیت پلاگین (API)
     */
    public function status(string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        
        if (!$plugin) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }

        return response()->json([
            'slug' => $plugin->slug,
            'name' => $plugin->name,
            'is_active' => $plugin->is_active,
            'is_installed' => $plugin->isInstalled(),
            'is_activated' => $plugin->isActivated(),
            'is_expired' => $plugin->isExpired(),
            'version' => $plugin->version,
            'category' => $plugin->category,
            'type' => $plugin->type,
            'price' => $plugin->price,
            'is_free' => $plugin->is_free,
            'view_count' => $plugin->view_count,
            'download_count' => $plugin->download_count,
        ]);
    }
}
