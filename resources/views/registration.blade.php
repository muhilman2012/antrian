@extends('layouts.app')

@section('content')
<div class="container-tight py-4">
    <div class="card card-md">
        <div class="card-body">
            <h2 class="h2 text-center mb-4">Registrasi CKG SETWAPRES</h2>
            
            <form action="{{ route('queue.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nomor Antrian</label>
                    <input type="text" name="number" class="form-control bg-muted-lt font-weight-bold text-center" style="font-size: 1.5rem;" value="{{ $formatNumber }}" readonly>
                    <small class="form-hint">Nomor antrian dibuat otomatis.</small>
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
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session("success") }}',
            showConfirmButton: false,
            timer: 2000 
        });
    @endif
</script>
@endsection