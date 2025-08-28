<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // dalam fungsi up()
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');     // Untuk "Nama Lengkap" [cite: 116]
            $table->string('email')->unique(); // Untuk "Alamat Email" [cite: 118]
            $table->string('institution');  // Untuk "Asal Instansi" [cite: 120]
            $table->text('purpose');        // Untuk "Tujuan Kunjungan" [cite: 122]
            $table->string('face_photo_path'); // Untuk menyimpan path file "Foto Wajah" [cite: 124]
            $table->timestamp('check_in_time')->nullable(); // Waktu check-in [cite: 37]
            $table->timestamp('check_out_time')->nullable(); // Waktu check-out [cite: 48]
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
