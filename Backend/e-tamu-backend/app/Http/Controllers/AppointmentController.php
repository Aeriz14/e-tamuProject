<?php
namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Koki untuk Janji Temu (AppointmentController)
 * Bertanggung jawab penuh atas semua logika yang berkaitan dengan janji temu.
 */
class AppointmentController extends Controller
{
    /**
     * Menghapus janji temu.
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }
        // Hapus juga file foto jika ada
        if ($appointment->face_photo_path) {
            Storage::disk('public')->delete($appointment->face_photo_path);
        }
        $appointment->delete();
        return response()->json(['message' => 'Janji temu berhasil dihapus']);
    }

    /**
     * Memberikan daftar janji temu sesuai peran staf.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'super_admin') {
            // Super Admin dapat melihat semua janji temu
            $appointments = Appointment::with('division')->latest()->get();
        } else {
            // Admin biasa hanya melihat janji temu untuk divisinya
            $appointments = Appointment::with('division')
                ->where('division_id', $user->division_id)
                ->latest()
                ->get();
        }
        return response()->json($appointments);
    }

    /**
     * Memberikan detail satu janji temu spesifik.
     */
    public function show($id)
    {
        $appointment = Appointment::with('division')->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        return response()->json($appointment);
    }

    /**
     * Menyimpan janji temu baru.
     */
    public function store(Request $request)
    {
        // PERBAIKAN: Menambahkan validasi untuk email dan phone_number
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:appointments,email',
            'phone_number' => 'required|string|max:20',
            'institution' => 'required|string|max:255',
            'purpose' => 'required|string',
            'face_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'division_id' => 'required_without:custom_division|exists:divisions,id',
            'custom_division' => 'required_without:division_id|string|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required', // Dihapus format H:i agar lebih fleksibel
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $divisionId = $request->division_id;
        if ($request->custom_division) {
            $division = Division::firstOrCreate(['name' => $request->custom_division]);
            $divisionId = $division->id;
        }

        $facePhotoPath = null;
        if ($request->hasFile('face_photo')) {
            $facePhotoPath = $request->file('face_photo')->store('face_photos', 'public');
        }

        // PERBAIKAN: Menambahkan email dan phone_number saat membuat data
        $appointment = Appointment::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'institution' => $request->institution,
            'purpose' => $request->purpose,
            'face_photo_path' => $facePhotoPath,
            'division_id' => $divisionId,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status_pertemuan' => 'pending',
        ]);

        return response()->json(['message' => 'Janji temu berhasil dibuat, silakan tunggu konfirmasi.', 'data' => $appointment], 201);
    }

    /**
     * Memperbarui status janji temu.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'status_pertemuan' => 'required|in:pending,selesai,ditolak',
        ]);

        $appointment->status_pertemuan = $validatedData['status_pertemuan'];
        $appointment->save();

        return response()->json($appointment);
    }
}
