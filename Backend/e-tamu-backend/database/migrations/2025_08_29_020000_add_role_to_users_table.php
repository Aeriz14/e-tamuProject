<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * File Migrasi Baru
 * Menambahkan kolom 'role' ke dalam tabel 'users'.
 * Ini adalah perbaikan kritis karena seeder membutuhkan kolom ini untuk membuat akun.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom 'role' setelah kolom 'password'
            // Nilai defaultnya adalah 'admin'
            $table->string('role')->after('password')->default('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom jika migrasi di-rollback
            $table->dropColumn('role');
        });
    }
};
