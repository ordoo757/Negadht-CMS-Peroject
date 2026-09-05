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
use App\Modules\ContentManager\Models\Comment;
use App\Modules\ContentManager\Models\Page;
use App\Modules\ContentManager\Services\ContentService;
use Illuminate\Http\Request;

class CommentController extends AdminController
{
    protected ContentService $service;

    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    /**
     * لیست نظرات
     */
    public function index(Request $request)
    {
        $filters = $request->only(['page_id', 'is_approved', 'search']);
        $comments = $this->service->getComments($filters);

        $stats = [
            'total' => Comment::count(),
            'approved' => Comment::where('is_approved', true)->count(),
            'pending' => Comment::where('is_approved', false)->count(),
            'per_page' => $comments->perPage(),
        ];

        $pages = Page::published()->pluck('title', 'id');

        return view('content-manager::admin.comments.index', compact('comments', 'stats', 'filters', 'pages'));
    }

    /**
     * تأیید نظر
     */
    public function approve(int $id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $this->service->approveComment($comment);

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "نظر با موفقیت تأیید شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در تأیید نظر: ' . $e->getMessage());
        }
    }

    /**
     * رد نظر
     */
    public function reject(int $id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $this->service->rejectComment($comment);

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "نظر با موفقیت رد شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در رد نظر: ' . $e->getMessage());
        }
    }

    /**
     * تأیید چندگانه نظرات
     */
    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:comments,id',
            ]);

            $count = 0;
            foreach ($request->ids as $id) {
                $comment = Comment::find($id);
                if ($comment && !$comment->is_approved) {
                    $this->service->approveComment($comment);
                    $count++;
                }
            }

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "{$count} نظر با موفقیت تأیید شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در تأیید نظرات: ' . $e->getMessage());
        }
    }

    /**
     * رد چندگانه نظرات
     */
    public function bulkReject(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:comments,id',
            ]);

            $count = 0;
            foreach ($request->ids as $id) {
                $comment = Comment::find($id);
                if ($comment && $comment->is_approved) {
                    $this->service->rejectComment($comment);
                    $count++;
                }
            }

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "{$count} نظر با موفقیت رد شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در رد نظرات: ' . $e->getMessage());
        }
    }

    /**
     * حذف نظر
     */
    public function destroy(int $id)
    {
        try {
            $comment = Comment::findOrFail($id);
            $this->service->deleteComment($comment);

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "نظر با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف نظر: ' . $e->getMessage());
        }
    }

    /**
     * حذف چندگانه نظرات
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:comments,id',
            ]);

            $count = 0;
            foreach ($request->ids as $id) {
                $comment = Comment::find($id);
                if ($comment) {
                    $this->service->deleteComment($comment);
                    $count++;
                }
            }

            return redirect()->route('admin.content-manager.comments.index')
                ->with('success', "{$count} نظر با موفقیت حذف شد.");

        } catch (\Exception $e) {
            return back()->with('error', 'خطا در حذف نظرات: ' . $e->getMessage());
        }
    }

    /**
     * دریافت نظرات یک صفحه (API)
     */
    public function getPageComments(int $pageId)
    {
        try {
            $comments = Comment::where('page_id', $pageId)
                ->where('is_approved', true)
                ->whereNull('parent_id')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'comments' => $comments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * آمار نظرات (API)
     */
    public function stats()
    {
        try {
            $stats = [
                'today' => Comment::whereDate('created_at', today())->count(),
                'week' => Comment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'month' => Comment::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
                'pending' => Comment::where('is_approved', false)->count(),
                'approved' => Comment::where('is_approved', true)->count(),
                'total' => Comment::count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
