<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
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
    Route::get('/abilities', [AdminController::class, 'abilities']);
    Route::get('/ability-menu', [AdminController::class, 'abilityMenu']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/users/{user_id}', [AdminController::class, 'userDetailAbility']);
    Route::put('/users/update-ability', [AdminController::class, 'userUpdateAbility']);
});

Route::group(['middleware' => 'api','prefix' => 'v1'], function ($router) {
    Route::get('/pegawai', [PegawaiController::class, 'pegawai']);
    Route::get('/pegawai/options/unit-kerja', [PegawaiController::class, 'unitKerja']);
    Route::get('/pegawai/options/jabatan', [PegawaiController::class, 'jabatan']);
    Route::post('/pegawai/store', [PegawaiController::class, 'store']);
    Route::put('/pegawai/update/{id_user_pegawai}', [PegawaiController::class, 'update']);
});



Route::get('/unauthorized', function () {
    return response()->json([
        'status' => 'failed', 
        'code' => 401,
        'message' => 'Unauthorized.' 
    ], 401);
})->name('unauthorized');