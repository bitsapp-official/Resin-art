<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProcessPage;

class ProcessController extends Controller
{
    public function index()
    {
        $processPage = ProcessPage::getPublishedPage();

        return view('process.index', [
            'processPage' => $processPage,
            'steps' => $processPage ? $processPage->activeSteps : collect(),
        ]);
    }
}
