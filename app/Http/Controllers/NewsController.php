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
        $this->middleware('auth:api')->except(['index']);
    }

    // ✅ Fetch all news
    public function index()
    {
        $news = News::orderBy('created_at', 'desc')->get();

        // Convert image paths to full URLs
        $news->transform(function ($item) {
            if ($item->image) {
                $item->image = asset("storage/" . str_replace("public/", "", $item->image));
            }
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
            'images' => 'nullable|array', // ✅ Allow multiple images
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048', // ✅ Validate each image
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // ✅ Ensure category folder exists
        $categoryFolder = 'public/' . strtolower($request->category);
        if (!Storage::exists($categoryFolder)) {
            Storage::makeDirectory($categoryFolder);
        }
    
        // ✅ Handle Multiple Image Uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs($categoryFolder, $imageName);
                $imagePaths[] = str_replace("public/", "", $imagePath); // ✅ Store relative path
            }
        }
    
        $news = News::create([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'status' => $request->status,
            'images' => json_encode($imagePaths), // ✅ Store as JSON
        ]);
    
        return response()->json(['message' => 'News created successfully', 'news' => $news], 201);
    }
    public function update(Request $request, $id)
    {
        $news = News::find($id);
        if (!$news) {
            return response()->json(['error' => 'News not found'], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'category' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:draft,published,unpublished',
            'images' => 'nullable|array', // ✅ Allow multiple images
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048', // ✅ Validate each image
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
