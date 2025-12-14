@extends('layouts.app')

@section('content')
<div class="container-tight py-4">
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Registrasi CKG SETWAPRES</h2>
            
            <form action="{{ route('queue.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label">Pilih Layanan</label>
                    <select name="category" id="categorySelect" class="form-select">
                        <option value="rontgen">Rontgen (MCU)</option>
                        <option value="hpv">Test HPV</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Estimasi Nomor Antrian</label>
                    <input type="text" id="numberDisplay" name="number_display" class="form-control bg-muted-lt font-weight-bold text-center" style="font-size: 1.5rem;" readonly>
                    <small class="form-hint">Nomor pasti akan dikonfirmasi setelah klik Daftar.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Peserta</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Detail / Keluhan (Opsional)</label>
                    <textarea name="details" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">Daftar Antrian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. Script Notifikasi Sukses
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 3000
        });
    @endif

    // 2. Script Ganti Nomor Otomatis saat Ganti Kategori
    const categorySelect = document.getElementById('categorySelect');
    const numberDisplay = document.getElementById('numberDisplay');

    function fetchNextNumber() {
        const cat = categorySelect.value;
        // Panggil API Laravel
        fetch(`/api/next-number/${cat}`)
            .then(response => response.json())
            .then(data => {
                numberDisplay.value = data.number;
            });
    }

    // Jalankan saat pertama kali load
    fetchNextNumber();

    // Jalankan saat dropdown berubah
    categorySelect.addEventListener('change', fetchNextNumber);
</script>
@endsection