<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateMediaController extends Controller
{
    /**
     * Stream private media files securely.
     */
    public function show(Request $request, string $path): StreamedResponse|\Illuminate\Http\Response
    {
        // Add basic authorization here (e.g. check if user is admin or the owner)
        // For phase 6, since we don't have full customer login enforced everywhere yet,
        // we'll restrict to authenticated users (admins) or signed URLs.
        
        // Simple security: for now require auth (admins in filament are authenticated)
        if (!auth()->check()) {
            abort(403, 'Unauthorized access to private media.');
        }

        $fullPath = 'custom-requests/' . $path;

        if (!Storage::disk('private')->exists($fullPath)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->response($fullPath);
    }
}
