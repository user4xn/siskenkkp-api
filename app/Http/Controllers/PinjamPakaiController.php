<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\KendaraanController;
use App\Models\Pinjam;
use App\Models\Kendaraan;
use App\Models\DetailPinjam;
use App\Models\DetailPengembalian;
use App\Models\FotoPinjam;
use Validator;

class PinjamPakaiController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function cariPinjaman (Request $request) {
        $KendaraanController = new KendaraanController();
        return $KendaraanController->kendaraan($request);
    }

    public function pinjaman (Request $request) {
        return $fetch = Pinjam::with(['detailPinjaman.detailKendaraan'])
            ->with('detailPengembalian')
            ->where('nip', $request->nip)
            ->get();

        $data = [];
        // foreach ($fetch as $all) {
        //     foreach($all->detail_pinjam as $pinjam) {
        //         $data[] = [
        //             ''
        //         ]
        //     }
        // }
    }

    public function storePinjaman (Request $request) {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|exists:pegawai,nip',
            'idkdrn.*' => 'required|exists:kendaraan,id',
            'kmsebelum.*' => 'required|integer',
            'foto.*.*' => 'mimes:jpg,jpeg,png,svg,webp|max:10240'
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
            $pinjam = new Pinjam();
            $pinjam->nip = $request->nip;
            $pinjam->tglpinjam = date('Y-m-d H:i:s');
            $pinjam->save();
            for($n=0; $n < count($request->idkdrn) ; $n++) {
                $checkStatusKendaraan = Kendaraan::where('id', $request->idkdrn[$n])->first();
                if($checkStatusKendaraan->status != 'Tersedia'){
                    DB::rollback();
                    return response()->json([
                        'status' => 'failed',
                        'code' => 400,
                        'message' => 'canceled, id kendaraan '.$request->idkdrn[$n].' is not available',
                    ], 400);
                }
                $detailPinjam = new DetailPinjam();
                $detailPinjam->idpinjam = $pinjam->id;
                $detailPinjam->idkdrn = $request->idkdrn[$n];
                $detailPinjam->tglpinjam = date('Y-m-d H:i:s');
                $detailPinjam->kmsebelum = $request->kmsebelum[$n];
                $detailPinjam->remark = $request->remark[$n];
                $detailPinjam->save();
                $images = $request->file('foto')[$n];
                if($images != null){
                    for ($i=0; $i < count($images) ; $i++) { 
                        $image = $images[$i];
                        $name = time().'.'.$image->getClientOriginalExtension();
                        $destinationPath = storage_path('../public/foto_pinjaman');
                        $imgFile = Image::make($image->getRealPath());
                        $imgFile->resize(700, 700, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$name);
                        $path = 'foto_pinjaman/'.$name;
                        $saveImage = new FotoPinjam();
                        $saveImage->reference_id = $detailPinjam->id;
                        $saveImage->type = 'Pinjaman';
                        $saveImage->urlfoto = env('APP_URL').$path;
                        $saveImage->save();
                    }
                }
                $updateStatusKendaraan = Kendaraan::where('id', $request->idkdrn[$n])->update([
                    'status' => 'Dipinjam'
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Successfully Store Data Pinjaman',
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
