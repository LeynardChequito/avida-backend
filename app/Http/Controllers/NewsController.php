<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    // ✅ Require authentication except for fetching news
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']); // ✅ Allow public access to index & show
    }
    
    public function index()
    {
        $news = News::orderBy('created_at', 'desc')->get();
    
        $news->transform(function ($item) {
            // ✅ Ensure `images` is always an array
            $imagePaths = is_string($item->images) ? json_decode($item->images, true) : $item->images;
            if (!is_array($imagePaths)) {
                $imagePaths = [];
            }
    
            // ✅ Dynamically determine storage path based on category
            $categoryFolder = strtolower($item->category); // Example: 'news', 'blog', 'announcement'
            
            $item->images = array_map(fn($img) => asset("storage/{$categoryFolder}/" . basename($img)), $imagePaths);
    
            return $item;
        });
    
        return response()->json($news, 200);
    }
    
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'category' => 'required|string',
        'content' => 'required|string',
        'status' => 'required|in:draft,published,unpublished',
        'images' => 'nullable|array',
        'images.*' => 'required|file|image|mimes:jpg,jpeg,png|max:5120',
    ]);
    
    if ($validator->fails()) {
        \Log::error('Validation failed:', $validator->errors()->toArray());
        return response()->json(['errors' => $validator->errors()], 422);
    }
    
    $categoryFolder = 'public/' . strtolower($request->category);
    if (!Storage::exists($categoryFolder)) {
        Storage::makeDirectory($categoryFolder);
    }

    $imagePaths = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs($categoryFolder, $imageName);
            $imagePaths[] = str_replace('public/', '', $path); // store relative path
        }
    }

    $news = News::create([
        'title' => $request->title,
        'category' => $request->category,
        'content' => $request->content,
        'status' => $request->status,
        'images' => json_encode($imagePaths), // store as JSON
    ]);

    return response()->json(['message' => 'News created', 'news' => $news], 201);
}

    public function update(Request $request, $id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['error' => 'News not found'], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'content' => 'required|string',
            'status' => 'required|in:draft,published,unpublished',
            'images' => 'nullable|array',
            'images.*' => 'required|file|image|mimes:jpg,jpeg,png|max:5120', // ⬅️ increase size to 5MB
        ]);
        
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // ✅ Ensure new category folder exists
        $newCategoryFolder = 'public/' . strtolower($request->category ?? $news->category);
        if (!Storage::exists($newCategoryFolder)) {
            Storage::makeDirectory($newCategoryFolder);
        }
    
        // ✅ Handle Multiple Image Uploads
        $imagePaths = json_decode($news->images, true) ?? [];
    
        if ($request->hasFile('images')) {
            // ✅ Delete old images before saving new ones
            foreach ($imagePaths as $oldImage) {
                Storage::delete('public/' . $oldImage);
            }
            $imagePaths = [];
    
            // ✅ Save new images
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs($newCategoryFolder, $imageName);
                $imagePaths[] = str_replace("public/", "", $imagePath);
            }
        }
    
        // ✅ Update news data
        $news->fill($request->only(['title', 'category', 'content', 'status']));
        $news->images = json_encode($imagePaths);
        $news->updated_at = now();
        $news->save();
    
        return response()->json(['message' => 'News updated successfully', 'news' => $news], 200);
    }
    public function show($id)
    {
        $news = News::find($id);
    
        if (!$news) {
            return response()->json(['error' => 'News not found'], 404);
        }
    
        // Decode image paths and convert them to full URLs
        $imagePaths = json_decode($news->images, true) ?? [];
        $fullImageUrls = array_map(fn($image) => asset("storage/" . $image), $imagePaths);
    
        return response()->json([
            'id' => $news->id,
            'title' => $news->title,
            'category' => $news->category,
            'content' => $news->content,
            'status' => $news->status,
            'images' => $fullImageUrls, // ✅ Now returns full URLs
            'created_at' => $news->created_at,
            'updated_at' => $news->updated_at
        ], 200);
    }
    
    // ✅ Delete news
    public function destroy($id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['error' => 'News not found'], 404);
        }

        // ✅ Delete image file if exists
        if ($news->image) {
            Storage::disk('public')->delete(str_replace(asset('storage/'), '', $news->image));
        }

        $news->delete();

        return response()->json(['message' => 'News deleted successfully'], 200);
    }
}
