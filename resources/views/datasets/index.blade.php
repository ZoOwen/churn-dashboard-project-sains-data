@extends('layouts.app')

@section('content')
<div class="page-header">
    <h2>Datasets</h2>

    <div class="actions">
        <form action="{{ route('datasets.index') }}" method="GET" class="search-box">
            <input type="text" name="q" placeholder="Search datasets..." value="{{ request('q') }}">
            <button type="submit">Search</button>
        </form>

        <a href="{{ route('datasets.create') }}" class="btn-upload">
            Upload Dataset
        </a>
    </div>
</div>


<div class="table-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Dataset</th>
                <th>File</th>
                <th>Uploaded At</th>
                <th style="width:140px;">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($datasets as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->original_filename }}</td>
                    <td>{{ $item->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('datasets.show', $item->id) }}" class="btn-sm">View</a>
                        <a href="{{ route('datasets.download', $item->id) }}" class="btn-sm">Download</a>
                        <form action="{{ route('datasets.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn-sm btn-danger" onclick="return confirm('Hapus dataset ini?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada dataset</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
