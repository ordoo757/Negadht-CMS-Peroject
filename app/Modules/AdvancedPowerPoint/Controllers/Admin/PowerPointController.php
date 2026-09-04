<?php

namespace App\Modules\AdvancedPowerPoint\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\AdvancedPowerPoint\Models\Presentation;
use App\Modules\AdvancedPowerPoint\Models\Slide;
use App\Modules\AdvancedPowerPoint\Services\PowerPointService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PowerPointController extends AdminController
{
    protected PowerPointService $service;

    public function __construct(PowerPointService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست ارائه‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'is_active', 'is_public']);
        $presentations = $this->service->getPresentations($filters);
        $stats = $this->service->getStats();

        return view('advanced-powerpoint::admin.index', compact('presentations', 'stats', 'filters'));
    }

    /**
     * نمایش فرم ایجاد ارائه جدید
     */
    public function create()
    {
        return view('advanced-powerpoint::admin.create');
    }

    /**
     * ذخیره ارائه جدید
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
                'is_public' => 'nullable|boolean',
                'theme' => 'nullable|string|max:50',
                'settings' => 'nullable|array',
            ]);

            $presentation = $this->service->createPresentation($validated);

            return redirect()->route('admin.advanced-powerpoint.index')
                ->with('success', "ارائه '{$presentation->name}' با موفقیت ایجاد شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد ارائه: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * نمایش ویرایشگر ارائه
     */
    public function edit(int $id)
    {
        $presentation = $this->service->getPresentation($id);

        if (!$presentation) {
            abort(404, 'ارائه یافت نشد.');
        }

        return view('advanced-powerpoint::admin.editor', compact('presentation'));
    }

    /**
     * بروزرسانی ارائه
     */
    public function update(Request $request, int $id)
    {
        try {
            $presentation = Presentation::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
                'is_public' => 'nullable|boolean',
                'theme' => 'nullable|string|max:50',
                'settings' => 'nullable|array',
            ]);

            $this->service->updatePresentation($presentation, $validated);

            return redirect()->route('admin.advanced-powerpoint.index')
                ->with('success', "ارائه '{$presentation->name}' با موفقیت بروزرسانی شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی ارائه: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * حذف ارائه
     */
    public function destroy(int $id)
    {
        try {
            $presentation = Presentation::findOrFail($id);
            $this->service->deletePresentation($presentation);

            return redirect()->route('admin.advanced-powerpoint.index')
                ->with('success', "ارائه '{$presentation->name}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف ارائه: ' . $e->getMessage());
        }
    }

    // ===== مدیریت اسلایدها =====

    /**
     * ایجاد اسلاید جدید (AJAX)
     */
    public function createSlide(Request $request, int $presentationId)
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'layout' => 'nullable|string|max:50',
                'background' => 'nullable|string|max:50',
                'transition' => 'nullable|string|max:50',
            ]);

            $slide = $this->service->createSlide($presentationId, $validated);

            return response()->json([
                'success' => true,
                'slide' => $slide,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * بروزرسانی اسلاید (AJAX)
     */
    public function updateSlide(Request $request, int $slideId)
    {
        try {
            $slide = Slide::findOrFail($slideId);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'layout' => 'nullable|string|max:50',
                'background' => 'nullable|string|max:50',
                'transition' => 'nullable|string|max:50',
            ]);

            $slide = $this->service->updateSlide($slide, $validated);

            return response()->json([
                'success' => true,
                'slide' => $slide,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف اسلاید (AJAX)
     */
    public function deleteSlide(int $slideId)
    {
        try {
            $slide = Slide::findOrFail($slideId);
            $this->service->deleteSlide($slide);

            return response()->json([
                'success' => true,
                'message' => 'اسلاید با موفقیت حذف شد.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * اضافه کردن عنصر به اسلاید (AJAX)
     */
    public function addElement(Request $request, int $slideId)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|string|in:text,image,shape,chart,table,video',
                'content' => 'nullable|string',
                'style' => 'nullable|array',
                'position' => 'nullable|array',
                'size' => 'nullable|array',
                'animation' => 'nullable|string',
            ]);

            $element = $this->service->addElement($slideId, $validated);

            return response()->json([
                'success' => true,
                'element' => $element,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * بروزرسانی عنصر (AJAX)
     */
    public function updateElement(Request $request, int $elementId)
    {
        try {
            $element = \App\Modules\AdvancedPowerPoint\Models\SlideElement::findOrFail($elementId);

            $validated = $request->validate([
                'content' => 'nullable|string',
                'style' => 'nullable|array',
                'position' => 'nullable|array',
                'size' => 'nullable|array',
                'animation' => 'nullable|string',
            ]);

            $element = $this->service->updateElement($element, $validated);

            return response()->json([
                'success' => true,
                'element' => $element,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف عنصر (AJAX)
     */
    public function deleteElement(int $elementId)
    {
        try {
            $element = \App\Modules\AdvancedPowerPoint\Models\SlideElement::findOrFail($elementId);
            $this->service->deleteElement($element);

            return response()->json([
                'success' => true,
                'message' => 'عنصر با موفقیت حذف شد.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * تنظیمات دسترسی کاربران
     */
    public function permissions(int $id)
    {
        $presentation = Presentation::findOrFail($id);
        $users = \App\Models\User::all();

        return view('advanced-powerpoint::admin.permissions', compact('presentation', 'users'));
    }

    /**
     * بروزرسانی دسترسی کاربران
     */
    public function updatePermissions(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'can_edit' => 'nullable|boolean',
                'can_delete' => 'nullable|boolean',
                'can_share' => 'nullable|boolean',
                'can_present' => 'nullable|boolean',
            ]);

            $this->service->updateUserPermissions(
                $id,
                $validated['user_id'],
                [
                    'can_edit' => $validated['can_edit'] ?? false,
                    'can_delete' => $validated['can_delete'] ?? false,
                    'can_share' => $validated['can_share'] ?? false,
                    'can_present' => $validated['can_present'] ?? false,
                ]
            );

            return redirect()->back()->with('success', 'دسترسی‌ها با موفقیت بروزرسانی شد.');

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی دسترسی‌ها: ' . $e->getMessage());
        }
    }

    /**
     * جاسازی در صفحه (Embed)
     */
    public function embed(int $id)
    {
        $presentation = $this->service->getPresentation($id);

        if (!$presentation || !$presentation->is_public) {
            abort(404, 'ارائه یافت نشد یا عمومی نیست.');
        }

        $presentation->incrementViews();

        return view('advanced-powerpoint::admin.embed', compact('presentation'));
    }
}
