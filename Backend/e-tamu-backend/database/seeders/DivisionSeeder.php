<?php
namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    /**
     * Menjalankan seeder untuk membuat data divisi sesuai screenshot.
     */
    public function run(): void
    {
        // Mengosongkan tabel terlebih dahulu untuk menghindari duplikasi
        DB::table('divisions')->delete();

        // Membuat data divisi yang baru
        Division::create(['name' => 'Sekretariat']);
        Division::create(['name' => 'Statistik']);
        Division::create(['name' => 'IKP']);
        Division::create(['name' => 'Sandikami']);
        Division::create(['name' => 'Aptika']);
        Division::create(['name' => 'UPT Persandian']);
    }
}
