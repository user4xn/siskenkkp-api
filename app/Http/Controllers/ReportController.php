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
        $fetch = Pinjam::with(['detailPinjaman.kendaraan'])
            ->with('detailPegawai')
            ->select('id', 'nip', 'tglpinjam', 'catatan', 'jenispinjam')
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->when($request->nip, function ($query) use ($request) {
                return $query->where('nip', '=', $request->nip);
            })
            ->where('jenispinjam', $request->tipe)
            ->where('status', 'Disetujui')
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
                            'penanggungjawab' => $pinjaman->detailPegawai->nama,
                            'nip' => $pinjaman->detailPegawai->nip,
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
                        'penanggungjawab' => $pinjaman->detailPegawai->nama,
                        'nip' => $pinjaman->detailPegawai->nip,
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
            ->select('id', 'nip', 'tglpinjam')
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
                'nip' => $fetch->nip,
                'jabatan' => $fetch->detailPegawai->jabatan->namajabatan,
                'unitkerja' => $fetch->detailPegawai->unitKerja->unitkerja,
                'idkdrn'=> $detail->detailKendaraan->id,
                'jenis' => $detail->detailKendaraan->jenis ? $detail->detailKendaraan->jenis->jenis : false,
                'merk' => $detail->detailKendaraan->merk ? $detail->detailKendaraan->merk->merk : false,
                'type' => $detail->detailKendaraan->type ? $detail->detailKendaraan->type->type : false,
                'bahanbakar' => '',
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
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response,
        ], 200);
    }
}
