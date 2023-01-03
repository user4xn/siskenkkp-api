<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pinjam;
use App\Models\Bbm;
use Validator;

class BbmController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function storeBbm (Request $request) {
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
}
