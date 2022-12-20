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
            ->with('detailPengembalian.kendaraan')
            ->select('id', 'nip', 'tglpinjam')
            ->where('nip', $request->nip)
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->get();
        $data = [];
        foreach ($fetch as $pinjam) {
            $total_pijaman = count($pinjam->detailPinjaman);
            $total_pengembalian = count($pinjam->detailPengembalian);
            foreach ($pinjam->detailPinjaman as $dpj){
                $detailPinjam[] = [
                    'detail_pinjam_id' => $dpj->id,
                    'tgl_pinjam' => $dpj->tglpinjam,
                    'kmsebelum' => $dpj->kmsebelum,
                    'remark' => $dpj->remark,
                    'id_kendaraan' => $dpj->kendaraan->id,
                    'nopolisi' => $dpj->kendaraan->nopolisi,
                    'label' => $dpj->kendaraan->jenis->jenis.' '.$dpj->kendaraan->merk->merk.' '.$dpj->kendaraan->type->type,
                    'warna' => $dpj->kendaraan->warna,
                    'urlfoto' => $dpj->kendaraan->foto[0]->urlfoto,
                ];
            }
            foreach ($pinjam->detailPengembalian as $dpb){
                $detailKembali[] = [
                    'detail_pinjam_id' => $dpb->id,
                    'tgl_kembali' => $dpb->tglkembali,
                    'kmsesudah' => $dpb->kmsesudah,
                    'remark' => $dpb->remark,
                    'id_kendaraan' => $dpb->kendaraan->id,
                    'nopolisi' => $dpb->kendaraan->nopolisi,
                    'label' => $dpb->kendaraan->jenis->jenis.' '.$dpb->kendaraan->merk->merk.' '.$dpb->kendaraan->type->type,
                    'warna' => $dpb->kendaraan->warna,
                    'urlfoto' => $dpb->kendaraan->foto[0]->urlfoto,
                ];
            }
            $data[] = [
                'id_pinjam' => $pinjam->id,
                'nip' => $pinjam->nip,
                'tgl_pinjam' => $pinjam->tglpinjam,
                'total_pinjam' => $total_pijaman,
                'status_pinjaman' => $total_pengembalian == $total_pijaman ? 'Selesai' : 'Belum Selesai',
                'total_dikembalikan' => $total_pengembalian,
                'detail_pinjaman' => $detailPinjam,
                'detail_pengembalian' => $detailKembali,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ], 200);
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

    public function storePengembalian (Request $request) {
        $validator = Validator::make($request->all(), [
            'idpinjam' => 'required|integer|exists:pinjam,id',
            'idkdrn.*' => 'required|integer',
            'kmsesudah.*' => 'required|integer',
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
            for($n=0; $n < count($request->idkdrn) ; $n++) {
                $check = DetailPinjam::where(['idpinjam' => $request->idpinjam, 'idkdrn' => $request->idkdrn[$n]])->first();
                if(!$check) {
                    DB:rollback();
                    return response()->json([
                        'status' => 'failed',
                        'code' => 400,
                        'message' => 'Invalid idkdrn '.$request->idkdrn.' on idpinjam '.$request->idpinjam,
                    ],400);
                }
                $detailPengembalian = new DetailPengembalian();
                $detailPengembalian->idpinjam = $request->idpinjam;
                $detailPengembalian->idkdrn = $request->idkdrn[$n];
                $detailPengembalian->tglkembali = date('Y-m-d');
                $detailPengembalian->kmsesudah = $request->kmsesudah[$n];
                $detailPengembalian->remark = $request->remark[$n];
                $detailPengembalian->save();
                $images = $request->file('foto')[$n];
                if($images != null){
                    for ($i=0; $i < count($images) ; $i++) { 
                        $image = $images[$i];
                        $name = time().'.'.$image->getClientOriginalExtension();
                        $destinationPath = storage_path('../public/foto_pengembalian');
                        $imgFile = Image::make($image->getRealPath());
                        $imgFile->resize(700, 700, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath.'/'.$name);
                        $path = 'foto_pengembalian/'.$name;
                        $saveImage = new FotoPinjam();
                        $saveImage->reference_id = $detailPengembalian->id;
                        $saveImage->type = 'Pengembalian';
                        $saveImage->urlfoto = env('APP_URL').$path;
                        $saveImage->save();
                    }
                }
                $updateStatusKendaraan = Kendaraan::where('id', $request->idkdrn[$n])->update([
                    'status' => 'Tersedia',
                    'jaraktempuh' => $request->kmsesudah[$n],
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Successfully Store Data Pengembalian',
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
        $fetch = Pinjam::with('detailPinjaman.detailKendaraan')
            ->with('detailPinjaman.fotoPinjam')
            ->with('detailPengembalian.detailKendaraan')
            ->with('detailPengembalian.fotoPinjam')
            ->select('id', 'nip', 'tglpinjam')
            ->where('id', $request->id_pinjaman)
            ->first();
        if($fetch == null) {
            return response()->json([
                'status' => 'success',
                'code' => 400,
                'message' => 'Invalid id pinjam',
            ], 400);
        }
        $total_pijaman = count($fetch->detailPinjaman);
        $total_pengembalian = count($fetch->detailPengembalian);
        foreach ($fetch->detailPinjaman as $dpj){
            $checkPengembalian = DetailPengembalian::where(['idpinjam' => $request->id_pinjaman, 'idkdrn' => $dpj->detailKendaraan->id])->count();
            $detailPinjam[] = [
                'detail_pinjam_id' => $dpj->id,
                'tgl_pinjam' => $dpj->tglpinjam,
                'kmsebelum' => $dpj->kmsebelum,
                'remark' => $dpj->remark,
                'status_pinjaman' => $checkPengembalian > 0 ? 'Sudah Dikembalikan' : 'Belum Dikembalikan',
                'id_kendaraan' => $dpj->detailKendaraan->id,
                'nobpkb' => $dpj->detailKendaraan->nobpkb,
                'nomesin' => $dpj->detailKendaraan->nomesin,
                'norangka' => $dpj->detailKendaraan->norangka,
                'nopolisi' => $dpj->detailKendaraan->nopolisi,
                'thnkdrn' => $dpj->detailKendaraan->thnkdrn,
                'tglpajak' => $dpj->detailKendaraan->tglpajak,
                'tglmatipajak' => $dpj->detailKendaraan->tglmatipajak,
                'merk' => $dpj->detailKendaraan->merk->merk,
                'jenis' => $dpj->detailKendaraan->jenis->jenis,
                'type' => $dpj->detailKendaraan->type->type,
                'warna' => $dpj->detailKendaraan->warna,
                'foto_kendaraan' => $dpj->detailKendaraan->foto,
                'foto_peminjaman' => $dpj->fotoPinjam,  
            ];
        }
        foreach ($fetch->detailPengembalian as $dpb){
            $detailKembali[] = [
                'detail_pinjam_id' => $dpb->id,
                'tgl_kembali' => $dpb->tglkembali,
                'kmsesudah' => $dpb->kmsesudah,
                'remark' => $dpb->remark,
                'id_kendaraan' => $dpb->detailKendaraan->id,
                'nobpkb' => $dpb->detailKendaraan->nobpkb,
                'nomesin' => $dpb->detailKendaraan->nomesin,
                'norangka' => $dpb->detailKendaraan->norangka,
                'nopolisi' => $dpb->detailKendaraan->nopolisi,
                'thnkdrn' => $dpb->detailKendaraan->thnkdrn,
                'tglpajak' => $dpb->detailKendaraan->tglpajak,
                'tglmatipajak' => $dpb->detailKendaraan->tglmatipajak,
                'merk' => $dpb->detailKendaraan->merk->merk,
                'jenis' => $dpb->detailKendaraan->jenis->jenis,
                'type' => $dpb->detailKendaraan->type->type,
                'warna' => $dpb->detailKendaraan->warna,
                'foto_kendaraan' => $dpb->detailKendaraan->foto,
                'foto_peminjaman' => $dpb->fotoPinjam,  
            ];
        }
        $data = [
            'id_pinjam' => $fetch->id,
            'nip' => $fetch->nip,
            'tgl_pinjam' => $fetch->tglpinjam,
            'total_pinjam' => $total_pijaman,
            'total_dikembalikan' => $total_pengembalian,
            'status_pinjaman' => $total_pengembalian == $total_pijaman ? 'Selesai' : 'Belum Selesai',
            'detail_pinjaman' => $detailPinjam,
            'detail_pengembalian' => $detailKembali,
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ], 200);
    }
}
