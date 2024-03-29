<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Servis;
use App\Models\DetailServis;
use App\Models\JenisServis;
use App\Models\Kendaraan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KendaraanController;
use Validator;  

class ServisController extends Controller
{
    public function listKendaraan (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Service', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $KendaraanController = new KendaraanController();
        return $KendaraanController->kendaraan($request);
    }

    public function detailServis (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Service', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $fetchKendaraan = Kendaraan::select('id')->where(['id' => $request->id_kendaraan])->first();
        if($fetchKendaraan ==  null) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => "Invalid id_kendaraan",
            ],400);
        }
        $fetchServis = Servis::where(['idkdrn' => $request->id_kendaraan])
            ->with('detailServis.detailJenis')    
            ->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetchServis,
        ], 200);
    }

    public function storeServis (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Service', 'Create');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $validator = Validator::make($request->all(), [
            'idkdrn' => 'required|exists:kendaraan,id',
            'tgl' => 'required|date',
            'jaraktempuh' => 'required|integer',
            'nmbengkel' => 'required|string',
            'detail_servis' => 'required|array',
            'detail_servis.*.idjenisservis' => 'required|exists:jenis_servis,id',
            'detail_servis.*.description' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        DB::beginTransaction();
        try {
            $servis = new Servis();
            $servis->idkdrn = $request->idkdrn;
            $servis->tgl = $request->tgl;
            $servis->jaraktempuh = $request->jaraktempuh;
            $servis->nmbengkel = $request->nmbengkel;
            $servis->save();
            $getKendaraan = Kendaraan::where('id', $request->idkdrn)->first();
            if($getKendaraan->jaraktempuh < $request->jaraktempuh) {
                Kendaraan::where('id', $request->idkdrn)->update([
                    'jaraktempuh' => $request->jaraktempuh
                ]);
            }
            for($n=0; $n < count($request->detail_servis) ; $n++) {
                $detailServis = new DetailServis();
                $detailServis->idservis = $servis->id;
                $detailServis->idjenisservis = $request->detail_servis[$n]['idjenisservis'];
                $detailServis->description = $request->detail_servis[$n]['description'];
                $detailServis->save();
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Successfully Store Data Servis',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ], 400);
        }
    } 
}
