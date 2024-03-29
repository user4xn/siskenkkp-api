<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Models\Abilities;
use App\Models\AbilityMenu;
use App\Models\UserAbility;
use App\Models\Pegawai;
use App\Models\Pinjam;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\UserPegawai;
use App\Models\User;
use App\Models\Roles;
use Validator;

class PegawaiController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function pegawai (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Data Pegawai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        if($request->search){
            $request->idbiro = null;
            $request->idjabatan = null;
        }
        $fetch = Pegawai::select('nip', 'nama', 'jk', 'alamat', 'idbiro', 'idjabatan')
            ->with('unitkerja')
            ->with('jabatan')
            ->with('userPegawai.user')
            ->when($request->idbiro, function ($query) use ($request){
                return $query->where('idbiro', $request->idbiro);
            })
            ->when($request->idjabatan, function ($query) use ($request){
                return $query->where('idjabatan', $request->idjabatan);
            })
            ->when($request->jk, function ($query) use ($request){
                return $query->where('jk', $request->jk);
            })
            ->when($request->search && !$request->idbiro && !$request->idjabatan, function ($query) use ($request){
                return $query->where('nama', 'LIKE', '%'.$request->search.'%')
                    ->orwhere('nip', 'LIKE', '%'.$request->search.'%');
            })
            ->get();
        if (count($fetch) < 1) {
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
                'unit_kerja' => $pegawai->unitkerja ? $pegawai->unitkerja->unitkerja : false,
                'jabatan' => $pegawai->jabatan ? $pegawai->jabatan->namajabatan : false,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function detailPegawai (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Data Pegawai', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $fetch = Pegawai::select('nip', 'nama', 'jk', 'alamat', 'idbiro', 'idjabatan')
            ->with('unitkerja')
            ->with('jabatan')
            ->with('userPegawai.user')
            ->where('nip', $request->nip)
            ->first();
        if (!$fetch) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Invalid NIP',
            ],400);
        }
        $getAbility = UserAbility::where('user_id', $fetch->userPegawai->userid)
            ->select('id', 'ability_id', 'ability_menu_id')
            ->with('abilities')
            ->with('abilityMenu')
            ->get();
        $abilities = [];
        foreach ($getAbility as $ability) {
            $abilities[] = [
                'data_id' => $ability->id,
                'ability_id' => $ability->ability_id,
                'ability_name' => $ability->abilities->ability_name,
                'menu_id' => $ability->ability_menu_id,
                'menu_name' => $ability->abilityMenu->name,
                'parent_menu' => $ability->abilityMenu->parentMenu->name,
            ];
        }
        $data = [
            'id_user' => $fetch->userPegawai->user->id,
            'nip' => $fetch->nip,
            'nama' => $fetch->nama,
            'email' => $fetch->userPegawai->user->email,
            'jk' => $fetch->jk,
            'alamat' => $fetch->alamat,
            'unit_kerja' => $fetch->unitkerja ? $fetch->unitkerja->unitkerja : false,
            'jabatan' => $fetch->jabatan ? $fetch->jabatan->namajabatan : false,
            'abilities' => $abilities
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function store (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Data Pegawai', 'Create');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|max:100|unique:users',
            'password' => 'string|confirmed|min:6',
            'role_id' => 'required|integer:exists:roles,id',
            'nama' => 'required',
            'nip' => 'required|unique:pegawai',
            'jk' => 'required',
            'alamat' => 'required',
            'idbiro' => 'required|exists:unit_kerja,id',
            'idjabatan' => 'required|exists:jabatan,id',
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
            if($request->abilities){
                $checkAbility = (new AuthController)->checkAbility('User Ability', 'Create');
                if(!$checkAbility){
                    return response()->json([
                        'status' => 'failed',
                        'code' => 400,
                        'message' => 'Unauthorized User Ability',
                    ],400);
                }
            }
            if($request->email != null) {
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
                if($request->abilities){
                    $data_abilities = $request->abilities;
                    foreach ($data_abilities as $ability) {
                        $userAbility = new UserAbility();
                        $userAbility->user_id = $user->id;
                        $userAbility->ability_id = $ability['ability_id'];
                        $userAbility->ability_menu_id = $ability['ability_menu_id'];
                        $userAbility->save();
                    }
                }
            } else {
                $name = explode(',', $request->nama);
                $lowered = strtolower($name[0]);
                $cleaned = preg_replace('/[^A-Za-z0-9\-]/', '', $lowered);
                $email = $cleaned.'@site.com';
                $user = new User();
                $user->email = $email;
                $user->role_id = $request->role_id;
                $user->password = bcrypt($request->nip);
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
                if($request->abilities){
                    $data_abilities = $request->abilities;
                    foreach ($data_abilities as $ability) {
                        $userAbility = new UserAbility();
                        $userAbility->user_id = $user->id;
                        $userAbility->ability_id = $ability['ability_id'];
                        $userAbility->ability_menu_id = $ability['ability_menu_id'];
                        $userAbility->save();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'created',
                'code' => 201,
                'message' => 'Sucessfully Created',
                'data' => $user
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function update (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Data Pegawai', 'Update');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        if($request->password){
            $validator = Validator::make($request->all(), [
                'idbiro' => 'exists:unit_kerja,id',
                'idjabatan' => 'exists:jabatan,id',
                'password' => 'string|confirmed|min:6',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'idbiro' => 'exists:unit_kerja,id',
                'idjabatan' => 'exists:jabatan,id',
            ]);
        }
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        DB::beginTransaction();
        try {
            if($request->abilities){
                $checkAbility = (new AuthController)->checkAbility('User Ability', 'Update');
                if(!$checkAbility){
                    return response()->json([
                        'status' => 'failed',
                        'code' => 400,
                        'message' => 'Unauthorized User Ability',
                    ],400);
                }
            }
            $dataUserPegawai = UserPegawai::where('nip', $request->nip)->first();
            $dataPegawai = Pegawai::select('nip', 'nama', 'jk', 'alamat', 'idbiro', 'idjabatan')
                ->where('nip', $request->nip)
                ->first();
            if ($request->password) {
                $dataUser = User::where('id', $dataUserPegawai->userid)->update([
                    'password' => bcrypt($request->password)
                ]);
            }
            if (!$dataPegawai) {
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Invalid NIP',
                ],400);
            }
            $dataPegawai->where('nip', $request->nip)->update([
                'nama' => $request->nama,
                'jk' => $request->jk,
                'alamat' => $request->alamat,
                'idbiro' => $request->idbiro,
                'idjabatan' => $request->idjabatan,
            ]);
            if($request->email){
                $updateUser = User::where('id', $dataUserPegawai->userid)->first();
                if($request->email != $updateUser->email){
                    $checkEmail = User::where('email', $request->email)->count();
                    if($checkEmail > 0){
                        return response()->json([
                            'status' => 'failed',
                            'code' => 400,
                            'message' => 'Email already taken',
                        ], 400);
                    }
                    $updateUser->where('id', $dataUserPegawai->userid)->update([
                        'email' => $request->email
                    ]);
                }
            }
            if($request->abilities){
                $fetchAbilityMenu = AbilityMenu::all()->toArray();
                UserAbility::where('user_id', $dataUserPegawai->userid)->delete();
                $data_abilities = array_unique($request->abilities, SORT_REGULAR);
                foreach ($data_abilities as $ability) {
                    $key = array_column($fetchAbilityMenu, 'id');
                    $index = array_search($ability['ability_menu_id'], $key);
                    $userAbility = new UserAbility();
                    $userAbility->user_id = $dataUserPegawai->userid;
                    $userAbility->ability_id = $ability['ability_id'];
                    $userAbility->ability_menu_id = $ability['ability_menu_id'];
                    $userAbility->save();
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Sucessfully Update',
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

    public function destroy (Request $request) {
        $checkAbility = (new AuthController)->checkAbility('Data Pegawai', 'Delete');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $userPegawai = UserPegawai::select('userid', 'nip')->where('userid', $request->user_id)->first();
            if($userPegawai == null) {
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Invalid User ID',
                ], 400);
            }
        $check = Pinjam::where('nip', $userPegawai->nip)
            ->orWhere('nippemakai', $userPegawai->nip)
            ->orWhere('nippenanggungjawab', $userPegawai->nip)
            ->orWhere('nippenyetuju', $userPegawai->nip)
            ->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $user = new User();
            $deletePegawai = $user->deleteAll($request->user_id, $userPegawai->nip);
            if($deletePegawai){
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Sucessfully delete',
                ], 200);
            }
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