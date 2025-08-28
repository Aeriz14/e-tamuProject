<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CheckInController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $image = $request->file('image');

        // Kirim gambar ke servis Python
        $response = Http::attach(
            'face_image', file_get_contents($image), $image->getClientOriginalName()
        )->post('http://127.0.0.1:5001/verify');

        // Kembalikan respons dari servis Python ke frontend
        return $response->json();
    }
}
