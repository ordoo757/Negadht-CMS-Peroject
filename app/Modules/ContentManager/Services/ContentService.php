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

namespace App\Modules\ContentManager\Services;

use App\Modules\ContentManager\Models\Page;
use App\Modules\ContentManager\Models\Category;
use App\Modules\ContentManager\Models\Media;
use App\Modules\ContentManager\Models\Comment;
use App\Modules\AiKernel\Services\AiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentService
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    // ===== Pages =====

    public function getPages(array $filters = [], int $perPage = 20)
    {
        $query = Page::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_home'])) {
            $query->where('is_home', $filters['is_home']);
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortField, $sortDir);

        return $query->paginate($perPage);
    }

    public function getPage(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }

    public function createPage(array $data): Page
    {
        $page = Page::create($data);
        $page->clearCache();
        
        Log::info("Page created: {$page->title} ({$page->slug})");
        return $page;
    }

    public function updatePage(Page $page, array $data): Page
    {
        $page->update($data);
        $page->clearCache();
        
        Log::info("Page updated: {$page->title} ({$page->slug})");
        return $page;
    }

    public function deletePage(Page $page): bool
    {
        $page->delete();
        $page->clearCache();
        
        Log::info("Page deleted: {$page->title} ({$page->slug})");
        return true;
    }

    public function forceDeletePage(Page $page): bool
    {
        $page->forceDelete();
        $page->clearCache();
        
        Log::info("Page permanently deleted: {$page->title} ({$page->slug})");
        return true;
    }

    public function restorePage(string $slug): ?Page
    {
        $page = Page::withTrashed()->where('slug', $slug)->first();
        
        if (!$page) {
            return null;
        }

        $page->restore();
        $page->clearCache();
        
        Log::info("Page restored: {$page->title} ({$page->slug})");
        return $page;
    }

    // ===== AI Content Generation =====

    public function generateContent(string $topic, string $type = 'article'): array
    {
        $prompt = "Generate a {$type} about '{$topic}'. Include title, content, excerpt and keywords.";

        $result = $this->aiService->generate($prompt, 'text');

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => $result['error'] ?? 'AI generation failed',
            ];
        }

        // Parse AI response (simplified)
        $content = $result['data'];

        return [
            'success' => true,
            'title' => $this->extractTitle($content, $topic),
            'content' => $content,
            'excerpt' => $this->extractExcerpt($content),
            'keywords' => $this->extractKeywords($content),
        ];
    }

    protected function extractTitle(string $content, string $default): string
    {
        // Extract title from content (simplified)
        if (preg_match('/^#\s*(.+)$/m', $content, $matches)) {
            return $matches[1];
        }
        return $default;
    }

    protected function extractExcerpt(string $content): string
    {
        $text = strip_tags($content);
        return Str::limit($text, 200);
    }

    protected function extractKeywords(string $content): string
    {
        // Extract keywords from content (simplified)
        $words = str_word_count(strip_tags($content), 1);
        $words = array_slice($words, 0, 10);
        return implode(', ', $words);
    }

    // ===== Categories =====

    public function getCategories()
    {
        return Category::active()->orderBy('order')->get();
    }

    public function getCategory(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    public function createCategory(array $data): Category
    {
        $category = Category::create($data);
        Log::info("Category created: {$category->name} ({$category->slug})");
        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        Log::info("Category updated: {$category->name} ({$category->slug})");
        return $category;
    }

    public function deleteCategory(Category $category): bool
    {
        $category->delete();
        Log::info("Category deleted: {$category->name} ({$category->slug})");
        return true;
    }

    // ===== Media =====

    public function getMedia(array $filters = [], int $perPage = 20)
    {
        $query = Media::query();

        if (!empty($filters['mime_type'])) {
            $query->where('mime_type', 'like', "{$filters['mime_type']}%");
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%")
                  ->orWhere('alt', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($perPage);
    }

    public function uploadMedia($file, string $alt = null): Media
    {
        return Media::upload($file, 'uploads/content', $alt);
    }

    public function deleteMedia(Media $media): bool
    {
        return $media->deleteFile();
    }

    // ===== Comments =====

    public function getComments(array $filters = [], int $perPage = 20)
    {
        $query = Comment::query();

        if (!empty($filters['page_id'])) {
            $query->where('page_id', $filters['page_id']);
        }

        if (isset($filters['is_approved'])) {
            $query->where('is_approved', $filters['is_approved']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('content', 'like', "%{$search}%");
        }

        $query->orderBy('created_at', 'desc');
        return $query->paginate($perPage);
    }

    public function approveComment(Comment $comment): void
    {
        $comment->approve();
        Log::info("Comment approved: ID {$comment->id}");
    }

    public function rejectComment(Comment $comment): void
    {
        $comment->reject();
        Log::info("Comment rejected: ID {$comment->id}");
    }

    public function deleteComment(Comment $comment): bool
    {
        $comment->delete();
        Log::info("Comment deleted: ID {$comment->id}");
        return true;
    }

    // ===== Stats =====

    public function getStats(): array
    {
        return [
            'total_pages' => Page::count(),
            'published_pages' => Page::published()->count(),
            'draft_pages' => Page::draft()->count(),
            'total_categories' => Category::count(),
            'total_media' => Media::count(),
            'total_comments' => Comment::count(),
            'pending_comments' => Comment::pending()->count(),
            'total_views' => Page::sum('views'),
        ];
    }
}
