<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seeder utama untuk mengisi database.
     */
    public function run(): void
    {
        // Mengosongkan tabel user terlebih dahulu
        DB::table('users')->delete();

        // 1. Panggil seeder divisi untuk memastikan semua divisi terbuat
        $this->call(DivisionSeeder::class);

        // 2. Buat akun Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'division_id' => null, // Super admin tidak terikat pada divisi manapun
        ]);

        // 3. Ambil semua divisi yang ada
        $divisions = Division::all();

        // 4. Buat satu akun admin untuk setiap divisi
        foreach ($divisions as $division) {
            // Membuat format email yang mudah diingat, cth: aptika@example.com
            $email = strtolower(str_replace(' ', '', $division->name)) . '@example.com';

            User::create([
                'name' => 'Admin ' . $division->name,
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'admin', // Peran sebagai admin divisi
                'division_id' => $division->id, // Mengaitkan user dengan divisinya
            ]);
        }
    }
}
