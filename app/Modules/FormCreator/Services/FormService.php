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

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class FormService
{
    protected string $cachePrefix = 'form_';

    public function getForm(int $id): ?object
    {
        return DB::table('forms')->where('id', $id)->first();
    }

    public function getFormBySlug(string $slug): ?object
    {
        $cacheKey = "{$this->cachePrefix}{$slug}";

        if (Cache::has($cacheKey)) {
            return (object) Cache::get($cacheKey);
        }

        $form = DB::table('forms')->where('slug', $slug)->first();

        if ($form) {
            Cache::put($cacheKey, (array) $form, now()->addHours(2));
        }

        return $form;
    }

    public function getAllForms(): array
    {
        return DB::table('forms')->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function createForm(array $data): int
    {
        $id = DB::table('forms')->insertGetId([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']),
            'description' => $data['description'] ?? '',
            'fields' => json_encode($data['fields'] ?? []),
            'settings' => json_encode($data['settings'] ?? []),
            'css_class' => $data['css_class'] ?? '',
            'success_message' => $data['success_message'] ?? 'فرم با موفقیت ارسال شد.',
            'error_message' => $data['error_message'] ?? 'خطا در ارسال فرم.',
            'email_notifications' => $data['email_notifications'] ?? false,
            'notification_email' => $data['notification_email'] ?? '',
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function updateForm(int $id, array $data): bool
    {
        $form = $this->getForm($id);
        if (!$form) return false;

        DB::table('forms')->where('id', $id)->update([
            'name' => $data['name'] ?? $form->name,
            'description' => $data['description'] ?? $form->description,
            'fields' => isset($data['fields']) ? json_encode($data['fields']) : $form->fields,
            'settings' => isset($data['settings']) ? json_encode($data['settings']) : $form->settings,
            'success_message' => $data['success_message'] ?? $form->success_message,
            'error_message' => $data['error_message'] ?? $form->error_message,
            'email_notifications' => $data['email_notifications'] ?? $form->email_notifications,
            'notification_email' => $data['notification_email'] ?? $form->notification_email,
            'is_active' => $data['is_active'] ?? $form->is_active,
            'updated_at' => now(),
        ]);

        Cache::forget("{$this->cachePrefix}{$form->slug}");

        return true;
    }

    public function deleteForm(int $id): bool
    {
        $form = $this->getForm($id);
        if (!$form) return false;

        DB::table('forms')->where('id', $id)->delete();
        DB::table('form_responses')->where('form_id', $id)->delete();

        Cache::forget("{$this->cachePrefix}{$form->slug}");

        return true;
    }

    public function renderForm(int $formId, array $options = []): string
    {
        $form = $this->getForm($formId);
        if (!$form || !$form->is_active) {
            return '<div class="alert alert-warning">فرم یافت نشد یا غیرفعال است.</div>';
        }

        $fields = json_decode($form->fields, true) ?? [];
        $settings = json_decode($form->settings, true) ?? [];

        $html = '<form ';
        $html .= 'action="' . route('form.submit', $form->slug) . '" ';
        $html .= 'method="POST" ';
        $html .= 'class="neuro-form ' . ($form->css_class ?? '') . '" ';
        $html .= 'id="form-' . $form->slug . '" ';

        if ($settings['ajax'] ?? false) {
            $html .= 'data-ajax="true" ';
        }

        if ($settings['multipart'] ?? false) {
            $html .= 'enctype="multipart/form-data" ';
        }

        $html .= '>';
        $html .= csrf_field();

        foreach ($fields as $field) {
            $html .= $this->renderField($field);
        }

        $html .= '<div class="form-group">';
        $html .= '<button type="submit" class="btn btn-primary">' . ($settings['submit_text'] ?? 'ارسال') . '</button>';
        $html .= '</div>';

        $html .= '</form>';

        return $html;
    }

    public function submitForm(int $formId, array $data): array
    {
        $form = $this->getForm($formId);
        if (!$form) {
            return ['success' => false, 'error' => 'Form not found'];
        }

        $fields = json_decode($form->fields, true) ?? [];
        $rules = $this->buildValidationRules($fields);

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        // Save response
        $responseId = DB::table('form_responses')->insertGetId([
            'form_id' => $formId,
            'data' => json_encode($data),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        // Send notification
        if ($form->email_notifications && $form->notification_email) {
            $this->sendNotification($form, $data);
        }

        return [
            'success' => true,
            'message' => $form->success_message,
            'response_id' => $responseId,
        ];
    }

    public function getResponses(int $formId, array $filters = []): array
    {
        $query = DB::table('form_responses')->where('form_id', $formId);

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function getFieldTypes(): array
    {
        return [
            'text' => 'متن کوتاه',
            'textarea' => 'متن بلند',
            'email' => 'ایمیل',
            'number' => 'عدد',
            'tel' => 'تلفن',
            'url' => 'آدرس وب',
            'password' => 'رمز عبور',
            'date' => 'تاریخ',
            'time' => 'زمان',
            'datetime' => 'تاریخ و زمان',
            'select' => 'لیست کشویی',
            'multiselect' => 'چند انتخابی',
            'radio' => 'دکمه رادیویی',
            'checkbox' => 'چک‌باکس',
            'file' => 'فایل',
            'image' => 'تصویر',
            'color' => 'رنگ',
            'range' => 'محدوده',
            'hidden' => 'مخفی',
            'html' => 'HTML',
            'divider' => 'جداکننده',
            'captcha' => 'کپچا',
            'rating' => 'امتیازدهی',
            'map' => 'نقشه',
            'signature' => 'امضا',
        ];
    }

    protected function renderField(array $field): string
    {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $required = ($field['required'] ?? false) ? 'required' : '';
        $placeholder = $field['placeholder'] ?? '';
        $class = $field['class'] ?? 'form-control';
        $value = $field['default'] ?? '';

        $html = '<div class="form-group">';

        if ($label && $type !== 'hidden') {
            $html .= '<label for="' . $name . '">' . $label;
            if ($required) {
                $html .= ' <span class="required">*</span>';
            }
            $html .= '</label>';
        }

        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . $name . '" id="' . $name . '" ';
                $html .= 'class="' . $class . '" ';
                $html .= 'placeholder="' . $placeholder . '" ';
                $html .= $required . '>' . $value . '</textarea>';
                break;

            case 'select':
                $html .= '<select name="' . $name . '" id="' . $name . '" ';
                $html .= 'class="' . $class . '" ' . $required . '>';
                $html .= '<option value="">انتخاب کنید...</option>';
                foreach ($field['options'] ?? [] as $option) {
                    $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
                }
                $html .= '</select>';
                break;

            case 'radio':
                foreach ($field['options'] ?? [] as $option) {
                    $html .= '<label class="radio-label">';
                    $html .= '<input type="radio" name="' . $name . '" value="' . $option['value'] . '" ' . $required . '> ';
                    $html .= $option['label'];
                    $html .= '</label>';
                }
                break;

            case 'checkbox':
                foreach ($field['options'] ?? [] as $option) {
                    $html .= '<label class="checkbox-label">';
                    $html .= '<input type="checkbox" name="' . $name . '[]" value="' . $option['value'] . '"> ';
                    $html .= $option['label'];
                    $html .= '</label>';
                }
                break;

            case 'file':
            case 'image':
                $html .= '<input type="file" name="' . $name . '" id="' . $name . '" ';
                $html .= 'class="' . $class . '" ' . $required . '>';
                break;

            default:
                $html .= '<input type="' . $type . '" name="' . $name . '" id="' . $name . '" ';
                $html .= 'class="' . $class . '" ';
                $html .= 'value="' . $value . '" ';
                $html .= 'placeholder="' . $placeholder . '" ';
                $html .= $required . '>';
        }

        if (!empty($field['help'])) {
            $html .= '<small class="form-help">' . $field['help'] . '</small>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function buildValidationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $name = $field['name'] ?? '';
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type'] ?? 'text') {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'tel':
                    $fieldRules[] = 'regex:/^[0-9+\-\s]+$/';
                    break;
                case 'file':
                case 'image':
                    $fieldRules[] = 'file';
                    if ($field['max_size'] ?? false) {
                        $fieldRules[] = 'max:' . ($field['max_size'] * 1024);
                    }
                    break;
            }

            if (!empty($field['min'])) {
                $fieldRules[] = 'min:' . $field['min'];
            }

            if (!empty($field['max'])) {
                $fieldRules[] = 'max:' . $field['max'];
            }

            if (!empty($field['pattern'])) {
                $fieldRules[] = 'regex:/' . $field['pattern'] . '/';
            }

            $rules[$name] = implode('|', $fieldRules);
        }

        return $rules;
    }

    protected function sendNotification($form, array $data): void
    {
        // In production, implement actual email sending
        // Mail::to($form->notification_email)->send(new FormSubmissionMail($form, $data));
    }
}
