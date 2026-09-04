<?php

namespace App\Modules\TemplateManager\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Modules\TemplateManager\Services\TemplateMakerService;

class TemplateMakerController extends Controller
{
    protected TemplateMakerService $maker;

    public function __construct()
    {
        $this->maker = app('template.maker');
        $this->middleware('can:template.read');
    }

    public function index()
    {
        $templates = DB::table('templates')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('TemplateManager::admin.maker.index', compact('templates'));
    }

