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

namespace App\Modules\FormCreator\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FormController extends Controller
{
    protected $formService;

    public function __construct()
    {
        $this->formService = app('form.service');
    }

    public function index()
    {
        return view('FormCreator::admin.index', [
            'forms' => $this->formService->getAllForms(),
        ]);
    }

    public function create()
    {
        return view('FormCreator::admin.create', [
            'fieldTypes' => $this->formService->getFieldTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $id = $this->formService->createForm($request->all());
        return redirect()->route('admin.form.index')->with('status', 'Form created');
    }

    public function aiBuilder()
    {
        return view('FormCreator::admin.ai-builder');
    }

    public function responses()
    {
        return view('FormCreator::admin.responses');
    }
}
