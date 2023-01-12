<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\AuthController;
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
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $KendaraanController = new KendaraanController();
        return $KendaraanController->kendaraan($request);
    }

    public function pinjaman (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $validator = Validator::make($request->all(), [
            'nip' => 'required|exists:pegawai,nip',
            'start_date' => 'date',
            'end_date' => 'date',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $fetch = Pinjam::with(['detailPinjaman.kendaraan'])
            ->select(
                'id', 
                'nip',
                'tglpinjam',
                'es1',
                'es2',
                'es3',
                'es4',
                'status',
                'nippenanggungjawab',
                'nippemakai',
                'nippenyetuju',
                'es4',
                'jenispinjam',
                'tglpengembalian'
            )
            ->where('nip', $request->nip)
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->with('penanggungJawab')
            ->with('pemakai')
            ->with('penyetuju')
            ->where('jenispinjam', strtoupper($request->tipe))
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->get();
        $data = [];
        foreach ($fetch as $pinjam) {
            $total_pijaman = count($pinjam->detailPinjaman);
            $total_pengembalian = count($pinjam->detailPengembalian);
            $detailPinjam = [];
            foreach ($pinjam->detailPinjaman as $dpj){
                $jenis_dpj = $dpj->kendaraan->jenis ? $dpj->kendaraan->jenis->jenis : '{jenis}';
                $merk_dpj = $dpj->kendaraan->merk ? $dpj->kendaraan->merk->merk : '{merk}';
                $type_dpj = $dpj->kendaraan->type ? $dpj->kendaraan->type->type : '{type}';
                $detailPinjam[] = [
                    'detail_pinjam_id' => $dpj->id,
                    'tgl_pinjam' => $dpj->tglpinjam,
                    'kmsebelum' => $dpj->kmsebelum,
                    'remark' => $dpj->remark,
                    'id_kendaraan' => $dpj->kendaraan->id,
                    'nomor_sk' => $dpj->nomorsk,
                    'nopolisi' => $dpj->kendaraan->nopolisi,
                    'label' => $jenis_dpj.' '.$merk_dpj.' '.$type_dpj,
                    'warna' => $dpj->kendaraan->warna,
                    'urlfoto' => $dpj->kendaraan->foto[0]->urlfoto,
                ];
            }
            $data[] = [
                'id_pinjam' => $pinjam->id,
                'nip' => $pinjam->nip,
                'penanggung_jawab' => $pinjam->penanggungJawab->nama,
                'pemakai' => $pinjam->pemakai->nama,
                'penyetuju' => $pinjam->penyetuju ? $pinjam->penyetuju->nama : '',
                'status_pengajuan' => $pinjam->status,
                'tgl_pinjam' => $pinjam->tglpinjam,
                'tgl_pengembalian' => $pinjam->tglpengembalian,
                'jenispinjam' => $pinjam->jenispinjam,
                'detail_pinjaman' => $detailPinjam,
                'es1' => ['id' => $pinjam->es1,'name' => ucwords($pinjam->eselon1->nama)],
                'es2' => ['id' => $pinjam->es2,'name' => ucwords($pinjam->eselon2->nama)],
                'es3' => ['id' => $pinjam->es3,'name' => ucwords($pinjam->eselon3->nama)],
                'es4' => ['id' => $pinjam->es4,'name' => ucwords($pinjam->eselon4->nama)],
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    public function storePinjaman (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai', 'Create');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai KOJ', 'Create');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $validator = Validator::make($request->all(), [
            'nip' => 'required|exists:pegawai,nip',
            'es1' => 'required|exists:eselon,id,nip,'.$request->nip,
            'es2' => 'required|exists:eselon,id,nip,'.$request->nip,
            'es3' => 'required|exists:eselon,id,nip,'.$request->nip,
            'es4' => 'required|exists:eselon,id,nip,'.$request->nip,
            'jenispinjam' => 'required',
            'nippenanggungjawab' => 'required',
            'nippemakai' => 'required',
            'tglpengembalian' => 'date|required_if:jenispinjam,==,PPKO',
            'idkdrn.*' => 'required|exists:kendaraan,id',
            'kmsebelum.*' => 'required|integer',
            'nomorsk' => 'required_if:jenispinjam,==,KOJ',
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
            $pinjam->es1 = $request->es1;
            $pinjam->es2 = $request->es2;
            $pinjam->es3 = $request->es3;
            $pinjam->es4 = $request->es4;
            $pinjam->nippenyetuju = null;
            $pinjam->nippenanggungjawab = $request->nippenanggungjawab;
            $pinjam->nippemakai = $request->nippemakai;
            $pinjam->jenispinjam = $request->jenispinjam;
            $pinjam->tglpengembalian = $request->tglpengembalian ? $request->tglpengembalian : null;
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
                $detailPinjam->nomorsk = $request->nomorsk ? $request->nomorsk[$n] : null;
                $detailPinjam->save();
                $images = $request->file('foto') ? $request->file('foto')[$n] : null;
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

    public function detailPinjaman (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $fetch = Pinjam::with('detailPinjaman.detailKendaraan')
            ->with('detailPinjaman.fotoPinjam')
            ->with('detailPinjaman.detailBbm')
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->with('penanggungJawab')
            ->with('pemakai')
            ->with('penyetuju')
            ->select(
                'id', 
                'nip',
                'tglpinjam',
                'es1',
                'es2',
                'es3',
                'es4',
                'status',
                'nippenanggungjawab',
                'nippemakai',
                'nippenyetuju',
                'es4',
                'jenispinjam',
                'tglpengembalian'
            )
            ->where('id', $request->id_pinjaman)
            ->first();
        if($fetch == null) {
            return response()->json([
                'status' => 'success',
                'code' => 400,
                'message' => 'Invalid id pinjam',
            ], 400);
        }
        $detailPinjam = [];
        foreach ($fetch->detailPinjaman as $dpj){
            $detailPinjam[] = [
                'detail_pinjam_id' => $dpj->id,
                'nomorsk' => $dpj->nomorsk,
                'tgl_pinjam' => $dpj->tglpinjam,
                'kmsebelum' => $dpj->kmsebelum,
                'remark' => $dpj->remark,
                'id_kendaraan' => $dpj->detailKendaraan->id,
                'nobpkb' => $dpj->detailKendaraan->nobpkb,
                'nomesin' => $dpj->detailKendaraan->nomesin,
                'norangka' => $dpj->detailKendaraan->norangka,
                'nopolisi' => $dpj->detailKendaraan->nopolisi,
                'thnkdrn' => $dpj->detailKendaraan->thnkdrn,
                'tglpajak' => $dpj->detailKendaraan->tglpajak,
                'tglmatipajak' => $dpj->detailKendaraan->tglmatipajak,
                'merk' => $dpj->detailKendaraan->merk ? $dpj->detailKendaraan->merk->merk : false,
                'jenis' => $dpj->detailKendaraan->jenis ? $dpj->detailKendaraan->jenis->jenis : false,
                'type' => $dpj->detailKendaraan->type ? $dpj->detailKendaraan->type->type : false,
                'warna' => $dpj->detailKendaraan->warna,
                'foto_kendaraan' => $dpj->detailKendaraan->foto,
                'foto_peminjaman' => $dpj->fotoPinjam, 
                'data_bbm' => $dpj->detailBbm,  
            ];
        }
        $data = [
            'id_pinjam' => $fetch->id,
            'nip' => $fetch->nip,
            'es1' => ['id' => $fetch->es1,'name' => ucwords($fetch->eselon1->nama)],
            'es2' => ['id' => $fetch->es2,'name' => ucwords($fetch->eselon2->nama)],
            'es3' => ['id' => $fetch->es3,'name' => ucwords($fetch->eselon3->nama)],
            'es4' => ['id' => $fetch->es4,'name' => ucwords($fetch->eselon4->nama)],
            'penanggung_jawab' => $fetch->penanggungJawab->nama,
            'pemakai' => $fetch->pemakai->nama,
            'penyetuju' => $fetch->penyetuju ? $fetch->penyetuju->nama : '',
            'status_pengajuan' => $fetch->status,
            'tgl_pinjam' => $fetch->tglpinjam,
            'tgl_pengembalian' => $fetch->tglpengembalian,
            'jenispinjam' => $fetch->jenispinjam,
            'detail_pinjaman' => $detailPinjam,
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    public function lastestRecord (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $validator = Validator::make($request->all(), [
            'nip' => 'required|exists:pegawai,nip',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $fetch = Pinjam::select('id', 'nip', 'tglpinjam', 'es1', 'es2', 'es3', 'es4', 'jenispinjam', 'tglpengembalian')
            ->where('nip', $request->nip)
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->orderBy('created_at', 'DESC')
            ->first();
        $response = [
            'lastidpinjaman' => $fetch->id,
            'nip' => $fetch->nip,
            'tglpinjam' => $fetch->tglpinjam,
            'jenispinjam' => $fetch->jenispinjam,
            'es1' => ['id' => $fetch->es1,'name' => ucwords($fetch->eselon1->nama)],
            'es2' => ['id' => $fetch->es2,'name' => ucwords($fetch->eselon2->nama)],
            'es3' => ['id' => $fetch->es3,'name' => ucwords($fetch->eselon3->nama)],
            'es4' => ['id' => $fetch->es4,'name' => ucwords($fetch->eselon4->nama)],
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response,
        ], 200);
    }
}
