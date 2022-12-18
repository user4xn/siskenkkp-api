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
use Validator;

class OptionsController extends Controller
{
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
        $fetch = MerkKendaraan::all();
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
        $fetch = TypeKendaraan::all();
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
        $fetch = JenisKendaraan::all();
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
        $fetch = UnitKerja::all();
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
        $fetch = Jabatan::all();
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
}
