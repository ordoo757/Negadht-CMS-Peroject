<?php

namespace App\Modules\MenuMaker\Services;

use App\Modules\AiKernel\Services\AiService;
use Illuminate\Support\Facades\DB;

class AiMenuBuilder
{
    protected AiService $ai;
    protected MenuService $menuService;

    public function __construct(AiService $ai, MenuService $menuService)
    {
        $this->ai = $ai;
        $this->menuService = $menuService;
    }

    public function generateSmartMenu(array $context): array
    {
        $prompt = $this->buildPrompt($context);

        $result = $this->ai->generate($prompt, 'json');

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $menuStructure = json_decode($result['data'], true);

        if (!$menuStructure) {
            return ['success' => false, 'error' => 'Invalid AI response format'];
        }

        return [
            'success' => true,
            'menu' => $menuStructure,
            'preview' => $this->generatePreview($menuStructure),
        ];
    }

    public function suggestMenuImprovements(int $menuId): array
    {
        $menu = DB::table('menus')->where('id', $menuId)->first();
        $items = DB::table('menu_items')->where('menu_id', $menuId)->get();

        $prompt = "Analyze this menu structure and suggest improvements:
";
        $prompt .= "Menu: {$menu->name}
";
        $prompt .= "Items: " . json_encode($items, JSON_PRETTY_PRINT) . "
";
        $prompt .= "Provide suggestions for better UX, accessibility, and SEO.";

        $result = $this->ai->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    public function autoGenerateFromSitemap(string $url): array
    {
        try {
            $html = file_get_contents($url);
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);

            $links = $dom->getElementsByTagName('a');
            $menuItems = [];

            foreach ($links as $link) {
                $href = $link->getAttribute('href');
                $title = trim($link->textContent);

                if ($title && $href && !str_starts_with($href, '#')) {
                    $menuItems[] = [
                        'title' => $title,
                        'url' => $href,
                    ];
                }
            }

            return [
                'success' => true,
                'items' => array_slice($menuItems, 0, 20),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function analyzeUserNavigation(int $userId): array
    {
        $history = DB::table('user_navigation_history')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select('path', DB::raw('COUNT(*) as visits'))
            ->groupBy('path')
            ->orderBy('visits', 'desc')
            ->limit(20)
            ->get();

        $prompt = "Based on this user navigation history, suggest personalized menu items:
";
        $prompt .= json_encode($history, JSON_PRETTY_PRINT);

        $result = $this->ai->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    protected function buildPrompt(array $context): string
    {
        $siteType = $context['site_type'] ?? 'general';
        $audience = $context['audience'] ?? 'general';
        $language = $context['language'] ?? 'fa';
        $pages = $context['pages'] ?? [];

        $prompt = "Generate a professional menu structure for a {$siteType} website.
";
        $prompt .= "Target audience: {$audience}
";
        $prompt .= "Language: {$language}
";
        $prompt .= "Available pages: " . implode(', ', $pages) . "

";
        $prompt .= "Generate a JSON structure with this format:
";
        $prompt .= json_encode([
            'name' => 'Menu Name',
            'type' => 'horizontal',
            'items' => [
                [
                    'title' => 'Home',
                    'url' => '/',
                    'icon' => 'home',
                    'children' => [],
                ],
            ],
        ], JSON_PRETTY_PRINT);

        return $prompt;
    }

    protected function generatePreview(array $menuStructure): string
    {
        // Generate HTML preview
        $html = '<nav class="ai-menu-preview">';
        $html .= '<ul>';

        foreach ($menuStructure['items'] ?? [] as $item) {
            $html .= $this->renderPreviewItem($item);
        }

        $html .= '</ul></nav>';

        return $html;
    }

    protected function renderPreviewItem(array $item, int $level = 0): string
    {
        $indent = str_repeat('  ', $level);
        $html = "{$indent}<li>";
        $html .= "<a href="{$item['url']}">";
        if (!empty($item['icon'])) {
            $html .= "<i class="icon-{$item['icon']}"></i> ";
        }
        $html .= $item['title'];
        $html .= "</a>";

        if (!empty($item['children'])) {
            $html .= "
{$indent}<ul>";
            foreach ($item['children'] as $child) {
                $html .= $this->renderPreviewItem($child, $level + 1);
            }
            $html .= "
{$indent}</ul>";
        }

        $html .= "</li>";

        return $html;
    }
}
