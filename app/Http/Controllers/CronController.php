<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pinjam;
use App\Models\Kendaraan;
use App\Models\DetailPinjam;
use Validator;

class CronController extends Controller
{
    public function pengembalianPinjamPakai (Request $request) {
        DB::beginTransaction();
        try {
            $now = date('Y-m-d');
            $fetch = Pinjam::with('detailPinjaman')->where('tglpengembalian', $now)->get();
            if (count($fetch) < 1) {
                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'no updated data',
                ],200);
            }
            $idkdrn = [];
            foreach ($fetch as $pinjaman) {
                foreach ($pinjaman->detailPinjaman as $detail) {
                    $idkdrn[] = $detail->idkdrn;
                }
            }
            $update = Kendaraan::whereIn('id', $idkdrn)->update(['status' => 'Tersedia']);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'updated '.count($idkdrn).' data',
            ],200);
        } catch (\Throwable $th) {
            DB::rolback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ],400);
        }
    }
}
