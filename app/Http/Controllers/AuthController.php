<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserAbility;
use App\Models\AbilityMenu;
use Validator;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
   
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors()->toJson(),
            ],400);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Invalid email or password',
            ],400);
        }
        return $this->createNewToken($token);
    }
    
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role_id' => 'required|integer',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
            'status' => 'created',
            'code' => 201,
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    public function logout() {
        auth()->logout();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'User successfully signed out'
        ],200); 
    }

    public function refresh() {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => auth()->refresh(),
        ],200);
    }
  
    public function userProfile() {
        try {
            $dataUser = auth()->user();
            $getUserDetail = User::select('id', 'email', 'role_id')
                ->where('id', $dataUser->id)
                ->with('roleDetail')
                ->with('userPegawai.detail')
                ->first();
            $getAbility = UserAbility::where('user_id', $dataUser->id)
                ->select('id', 'ability_id', 'ability_menu_id')
                ->with('abilities')
                ->with('abilityMenu')
                ->get();
            $getParent = AbilityMenu::where('parent_id', 0)
                ->select('id', 'name')
                ->get()
                ->toArray();
            $menus = [];
            foreach ($getAbility as $ability) {
                $keys = array_column($menus, 'menu_id');
                $index = array_search($ability->ability_menu_id, $keys);
                if($index !== false) {
                    $menus[$index] = [
                        'parent_id' => $ability->abilityMenu->parentMenu->id,
                        'menu_id' => $ability->ability_menu_id,
                        'menu_name' => $ability->abilityMenu->name,
                    ];
                }else{
                    $menus[] = [
                        'parent_id' => $ability->abilityMenu->parentMenu->id,
                        'menu_id' => $ability->ability_menu_id,
                        'menu_name' => $ability->abilityMenu->name,
                    ];
                }
            }
            foreach ($getAbility as $abs) {
                $keys = array_column($menus, 'menu_id');
                $index = array_search($abs->ability_menu_id, $keys);
                if($index !== false) {
                    $menus[$index]['abilities'][] = [
                        'data_id' => $abs->id,
                        'ability_id' => $abs->ability_id,
                        'ability_name' => $abs->abilities->ability_name,
                    ];
                }
            }
            foreach ($menus as $menu) {
                $keys = array_column($getParent, 'id');
                $index = array_search($menu['parent_id'], $keys);
                if($index !== false) {
                    $getParent[$index]['child_menu'][] = $menu;
                }
            }
            $abilities = [];
            foreach ($getParent as $each) {
                if (isset($each['child_menu'])) {
                    $abilities[] = $each;
                };
            }
            $data = [
                'id' => $getUserDetail->id,
                'email' => $getUserDetail->email,
                'role' => $getUserDetail->roleDetail->name,
                'nip' => $getUserDetail->userPegawai->nip,
                'nama' => $getUserDetail->userPegawai->detail->nama,
                'jk' => $getUserDetail->userPegawai->detail->jk,
                'alamat' => $getUserDetail->userPegawai->detail->alamat,
                'unit_kerja' => $getUserDetail->userPegawai->detail->unitKerja->unitkerja,
                'jabatan' => $getUserDetail->userPegawai->detail->jabatan->namajabatan,
                'created_at' => $getUserDetail->userPegawai->detail->created_at,
                'abilities' => $abilities,
            ];
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => $data,
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'data' => $th->getMessage(),
            ],400);
        }
    }
        
    protected function createNewToken($token){
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
            'user' => auth()->user()
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function checkAbility ($menu, $ability) {
        $dataUser = auth()->user();
        $getAbilitySU = AbilityMenu::where('name', $menu)->first();
        $checkAbility = UserAbility::join('abilities', 'ability_id', '=', 'abilities.id')
            ->where('ability_name', $ability)
            ->where(['user_id' => $dataUser->id, 'ability_menu_id' => $getAbilitySU->id])
            ->first();
        if(!$checkAbility){
            return false;
        }
        return true;
    }
}