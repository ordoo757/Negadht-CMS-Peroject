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

namespace App\Modules\FormCreator\Services;

use App\Modules\AiKernel\Services\AiService;

class AiFormBuilder
{
    protected AiService $ai;
    protected FormService $formService;

    public function __construct(AiService $ai, FormService $formService)
    {
        $this->ai = $ai;
        $this->formService = $formService;
    }

    public function generateSmartForm(array $requirements): array
    {
        $prompt = $this->buildPrompt($requirements);

        $result = $this->ai->generate($prompt, 'json');

        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $formStructure = json_decode($result['data'], true);

        if (!$formStructure) {
            return ['success' => false, 'error' => 'Invalid AI response'];
        }

        return [
            'success' => true,
            'form' => $formStructure,
            'preview' => $this->generatePreview($formStructure),
        ];
    }

    public function analyzeFormPerformance(int $formId): array
    {
        $responses = $this->formService->getResponses($formId);

        if (empty($responses)) {
            return ['success' => false, 'message' => 'No data available'];
        }

        $prompt = "Analyze this form submission data and provide insights:
";
        $prompt .= json_encode($responses, JSON_PRETTY_PRINT);
        $prompt .= "
Provide: completion rate, common issues, field analysis, and recommendations.";

        $result = $this->ai->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    public function suggestFormImprovements(int $formId): array
    {
        $form = $this->formService->getForm($formId);
        if (!$form) return [];

        $prompt = "Analyze this form and suggest UX improvements:
";
        $prompt .= "Form: {$form->name}
";
        $prompt .= "Fields: {$form->fields}
";
        $prompt .= "Provide suggestions for better conversion, accessibility, and user experience.";

        $result = $this->ai->generate($prompt, 'json');

        if ($result['success']) {
            return json_decode($result['data'], true) ?? [];
        }

        return [];
    }

    protected function buildPrompt(array $requirements): string
    {
        $purpose = $requirements['purpose'] ?? 'general';
        $audience = $requirements['audience'] ?? 'general';
        $language = $requirements['language'] ?? 'fa';
        $fields = $requirements['fields'] ?? [];

        $prompt = "Generate a professional form structure for: {$purpose}
";
        $prompt .= "Target audience: {$audience}
";
        $prompt .= "Language: {$language}
";

        if (!empty($fields)) {
            $prompt .= "Required fields: " . implode(', ', $fields) . "
";
        }

        $prompt .= "
Generate JSON with this structure:
";
        $prompt .= json_encode([
            'name' => 'Form Name',
            'description' => 'Form description',
            'fields' => [
                [
                    'name' => 'field_name',
                    'label' => 'Field Label',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Placeholder text',
                    'help' => 'Help text',
                ],
            ],
            'settings' => [
                'submit_text' => 'Submit',
                'success_message' => 'Success message',
                'ajax' => true,
            ],
        ], JSON_PRETTY_PRINT);

        return $prompt;
    }

    protected function generatePreview(array $formStructure): string
    {
        return $this->formService->renderForm(0, ['preview' => true, 'structure' => $formStructure]);
    }
}
