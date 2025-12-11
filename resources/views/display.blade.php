@extends('layouts.app')

@section('content')
<div id="start-overlay" style="position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; color: white;">
    <h1 class="mb-4">Display Antrian Siap</h1>
    <button id="btn-start" class="btn btn-primary btn-lg" style="font-size: 2rem; padding: 20px 60px;">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-microphone" width="40" height="40" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="5" y="2" width="14" height="16" rx="3" /><line x1="8" y1="23" x2="16" y2="23" /><line x1="12" y1="17" x2="12" y2="23" /><path d="M19 10v2a7 7 0 0 1 -14 0v-2" /></svg>
        AKTIFKAN SUARA
    </button>
</div>

<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <h1>ANTRIAN RONTGEN</h1>
        </div>
        <div class="card card-md text-center py-5" style="border: 3px solid #206bc4; min-height: 400px;">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                
                <div id="queue-content" style="display: none;">
                    <div class="text-muted text-uppercase font-weight-bold">Nomor Antrian Rontgen</div>
                    <div class="display-1 fw-bold text-primary" style="font-size: 8rem;" id="display-number">
                        -
                    </div>
                    <h2 class="mt-4" id="display-name">-</h2>
                    <p class="text-muted" id="display-details">-</p>
                </div>

                <div id="queue-empty">
                    <h2 class="text-muted">Menunggu Panggilan...</h2>
                    <div class="spinner-border text-primary mt-3" role="status"></div>
                </div>

                </div>
        </div>
    </div>
</div>

<script>
    let lastPlayedId = null; 
    const synth = window.speechSynthesis;
    let voices = [];

    // Load voices
    function loadVoices() {
        voices = synth.getVoices();
    }
    loadVoices();
    if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = loadVoices;
    }

    // 1. Tombol Start
    document.getElementById('btn-start').addEventListener('click', function() {
        // Pancing TTS agar aktif
        synth.speak(new SpeechSynthesisUtterance(""));
        document.getElementById('start-overlay').style.display = 'none';
        startPolling();
    });

    // 2. Polling Data (DIPERCEPAT jadi 1000ms / 1 detik)
    function startPolling() {
        setInterval(function() {
            fetch("{{ route('api.queue') }}")
                .then(response => response.json())
                .then(result => {
                    updateDisplay(result.data);
                })
                .catch(err => console.error(err));
        }, 1000); 
    }

    // 3. Update Display
    function updateDisplay(data) {
        const contentDiv = document.getElementById('queue-content');
        const emptyDiv = document.getElementById('queue-empty');

        if (data) {
            emptyDiv.style.display = 'none';
            contentDiv.style.display = 'block';

            document.getElementById('display-number').innerText = data.number;
            document.getElementById('display-name').innerText = data.name;
            document.getElementById('display-details').innerText = data.details || '';

            if (lastPlayedId !== data.id) {
                lastPlayedId = data.id;
                
                // Hentikan suara sebelumnya (jika ada) agar tidak tabrakan
                synth.cancel();
                
                // Langsung bicara (Tanpa nunggu ting tung)
                speakIndonesian(data.number, data.name);
            }

        } else {
            contentDiv.style.display = 'none';
            emptyDiv.style.display = 'block';
        }
    }

    // 4. Logic Bicara Cepat
    function speakIndonesian(numberRaw, nameRaw) {
        // Ejaan Angka Manual (Tetap dipakai agar "Zero" jadi "Kosong")
        let numberString = numberRaw.toString().toUpperCase();
        let spelledNumber = "";

        const mapAngka = {
            '0': 'Kosong', 
            '1': 'Satu', '2': 'Dua', '3': 'Tiga', '4': 'Empat',
            '5': 'Lima', '6': 'Enam', '7': 'Tujuh', '8': 'Delapan', '9': 'Sembilan',
            '-': ' ' 
        };

        for (let char of numberString) {
            if (mapAngka[char]) {
                spelledNumber += mapAngka[char] + " "; // Spasi biasa, bukan titik
            } else {
                spelledNumber += char + " "; 
            }
        }

        // Kalimat dibuat lebih pendek jedanya
        let textToSpeak = `Nomor Antrian ${spelledNumber}. Atas nama ${nameRaw}. Silakan Masuk.`;

        let utterance = new SpeechSynthesisUtterance(textToSpeak);
        
        // Cari suara Indo
        let indoVoice = voices.find(v => v.lang.includes('id-ID') || v.name.includes('Indonesia'));
        if (indoVoice) {
            utterance.voice = indoVoice;
            utterance.lang = 'id-ID';
        }

        // KECEPATAN BICARA
        // 1 = Normal, > 1 = Cepat
        utterance.rate = 1.0; 
        utterance.volume = 1;
        utterance.pitch = 1;

        synth.speak(utterance);
    }
</script>
@endsection