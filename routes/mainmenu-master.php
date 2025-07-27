<?php

use App\Http\Controllers\AppFiturController;
use App\Http\Controllers\AppProfileController;
// use App\Http\Controllers\DataGuruController;
use App\Http\Controllers\KelolaKaryawanController;
use App\Http\Controllers\PenilaianBobotKaryawanController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\ProfilSekolahController;
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


    Route::resource('app_fiturs', AppFiturController::class);
    Route::post('app_fiturs/toggle-aktif', [AppFiturController::class, 'toggleAktif'])->name('app_fiturs.toggleAktif');

    Route::get('app_profiles', [AppProfileController::class, 'show'])->name('app_profiles.show');
    Route::get('app_profiles/edit', [AppProfileController::class, 'edit'])->name('app_profiles.edit');
    Route::put('app_profiles', [AppProfileController::class, 'update'])->name('app_profiles.update');
    //Route::get('/app_fiturs', [AppFiturController::class, 'index'])->name('app_fiturs');

    Route::get('/opsi_aplikasi', [MasterController::class, 'tools_opsi_aplikasi'])->name('opsi_aplikasi');
    Route::get('/impor_data_master', [MasterController::class, 'tools_impor_data_master'])->name('impor_data_master');
    Route::get('/ekspor_data_master', [MasterController::class, 'tools_ekspor_data_master'])->name('ekspor_data_master');
    Route::get('/backup_database', [MasterController::class, 'tools_backup_database'])->name('backup_database');
    Route::get('/data_login', [MasterController::class, 'tools_data_login'])->name('data_login');
});

//Profil Admin
Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('/profil_admin', [MasterController::class, 'master_profil'])->name('profil_admin');
});
//AKADEMIK
Route::middleware('admin')->prefix('karyawan')->group(function () {

    Route::get('profil-sekolah', [ProfilSekolahController::class, 'show'])->name('profil-sekolah.index');
    Route::get('profil-sekolah/edit', [ProfilSekolahController::class, 'edit'])->name('profil-sekolah.edit');
    Route::put('profil-sekolah', [ProfilSekolahController::class, 'update'])->name('profil-sekolah.update');


    //Route::get('/indentitas_sekolah', [MasterController::class, 'akademik_indentitas_sekolah'])->name('indentitas_sekolah');
    //Route::get('/tenaga_pendidik', [MasterController::class, 'akademik_tenaga_pendidik'])->name('tenaga_pendidik');

    Route::resource('kelola_karyawan', KelolaKaryawanController::class);
    Route::resource('kriteria_bobot', PenilaianBobotKaryawanController::class);
});

Route::middleware('admin')->prefix('penilaian')->group(function () {
    Route::resource('kriteria_bobot', PenilaianBobotKaryawanController::class);
    Route::resource('karyawan_nilai', PenilaianKaryawanController::class);
});

// Employee Scoring routes - accessible by Admin
Route::prefix('scoring')->group(function () {
    Route::get('/', [App\Http\Controllers\EmployeeScoringController::class, 'index'])->name('scoring.index');
    Route::get('/summary', [App\Http\Controllers\EmployeeScoringController::class, 'getSummary'])->name('scoring.summary');
    Route::get('/export', [App\Http\Controllers\EmployeeScoringController::class, 'export'])->name('scoring.export');
    Route::get('/ranking', [App\Http\Controllers\EmployeeScoringController::class, 'ranking'])->name('scoring.ranking');
    Route::get('/saw-details', [App\Http\Controllers\EmployeeScoringController::class, 'getSAWDetails'])->name('scoring.saw-details');
    Route::get('/criteria-stats', [App\Http\Controllers\EmployeeScoringController::class, 'getCriteriaStats'])->name('scoring.criteria-stats');
    Route::get('/{employee}/create', [App\Http\Controllers\EmployeeScoringController::class, 'create'])->name('scoring.create');
    Route::get('/{employee}/edit', [App\Http\Controllers\EmployeeScoringController::class, 'edit'])->name('scoring.edit');
    Route::get('/{employee}/show', [App\Http\Controllers\EmployeeScoringController::class, 'show'])->name('scoring.show');
    Route::post('/store', [App\Http\Controllers\EmployeeScoringController::class, 'store'])->name('scoring.store');
    Route::delete('/{assessment}', [App\Http\Controllers\EmployeeScoringController::class, 'destroy'])->name('scoring.destroy');
    Route::delete('/bulk-delete', [App\Http\Controllers\EmployeeScoringController::class, 'bulkDelete'])->name('scoring.bulk-delete');
});

// Results viewing routes - accessible by Admin and Pemimpin Perusahaan
Route::middleware(['auth'])->group(function () {
    Route::get('/results', [App\Http\Controllers\EmployeeScoringController::class, 'results'])->name('results.index');
    Route::get('/results/ranking', [App\Http\Controllers\EmployeeScoringController::class, 'resultsRanking'])->name('results.ranking');
    Route::get('/results/export', [App\Http\Controllers\EmployeeScoringController::class, 'resultsExport'])->name('results.export');
    Route::get('/results/summary', [App\Http\Controllers\EmployeeScoringController::class, 'getResultsSummary'])->name('results.summary');
    Route::get('/results/saw-details', [App\Http\Controllers\EmployeeScoringController::class, 'getSAWDetails'])->name('results.saw-details');
    Route::get('/results/{employee}/show', [App\Http\Controllers\EmployeeScoringController::class, 'resultsShow'])->name('results.show');
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
