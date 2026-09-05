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

namespace App\Modules\LanguageManager\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    protected $languageService;

    public function __construct()
    {
        $this->languageService = app('language.manager');
    }

    public function index()
    {
        return view('LanguageManager::admin.index', [
            'languages' => $this->languageService->getActiveLanguages(),
        ]);
    }

    public function create()
    {
        return view('LanguageManager::admin.create');
    }

    public function store(Request $request)
    {
        $id = $this->languageService->addLanguage($request->all());
        return redirect()->route('admin.language.index')->with('status', 'Language added');
    }

    public function translations()
    {
        return view('LanguageManager::admin.translations');
    }
}
