<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class DatasetController extends Controller
{
    // =========================
    // INDEX
    // =========================
    public function index(Request $request)
    {
        $q = $request->q;

        $datasets = Dataset::when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%$q%");
            })
            ->latest()
            ->get();

        return view('datasets.index', compact('datasets'));
    }

    // =========================
    // CREATE FORM
    // =========================
    public function create()
    {
        return view('datasets.create');
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        // VALIDASI
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|mimes:csv,xlsx,xls|max:10000',
        ]);

        $file = $request->file('file');

        // NAMA FILE DISIMPAN
        $storedName = time() . '_' . $file->getClientOriginalName();

        // SIMPAN FILE KE STORAGE
        $file->storeAs('datasets', $storedName, 'public');

        // INSERT DB
        Dataset::create([
            'name' => $request->name,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedName,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()
            ->route('datasets.index')
            ->with('success', 'Dataset berhasil diupload!');
    }

    // =========================
    // SHOW DETAILS
    // =========================
public function show($id, Request $request)
{
    $dataset = Dataset::findOrFail($id);
    $path = storage_path('app/public/datasets/' . $dataset->stored_filename);

    $allRows = []; // akan berisi semua baris termasuk header

    // --- BACA CSV ---
    if ($dataset->file_type === 'csv') {
        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 100000, ',')) !== false) {
                $allRows[] = $data;
            }
            fclose($handle);
        }
    }

    // --- BACA EXCEL (xlsx / xls) ---
    if (in_array($dataset->file_type, ['xlsx', 'xls'])) {
        $sheets = Excel::toArray([], $path);
        $allRows = $sheets[0] ?? [];
    }

    // jika masih kosong, kirim view kosong
    if (empty($allRows)) {
        $columns = [];
        $rows = [];       // tanpa header
        $preview = [];    // preview 10 baris
        $paginator = collect([]);
        $perPage = $request->get('per_page', 25);
        $selectedColumns = $request->get('columns', []);
        return view('datasets.show', compact(
            'dataset', 'columns', 'rows', 'preview', 'paginator', 'perPage', 'selectedColumns'
        ));
    }

    // Ambil header (kolom) dan data tanpa header
    $columns = $allRows[0];
    $rows = array_slice($allRows, 1); // setiap element adalah indexed array baris

    // Buat preview 10 baris (associative sesuai header) untuk bagian Preview
    $preview_raw = array_slice($rows, 0, 10);
    $preview = [];
    foreach ($preview_raw as $r) {
        // pastikan jumlah kolom cocok sebelum combine
        $row = array_pad($r, count($columns), null);
        $preview[] = array_combine($columns, $row);
    }

    // ========== Full-table handling (filter columns + pagination) ==========
    $perPage = (int) $request->get('per_page', 25);
    $selectedColumns = $request->get('columns', $columns);

    // Build filteredData as array of assoc rows keyed by column name
    $filteredData = array_map(function ($r) use ($columns) {
        $row = array_pad($r, count($columns), null);
        return array_combine($columns, $row);
    }, $rows);

    // If user selected specific columns, trim each assoc row
    if (!empty($selectedColumns)) {
        $filteredData = array_map(function ($assoc) use ($selectedColumns) {
            return array_filter($assoc, function ($k) use ($selectedColumns) {
                return in_array($k, $selectedColumns);
            }, ARRAY_FILTER_USE_KEY);
        }, $filteredData);
    }

    // Manual pagination
    $page = (int) $request->get('page', 1);
    $offset = ($page - 1) * $perPage;
    $paginated = array_slice($filteredData, $offset, $perPage);

    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $paginated,
        count($filteredData),
        $perPage,
        $page,
        ['path' => url()->current(), 'query' => request()->query()]
    );

    // Kirim semua variable yang view butuhkan:
    // - $columns (array of header names)
    // - $rows (raw rows without header; used by kaggle-style table if you want)
    // - $preview (assoc rows 10 first)
    // - $paginator (paginated assoc rows)
    // - $perPage, $selectedColumns
    if ($request->ajax()) {
    return view('datasets._table', compact('paginator', 'selectedColumns'));
}
    return view('datasets.show', compact(
        'dataset',
        'columns',
        'rows',
        'preview',
        'paginator',
        'perPage',
        'selectedColumns'
    ));
}




    // =========================
    // DOWNLOAD FILE
    // =========================
    public function download($id)
    {
        $dataset = Dataset::findOrFail($id);

        return Storage::disk('public')->download(
            "datasets/" . $dataset->stored_filename,
            $dataset->original_filename
        );
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $dataset = Dataset::findOrFail($id);

        // HAPUS FILE
        Storage::disk('public')->delete("datasets/" . $dataset->stored_filename);

        // HAPUS RECORD
        $dataset->delete();

        return redirect()
            ->route('datasets.index')
            ->with('success', 'Dataset berhasil dihapus!');
    }
}
