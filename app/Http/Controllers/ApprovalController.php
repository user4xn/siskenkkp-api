<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Models\User;
use App\Models\Kendaraan;
use App\Models\Pinjam;
use App\Models\DetailPinjam;
use App\Models\Eselon;
use App\Http\Controllers\AuthController;
use Validator;

class ApprovalController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function approvalPinjaman (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Approval Pinjam Pakai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $validator = Validator::make($request->all(), [
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
                'catatan',
                'nippenanggungjawab',
                'nippemakai',
                'nippenyetuju',
                'es4',
                'jenispinjam',
                'tglpengembalian'
            )
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->with('penanggungJawab')
            ->with('pemakai')
            ->with('penyetuju')
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->when($request->status, function ($query) use ($request){
                return $query->where('status', $request->status);
            })
            ->orderBy('pinjam.created_at', 'DESC')
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
                'catatan' => $pinjam->catatan,
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

    public function detailApproval (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Approval Pinjam Pakai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
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
                'catatan',
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
            'catatan' => $fetch->catatan,
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

    public function approvePinjaman (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Approval Pinjam Pakai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $dataUser = auth()->user();
            $getUserDetail = User::select('id', 'email', 'role_id')
                ->where('id', $dataUser->id)
                ->with('userPegawai')
                ->first();
            $nippenyetuju = $getUserDetail->userPegawai->nip;
            $update = Pinjam::where('id', $request->id_pinjam)->update([
                'nippenyetuju' => $nippenyetuju,
                'status' => 'Disetujui'
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Approved Successfully',
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

    public function rejectPinjaman (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Approval Pinjam Pakai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $validator = Validator::make($request->all(), [
            'catatan' => 'required',
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
            $dataUser = auth()->user();
            $fetchKendaraan = DetailPinjam::where('idpinjam', $request->id_pinjam)->get();
            $idskendaraan = [];
            foreach($fetchKendaraan as $each) {
                $idskendaraan[] = $each->idkdrn;
            }
            $kendaraan = Kendaraan::whereIn('id', $idskendaraan)->update([
                'status' => 'Tersedia'
            ]);
            $getUserDetail = User::select('id', 'email', 'role_id')
                ->where('id', $dataUser->id)
                ->with('userPegawai')
                ->first();
            $nippenyetuju = $getUserDetail->userPegawai->nip;
            $update = Pinjam::where('id', $request->id_pinjam)->update([
                'nippenyetuju' => $nippenyetuju,
                'status' => 'Ditolak',
                'catatan' => $request->catatan
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Rejected Successfully',
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