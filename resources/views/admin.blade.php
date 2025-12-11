@extends('layouts.app')

@section('content')
<div class="container-xl py-4">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manajemen Antrian (Real-time)</h3>
                    <div class="card-actions">
                         <a href="{{ route('display') }}" target="_blank" class="btn btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-tv" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="3" y="7" width="18" height="13" rx="2" /><polyline points="16 3 12 7 8 3" /></svg>
                            Buka Layar TV
                         </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Detail</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        {{-- ID 'queue-table-body' ini penting untuk target update JS --}}
                        <tbody id="queue-table-body">
                            {{-- Load data awal menggunakan include --}}
                            @include('admin_rows')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi Auto Refresh Tabel
    setInterval(function() {
        // Panggil URL admin dengan parameter ?refresh_table=1
        fetch("{{ route('admin') }}?refresh_table=1")
            .then(response => response.text())
            .then(html => {
                // Ganti isi tbody dengan HTML baru yang didapat
                document.getElementById('queue-table-body').innerHTML = html;
            })
            .catch(error => console.error('Gagal merefresh tabel:', error));
    }, 5000); // Update setiap 5 detik
</script>
@endsection