<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File Migrasi Baru
 * Menambahkan kolom 'phone_number' ke tabel 'appointments' untuk konsistensi data.
 * Ini adalah perbaikan bug kritis karena nomor telepon digunakan di frontend tetapi tidak ada di database.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Menambahkan kolom nomor telepon setelah 'email' agar data lebih terstruktur.
            // Kolom ini bisa null untuk mengakomodasi data lama jika ada.
            $table->string('phone_number')->after('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback.
            $table->dropColumn('phone_number');
        });
    }
};
