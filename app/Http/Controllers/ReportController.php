<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pinjam;
use App\Http\Controllers\AuthController;
use Validator;  

class ReportController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function pinjamPakai (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Laporan Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Laporan Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $validator = Validator::make($request->all(), [
            'idkdrn' => 'integer|exists:kendaraan,id',
            'nip' => 'integer|exists:pegawai,nip',
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
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = Pinjam::with(['detailPinjaman.kendaraan'])
            ->with('detailPegawai')
            ->select('id', 'nip', 'tglpinjam', 'catatan', 'jenispinjam', 'nippemakai')
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->when($request->nip, function ($query) use ($request) {
                return $query->where('nip', '=', $request->nip);
            })
            ->with('pemakai')
            ->where('jenispinjam', $request->tipe)
            ->where('status', 'Disetujui')
            ->limit($limit)
            ->offset($offset)
            ->get();
        $response = [];
        foreach ($fetch as $pinjaman) {
            foreach ($pinjaman->detailPinjaman as $detail) {
                if($request->idkdrn != null){
                    if($detail->kendaraan->id == $request->idkdrn) {
                        $response[] = [
                            'idpinjam' => $pinjaman->id,
                            'jenis' => $detail->kendaraan->jenis ? $detail->kendaraan->jenis->jenis : false,
                            'merk' => $detail->kendaraan->merk ? $detail->kendaraan->merk->merk : false,
                            'type' => $detail->kendaraan->type ? $detail->kendaraan->type->type : false,
                            'nopolisi' => $detail->kendaraan->nopolisi,
                            'tglpinjam' => $pinjaman->tglpinjam,
                            'jenispinjam' => $pinjaman->jenispinjam,
                            'catatan' => $pinjaman->catatan,
                            'penanggungjawab' => $pinjaman->nip.' - '.$pinjaman->detailPegawai->nama,
                            'pemakai' => $pinjaman->nippemakai.' - '.$pinjaman->pemakai->nama,
                            'unit_kerja' => $pinjaman->detailPegawai->unitKerja ? $pinjaman->detailPegawai->unitKerja->unitkerja : false,
                        ];
                    }
                } else {
                    $response[] = [
                        'idpinjam' => $pinjaman->id,
                        'jenis' => $detail->kendaraan->jenis ? $detail->kendaraan->jenis->jenis : false,
                        'merk' => $detail->kendaraan->merk ? $detail->kendaraan->merk->merk : false,
                        'type' => $detail->kendaraan->type ? $detail->kendaraan->type->type : false,
                        'nopolisi' => $detail->kendaraan->nopolisi,
                        'tglpinjam' => $pinjaman->tglpinjam,
                        'jenispinjam' => $pinjaman->jenispinjam,
                        'catatan' => $pinjaman->catatan,
                        'penanggungjawab' => $pinjaman->nip.' - '.$pinjaman->detailPegawai->nama,
                        'pemakai' => $pinjaman->nippemakai.' - '.$pinjaman->pemakai->nama,
                        'unit_kerja' => $pinjaman->detailPegawai->unitKerja ? $pinjaman->detailPegawai->unitKerja->unitkerja : false,
                    ];
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response,
        ], 200);
    }

    public function reportPinjamPakai (Request $request) {
        if($request->tipe == 'ppko'){
            $checkAbility = (new AuthController)->checkAbility('Laporan Pinjam Pakai', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        } else {
            $checkAbility = (new AuthController)->checkAbility('Laporan Pinjam Pakai KOJ', 'View');
            if(!$checkAbility){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Unauthorized User Ability',
                ],400);
            }
        }
        $fetch = Pinjam::with(['detailPinjaman.detailKendaraan'])
            ->with('detailPegawai')
            ->where('id', $request->id_pinjaman)
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
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->with('penanggungJawab')
            ->with('pemakai')
            ->with('penyetuju')
            ->where('jenispinjam', $request->tipe)
            ->first();
        if ($fetch == null) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Invalid id',
            ], 400);
        }
        $response = [];
        foreach ($fetch->detailPinjaman as $detail) {
            $response = [
                'id' => $fetch->id,
                'nama' => $fetch->detailPegawai->nama,
                'nippemakai' => $fetch->nippemakai,
                'nippenanggungjawab' => $fetch->nippenanggungjawab,
                'catatan' => $fetch->catatan,
                'jabatan' => $fetch->detailPegawai->jabatan->namajabatan,
                'unitkerja' => $fetch->detailPegawai->unitKerja->unitkerja,
                'idkdrn'=> $detail->detailKendaraan->id,
                'jenis' => $detail->detailKendaraan->jenis ? $detail->detailKendaraan->jenis->jenis : false,
                'merk' => $detail->detailKendaraan->merk ? $detail->detailKendaraan->merk->merk : false,
                'type' => $detail->detailKendaraan->type ? $detail->detailKendaraan->type->type : false,
                'bahanbakar' => $detail->detailKendaraan->jenisbbm,
                'nobpkb'=> $detail->detailKendaraan->nobpkb,
                'nomesin'=> $detail->detailKendaraan->nomesin,
                'norangka'=> $detail->detailKendaraan->norangka,
                'nopolisi'=> $detail->detailKendaraan->nopolisi,
                'warna'=> $detail->detailKendaraan->warna,
                'kondisi'=> $detail->detailKendaraan->kondisi,
                'thnkdrn'=> $detail->detailKendaraan->thnkdrn,
                'tglpajak' => $detail->detailKendaraan->tglpajak,
                'jatuhtempo' => $detail->detailKendaraan->tglmatipajak,
                'foto' => $detail->detailKendaraan->foto,
                'es1' => ['id' => $fetch->es1,'name' => ucwords($fetch->eselon1->nama)],
                'es2' => ['id' => $fetch->es2,'name' => ucwords($fetch->eselon2->nama)],
                'es3' => ['id' => $fetch->es3,'name' => ucwords($fetch->eselon3->nama)],
                'es4' => ['id' => $fetch->es4,'name' => ucwords($fetch->eselon4->nama)],
                'penanggung_jawab' => $fetch->penanggungJawab ? $fetch->penanggungJawab->nama : '',
                'pemakai' => $fetch->pemakai ? $fetch->pemakai->nama : '',
                'penyetuju' => $fetch->penyetuju ? $fetch->penyetuju->nama : '',
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response,
        ], 200);
    }
}
