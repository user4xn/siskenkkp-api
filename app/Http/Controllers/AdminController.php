<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
   
    public function abilities(Request $request) {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => Abilities::all()
        ], 200);
    }

    public function abilityMenu(Request $request) {
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => AbilityMenu::all()
        ], 200);
    }

    public function users(Request $request) {
        $fetch = User::select('id', 'name', 'email', 'email_verified_at', 'role_id')->get();
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch
        ], 200);
    }
}