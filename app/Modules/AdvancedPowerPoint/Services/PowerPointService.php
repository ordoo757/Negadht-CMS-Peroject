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

namespace App\Modules\AdvancedPowerPoint\Services;

use App\Modules\AdvancedPowerPoint\Models\Presentation;
use App\Modules\AdvancedPowerPoint\Models\Slide;
use App\Modules\AdvancedPowerPoint\Models\SlideElement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PowerPointService
{
    /**
     * دریافت لیست ارائه‌ها
     */
    public function getPresentations(array $filters = [], int $perPage = 20)
    {
        $query = Presentation::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($perPage);
    }

    /**
     * دریافت یک ارائه
     */
    public function getPresentation(int $id): ?Presentation
    {
        return Cache::remember("presentation_{$id}", 3600, function () use ($id) {
            return Presentation::with('slides.elements')->find($id);
        });
    }

    /**
     * ایجاد ارائه جدید
     */
    public function createPresentation(array $data): Presentation
    {
        $presentation = Presentation::create($data);
        $presentation->clearCache();

        // ایجاد اسلاید پیش‌فرض
        $this->createDefaultSlide($presentation);

        Log::info("Presentation created: {$presentation->name} (ID: {$presentation->id})");
        return $presentation;
    }

    /**
     * ایجاد اسلاید پیش‌فرض
     */
    protected function createDefaultSlide(Presentation $presentation): void
    {
        $slide = Slide::create([
            'presentation_id' => $presentation->id,
            'title' => 'اسلاید ۱',
            'order' => 0,
            'layout' => 'title',
        ]);

        SlideElement::create([
            'slide_id' => $slide->id,
            'type' => 'text',
            'content' => $presentation->name,
            'style' => [
                'font_size' => 48,
                'color' => '#333333',
                'text_align' => 'center',
                'font_weight' => 'bold',
            ],
            'position' => ['x' => 50, 'y' => 40],
            'size' => ['width' => 80, 'height' => 20],
        ]);
    }

    /**
     * بروزرسانی ارائه
     */
    public function updatePresentation(Presentation $presentation, array $data): Presentation
    {
        $presentation->update($data);
        $presentation->clearCache();

        Log::info("Presentation updated: {$presentation->name} (ID: {$presentation->id})");
        return $presentation;
    }

    /**
     * حذف ارائه
     */
    public function deletePresentation(Presentation $presentation): bool
    {
        $presentation->delete();
        $presentation->clearCache();

        Log::info("Presentation deleted: {$presentation->name} (ID: {$presentation->id})");
        return true;
    }

    /**
     * ایجاد اسلاید جدید
     */
    public function createSlide(int $presentationId, array $data): Slide
    {
        $presentation = Presentation::findOrFail($presentationId);
        $order = $presentation->slides()->count();

        $slide = Slide::create([
            'presentation_id' => $presentationId,
            'title' => $data['title'] ?? 'اسلاید ' . ($order + 1),
            'order' => $order,
            'layout' => $data['layout'] ?? 'default',
            'background' => $data['background'] ?? null,
            'transition' => $data['transition'] ?? null,
        ]);

        $presentation->clearCache();

        Log::info("Slide created: {$slide->title} in presentation {$presentationId}");
        return $slide;
    }

    /**
     * بروزرسانی اسلاید
     */
    public function updateSlide(Slide $slide, array $data): Slide
    {
        $slide->update($data);
        $slide->presentation->clearCache();

        Log::info("Slide updated: {$slide->title} (ID: {$slide->id})");
        return $slide;
    }

    /**
     * حذف اسلاید
     */
    public function deleteSlide(Slide $slide): bool
    {
        $presentationId = $slide->presentation_id;
        $slide->delete();
        Presentation::find($presentationId)->clearCache();

        Log::info("Slide deleted: {$slide->title} (ID: {$slide->id})");
        return true;
    }

    /**
     * اضافه کردن عنصر به اسلاید
     */
    public function addElement(int $slideId, array $data): SlideElement
    {
        $slide = Slide::findOrFail($slideId);
        $order = $slide->elements()->count();

        $element = SlideElement::create([
            'slide_id' => $slideId,
            'type' => $data['type'],
            'content' => $data['content'] ?? null,
            'style' => $data['style'] ?? [],
            'position' => $data['position'] ?? ['x' => 50, 'y' => 50],
            'size' => $data['size'] ?? ['width' => 80, 'height' => 30],
            'order' => $order,
            'animation' => $data['animation'] ?? null,
        ]);

        $slide->presentation->clearCache();

        Log::info("Element added to slide {$slideId}: type {$data['type']}");
        return $element;
    }

    /**
     * بروزرسانی عنصر
     */
    public function updateElement(SlideElement $element, array $data): SlideElement
    {
        $element->update($data);
        $element->slide->presentation->clearCache();

        Log::info("Element updated: ID {$element->id}");
        return $element;
    }

    /**
     * حذف عنصر
     */
    public function deleteElement(SlideElement $element): bool
    {
        $presentationId = $element->slide->presentation_id;
        $element->delete();
        Presentation::find($presentationId)->clearCache();

        Log::info("Element deleted: ID {$element->id}");
        return true;
    }

    /**
     * دریافت تنظیمات دسترسی کاربر
     */
    public function getUserPermissions(int $presentationId, int $userId): array
    {
        $permission = \App\Modules\AdvancedPowerPoint\Models\PresentationUserPermission::where([
            'presentation_id' => $presentationId,
            'user_id' => $userId,
        ])->first();

        if (!$permission) {
            return [
                'can_edit' => false,
                'can_delete' => false,
                'can_share' => false,
                'can_present' => false,
            ];
        }

        return [
            'can_edit' => $permission->can_edit,
            'can_delete' => $permission->can_delete,
            'can_share' => $permission->can_share,
            'can_present' => $permission->can_present,
        ];
    }

    /**
     * بروزرسانی تنظیمات دسترسی کاربر
     */
    public function updateUserPermissions(int $presentationId, int $userId, array $permissions): void
    {
        \App\Modules\AdvancedPowerPoint\Models\PresentationUserPermission::updateOrCreate(
            ['presentation_id' => $presentationId, 'user_id' => $userId],
            $permissions
        );
    }

    /**
     * دریافت آمار
     */
    public function getStats(): array
    {
        return [
            'total_presentations' => Presentation::count(),
            'active_presentations' => Presentation::where('is_active', true)->count(),
            'total_slides' => Slide::count(),
            'total_elements' => SlideElement::count(),
        ];
    }

    /**
     * صادرات به فایل
     */
    public function export(Presentation $presentation): array
    {
        $data = [
            'name' => $presentation->name,
            'description' => $presentation->description,
            'theme' => $presentation->theme,
            'settings' => $presentation->settings,
            'slides' => [],
        ];

        foreach ($presentation->slides as $slide) {
            $slideData = [
                'title' => $slide->title,
                'layout' => $slide->layout,
                'background' => $slide->background,
                'transition' => $slide->transition,
                'elements' => [],
            ];

            foreach ($slide->elements as $element) {
                $slideData['elements'][] = [
                    'type' => $element->type,
                    'content' => $element->content,
                    'style' => $element->style,
                    'position' => $element->position,
                    'size' => $element->size,
                ];
            }

            $data['slides'][] = $slideData;
        }

        return $data;
    }
}
