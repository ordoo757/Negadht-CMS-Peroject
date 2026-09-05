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

namespace App\Modules\Antivirus\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\Antivirus\Models\VirusDefinition;
use App\Modules\Antivirus\Services\AntivirusService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VirusDefinitionController extends AdminController
{
    protected AntivirusService $service;

    public function __construct(AntivirusService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست تعاریف ویروس‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'severity', 'is_active', 'search']);
        $definitions = $this->service->getVirusDefinitions($filters);

        $stats = [
            'total' => VirusDefinition::count(),
            'active' => VirusDefinition::where('is_active', true)->count(),
            'inactive' => VirusDefinition::where('is_active', false)->count(),
        ];

        return view('antivirus::admin.virus-definitions', compact('definitions', 'stats', 'filters'));
    }

    /**
     * نمایش فرم ایجاد تعریف جدید
     */
    public function create()
    {
        $types = ['php', 'javascript', 'html', 'sql', 'python', 'ruby', 'perl', 'java', 'c', 'cpp', 'go', 'rust', 'other'];
        $severities = ['low', 'medium', 'high', 'critical'];

        return view('antivirus::admin.virus-definition-create', compact('types', 'severities'));
    }

    /**
     * ذخیره تعریف جدید
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:virus_definitions,name',
                'pattern' => 'required|string',
                'type' => 'required|string|in:php,javascript,html,sql,python,ruby,perl,java,c,cpp,go,rust,other',
                'severity' => 'required|string|in:low,medium,high,critical',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
            ]);

            $definition = $this->service->createVirusDefinition($validated);

            return redirect()->route('admin.antivirus.virus-definitions')
                ->with('success', "تعریف ویروس '{$definition->name}' با موفقیت ایجاد شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد تعریف ویروس: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * نمایش فرم ویرایش تعریف
     */
    public function edit(int $id)
    {
        $definition = VirusDefinition::findOrFail($id);
        $types = ['php', 'javascript', 'html', 'sql', 'python', 'ruby', 'perl', 'java', 'c', 'cpp', 'go', 'rust', 'other'];
        $severities = ['low', 'medium', 'high', 'critical'];

        return view('antivirus::admin.virus-definition-edit', compact('definition', 'types', 'severities'));
    }

    /**
     * بروزرسانی تعریف
     */
    public function update(Request $request, int $id)
    {
        try {
            $definition = VirusDefinition::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:virus_definitions,name,' . $definition->id,
                'pattern' => 'required|string',
                'type' => 'required|string|in:php,javascript,html,sql,python,ruby,perl,java,c,cpp,go,rust,other',
                'severity' => 'required|string|in:low,medium,high,critical',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
            ]);

            $definition = $this->service->updateVirusDefinition($definition, $validated);

            return redirect()->route('admin.antivirus.virus-definitions')
                ->with('success', "تعریف ویروس '{$definition->name}' با موفقیت بروزرسانی شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی تعریف ویروس: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * حذف تعریف
     */
    public function destroy(int $id)
    {
        try {
            $definition = VirusDefinition::findOrFail($id);
            $this->service->deleteVirusDefinition($definition);

            return redirect()->route('admin.antivirus.virus-definitions')
                ->with('success', "تعریف ویروس '{$definition->name}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف تعریف ویروس: ' . $e->getMessage());
        }
    }

    /**
     * فعال/غیرفعال کردن تعریف
     */
    public function toggle(int $id)
    {
        try {
            $definition = VirusDefinition::findOrFail($id);
            $definition->is_active = !$definition->is_active;
            $definition->save();

            $status = $definition->is_active ? 'فعال' : 'غیرفعال';

            return redirect()->route('admin.antivirus.virus-definitions')
                ->with('success', "تعریف ویروس '{$definition->name}' با موفقیت {$status} شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در تغییر وضعیت تعریف ویروس: ' . $e->getMessage());
        }
    }

    /**
     * واردات تعاریف از فایل JSON
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:json|max:2048',
            ]);

            $content = file_get_contents($request->file('file')->getRealPath());
            $data = json_decode($content, true);

            if (!is_array($data) || empty($data['definitions'])) {
                throw new \Exception('فرمت فایل نامعتبر است. باید شامل کلید definitions باشد.');
            }

            $count = 0;
            foreach ($data['definitions'] as $definition) {
                VirusDefinition::updateOrCreate(
                    ['name' => $definition['name']],
                    $definition
                );
                $count++;
            }

            return redirect()->route('admin.antivirus.virus-definitions')
                ->with('success', "{$count} تعریف ویروس با موفقیت وارد شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در واردات تعاریف: ' . $e->getMessage());
        }
    }

    /**
     * خروجی تعاریف به فایل JSON
     */
    public function export()
    {
        $definitions = VirusDefinition::all();

        $data = [
            'version' => '1.0.0',
            'exported_at' => now()->toDateTimeString(),
            'total' => $definitions->count(),
            'definitions' => $definitions->toArray(),
        ];

        $filename = "virus_definitions_{$data['version']}_{$data['exported_at']}.json";

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
