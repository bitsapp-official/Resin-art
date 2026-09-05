<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\AboutPage;

class AboutController extends Controller
{
    public function index()
    {
        $aboutPage = AboutPage::getPublished();

        return view('about.index', [
            'aboutPage' => $aboutPage,
        ]);
    }
}
