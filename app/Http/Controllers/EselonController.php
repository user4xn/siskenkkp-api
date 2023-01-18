<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Models\Pinjam;
use App\Models\Eselon;
use App\Http\Controllers\AuthController;
use Validator;

class EselonController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function eselon (Request $request) {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|exists:pegawai,nip',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $checkAbility = (new AuthController)->checkAbility('Data Unit Eselon', 'View');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        $limit = $request->limit ? $request->limit : 50;
        $offset = $request->offset ? $request->offset : 0;
        $fetch = Eselon::where('nip', $request->nip)
            ->when($request->tipe, function ($query) use ($request){
                return $query->where('tipe', $request->tipe);
            })
            ->select('id', 'nip', 'nama', 'tipe', 'created_at')
            ->limit($limit)
            ->offset($offset)
            ->get();
        if (count($fetch) < 1) {
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'data' => [],
            ],200);
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $fetch,
        ],200);
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|integer|exists:pegawai,nip',
            'nama' => 'required|string',
            'tipe' => 'required|string',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $checkAbility = (new AuthController)->checkAbility('Data Unit Eselon', 'Create');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $eselon = new Eselon();
            $eselon->nip = $request->nip;
            $eselon->nama = $request->nama;
            $eselon->tipe = $request->tipe;
            $eselon->save();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Sucessfully created eselon data'
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
        $checkAbility = (new AuthController)->checkAbility('Data Unit Eselon', 'Delete');
        if(!$checkAbility){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => 'Unauthorized User Ability',
            ],400);
        }
        DB::beginTransaction();
        try {
            $check = Pinjam::where('es1', $request->id_eselon)
                ->orWhere('es2', $request->id_eselon)
                ->orWhere('es3', $request->id_eselon)
                ->orWhere('es4', $request->id_eselon)
                ->count();
            if($check > 0){
                return response()->json([
                    'status' => 'failed',
                    'code' => 400,
                    'message' => 'Failed, Data Used',
                ], 400); 
            }
            $delete = Eselon::where('id', $request->id_eselon)->delete();
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