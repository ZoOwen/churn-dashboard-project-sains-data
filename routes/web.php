<?php
use App\Http\Controllers\DatasetController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChurnDatasetController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth'])->group(function () {

    // INDEX (list datasets)
    Route::get('/datasets', [DatasetController::class, 'index'])->name('datasets.index');

    // FORM upload
    Route::get('/datasets/create', [DatasetController::class, 'create'])->name('datasets.create');

    // STORE uploaded file
    Route::post('/datasets', [DatasetController::class, 'store'])->name('datasets.store');

    // SHOW detail dataset (opsional)
    Route::get('/datasets/{dataset}', [DatasetController::class, 'show'])->name('datasets.show');

    // DELETE dataset
    Route::delete('/datasets/{dataset}', [DatasetController::class, 'destroy'])->name('datasets.destroy');

    //download excel
    Route::get('/datasets/{dataset}/download', [DatasetController::class, 'download'])
    ->name('datasets.download');

});


//churndatasets
Route::get('/churn-datasets', [ChurnDatasetController::class, 'index'])->name('churn-datasets.index');

Route::get('/dashboardv2/menu1', function () {
    $datasets = \App\Models\Dataset::all();
    return view('v2.index', compact('datasets'));
})->name('dashboardv2.menu1');



require __DIR__.'/auth.php';
