<?php

use App\Http\Controllers\AppFiturController;
use App\Http\Controllers\AppProfileController;
// use App\Http\Controllers\DataGuruController;
use App\Http\Controllers\KelolaKaryawanController;
use App\Http\Controllers\PenilaianBobotKaryawanController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PenilaianKaryawanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* Route::get('/', function () {
    return view('welcome');
});
 */


// Route::fallback([TemplateController::class, 'error_pages']);

/* Route::middleware(['auth'])->group(function () {
    Route::resource('app_fiturs', AppFiturController::class);
    Route::post('app_fiturs/toggle-aktif', [AppFiturController::class, 'toggleAktif'])->name('app_fiturs.toggleAktif');

    Route::get('app_profiles', [AppProfileController::class, 'show'])->name('app_profiles.show');
    Route::get('app_profiles/edit', [AppProfileController::class, 'edit'])->name('app_profiles.edit');
    Route::put('app_profiles', [AppProfileController::class, 'update'])->name('app_profiles.update');
    //Route::get('/app_fiturs', [AppFiturController::class, 'index'])->name('app_fiturs');

    Route::get('/master_profil', [MasterController::class, 'master_profil'])->name('master_profil');
}); */
//TOOLS
Route::middleware('admin')->prefix('tools')->group(function () {

    Route::get('app_profiles', [AppProfileController::class, 'show'])->name('app_profiles.show');
    Route::get('app_profiles/edit', [AppProfileController::class, 'edit'])->name('app_profiles.edit');
    Route::put('app_profiles', [AppProfileController::class, 'update'])->name('app_profiles.update');
    //Route::get('/app_fiturs', [AppFiturController::class, 'index'])->name('app_fiturs');
});

//Profil Admin
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/profil_admin', [MasterController::class, 'master_profil'])->name('profil_admin');
});
//AKADEMIK
Route::middleware(['auth'])->group(function () {
    Route::middleware(['admin'])->group(function () {
    // Employee Scoring routes - accessible by Admin
        Route::prefix('karyawan')->group(function () {
            Route::resource('kelola-karyawan', KelolaKaryawanController::class);
            Route::resource('kriteria_bobot', PenilaianBobotKaryawanController::class);
        });

        Route::prefix('penilaian')->group(function () {
            Route::resource('kriteria_bobot', PenilaianBobotKaryawanController::class);
        });
    });

    // Employee Scoring routes - accessible by Admin
    Route::prefix('penilaian-karyawan')->group(function () {
        Route::get('/', [PenilaianKaryawanController::class, 'index'])->name('penilaian_karyawan.index');
        Route::get('/summary', [PenilaianKaryawanController::class, 'getSummary'])->name('penilaian_karyawan.summary');
        Route::get('/export', [PenilaianKaryawanController::class, 'export'])->name('penilaian_karyawan.export');
        Route::get('/ranking', [PenilaianKaryawanController::class, 'ranking'])->name('penilaian_karyawan.ranking');
        Route::get('/saw-details', [PenilaianKaryawanController::class, 'getSAWDetails'])->name('penilaian_karyawan.saw-details');
        Route::get('/criteria-stats', [PenilaianKaryawanController::class, 'getCriteriaStats'])->name('penilaian_karyawan.criteria-stats');
        Route::get('/{employee}/create', [PenilaianKaryawanController::class, 'create'])->name('penilaian_karyawan.create');
        Route::get('/{employee}/edit', [PenilaianKaryawanController::class, 'edit'])->name('penilaian_karyawan.edit');
        Route::get('/{employee}/show', [PenilaianKaryawanController::class, 'show'])->name('penilaian_karyawan.show');
        Route::post('/store', [PenilaianKaryawanController::class, 'store'])->name('penilaian_karyawan.store');
        Route::post('/{assessment}', [PenilaianKaryawanController::class, 'destroy'])->name('penilaian_karyawan.destroy');
        Route::post('/bulk-delete', [PenilaianKaryawanController::class, 'bulkDelete'])->name('penilaian_karyawan.bulk-delete');
    });

        // Approval routes - accessible by leadership roles
    Route::middleware('approval')->prefix('approval')->group(function () {
        Route::get('/', [App\Http\Controllers\PenilaianApprovalController::class, 'index'])->name('approval.index');
        Route::get('/history', [App\Http\Controllers\PenilaianApprovalController::class, 'history'])->name('approval.history');
        Route::get('/stats', [App\Http\Controllers\PenilaianApprovalController::class, 'getStats'])->name('approval.stats');
        Route::get('/{id}', [App\Http\Controllers\PenilaianApprovalController::class, 'show'])->name('approval.show');
        Route::post('/{id}/approve', [App\Http\Controllers\PenilaianApprovalController::class, 'approve'])->name('approval.approve');
        Route::post('/{id}/reject', [App\Http\Controllers\PenilaianApprovalController::class, 'reject'])->name('approval.reject');
        Route::post('/bulk-approve', [App\Http\Controllers\PenilaianApprovalController::class, 'bulkApprove'])->name('approval.bulk-approve');
        Route::post('/bulk-reject', [App\Http\Controllers\PenilaianApprovalController::class, 'bulkReject'])->name('approval.bulk-reject');
    });

    Route::prefix('penilaian-karyawan')->group(function () {
        Route::get('/hasil-penilaian', [PenilaianKaryawanController::class, 'results'])->name('results.index');
        Route::get('/hasil-penilaian/ranking', [PenilaianKaryawanController::class, 'resultsRanking'])->name('results.ranking');
        Route::get('/hasil-penilaian/export', [PenilaianKaryawanController::class, 'resultsExport'])->name('results.export');
        Route::get('/hasil-penilaian/summary', [PenilaianKaryawanController::class, 'getResultsSummary'])->name('results.summary');
        Route::get('/hasil-penilaian/saw-details', [PenilaianKaryawanController::class, 'getSAWDetails'])->name('results.saw-details');
        Route::get('/hasil-penilaian/{employee}/show', [PenilaianKaryawanController::class, 'resultsShow'])->name('results.show');
    });

    Route::middleware(['karyawan'])->group(function () {
        Route::get('/hasil_penilaian_saya', [PenilaianKaryawanController::class, 'myResults'])->name('employee.my_results');
    });
});
