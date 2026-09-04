<?php

namespace App\Modules\TemplateManager\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    protected $templateService;

    public function __construct()
    {
        $this->templateService = app('template.service');
    }

    public function index()
    {
        return view('TemplateManager::admin.index', [
            'templates' => $this->templateService->getAllTemplates(),
        ]);
    }

    public function create()
    {
        return view('TemplateManager::admin.create');
    }

    public function store(Request $request)
    {
        $id = $this->templateService->createTemplate($request->all());
        return redirect()->route('admin.template.index')->with('status', 'Template created');
    }

    public function aiBuilder()
    {
        return view('TemplateManager::admin.ai-builder');
    }

    public function positions()
    {
        return view('TemplateManager::admin.positions');
    }

    public function settings()
    {
        return view('TemplateManager::admin.settings');
    }
}
