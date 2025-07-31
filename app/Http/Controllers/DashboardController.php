<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Pastikan DB facade di-import
use Carbon\Carbon; // Import Carbon untuk manipulasi tanggal

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dengan ringkasan data aset.
     */
    public function index()
    {
        // Menghitung total semua aset
        $totalAssets = Asset::count();

        // Menghitung aset dengan kondisi 'Baik'
        $assetsBaik = Asset::where('kondisi', 'Baik')->count();

        // Menghitung aset dengan kondisi 'Rusak'
        $assetsRusak = Asset::where('kondisi', 'Rusak')->count();

        // --- LOGIKA UNTUK DATA GRAFIK ---
        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];

        // Loop untuk mendapatkan data 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            // Setel Carbon ke awal bulan untuk setiap iterasi
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            
            // Tambahkan nama bulan ke array labels (e.g., "Juli")
            // 'translatedFormat' akan menerjemahkan nama bulan ke Bahasa Indonesia
            $labels[] = $month->translatedFormat('F');

            // Query data aset masuk (dibuat pada bulan dan tahun terkait)
            $dataMasuk[] = Asset::whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)
                                ->count();

            // Query data aset keluar
            // Asumsi: 'keluar' berarti aset di-soft-delete (memiliki nilai di kolom 'deleted_at').
            // Sesuaikan query ini jika logika 'keluar' Anda berbeda (misal: berdasarkan status 'dijual' atau 'dihapus').
            // Pastikan model Asset Anda menggunakan trait SoftDeletes.
            $dataKeluar[] = Asset::onlyTrashed() // Hanya ambil yang di-soft delete
                                 ->whereYear('deleted_at', $month->year)
                                 ->whereMonth('deleted_at', $month->month)
                                 ->count();
        }
        // --- AKHIR LOGIKA GRAFIK ---

        // Mengirim semua data yang diperlukan ke view
        return view('Dashboard.index', compact(
            'totalAssets',
            'assetsBaik',
            'assetsRusak',
            'labels',
            'dataMasuk',
            'dataKeluar'
        ));
    }
}
