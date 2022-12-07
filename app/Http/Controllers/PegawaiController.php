<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Abilities;
use App\Models\AbilityMenu;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\UserPegawai;
use App\Models\User;
use Validator;

class PegawaiController extends Controller
{
    public function pegawai(Request $request) {
        $limit = $request->limit ? $request->limit : 10;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = Pegawai::select('nip', 'nama', 'jk', 'alamat', 'idbiro', 'idjabatan')
            ->with('unitkerja')
            ->with('jabatan')
            ->with('userPegawai.user')
            ->limit($limit)
            ->offset($offset)
            ->get();
        if (count($fetch) > 1) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [],
            ],200);
        }
        foreach($fetch as $pegawai) {
            $data[] = [
                'id_user' => $pegawai->userPegawai->user->id,
                'nip' => $pegawai->nip,
                'nama' => $pegawai->nama,
                'email' => $pegawai->userPegawai->user->email,
                'jk' => $pegawai->jk,
                'alamat' => $pegawai->alamat,
                'unit_kerja' => $pegawai->unitkerja->unitkerja,
                'jabatan' => $pegawai->jabatan->namajabatan,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role_id' => 'required|integer',
            'nama' => 'required',
            'nip' => 'required|unique:pegawai',
            'jk' => 'required',
            'alamat' => 'required',
            'idbiro' => 'required',
            'idjabatan' => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        DB::beginTransaction();
        try{
            $user = new User();
            $user->email = $request->email;
            $user->role_id = $request->role_id;
            $user->password = bcrypt($request->password);
            $user->save();
            $pegawai = new Pegawai();
            $pegawai->nip = $request->nip;
            $pegawai->nama = $request->nama;
            $pegawai->jk = $request->jk;
            $pegawai->alamat = $request->alamat;
            $pegawai->idbiro = $request->idbiro;
            $pegawai->idjabatan = $request->idjabatan;
            $pegawai->save();
            $userPegawai = new UserPegawai();
            $userPegawai->userid = $user->id;
            $userPegawai->nip = $request->nip;
            $userPegawai->save();
            $user->detail = $pegawai;
            DB::commit();
            return response()->json([
                'status' => 'created',
                'code' => 201,
                'message' => 'Sucessfully Created',
                'data' => $user
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function unitKerja() {
        $fetch = UnitKerja::all();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }

    public function jabatan() {
        $fetch = Jabatan::all();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }
}