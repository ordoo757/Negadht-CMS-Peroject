<?php

namespace App\Modules\AiKernel\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $ai = app('ai.service');
        return view('AiKernel::admin.dashboard', [
            'systemStatus' => $ai->monitorSystem(),
        ]);
    }

    public function security()
    {
        $security = app('ai.security');
        return view('AiKernel::admin.security', [
            'threats' => $security->scanForThreats(),
        ]);
    }

    public function learning()
    {
        return view('AiKernel::admin.learning');
    }

    public function settings()
    {
        return view('AiKernel::admin.settings');
    }
}
