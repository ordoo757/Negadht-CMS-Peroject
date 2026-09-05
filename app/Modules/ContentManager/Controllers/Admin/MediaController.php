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
use App\Modules\ContentManager\Models\Media;
use App\Modules\ContentManager\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends AdminController
{
    protected ContentService $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست رسانه‌ها
     */
    public function index(Request $request)
    {
        $filters = $request->only(['mime_type', 'search']);
        $media = $this->service->getMedia($filters);

        $stats = [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'like', 'application/%')->count(),
            'others' => Media::whereNotIn('mime_type', ['image/%', 'video/%', 'application/%'])->count(),
            'total_size' => Media::sum('size'),
        ];

        return view('content-manager::admin.media.index', compact('media', 'stats', 'filters'));
    }

    /**
     * آپلود فایل
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:20480', // 20MB
                'alt' => 'nullable|string|max:255',
                'caption' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            $alt = $request->input('alt');

            $media = $this->service->uploadMedia($file, $alt);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'media' => $media,
                    'message' => 'فایل با موفقیت آپلود شد.',
                ]);
            }

            return redirect()->route('admin.content-manager.media.index')
                ->with('success', "فایل '{$media->original_name}' با موفقیت آپلود شد.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'خطا در آپلود فایل: ' . $e->getMessage());
        }
    }

    /**
     * حذف فایل
     */
    public function destroy(int $id)
    {
        try {
            $media = Media::findOrFail($id);
            $filename = $media->original_name;

            $this->service->deleteMedia($media);

            return redirect()->route('admin.content-manager.media.index')
                ->with('success', "فایل '{$filename}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف فایل: ' . $e->getMessage());
        }
    }

    /**
     * حذف چندگانه فایل‌ها
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:media,id',
            ]);

            $count = 0;
            foreach ($request->ids as $id) {
                $media = Media::find($id);
                if ($media) {
                    $this->service->deleteMedia($media);
                    $count++;
                }
            }

            return redirect()->route('admin.content-manager.media.index')
                ->with('success', "{$count} فایل با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف فایل‌ها: ' . $e->getMessage());
        }
    }

    /**
     * دریافت اطلاعات فایل (API)
     */
    public function info(int $id)
    {
        try {
            $media = Media::findOrFail($id);

            return response()->json([
                'success' => true,
                'media' => $media,
                'url' => $media->url,
                'size' => $media->size_label,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * جستجوی فایل‌ها (API)
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $type = $request->get('type', 'all');

            $mediaQuery = Media::query();

            if ($query) {
                $mediaQuery->where('original_name', 'like', "%{$query}%")
                          ->orWhere('alt', 'like', "%{$query}%")
                          ->orWhere('filename', 'like', "%{$query}%");
            }

            if ($type !== 'all') {
                $mimeTypes = [
                    'image' => 'image/%',
                    'video' => 'video/%',
                    'audio' => 'audio/%',
                    'document' => 'application/%',
                ];

                if (isset($mimeTypes[$type])) {
                    $mediaQuery->where('mime_type', 'like', $mimeTypes[$type]);
                }
            }

            $media = $mediaQuery->limit(20)->get();

            return response()->json([
                'success' => true,
                'media' => $media,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
