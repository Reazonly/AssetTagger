<?php

/**
 * File Konfigurasi untuk Master Data Logis Aplikasi Aset.
 *
 * File ini berfungsi sebagai pusat untuk data yang bersifat master
 * namun tidak disimpan dalam tabel database tersendiri, seperti
 * daftar sub-kategori untuk setiap kategori aset.
 *
 * Cara Menggunakan:
 * - Untuk mengambil semua sub-kategori Elektronik: config('asset_data.sub_categories.ELEC')
 * - Untuk mengambil semua kondisi aset: config('asset_data.conditions')
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Sub-Kategori Aset
    |--------------------------------------------------------------------------
    |
    | Daftar ini mengelompokkan jenis barang (sub-kategori) berdasarkan
    | kode kategori utamanya (ELEC, VEHI, FURN, OFFI). Ini digunakan
    | untuk mengisi dropdown dinamis pada form pembuatan dan edit aset.
    |
    */
    'sub_categories' => [

        // Kategori: Elektronik (ELEC)
        'ELEC' => [
            'Laptop',
            'Desktop/PC',
            'Monitor',
            'Printer',
            'Proyektor',
            'Scanner',
            'UPS (Uninterruptible Power Supply)',
            'Speaker Aktif',
            'Peralatan Jaringan (Router, Switch)',
            'Lainnya (Elektronik)',
        ],

        // Kategori: Kendaraan (VEHI)
        'VEHI' => [
            'Mobil Penumpang',
            'Truk/Pick-up',
            'Motor',
            'Alat Berat (Excavator, Dozer)',
            'Bus',
            'Lainnya (Kendaraan)',
        ],

        // Kategori: Furniture (FURN)
        'FURN' => [
            'Meja (Kerja, Rapat)',
            'Kursi (Kerja, Tamu)',
            'Lemari/Kabinet Arsip',
            'Rak Penyimpanan',
            'Sofa',
            'Partisi Ruangan',
            'Lainnya (Furniture)',
        ],

        // Kategori: Peralatan Kantor (OFFI)
        'OFFI' => [
            'Mesin Penghancur Kertas',
            'Telepon Kantor',
            'Papan Tulis (Whiteboard)',
            'Dispenser Air',
            'Brankas',
            'Mesin Absensi',
            'Lainnya (Peralatan Kantor)',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kondisi Aset
    |--------------------------------------------------------------------------
    |
    | Daftar standar untuk pilihan kondisi aset di seluruh aplikasi.
    |
    */
    'conditions' => [
        'BAIK',
        'RUSAK',
        'DALAM PERBAIKAN',
        'PENGHAPUSAN',
    ],

];