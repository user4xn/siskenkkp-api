<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pinjam;
use App\Models\Bbm;
use App\Http\Controllers\AuthController;
use Validator;

class BbmController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function bbm (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Bahan Bakar', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $checkAbility = (new AuthController)->checkAbility('Kendaraan', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $fetch = Bbm::with('detailPinjam.kendaraan')
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                return $query->whereBetween(DB::raw('DATE(created_at)'), [$request->start_date, $request->end_date]);
            })
            ->get();
        if(count($fetch) < 1) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [],
            ],200);
        }
        $response = [];
        foreach ($fetch as $bbm) {
            $jenis = $bbm->detailPinjam->kendaraan->jenis ? $bbm->detailPinjam->kendaraan->jenis->jenis : '{jenis}';
            $merk = $bbm->detailPinjam->kendaraan->merk ? $bbm->detailPinjam->kendaraan->merk->merk : '{merk}';
            $type = $bbm->detailPinjam->kendaraan->type ? $bbm->detailPinjam->kendaraan->type->type : '{type}';
            $response[] = array(
                'id' => $bbm->id,
                'tanggal_input' => date('d-m-Y', strtotime($bbm->created_at)),
                'id_detail_pinjam' => $bbm->iddetailpinjam,
                'id_kdrn' => $bbm->detailPinjam->idkdrn,
                'label' => $jenis.' '.$merk.' '.$type,
                'nopolisi' => $bbm->detailPinjam->kendaraan->nopolisi,
                'foto_kdrn' => $bbm->detailPinjam->kendaraan->foto[0]->urlfoto,
                'km_sebelum' => $bbm->kmsebelum,
                'km_sesudah' => $bbm->kmsesudah,
                'km_spt' => $bbm->kmspt,
                'sisa_km' => $bbm->sisakm,
                'jml_liter' => $bbm->jmlliter,
                'harga' => $bbm->harga,
                'total' => $bbm->total,
            );
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response,
        ],200);
    }

    public function storeBbm (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Bahan Bakar', 'Create');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $validator = Validator::make($request->all(), [
            'iddetailpinjam' => 'required|exists:detail_pinjam,id',
            'kmsebelum' => 'required|integer',
            'kmsesudah' => 'required|integer',
            'kmspt' => 'required|integer',
            'sisakm' => 'required|integer',
            'jmlliter' => 'required|integer',
            'harga' => 'required|integer',
            'total' => 'required|integer',
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
            $bbm = new Bbm();
            $bbm->iddetailpinjam = $request->iddetailpinjam;
            $bbm->kmsebelum = $request->kmsebelum;
            $bbm->kmsesudah = $request->kmsesudah;
            $bbm->kmspt = $request->kmspt;
            $bbm->sisakm = $request->sisakm;
            $bbm->jmlliter = $request->jmlliter;
            $bbm->harga = $request->harga;
            $bbm->total = $request->total;
            $bbm->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'successfully insert bbm data',
            ],200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
               'status' => 'failed',
                'code' => 400,
               'message' => $th->getMessage(),
            ],400);
        }
    }

    public function updateBbm (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Bahan Bakar', 'Update');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $fetch = Bbm::where(['id' => $request->id_bbm])->first();
            if($fetch == null) {
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'invalid id',
                ],400);
            }
            $update = $fetch->where(['id' => $request->id_bbm])->update([
                'kmsebelum' => $request->kmsebelum,
                'kmsesudah' => $request->kmsesudah,
                'kmspt' => $request->kmspt,
                'sisakm' => $request->sisakm,
                'jmlliter' => $request->jmlliter,
                'harga' => $request->harga,
                'total' => $request->total,
            ]);
            if($update) {
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'successfully updated data bbm',
                ],200);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function deleteBbm (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Bahan Bakar', 'Delete');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $delete = Bbm::where('id', $request->id_bbm)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Deleted Successfully',
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
