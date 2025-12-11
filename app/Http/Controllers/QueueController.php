<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    // 1. Halaman Registrasi
    public function index()
    {
        // Ambil antrian terakhir berdasarkan ID
        $lastQueue = Queue::latest('id')->first();

        // Jika ada data terakhir, ambil nomornya, ubah jadi integer, tambah 1
        // Jika tidak ada (kosong), mulai dari 1
        if ($lastQueue) {
            // Asumsi format di database murni angka string "001", "002", dst.
            $nextNumber = (int) $lastQueue->number + 1;
        } else {
            $nextNumber = 1;
        }

        // Format angka menjadi 3 digit (contoh: 1 jadi "001")
        $formatNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Kirim variabel $formatNumber ke view
        return view('registration', compact('formatNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'name' => 'required',
        ]);

        Queue::create($request->all());

        return back()->with('success', 'Antrian berhasil didaftarkan!');
    }

    // 2. Halaman Admin (Pemanggil)
    public function admin(Request $request)
    {
        $queues = Queue::orderByRaw("FIELD(status, 'called', 'waiting', 'completed')")
                        ->orderBy('created_at', 'desc')
                        ->get();

        // JIKA request ini adalah permintaan update tabel dari Javascript (AJAX)
        // Maka kembalikan potongan tabelnya saja (admin_rows), bukan seluruh halaman.
        if ($request->has('refresh_table')) {
            return view('admin_rows', compact('queues'));
        }

        // Jika akses biasa, kembalikan halaman admin utuh
        return view('admin', compact('queues'));
    }

    public function call($id)
    {
        // Set antrian yang sedang dipanggil sebelumnya menjadi 'completed'
        Queue::where('status', 'called')->update(['status' => 'completed']);

        // Panggil antrian yang dipilih
        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'called']);

        return back();
    }

    // --- TAMBAHAN METHOD BARU ---
    public function complete($id)
    {
        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'completed']);

        return back();
    }
    
    // 3. Halaman Display (Layar TV/Monitor)
    public function display()
    {
        // Ambil antrian yang sedang dipanggil
        $current = Queue::where('status', 'called')->latest('updated_at')->first();
        return view('display', compact('current'));
    }

    public function getCurrentQueue()
    {
        // Ambil antrian yang statusnya 'called'
        $current = Queue::where('status', 'called')->latest('updated_at')->first();

        // Jika ada, kirim datanya. Jika tidak, kirim null
        return response()->json([
            'status' => 'success',
            'data' => $current ? [
                'id' => $current->id,
                'number' => $current->number,
                'name' => $current->name,
                'details' => $current->details
            ] : null
        ]);
    }
}