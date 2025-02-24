<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoomPlannerController extends Controller
{
    // Export layout as image
    public function exportLayout(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 encoded image
        ]);

        $imageData = $request->input('image');
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $imageName = 'layout-' . time() . '.png';

        $filePath = public_path('exports/' . $imageName);
        file_put_contents($filePath, base64_decode($image));

        return response()->json(['status' => 'success', 'image_url' => url('exports/' . $imageName)]);
    }

    // Frontend API integration example (using Axios)
    // axios.post('/api/export-layout', {
    //   image: yourBase64ImageData
    // }).then(response => console.log(response.data.image_url));
}
