@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detail Dataset</h2>
        <div>
            <a href="{{ route('datasets.download', $dataset->id) }}" class="btn btn-primary me-2">Download</a>
            <a href="{{ route('datasets.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    {{-- Dataset Info --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2"><strong>Nama Dataset:</strong> <p>{{ $dataset->name }}</p></div>
                <div class="col-md-4 mb-2"><strong>File Asli:</strong> <p>{{ $dataset->original_filename }}</p></div>
                <div class="col-md-4 mb-2"><strong>Tipe File:</strong> <p>{{ strtoupper($dataset->file_type) }}</p></div>
                <div class="col-md-4 mb-2"><strong>Ukuran File:</strong> <p>{{ number_format($dataset->file_size / 1024, 2) }} KB</p></div>
                <div class="col-md-4 mb-2"><strong>Uploaded At:</strong> <p>{{ $dataset->created_at->format('d M Y H:i') }}</p></div>
            </div>
        </div>
    </div>

    {{-- Rows per page --}}
    <form id="perPageForm" method="GET" class="d-flex align-items-center gap-2 mb-3">
        <label class="me-2 mb-0">Rows per page:</label>
        <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>

        @foreach ($selectedColumns as $col)
            <input type="hidden" name="columns[]" value="{{ $col }}">
        @endforeach
    </form>

    <h3 class="mb-3">Preview Dataset</h3>

    <div class="row">
        {{-- Sidebar Column Selector --}}
        <div class="col-md-3 mb-3">
            <div class="card p-3 h-100">
                <h5>Pilih Kolom</h5>
                <form id="colForm" method="GET">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    @foreach ($columns as $col)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input col-toggle" name="columns[]" value="{{ $col }}" data-col="{{ $col }}"
                            {{ in_array($col, $selectedColumns) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $col }}</label>
                        </div>
                    @endforeach
                    <!-- <button class="btn btn-primary w-100 mt-3">Terapkan Kolom</button> -->
                </form>
            </div>
        </div>

        {{-- Table + Pagination --}}
        <div class="col-md-9">
            <div id="dataset-table-container">
                @include('datasets._table')
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
 .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    position: relative;
}

/* optional: buat scrollbar muncul di atas */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background-color: #888;
    border-radius: 4px;
}

</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------
    // Toggle kolom langsung (client-side)
    // ------------------------------
    function bindColToggle() {
        document.querySelectorAll('.col-toggle').forEach(cb => {
            // bind listener langsung tanpa remove/clone
            cb.addEventListener('change', function () {
                const col = this.dataset.col;
                const show = this.checked;

                // toggle header
                document.querySelectorAll(`th[data-col="${col}"]`).forEach(th => {
                    th.style.display = show ? '' : 'none';
                });

                // toggle semua cell di kolom
                document.querySelectorAll(`td[data-col="${col}"]`).forEach(td => {
                    td.style.display = show ? '' : 'none';
                });
            });
        });
    }

    // bind pertama kali
    bindColToggle();

    // ------------------------------
    // Fungsi AJAX untuk pagination & form
    // ------------------------------
    function fetchPage(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                document.getElementById('dataset-table-container').innerHTML = html;
                // Tidak perlu rebind checkbox, listener di sidebar tetap berlaku
            });
    }

    // ------------------------------
    // Pagination click (AJAX)
    // ------------------------------
    document.getElementById('dataset-table-container').addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if(link && link.closest('.pagination')) {
            e.preventDefault();
            fetchPage(link.href);
        }
    });

    // ------------------------------
    // Submit form kolom (AJAX)
    // ------------------------------
    const colForm = document.getElementById('colForm');
    if(colForm){
        colForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchPage(this.action + '?' + new URLSearchParams(new FormData(this)).toString());
        });
    }

    // ------------------------------
    // Submit form rows per page (AJAX)
    // ------------------------------
    const perPageForm = document.getElementById('perPageForm');
    if(perPageForm){
        perPageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchPage(this.action + '?' + new URLSearchParams(new FormData(this)).toString());
        });
    }

});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
@endpush
