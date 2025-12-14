<div class="tab-pane active show" id="tabs-rontgen">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rontgen as $q)
                <tr class="{{ $q->status == 'called' ? 'bg-green-lt' : '' }}">
                    <td><span class="text-muted">{{ $q->number }}</span></td>
                    <td><span class="fw-bold">{{ $q->name }}</span></td>
                    <td>
                        @if($q->status == 'waiting') <span class="badge bg-yellow text-yellow-fg">Menunggu</span>
                        @else <span class="badge bg-green text-green-fg">Dipanggil</span> @endif
                    </td>
                    <td class="text-end">
                        @if($q->status != 'completed')
                            <form action="{{ route('queue.call', $q->id) }}" method="POST" class="d-inline">
                                @csrf <button type="submit" class="btn btn-primary btn-sm">Panggil</button>
                            </form>
                        @endif
                        @if($q->status == 'called')
                            <form action="{{ route('queue.complete', $q->id) }}" method="POST" class="d-inline ms-2">
                                @csrf <button type="submit" class="btn btn-danger btn-sm">Selesai</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Tidak ada antrian Rontgen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="tab-pane" id="tabs-hpv">
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hpv as $q)
                <tr class="{{ $q->status == 'called' ? 'bg-green-lt' : '' }}">
                    <td><span class="text-muted">{{ $q->number }}</span></td>
                    <td><span class="fw-bold">{{ $q->name }}</span></td>
                    <td>
                        @if($q->status == 'waiting') <span class="badge bg-yellow text-yellow-fg">Menunggu</span>
                        @else <span class="badge bg-green text-green-fg">Dipanggil</span> @endif
                    </td>
                    <td class="text-end">
                        @if($q->status != 'completed')
                            <form action="{{ route('queue.call', $q->id) }}" method="POST" class="d-inline">
                                @csrf <button type="submit" class="btn btn-purple btn-sm">Panggil</button>
                            </form>
                        @endif
                        @if($q->status == 'called')
                            <form action="{{ route('queue.complete', $q->id) }}" method="POST" class="d-inline ms-2">
                                @csrf <button type="submit" class="btn btn-danger btn-sm">Selesai</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">Tidak ada antrian HPV.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <h3 class="card-title">Riwayat Panggilan Terakhir (5 Terakhir)</h3>
    <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Nama</th>
                    <th>Waktu Selesai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($history as $h)
                <tr>
                    <td><span class="text-muted">{{ $h->number }}</span></td>
                    <td>{{ strtoupper($h->category) }}</td>
                    <td>{{ $h->name }}</td>
                    <td>{{ $h->updated_at->format('H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>