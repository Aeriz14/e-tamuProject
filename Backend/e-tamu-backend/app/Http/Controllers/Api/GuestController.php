<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment; // Diubah dari Guest ke Appointment
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * Koki untuk Tamu Walk-in (GuestController)
 * Tugasnya adalah menangani tamu yang mendaftar langsung di tempat.
 * PERUBAHAN: Sekarang koki ini akan mencatat tamu ke dalam "buku pesanan" janji temu (appointments),
 * bukan lagi ke buku tamu lama (guests), agar semua data terpusat.
 */
class GuestController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:appointments,email', // Validasi ke tabel appointments
            'phone_number' => 'required|string|max:20',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string',
            'face_photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'division_id' => 'required|exists:divisions,id', // Menambahkan validasi divisi
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Simpan foto di folder yang sama dengan janji temu agar konsisten
        $path = $request->file('face_photo')->store('face_photos', 'public');

        // Buat data di tabel Appointment, bukan Guest
        $appointment = Appointment::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'institution' => $request->institution,
            'purpose' => $request->purpose,
            'face_photo_path' => $path,
            'division_id' => $request->division_id,
            'appointment_date' => now()->toDateString(), // Tanggal hari ini
            'appointment_time' => now()->toTimeString(), // Waktu saat ini
            'status_pertemuan' => 'selesai', // Status langsung 'selesai' karena walk-in
        ]);

        return response()->json([
            'message' => 'Pendaftaran tamu berhasil! Kunjungan Anda telah dicatat.',
            'data' => $appointment
        ], 201);
    }

    // Fungsi-fungsi di bawah ini (index, show, update, destroy) masih mengacu pada model Guest lama.
    // Dapat dipertimbangkan untuk dihapus jika sudah tidak digunakan sama sekali.
    public function index()
    {
        $guests = \App\Models\Guest::all();
        return response()->json(['data' => $guests], 200);
    }

    public function show(string $id)
    {
        $guest = \App\Models\Guest::findOrFail($id);
        return response()->json(['data' => $guest], 200);
    }

    public function update(\Illuminate\Http\Request $request, string $id)
    {
        $guest = \App\Models\Guest::findOrFail($id);
        $guest->update($request->all());
        return response()->json(['data' => $guest], 200);
    }

    public function destroy(string $id)
    {
        $guest = \App\Models\Guest::findOrFail($id);
        $guest->delete();
        return response()->json(['message' => 'Data tamu lama berhasil dihapus'], 200);
    }
}
