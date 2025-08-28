<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory; // <-- Cukup satu

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Nama tabel yang terhubung dengan model ini.
     * @var string
     */
    protected $table = 'login_logs';

    /**
     * Kolom-kolom yang dapat diisi secara massal (mass assignable).
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'username',
        'login_time',
    ];

    /**
     * Menonaktifkan timestamps untuk model ini karena
     * tabel login_logs tidak memiliki kolom created_at dan updated_at.
     * @var bool
     */
    public $timestamps = false;
}
