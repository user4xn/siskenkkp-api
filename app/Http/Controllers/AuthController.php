<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserAbility;
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
                'code' => 401,
                'message' => 'Unauthorized',
            ],401);
        }
        return $this->createNewToken($token);
    }
    
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role_id' => 'required|integer',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors()->toJson(),
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
        $dataUser = auth()->user();
        $getAbility = UserAbility::where('user_id', $dataUser->id)
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
            'user_id' => $dataUser->id,
            'name' => $dataUser->name,
            'email' => $dataUser->email,
            'email_verified_at' => $dataUser->email_verified_at,
            'role_id' => $dataUser->role_id,
            'abilities' => $abilities,
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }
        
    protected function createNewToken($token){
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 24,
            'user' => auth()->user()
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }
}