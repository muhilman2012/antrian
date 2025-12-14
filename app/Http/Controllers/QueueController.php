<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * 1. HALAMAN REGISTRASI
     * Menampilkan form pendaftaran. Nomor antrian diambil real-time via API/Javascript,
     * jadi di sini kita hanya me-return view saja.
     */
    public function index()
    {
        return view('registration');
    }

    /**
     * API: Mengambil nomor antrian berikutnya (Dipanggil oleh Javascript di halaman Registrasi)
     * URL: /api/next-number/{category}
     */
    public function getNextNumber($category)
    {
        // Hitung total antrian di kategori tersebut
        $count = Queue::where('category', $category)->count();
        $next = $count + 1;

        // Tentukan Prefix: Rontgen = A, HPV = B
        // Jika nanti ada kategori lain, tinggal tambah logika di sini
        $prefix = ($category == 'hpv') ? 'B' : 'A';

        // Format angka menjadi 3 digit (contoh: 1 jadi "001")
        $formatted = $prefix . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        return response()->json(['number' => $formatted]);
    }

    /**
     * PROSES SIMPAN ANTRIAN
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'category' => 'required|in:rontgen,hpv', // Pastikan kategori valid
            'name' => 'required',
        ]);

        // --- GENERATE NOMOR DI BACKEND ---
        // Kita hitung ulang di sini agar aman dari duplikasi jika ada user daftar bersamaan
        $category = $request->category;
        
        // Hitung urutan ke berapa
        $count = Queue::where('category', $category)->count() + 1;
        
        // Tentukan Prefix
        $prefix = ($category == 'hpv') ? 'B' : 'A';
        
        // Susun Nomor Akhir (Misal: A-005)
        $number = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Simpan ke Database
        Queue::create([
            'category' => $category,
            'number' => $number,
            'name' => $request->name,
            'details' => $request->details,
            'status' => 'waiting' // Status awal selalu waiting
        ]);

        return back()->with('success', "Berhasil! Nomor Antrian Anda: $number");
    }

    /**
     * 2. HALAMAN ADMIN (DASHBOARD)
     * Menampilkan antrian Rontgen, HPV, dan Riwayat secara terpisah.
     */
    public function admin(Request $request)
    {
        // Ambil Data Rontgen (Hanya yang status Waiting & Called)
        $rontgen = Queue::where('category', 'rontgen')
                        ->whereIn('status', ['waiting', 'called'])
                        // Urutkan: Called di atas, Waiting di bawah
                        ->orderByRaw("FIELD(status, 'called', 'waiting')") 
                        ->orderBy('created_at', 'asc') // Yang daftar duluan ada di atas
                        ->get();

        // Ambil Data HPV (Hanya yang status Waiting & Called)
        $hpv = Queue::where('category', 'hpv')
                        ->whereIn('status', ['waiting', 'called'])
                        ->orderByRaw("FIELD(status, 'called', 'waiting')")
                        ->orderBy('created_at', 'asc')
                        ->get();

        // Ambil Riwayat (Status Completed) - Limit 5 terakhir
        $history = Queue::where('status', 'completed')
                        ->latest('updated_at') // Urutkan berdasarkan waktu selesai
                        ->take(20)
                        ->get();

        // LOGIKA REFRESH TABEL (AJAX)
        // Jika Javascript meminta update data, kembalikan hanya potongan HTML tabel (admin_rows)
        if ($request->has('refresh_table')) {
            return view('admin_rows', compact('rontgen', 'hpv', 'history'));
        }

        // Jika akses biasa, kembalikan halaman admin utuh
        return view('admin', compact('rontgen', 'hpv', 'history'));
    }

    /**
     * AKSI: PANGGIL ANTRIAN
     */
    public function call($id)
    {
        $queue = Queue::findOrFail($id);
        
        // Kita update timestamp-nya juga agar dia naik menjadi "terbaru" di layar TV
        $queue->status = 'called';
        $queue->touch(); // Update updated_at
        $queue->save();

        return back();
    }

    /**
     * AKSI: SELESAIKAN ANTRIAN (Tanpa memanggil yang baru)
     */
    public function complete($id)
    {
        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'completed']);

        return back();
    }
    
    /**
     * 3. HALAMAN DISPLAY (LAYAR TV)
     */
    public function display()
    {
        // Logic display tidak berubah, dia hanya menampilkan status 'called'
        // View akan otomatis menampilkan nomor (misal A-001 atau B-002)
        return view('display');
    }

    /**
     * API: Cek Antrian Saat Ini (Dipanggil oleh Javascript di Layar TV untuk Auto-Refresh)
     */
    public function getCurrentQueue()
    {
        // Ambil data antrian yang sedang dipanggil
        $current = Queue::where('status', 'called')->latest('updated_at')->first();

        // Ambil history
        $history = Queue::where('status', 'completed')
                        ->latest('updated_at')
                        ->take(10)
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $current ? [
                'id' => $current->id,
                'number' => $current->number,
                'category' => $current->category,
                'name' => $current->name,
                'details' => $current->details,
                // TAMBAHKAN BARIS INI (Kirim Waktu Update ke JSON)
                'updated_at' => $current->updated_at->toISOString(), 
            ] : null,
            'history' => $history 
        ]);
    }
}