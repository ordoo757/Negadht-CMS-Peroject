<?php

namespace App\Modules\ContentManager\Controllers\Admin;

use App\Core\Controllers\AdminController;
use App\Modules\ContentManager\Models\Page;
use App\Modules\ContentManager\Models\Category;
use App\Modules\ContentManager\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PageController extends AdminController
{
    protected ContentService $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'category', 'search', 'is_home']);
        $pages = $this->service->getPages($filters);
        $categories = $this->service->getCategories();
        $stats = $this->service->getStats();

        return view('content-manager::admin.pages.index', compact('pages', 'categories', 'stats', 'filters'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        $statuses = ['draft', 'published', 'pending'];
        return view('content-manager::admin.pages.create', compact('categories', 'statuses'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:pages,slug',
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string|max:500',
                'status' => 'required|in:draft,published,pending',
                'category_id' => 'nullable|exists:categories,id',
                'is_home' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'featured_image' => 'nullable|string|max:255',
            ]);

            $page = $this->service->createPage($validated);

            return redirect()->route('admin.content-manager.pages.index')
                ->with('success', "صفحه '{$page->title}' با موفقیت ایجاد شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در ایجاد صفحه: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $id)
    {
        $page = Page::findOrFail($id);
        $page->incrementViews();
        return view('content-manager::admin.pages.show', compact('page'));
    }

    public function edit(string $id)
    {
        $page = Page::findOrFail($id);
        $categories = Category::active()->get();
        $statuses = ['draft', 'published', 'pending'];
        return view('content-manager::admin.pages.edit', compact('page', 'categories', 'statuses'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $page = Page::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string|max:500',
                'status' => 'required|in:draft,published,pending',
                'category_id' => 'nullable|exists:categories,id',
                'is_home' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'featured_image' => 'nullable|string|max:255',
            ]);

            $page = $this->service->updatePage($page, $validated);

            return redirect()->route('admin.content-manager.pages.index')
                ->with('success', "صفحه '{$page->title}' با موفقیت بروزرسانی شد.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بروزرسانی صفحه: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $page = Page::findOrFail($id);
            $this->service->deletePage($page);

            return redirect()->route('admin.content-manager.pages.index')
                ->with('success', "صفحه '{$page->title}' با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف صفحه: ' . $e->getMessage());
        }
    }

    public function forceDestroy(string $id)
    {
        try {
            $page = Page::withTrashed()->findOrFail($id);
            $this->service->forceDeletePage($page);

            return redirect()->route('admin.content-manager.pages.index')
                ->with('success', "صفحه '{$page->title}' به طور دائمی حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف دائمی صفحه: ' . $e->getMessage());
        }
    }

    public function restore(string $slug)
    {
        try {
            $page = $this->service->restorePage($slug);

            if (!$page) {
                return back()->with('error', 'صفحه یافت نشد.');
            }

            return redirect()->route('admin.content-manager.pages.index')
                ->with('success', "صفحه '{$page->title}' با موفقیت بازیابی شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بازیابی صفحه: ' . $e->getMessage());
        }
    }

    // ===== AI Features =====

    public function generateWithAI(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'type' => 'required|in:article,page,blog',
        ]);

        try {
            $result = $this->service->generateContent($validated['topic'], $validated['type']);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
