<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Layar Antrian</title>
    
    <link rel="icon" href="{{ asset('logo/setneg.png') }}" type="image/x-icon"/>
    <link rel="shortcut icon" href="{{ asset('logo/setneg.png') }}" type="image/x-icon"/>

    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css" rel="stylesheet"/>
    <style>
      body { background-color: #f4f6fa; overflow: hidden; }
      .page-center { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
      
      .big-number { font-size: 8rem; font-weight: bold; line-height: 1; color: #206bc4; }
      .category-badge { font-size: 2rem; margin-bottom: 1rem; }
      
      .history-card { height: 90vh; overflow: hidden; }
      .history-item { border-bottom: 1px solid #e6e8e9; padding: 15px; }
      .history-number { font-size: 1.5rem; font-weight: bold; }

      /* OVERLAY WAJIB (Agar suara diizinkan browser) */
      #start-overlay {
          position: fixed; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(32, 107, 196, 0.95); z-index: 9999;
          display: flex; justify-content: center; align-items: center;
          color: white; flex-direction: column; cursor: pointer; text-align: center;
      }
    </style>
  </head>
  <body>

    <div id="start-overlay" onclick="startApp()">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-player-play" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
        <h1 class="mt-3">Klik Layar Untuk Mengaktifkan Suara</h1>
        <p class="fs-3">Sistem menggunakan suara bawaan Windows</p>
    </div>

    <div class="page page-center">
      <div class="container-fluid py-4">
        <div class="row align-items-center">
          
          <div class="col-lg-8">
            <div class="card card-md shadow-lg" style="min-height: 80vh; justify-content: center;">
              <div class="card-body text-center">
                
                <img src="{{ asset('logo/setneg.png') }}" width="80" class="mb-3">
                
                <h1 class="text-muted text-uppercase mb-4" style="letter-spacing: 2px;">Nomor Antrian</h1>
                
                <div id="category-badge" class="badge bg-blue-lt category-badge d-none">
                  KATEGORI
                </div>

                <div id="queue-number" class="big-number">---</div>
                
                <div class="mt-4">
                  <h2 id="queue-name" class="h1 text-dark">Menunggu...</h2>
                  <p id="queue-details" class="text-muted fs-2"></p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card history-card shadow-sm">
              <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">Riwayat Panggilan</h3>
              </div>
              <div class="card-body p-0" id="history-list">
                <div class="p-4 text-center text-muted">Belum ada riwayat.</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script>
      // Kita ganti variabel pelacak dari ID menjadi WAKTU
      let lastUpdated = null; 
      let isAudioEnabled = false; 

      function startApp() {
          document.getElementById('start-overlay').style.display = 'none';
          isAudioEnabled = true;
          speak("Sistem Antrian Aktif");
      }

      function updateDisplay() {
        fetch("{{ route('api.current.queue') }}")
          .then(response => response.json())
          .then(res => {
            const data = res.data;
            
            if (data) {
              // Update Tampilan Teks
              document.getElementById('queue-number').innerText = data.number;
              document.getElementById('queue-name').innerText = data.name;
              document.getElementById('queue-details').innerText = data.category === 'hpv' ? 'Pemeriksaan HPV' : 'Pemeriksaan Rontgen';
              
              const badge = document.getElementById('category-badge');
              badge.classList.remove('d-none');
              badge.innerText = data.category.toUpperCase();
              badge.className = data.category === 'hpv' 
                  ? 'badge bg-purple-lt category-badge' 
                  : 'badge bg-blue-lt category-badge';

              // --- LOGIKA BARU UNTUK SUARA (PANGGIL ULANG) ---
              // Kita cek: Apakah WAKTU update berubah? 
              // Jika Admin klik Panggil Ulang, updated_at di database berubah, 
              // maka kondisi ini akan TRUE meskipun ID-nya sama.
              if (lastUpdated !== data.updated_at) {
                lastUpdated = data.updated_at; // Simpan waktu terbaru
                
                if(isAudioEnabled) {
                    processSpeech(data.number);
                }
              }

            } else {
              // Jika Antrian Kosong / Istirahat
              document.getElementById('queue-number').innerText = "---";
              document.getElementById('queue-name').innerText = "Silakan Tunggu";
              document.getElementById('category-badge').classList.add('d-none');
              
              // Reset pelacak agar kalau antrian muncul lagi, dia bunyi
              lastUpdated = null; 
            }

            // UPDATE RIWAYAT (Sama seperti sebelumnya)
            const historyList = document.getElementById('history-list');
            if (res.history.length > 0) {
                let html = '';
                res.history.forEach(item => {
                    const color = item.category === 'hpv' ? 'bg-purple-lt' : 'bg-blue-lt';
                    const catName = item.category === 'hpv' ? 'HPV' : 'Rontgen';
                    html += `
                    <div class="history-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
                        <div>
                            <span class="badge ${color} mb-1">${catName}</span>
                            <div class="history-number text-dark">${item.number}</div>
                        </div>
                        <div class="text-end text-muted">
                           <div class="small text-truncate" style="max-width: 150px;">${item.name}</div>
                           <small>Selesai</small>
                        </div>
                    </div>`;
                });
                historyList.innerHTML = html;
            }
          })
          .catch(err => console.error(err));
      }

      function processSpeech(numberString) {
          const parts = numberString.split('-'); 
          const huruf = parts[0]; 
          const angkaRaw = parseInt(parts[1]); 

          let angkaBaca = angkaRaw;
          if(angkaRaw < 10) {
              angkaBaca = "Kosong Kosong " + angkaRaw;
          } else if (angkaRaw < 100) {
              angkaBaca = "Kosong " + angkaRaw;
          }

          // Tambahkan kata "Panggilan Ulang" jika perlu, atau biarkan standar
          const kalimat = `Nomor Antrian... ${huruf}... ${angkaBaca}... Silakan masuk.`;
          
          speak(kalimat);
      }

      function speak(text) {
          if ('speechSynthesis' in window) {
              window.speechSynthesis.cancel(); 
              const msg = new SpeechSynthesisUtterance(text);
              msg.lang = 'id-ID'; 
              msg.rate = 0.85;    
              msg.pitch = 1;
              msg.volume = 1;
              window.speechSynthesis.speak(msg);
          }
      }

      setInterval(updateDisplay, 3000);
      updateDisplay();
    </script>
  </body>
</html>