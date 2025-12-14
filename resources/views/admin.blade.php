@extends('layouts.app')

@section('content')
<div class="container-xl py-4">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                        <li class="nav-item">
                            <a href="#tabs-rontgen" id="link-rontgen" class="nav-link active" data-bs-toggle="tab">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-activity-heartbeat" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12h4.5l1.5 -6l4 12l2 -9l1.5 5h4.5" /></svg>
                                Antrian Rontgen
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tabs-hpv" id="link-hpv" class="nav-link" data-bs-toggle="tab">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-virus" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-5 0a5 5 0 1 0 10 0a5 5 0 1 0 -10 0" /><path d="M12 7v-4" /><path d="M11 3h2" /><path d="M15.536 8.464l2.828 -2.828" /><path d="M17.657 4.929l1.414 1.414" /><path d="M17 12h4" /><path d="M21 11v2" /><path d="M15.535 15.536l2.829 2.828" /><path d="M19.071 17.657l-1.414 1.414" /><path d="M12 17v4" /><path d="M13 21h-2" /><path d="M8.465 15.536l-2.829 2.828" /><path d="M6.343 19.071l-1.413 -1.414" /><path d="M7 12h-4" /><path d="M3 13v-2" /><path d="M8.464 8.464l-2.828 -2.828" /><path d="M4.929 6.343l1.414 -1.413" /></svg>
                                Antrian HPV
                            </a>
                        </li>
                    </ul>
                    <div class="card-actions">
                         <a href="{{ route('display') }}" target="_blank" class="btn btn-secondary">
                            Buka Layar TV
                         </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="queue-content-body">
                        @include('admin_rows')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // --- BAGIAN 1: LOGIKA SIMPAN POSISI TAB ---
        
        // Cek apakah ada tab yang tersimpan di LocalStorage browser
        const savedTab = localStorage.getItem('activeQueueTab');

        if (savedTab) {
            // Cari elemen tab yang href-nya sesuai dengan yang disimpan
            const triggerEl = document.querySelector(`a[href="${savedTab}"]`);
            
            if (triggerEl) {
                // Aktifkan Tab tersebut menggunakan Bootstrap API
                const tabInstance = new bootstrap.Tab(triggerEl);
                tabInstance.show();
            }
        }

        // Tambahkan Event Listener ke semua tombol Tab
        // Setiap kali tab diklik, simpan href-nya ke LocalStorage
        const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabLinks.forEach(tabLink => {
            tabLink.addEventListener('shown.bs.tab', function (event) {
                // Simpan ID tab (contoh: #tabs-hpv)
                localStorage.setItem('activeQueueTab', event.target.getAttribute('href'));
            });
        });

        // --- BAGIAN 2: LOGIKA AUTO REFRESH ---
        
        setInterval(function() {
            // Cek tab mana yang sedang aktif SAAT INI
            const activeLink = document.querySelector('.nav-link.active');
            // Jika tidak ada yang aktif (jarang terjadi), default ke rontgen
            const activeTabHref = activeLink ? activeLink.getAttribute('href') : '#tabs-rontgen';
            
            fetch("{{ route('admin') }}?refresh_table=1")
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Update isi konten tabel
                    document.getElementById('queue-content-body').innerHTML = doc.body.innerHTML;
                    
                    // Kembalikan posisi tab agar konten yang terlihat sesuai dengan tab yang aktif
                    
                    // 1. Hapus semua class active/show dari konten yang baru diambil
                    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active', 'show'));
                    
                    // 2. Ambil ID target (hapus tanda #)
                    const targetId = activeTabHref.substring(1); 
                    
                    // 3. Tambahkan class active ke konten tab yang sedang dipilih user
                    const targetEl = document.getElementById(targetId);
                    if(targetEl) {
                        targetEl.classList.add('active', 'show');
                    }
                })
                .catch(error => console.error('Gagal refresh:', error));
        }, 5000); // Refresh setiap 5 detik
    });
</script>
@endsection