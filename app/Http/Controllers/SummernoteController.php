<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SummernoteController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        // simpan ke storage/app/public/summernote
        $path = $request->file('image')->store('summernote', 'public');

        // return URL langsung (bukan JSON)
        return asset('storage/' . $path);
    }

    public function delete(Request $request)
    {
        $src = $request->src;

        // ambil path setelah /storage/
        $path = str_replace(asset('storage').'/', '', $src);

        Storage::disk('public')->delete($path);

        return response()->json(['success' => true]);
    }
}
