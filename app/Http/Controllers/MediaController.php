<?php

namespace App\Http\Controllers;

use App\Models\AdImage;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(AdImage $image)
    {
        $path = $image->path;
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        // Stream the file inline with correct MIME type
        $fullPath = storage_path('app/public/'.$path);
        return response()->file($fullPath);
    }
}

