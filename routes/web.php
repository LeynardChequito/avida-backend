<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{filename}', function ($filename) {
    $path = storage_path("app/public/{$filename}");

    if (!file_exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*', // Allow frontend access
        'Content-Type' => mime_content_type($path),
    ]);
})->where('filename', '.*');

