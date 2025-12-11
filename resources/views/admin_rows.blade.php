@foreach($queues as $q)
<tr class="{{ $q->status == 'called' ? 'bg-green-lt' : '' }}">
    <td><span class="text-muted">{{ $q->number }}</span></td>
    <td><span class="fw-bold">{{ $q->name }}</span></td>
    <td>{{ Str::limit($q->details, 30) }}</td>
    <td>
        @if($q->status == 'waiting')
            <span class="badge bg-yellow text-yellow-fg">Menunggu</span>
        @elseif($q->status == 'called')
            <span class="badge bg-green text-green-fg">Sedang Dipanggil</span>
        @else
            <span class="badge bg-gray text-gray-fg">Selesai</span>
        @endif
    </td>
    <td class="text-end">
        <div class="btn-list justify-content-end">
            @if($q->status != 'completed')
                <form action="{{ route('queue.call', $q->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        {{ $q->status == 'called' ? 'Panggil Ulang' : 'Panggil' }}
                    </button>
                </form>
            @endif

            @if($q->status == 'called')
                <form action="{{ route('queue.complete', $q->id) }}" method="POST" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">
                        Selesai
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
@endforeach