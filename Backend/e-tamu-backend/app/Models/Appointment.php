<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone_number', // <-- PERBAIKAN: Tambahkan 'phone_number' di sini
        'institution',
        'purpose',
        'face_photo_path',
        'division_id',
        'appointment_date',
        'appointment_time',
        'status_pertemuan'
    ];

    /**
     * Mendefinisikan relasi ke model Division.
     * Setiap janji temu (Appointment) dimiliki oleh satu Divisi.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
