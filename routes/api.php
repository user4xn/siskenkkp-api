<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\PinjamPakaiController;
use App\Http\Controllers\ServisController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\BbmController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EselonController;
use App\Http\Controllers\ApprovalController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

Route::group(['middleware' => 'api','prefix' => 'admin'], function ($router) {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/users/{user_id}', [AdminController::class, 'userDetailAbility']);
    Route::put('/users/update-ability', [AdminController::class, 'userUpdateAbility']);
    Route::get('/pinjam-pakai/{tipe}', [AdminController::class, 'pinjaman']);
    Route::get('/pinjam-pakai/{tipe}/detail/{id_pinjaman}', [AdminController::class, 'detailPinjaman']);
    Route::post('/pinjam-pakai/{tipe}/pinjaman/store', [AdminController::class, 'storePinjaman']);
    Route::get('/pinjam-pakai/{tipe}/lastest-record', [AdminController::class, 'lastestRecord']);
});

Route::group(['middleware' => 'api','prefix' => 'cron'], function ($router) {
    Route::put('/pinjam-pakai/pengembalian', [CronController::class, 'pengembalianPinjamPakai']);
});

Route::group(['middleware' => 'api','prefix' => 'v1'], function ($router) {
    Route::get('/pegawai', [PegawaiController::class, 'pegawai']);
    Route::post('/pegawai/store', [PegawaiController::class, 'store']);
    Route::get('/pegawai/detail/{nip}', [PegawaiController::class, 'detailPegawai']);
    Route::put('/pegawai/update/{nip}', [PegawaiController::class, 'update']);
    Route::delete('/pegawai/delete/{user_id}', [PegawaiController::class, 'destroy']);

    Route::get('/kendaraan', [KendaraanController::class, 'kendaraan']);
    Route::post('/kendaraan/store', [KendaraanController::class, 'store']);
    Route::get('/kendaraan/detail/{kendaraan_id}', [KendaraanController::class, 'detailKendaraan']);
    Route::post('/kendaraan/update/{kendaraan_id}', [KendaraanController::class, 'update']);
    Route::delete('/kendaraan/delete/{kendaraan_id}', [KendaraanController::class, 'destroy']);

    Route::get('/eselon', [EselonController::class, 'eselon']);
    Route::post('/eselon/store', [EselonController::class, 'store']);
    Route::delete('/eselon/delete/{id_eselon}', [EselonController::class, 'destroy']);

    Route::get('/options/abilities', [OptionsController::class, 'dataAbilities']);
    Route::get('/options/ability-menu', [OptionsController::class, 'dataAbilityMenu']);
    Route::get('/options/merk', [OptionsController::class, 'dataMerk']);
    Route::post('/options/merk/store', [OptionsController::class, 'storeMerk']);
    Route::delete('/options/merk/{id_merk}/delete', [OptionsController::class, 'deleteMerk']);
    Route::get('/options/jenis', [OptionsController::class, 'dataJenis']);
    Route::post('/options/jenis/store', [OptionsController::class, 'storeJenis']);
    Route::delete('/options/jenis/{id_jenis}/delete', [OptionsController::class, 'deleteJenis']);
    Route::get('/options/type', [OptionsController::class, 'dataType']);
    Route::post('/options/type/store', [OptionsController::class, 'storeType']);
    Route::delete('/options/type/{id_type}/delete', [OptionsController::class, 'deleteType']);
    Route::get('/options/unit-kerja', [OptionsController::class, 'dataUnitKerja']);
    Route::post('/options/unit-kerja/store', [OptionsController::class, 'storeUnitKerja']);
    Route::delete('/options/unit-kerja/{id_unitkerja}/delete', [OptionsController::class, 'deleteUnitKerja']);
    Route::get('/options/jabatan', [OptionsController::class, 'dataJabatan']);
    Route::post('/options/jabatan/store', [OptionsController::class, 'storeJabatan']);
    Route::delete('/options/jabatan/{id_jabatan}/delete', [OptionsController::class, 'deleteJabatan']);
    Route::get('/options/jenis-servis', [OptionsController::class, 'dataJenisServis']);
    Route::post('/options/jenis-servis/store', [OptionsController::class, 'storeJenisServis']);
    Route::delete('/options/jenis-servis/{id_jenis_servis}/delete', [OptionsController::class, 'deleteJenisServis']);
    Route::get('/options/detail-pinjaman', [OptionsController::class, 'dataDetailPinjaman']);

    Route::get('/pinjam-pakai/{tipe}', [PinjamPakaiController::class, 'pinjaman']);
    Route::get('/pinjam-pakai/{tipe}/detail/{id_pinjaman}', [PinjamPakaiController::class, 'detailPinjaman']);
    Route::get('/pinjam-pakai/{tipe}/cari', [PinjamPakaiController::class, 'cariPinjaman']);
    Route::post('/pinjam-pakai/{tipe}/pinjaman/store', [PinjamPakaiController::class, 'storePinjaman']);
    Route::get('/pinjam-pakai/{tipe}/lastest-record', [PinjamPakaiController::class, 'lastestRecord']);

    Route::get('/approval-pinjaman', [ApprovalController::class, 'approvalPinjaman']);
    Route::get('/approval-pinjaman/detail/{id_pinjaman}', [ApprovalController::class, 'detailApproval']);
    Route::put('/approval-pinjaman/{id_pinjam}/approve', [ApprovalController::class, 'approvePinjaman']);
    Route::put('/approval-pinjaman/{id_pinjam}/reject', [ApprovalController::class, 'rejectPinjaman']);
    
    
    Route::get('/bahan-bakar', [BbmController::class, 'bbm']);
    Route::post('/bahan-bakar/store', [BbmController::class, 'storeBbm']);
    Route::put('/bahan-bakar/{id_bbm}/update', [BbmController::class, 'updateBbm']);
    Route::delete('/bahan-bakar/{id_bbm}/delete', [BbmController::class, 'deleteBbm']);

    Route::get('/servis', [ServisController::class, 'listKendaraan']);
    Route::get('/servis/detail/{id_kendaraan}', [ServisController::class, 'detailServis']);
    Route::post('/servis/store', [ServisController::class, 'storeServis']);

    Route::get('/report/pinjam-pakai', [ReportController::class, 'pinjamPakai']);
    Route::get('/report/pinjam-pakai/{id_pinjaman}/report', [ReportController::class, 'reportPinjamPakai']);
});

Route::group(['middleware' => 'api','prefix' => 'v2'], function ($router) {
    Route::get('/pinjam-pakai', [PinjamPakaiControllerV2::class, 'pinjamanV2']);
    Route::get('/pinjam-pakai/detail/{id_pinjaman}', [PinjamPakaiControllerV2::class, 'detailPinjamanV2']);
    Route::post('/pinjam-pakai/pinjaman/store', [PinjamPakaiControllerV2::class, 'storePinjamanV2']);
    Route::post('/pinjam-pakai/pengembalian/store', [PinjamPakaiControllerV2::class, 'storePengembalianV2']);
});

Route::any('/unauthorized', function () {
    return response()->json([
        'status' => 'failed', 
        'code' => 401,
        'message' => 'Unauthorized.' 
    ], 401);
})->name('unauthorized');