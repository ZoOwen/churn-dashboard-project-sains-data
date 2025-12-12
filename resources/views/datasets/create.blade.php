@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/datasets.css') }}">

<div class="ds-container">

    <h2 class="ds-title">Upload Dataset</h2>

    @if ($errors->any())
        <div class="ds-alert">
            <strong>Terjadi Kesalahan!</strong>
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card ds-card">
        <div class="card-body">

            <form action="{{ route('datasets.store') }}" 
                  method="POST" 
                  enctype="multipart/form-data">
                @csrf

                {{-- Nama Dataset --}}
                <div class="mb-3">
                    <label class="ds-label">Nama Dataset</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="ds-input" 
                        placeholder="Nama dataset..."
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                {{-- Upload File --}}
                <div class="mb-3">
                    <label class="ds-label">Upload File Dataset</label>
                    <input 
                        type="file" 
                        name="file" 
                        class="ds-input"
                        accept=".csv,.xls,.xlsx"
                        required
                    >
                    <small>Hanya mendukung: CSV, XLS, XLSX</small>
                </div>

                {{-- Tombol --}}
                <button class="ds-btn-primary">Upload</button>
                <a href="{{ route('datasets.index') }}" class="ds-btn-secondary">Kembali</a>

            </form>

        </div>
    </div>

</div>

@push('scripts')
<script>
document.querySelector('input[name="file"]').addEventListener('change', function (e) {
    let file = e.target.files[0];
    if (!file) return;

    let name = file.name.replace(/\.[^/.]+$/, "");
    document.querySelector('input[name="name"]').value = name;
});
</script>
@endpush

@endsection
