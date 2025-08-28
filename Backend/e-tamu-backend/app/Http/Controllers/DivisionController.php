<?php
namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::all();
        // --- PERBAIKAN DI SINI ---
        // Hapus pembungkus ['data' => ...] agar API mengembalikan array langsung.
        return response()->json($divisions, 200);
    }
}
