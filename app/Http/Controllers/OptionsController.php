<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Abilities;
use App\Models\AbilityMenu;
use App\Models\MerkKendaraan;
use App\Models\JenisKendaraan;
use App\Models\TypeKendaraan;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\JenisServis;
use App\Models\Kendaraan;
use App\Models\Pegawai;
use App\Models\Pinjam;
use App\Models\DetailServis;
use Validator;

class OptionsController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }
    
    public function dataAbilities (Request $request) {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => Abilities::all()
        ], 200);
    }

    public function dataAbilityMenu (Request $request) {
        $fetch = AbilityMenu::select('id', 'parent_id', 'name')
            ->where('parent_id', 0)
            ->with('childMenu')
            ->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function dataMerk () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = MerkKendaraan::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeMerk (Request $request) {
        $validator = Validator::make($request->all(), [
            'merk' => 'required|string|unique:merk_kendaraan',
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
            $store = new MerkKendaraan();
            $store->merk = $request->merk;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteMerk (Request $request) {
        DB::beginTransaction();
        try {
            $check = Kendaraan::where('idmerkkdrn', $request->id_merk)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = MerkKendaraan::where('id', $request->id_merk)->delete();
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

    public function dataType () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = TypeKendaraan::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeType (Request $request) {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|unique:type_kendaraan',
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
            $store = new TypeKendaraan();
            $store->type = $request->type;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteType (Request $request) {
        DB::beginTransaction();
        try {
            $check = Kendaraan::where('idtypekdrn', $request->id_type)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = TypeKendaraan::where('id', $request->id_type)->delete();
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

    public function dataJenis () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = JenisKendaraan::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeJenis (Request $request) {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|string|unique:jenis_kendaraan',
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
            $store = new JenisKendaraan();
            $store->jenis = $request->jenis;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteJenis (Request $request) {
        DB::beginTransaction();
        try {
            $check = Kendaraan::where('idjeniskdrn', $request->id_jenis)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = JenisKendaraan::where('id', $request->id_jenis)->delete();
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

    public function dataUnitKerja () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = UnitKerja::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeUnitKerja (Request $request) {
        $validator = Validator::make($request->all(), [
            'unitkerja' => 'required|string|unique:unit_kerja',
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
            $store = new UnitKerja();
            $store->unitkerja = $request->unitkerja;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteUnitKerja (Request $request) {
        DB::beginTransaction();
        try {
            $check = Pegawai::where('idbiro', $request->id_unitkerja)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = UnitKerja::where('id', $request->id_unitkerja)->delete();
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

    public function dataJabatan () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = Jabatan::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeJabatan (Request $request) {
        $validator = Validator::make($request->all(), [
            'namajabatan' => 'required|string|unique:jabatan',
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
            $store = new Jabatan();
            $store->namajabatan = $request->namajabatan;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteJabatan (Request $request) {
        DB::beginTransaction();
        try {
            $check = Pegawai::where('idjabatan', $request->id_jabatan)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = Jabatan::where('id', $request->id_jabatan)->delete();
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

    public function dataJenisServis () {
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = JenisServis::limit($limit)->offset($offset)->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function storeJenisServis (Request $request) {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
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
            $store = new JenisServis();
            $store->description = $request->description;
            $store->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Stored Successfully',
                'data' => $store
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

    public function deleteJenisServis (Request $request) {
        DB::beginTransaction();
        try {
            $check = DetailServis::where('idjenisservis', $request->id_jenis_servis)->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = JenisServis::where('id', $request->id_jenis_servis)->delete();
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

    public function dataDetailPinjaman (Request $request) {
        $user = auth()->user();
        if($user->role_id != 1) {
            $validator = Validator::make($request->all(), [
                'nip' => 'required|integer',
            ]);
            if($validator->fails()){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => $validator->errors(),
                ],400);
            }
        }
        $fetch = Pinjam::with('detailPinjaman.detailKendaraan')
            ->select('id', 'nip', 'tglpinjam', 'es1', 'es2', 'es3', 'es4', 'jenispinjam', 'tglpengembalian')
            ->when($request->nip, function ($nip) use ($request) {
                return $nip->where('nip', $request->nip);
            })
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->get();
        $response = [];
        foreach ($fetch as $detail) {
            foreach ($detail->detailPinjaman as $detailPinjam) {
                if ($detailPinjam->detailKendaraan->status) {
                    $response[] = [
                        'iddetailpinjam' => $detailPinjam->id,
                        'tglpinjam' => $detailPinjam->tglpinjam,
                        'nopolisi' => $detailPinjam->detailKendaraan->nopolisi,
                    ];
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $response
        ], 200);
    }
}
