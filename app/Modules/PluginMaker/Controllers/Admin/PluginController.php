<?php

namespace App\Modules\PluginMaker\Controllers\Admin;

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

    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'type', 'search', 'is_active', 'is_free']);
        $perPage = $request->get('per_page', 20);

        $plugins = $this->service->getList($filters, $perPage);
        $stats = $this->service->getStats();

        return view('plugin-maker::admin.index', compact('plugins', 'stats', 'filters'));
    }

    public function create()
    {
        return view('plugin-maker::admin.create');
    }

    public function store(Request $request)
    {
        try {
            $plugin = $this->service->create($request->all());
            return redirect()->route('admin.plugin-maker.index')
                ->with('success', "Plugin '{$plugin->name}' created successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create plugin: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $slug)
    {
        $plugin = $this->service->getDetails($slug);
        if (!$plugin) abort(404, 'Plugin not found.');
        return view('plugin-maker::admin.show', compact('plugin'));
    }

    public function edit(string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) abort(404, 'Plugin not found.');
        return view('plugin-maker::admin.edit', compact('plugin'));
    }

    public function update(Request $request, string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) abort(404, 'Plugin not found.');

        try {
            $plugin = $this->service->update($plugin, $request->all());
            return redirect()->route('admin.plugin-maker.index')
                ->with('success', "Plugin '{$plugin->name}' updated successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update plugin: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(string $slug)
    {
        $plugin = Plugin::where('slug', $slug)->first();
        if (!$plugin) abort(404, 'Plugin not found.');

        $this->service->delete($plugin);
        return redirect()->route('admin.plugin-maker.index')
            ->with('success', "Plugin '{$plugin->name}' deleted successfully.");
    }

    public function forceDestroy(string $slug)
    {
        $plugin = Plugin::withTrashed()->where('slug', $slug)->first();
        if (!$plugin) abort(404, 'Plugin not found.');

        $this->service->forceDelete($plugin);
        return redirect()->route('admin.plugin-maker.index')
            ->with('success', "Plugin '{$plugin->name}' permanently deleted.");
    }

    public function restore(string $slug)
    {
        $plugin = $this->service->restore($slug);
        if (!$plugin) abort(404, 'Plugin not found.');

        return redirect()->route('admin.plugin-maker.index')
            ->with('success', "Plugin '{$plugin->name}' restored successfully.");
    }

    public function install(string $slug)
    {
        try {
            $this->service->install($slug);
            return back()->with('success', "Plugin '{$slug}' installed successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to install plugin: ' . $e->getMessage());
        }
    }

    public function uninstall(string $slug)
    {
        try {
            $this->service->uninstall($slug);
            return back()->with('success', "Plugin '{$slug}' uninstalled successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to uninstall plugin: ' . $e->getMessage());
        }
    }

    public function activate(string $slug)
    {
        try {
            $this->service->activate($slug);
            return back()->with('success', "Plugin '{$slug}' activated successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to activate plugin: ' . $e->getMessage());
        }
    }

    public function deactivate(string $slug)
    {
        try {
            $this->service->deactivate($slug);
            return back()->with('success', "Plugin '{$slug}' deactivated successfully.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to deactivate plugin: ' . $e->getMessage());
        }
    }
}
