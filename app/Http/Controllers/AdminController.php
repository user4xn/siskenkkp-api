<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Abilities;
use App\Models\AbilityMenu;
use App\Models\User;
use App\Models\UserAbility;
use Validator;

class AdminController extends Controller
{
    public function __construct() {
        $this->middleware('auth_admin');
    }

    public function users (Request $request) {
        $fetch = User::select('id', 'email', 'role_id')
            ->with('roleDetail')
            ->with('userPegawai.detail')
            ->get();
        if (count($fetch) < 1) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [],
            ],200);
        }
        foreach($fetch as $user) {
            if($user->userPegawai) {
                $data[] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->roleDetail->name,
                    'nip' => $user->userPegawai->nip,
                    'nama' => $user->userPegawai->detail->nama,
                    'jk' => $user->userPegawai->detail->jk,
                    'alamat' => $user->userPegawai->detail->alamat,
                    'unit_kerja' => $user->userPegawai->detail->unitKerja->unitkerja,
                    'jabatan' => $user->userPegawai->detail->jabatan->namajabatan,
                    'createddate' => $user->userPegawai->detail->createddate,
                ];
            }else{
                $data[] = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->roleDetail->name,
                ];
            }
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data
        ], 200);
    }

    public function userDetailAbility (Request $request) {
        $getUserDetail = User::select('id', 'email', 'role_id')
            ->where('id', $request->user_id)
            ->with('roleDetail')
            ->with('userPegawai.detail')
            ->first();
        if(!$getUserDetail) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Invalid user id.',
            ],400);
        }
        $getAbility = UserAbility::where('user_id', $request->user_id)
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
        if($getUserDetail->userPegawai) {
            $data[] = [
                'id' => $getUserDetail->id,
                'email' => $getUserDetail->email,
                'role' => $getUserDetail->roleDetail->name,
                'nip' => $getUserDetail->userPegawai->nip,
                'nama' => $getUserDetail->userPegawai->detail->nama,
                'jk' => $getUserDetail->userPegawai->detail->jk,
                'alamat' => $getUserDetail->userPegawai->detail->alamat,
                'unit_kerja' => $getUserDetail->userPegawai->detail->unitKerja->unitkerja,
                'jabatan' => $getUserDetail->userPegawai->detail->jabatan->namajabatan,
                'createddate' => $getUserDetail->userPegawai->detail->createddate,
                'abilities' => $abilities,
            ];
        } else {
            $data[] = [
                'id' => $getUserDetail->id,
                'email' => $getUserDetail->email,
                'role' => $getUserDetail->roleDetail->name,
                'abilities' => $abilities,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function userUpdateAbility (Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'data' => 'required|array',
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
            $fetchAbilityMenu = AbilityMenu::all()->toArray();
            UserAbility::where('user_id', $request->user_id)->delete();
            $data_abilities = array_unique($request->data, SORT_REGULAR);
            foreach ($data_abilities as $ability) {
                $key = array_column($fetchAbilityMenu, 'id');
                $index = array_search($ability['ability_menu_id'], $key);
                if($index !== false) {
                    $userAbility = new UserAbility();
                    $userAbility->user_id = $request->user_id;
                    $userAbility->ability_id = $ability['ability_id'];
                    $userAbility->ability_menu_id = $ability['ability_menu_id'];
                    $userAbility->save();
                }else{
                    return response()->json([
                        'status' => 'failed',
                        'code' => 400,
                        'message' => 'Invalid Menu id',
                    ],400);
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Successfully Update User Ability',
                'data' => ['user_id' => $request->user_id]
            ],200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $th->getMessage(),
            ],400);
        }
    }
}