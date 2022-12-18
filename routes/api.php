<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\OptionsController;
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

    Route::get('/pinjam-pakai', [PinjamPakaiController::class, 'pinjamPakai']);
    Route::get('/pinjam-pakai/inventaris', [PinjamPakaiController::class, 'merk']);
    Route::post('/pinjam-pakai/store', [PinjamPakaiController::class, 'store']);
    Route::get('/pijam-pakai/detail/{kendaraan_id}', [PinjamPakaiController::class, 'detailKendaraan']);
    Route::post('/pijam-pakai/update/{kendaraan_id}', [PinjamPakaiController::class, 'update']);
    Route::delete('/pijam-pakai/delete/{kendaraan_id}', [PinjamPakaiController::class, 'destroy']);
});



Route::get('/unauthorized', function () {
    return response()->json([
        'status' => 'failed', 
        'code' => 401,
        'message' => 'Unauthorized.' 
    ], 401);
})->name('unauthorized');