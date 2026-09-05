<?php

namespace App\Http\Controllers;

use App\Models\PolicyPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    public function show(string $slug)
    {
        $page = PolicyPage::findBySlug($slug);

        if (! $page) {
            abort(404);
        }

        return view('legal.policy', compact('page'));
    }
}
