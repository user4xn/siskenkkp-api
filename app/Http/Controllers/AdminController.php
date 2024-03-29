<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Abilities;
use App\Models\AbilityMenu;
use App\Models\User;
use App\Models\UserPegawai;
use App\Models\Pegawai;
use App\Models\UserAbility;
use App\Models\Pinjam;
use App\Http\Controllers\PinjamPakaiController;
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
                    'created_at' => $user->userPegawai->detail->created_at,
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
                'created_at' => $getUserDetail->userPegawai->detail->created_at,
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

    public function pinjaman (Request $request) {
        $validator = Validator::make($request->all(), [
            'start_date' => 'date',
            'end_date' => 'date',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'message' => $validator->errors(),
            ],400);
        }
        $fetch = Pinjam::with(['detailPinjaman.kendaraan'])
            ->select(
                'id', 
                'nip',
                'tglpinjam',
                'es1',
                'es2',
                'es3',
                'es4',
                'status',
                'catatan',
                'nippenanggungjawab',
                'nippemakai',
                'nippenyetuju',
                'es4',
                'jenispinjam',
                'tglpengembalian'
            )
            ->with('eselon1')
            ->with('eselon2')
            ->with('eselon3')
            ->with('eselon4')
            ->with('penanggungJawab')
            ->with('pemakai')
            ->with('penyetuju')
            ->where('jenispinjam', strtoupper($request->tipe))
            ->when($request->start_date && $request->end_date, function ($query) use ($request){
                return $query->whereBetween('tglpinjam', [$request->start_date, $request->end_date]);
            })
            ->orderBy('pinjam.created_at', 'DESC')
            ->get();
        $data = [];
        foreach ($fetch as $pinjam) {
            $total_pijaman = count($pinjam->detailPinjaman);
            $total_pengembalian = count($pinjam->detailPengembalian);
            $detailPinjam = [];
            foreach ($pinjam->detailPinjaman as $dpj){
                $jenis_dpj = $dpj->kendaraan->jenis ? $dpj->kendaraan->jenis->jenis : '{jenis}';
                $merk_dpj = $dpj->kendaraan->merk ? $dpj->kendaraan->merk->merk : '{merk}';
                $type_dpj = $dpj->kendaraan->type ? $dpj->kendaraan->type->type : '{type}';
                $detailPinjam[] = [
                    'detail_pinjam_id' => $dpj->id,
                    'tgl_pinjam' => $dpj->tglpinjam,
                    'kmsebelum' => $dpj->kmsebelum,
                    'remark' => $dpj->remark,
                    'id_kendaraan' => $dpj->kendaraan->id,
                    'nomor_sk' => $dpj->nomorsk,
                    'nopolisi' => $dpj->kendaraan->nopolisi,
                    'label' => $jenis_dpj.' '.$merk_dpj.' '.$type_dpj,
                    'warna' => $dpj->kendaraan->warna,
                    'urlfoto' => $dpj->kendaraan->foto[0]->urlfoto,
                ];
            }
            $data[] = [
                'id_pinjam' => $pinjam->id,
                'nip' => $pinjam->nip,
                'penanggung_jawab' => $pinjam->penanggungJawab ? $pinjam->penanggungJawab->nama : '',
                'pemakai' => $pinjam->pemakai ? $pinjam->pemakai->nama : '',
                'penyetuju' => $pinjam->penyetuju ? $pinjam->penyetuju->nama : '',
                'status_pengajuan' => $pinjam->status,
                'catatan_tolak' => $pinjam->catatan,
                'es1' => ['id' => $pinjam->es1,'name' => ucwords($pinjam->eselon1->nama)],
                'es2' => ['id' => $pinjam->es2,'name' => ucwords($pinjam->eselon2->nama)],
                'es3' => ['id' => $pinjam->es3,'name' => ucwords($pinjam->eselon3->nama)],
                'es4' => ['id' => $pinjam->es4,'name' => ucwords($pinjam->eselon4->nama)],
                'tgl_pinjam' => $pinjam->tglpinjam,
                'tgl_pengembalian' => $pinjam->tglpengembalian,
                'jenispinjam' => $pinjam->jenispinjam,
                'detail_pinjaman' => $detailPinjam,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ], 200);
    }

    public function storePinjaman (Request $request) {
        $PinjamPakaiController = new PinjamPakaiController();
        return $PinjamPakaiController->storePinjaman($request);
    }

    public function detailPinjaman (Request $request) {
        $PinjamPakaiController = new PinjamPakaiController();
        return $PinjamPakaiController->detailPinjaman($request);
    }

    public function lastestRecord (Request $request) {
        $PinjamPakaiController = new PinjamPakaiController();
        return $PinjamPakaiController->lastestRecord($request);
    }

    public function userCreateByPegawai () {
        DB::beginTransaction();
        try{
            $fetch = Pegawai::leftjoin('user_pegawai', 'user_pegawai.nip', '=', 'pegawai.nip')
            ->select('pegawai.*')
            ->whereRaw('user_pegawai.id IS NULL')
            ->limit(20)
            ->get();
            $num = 0;
            foreach ($fetch as $nonUser) {
                $num++;
                $name = explode(',', $nonUser->nama);
                $lowered = strtolower($name[0]);
                $cleaned = preg_replace('/[^A-Za-z0-9\-]/', '', $lowered);
                $email = $cleaned.'@site.com';
                $nip = $nonUser->nip;
                $user = new User();
                $user->email = $email;
                $user->role_id = 2;
                $user->password = bcrypt($nip);
                $user->save();
                $userPegawai = new UserPegawai();
                $userPegawai->userid = $user->id;
                $userPegawai->nip = $nip;
                $userPegawai->save();
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 400,
                'message' => 'created '.$num.' user by pegawai',
            ],400);
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