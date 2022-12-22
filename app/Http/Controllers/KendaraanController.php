<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use App\Models\Kendaraan;
use App\Models\MerkKendaraan;
use App\Models\JenisKendaraan;
use App\Models\TypeKendaraan;
use App\Models\Foto;
use Validator;

class KendaraanController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api');
    }

    public function kendaraan (Request $request) {
        $limit = $request->limit ? $request->limit : 10;
        $offset = $request->offset ? $request->offset : 0;
        if($request->search){
            $request->idmerk = null;
            $request->idjenis = null;
            $request->idtype = null;
        }
        $fetch = Kendaraan::with('type')
            ->with('merk')
            ->with('jenis')
            ->with('foto')
            ->when($request->idmerk, function ($query) use ($request){
                return $query->where('idmerkkdrn', $request->idmerk);
            })
            ->when($request->idjenis, function ($query) use ($request){
                return $query->where('idjeniskdrn', $request->idjenis);
            })
            ->when($request->idtype, function ($query) use ($request){
                return $query->where('idtypekdrn', $request->idtype);
            })
            ->when($request->search && !$request->idmerk && !$request->idjenis && !$request->idtype, function ($query) use ($request){
                return $query->where('nopolisi', 'LIKE', '%'.$request->search.'%');
            })
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
        foreach($fetch as $kendaraan) {
            $data[] = [
                'id' => $kendaraan->id,
                'type' => $kendaraan->type ? $kendaraan->type->type : false,
                'merk' => $kendaraan->merk ? $kendaraan->merk->merk : false,
                'jenis' => $kendaraan->jenis ? $kendaraan->jenis->jenis : false,
                'url_foto' => $kendaraan->foto ? $kendaraan->foto[0]->urlfoto : '',
                'nopolisi' => $kendaraan->nopolisi,
                'warna' => $kendaraan->warna,
                'status' => $kendaraan->status,
            ];
        }
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function detailKendaraan (Request $request) {
        $fetch = Kendaraan::with('type')
            ->with('merk')
            ->with('jenis')
            ->with('foto')
            ->where('id', $request->kendaraan_id)
            ->first();
        if (!$fetch) {
            return response()->json([
                'status' => 'failed',
                'code' => 400,
                'data' => 'Invalid kendaraan id',
            ],400);
        }
        $data = [
            'id' => $fetch->id,
            'idtype' => $fetch->idtypekdrn,
            'type' => $fetch->type ? $fetch->type->type : false,
            'idmerk' => $fetch->idmerkkdrn,
            'merk' => $fetch->merk ? $fetch->merk->merk : false,
            'idjenis' => $fetch->idjeniskdrn,
            'jenis' => $fetch->jenis ? $fetch->jenis->jenis : false,
            'foto' => $fetch->foto,
            'nopolisi' => $fetch->nopolisi,
            'nobpkb' => $fetch->nobpkb,
            'nomesin' => $fetch->nomesin,
            'norangka' => $fetch->norangka,
            'thnkdrn' => $fetch->thnkdrn,
            'warna' => $fetch->warna,
            'status' => $fetch->status,
            'kondisi' => $fetch->kondisi,
            'jaraktempuh' => $fetch->jaraktempuh,
            'tglpajak' => $fetch->tglpajak,
            'tglmatipajak' => $fetch->tglmatipajak,
            'created_at' => date('Y-m-d H:i:s', strtotime($fetch->created_at)),
        ];
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ],200);
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'idtype' => 'required|integer|exists:type_kendaraan,id',
            'idjenis' => 'required|integer|exists:jenis_kendaraan,id',
            'nobpkb' => 'required|string|unique:kendaraan',
            'nomesin' => 'required|string|unique:kendaraan',
            'norangka' => 'required|string|unique:kendaraan',
            'nopolisi' => 'required|string|unique:kendaraan',
            'thnkdrn' => 'required',
            'tglpajak' => 'required|date',
            'tglmatipajak' => 'required|date',
            'idmerk' => 'required|integer|exists:merk_kendaraan,id',
            'warna' => 'required',
            'foto' => 'array',
            'foto.*' => 'mimes:jpg,jpeg,png,svg,webp|max:10240'
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
            $kendaraan = new Kendaraan();
            $kendaraan->idtypekdrn = $request->idtype;
            $kendaraan->idjeniskdrn = $request->idjenis;
            $kendaraan->nobpkb = $request->nobpkb;
            $kendaraan->nomesin = $request->nomesin;
            $kendaraan->norangka = $request->norangka;
            $kendaraan->nopolisi = $request->nopolisi;
            $kendaraan->thnkdrn = $request->thnkdrn;
            $kendaraan->tglpajak = $request->tglpajak;
            $kendaraan->tglmatipajak = $request->tglmatipajak;
            $kendaraan->jaraktempuh = $request->jaraktempuh;
            $kendaraan->idmerkkdrn = $request->idmerk;
            $kendaraan->warna = $request->warna;
            $kendaraan->kondisi = $request->kondisi;
            $kendaraan->save();

            $images = $request->file('foto');
            if ($images != null) {
                for ($i=0; $i < count($images) ; $i++) { 
                    $image = $images[$i];
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = storage_path('../public/foto_kendaraan');
                    $imgFile = Image::make($image->getRealPath());
                    $imgFile->resize(700, 700, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name);
                    $path = 'foto_kendaraan/'.$name;

                    $saveImage = new Foto();
                    $saveImage->idkdrn = $kendaraan->id;
                    $saveImage->urlfoto = env('APP_URL').$path;
                    $saveImage->tglupload = date('Y-m-d H:i:s');
                    $saveImage->caption = null;
                    $saveImage->save();
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'created',
                'code' => 201,
                'message' => 'Sucessfully Created'
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
        $validator = Validator::make($request->all(), [
            'idtype' => 'required|integer|exists:type_kendaraan,id',
            'idjenis' => 'required|integer|exists:jenis_kendaraan,id',
            'nobpkb' => 'required|string|unique:kendaraan,nobpkb,'.$request->kendaraan_id,
            'nomesin' => 'required|string|unique:kendaraan,nomesin,'.$request->kendaraan_id,
            'norangka' => 'required|string|unique:kendaraan,norangka,'.$request->kendaraan_id,
            'nopolisi' => 'required|string|unique:kendaraan,nopolisi,'.$request->kendaraan_id,
            'tglpajak' => 'required|date',
            'tglmatipajak' => 'required|date',
            'idmerk' => 'required|integer|exists:merk_kendaraan,id',
            'foto_insert' => 'array',
            'foto_insert.*' => 'mimes:jpg,jpeg,png,svg,webp|max:10240'
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
            $fetch = Kendaraan::where('id', $request->kendaraan_id)->first();
            if (!$fetch) {
                return response()->json([
                    'status' => 'success',
                    'code' => 400,
                    'data' => 'Invalid kendaraan id',
                ],400);
            }
            $fetch->update([
                'idtypekdrn' => $request->idtype,
                'idjeniskdrn' => $request->idjenis,
                'nobpkb' => $request->nobpkb,
                'nomesin' => $request->nomesin,
                'norangka' => $request->norangka,
                'nopolisi' => $request->nopolisi,
                'thnkdrn' => $request->thnkdrn,
                'tglpajak' => $request->tglpajak,
                'tglmatipajak' => $request->tglmatipajak,
                'jaraktempuh' => $request->jaraktempuh,
                'idmerkkdrn' => $request->idmerk,
                'warna' => $request->warna,
                'status' => $request->status,
                'kondisi' => $request->kondisi,
            ]);
            $images = $request->file('foto_insert');
            if ($images != null) {
                for ($i=0; $i < count($images) ; $i++) { 
                    $image = $images[$i];
                    $name = time().'.'.$image->getClientOriginalExtension();
                    $destinationPath = storage_path('../public/foto_kendaraan');
                    $imgFile = Image::make($image->getRealPath());
                    $imgFile->resize(700, 700, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$name);
                    $path = 'foto_kendaraan/'.$name;

                    $saveImage = new Foto();
                    $saveImage->idkdrn = $request->kendaraan_id;
                    $saveImage->urlfoto = env('APP_URL').$path;
                    $saveImage->tglupload = date('Y-m-d H:i:s');
                    $saveImage->caption = null;
                    $saveImage->save();
                }
            }
            $images_delete = $request->foto_delete_url;
            if ($images_delete != null) {
                foreach ($images_delete as $value) {
                    $path_image = str_replace(env('APP_URL'),"",$value);
                    $path_unlink = storage_path('../public/').$path_image;
                    @unlink($path_unlink);
                    Foto::where('urlfoto',$value)->where('idkdrn',$request->kendaraan_id)->delete();   
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Sucessfully Updated'
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
        DB::beginTransaction();
        try {
            $fetch = Kendaraan::where('id', $request->kendaraan_id)->first();
            if (!$fetch) {
                return response()->json([
                    'status' => 'success',
                    'code' => 400,
                    'data' => 'Invalid kendaraan id',
                ],400);
            }
            $fetchFoto = Foto::where('idkdrn', $request->kendaraan_id)->get();
            if(count($fetchFoto) > 0) {
                foreach ($fetchFoto as $foto) {
                    $value = $foto->urlfoto;
                    $path_image = str_replace(env('APP_URL'),"",$value);
                        $path_unlink = storage_path('../public/').$path_image;
                        @unlink($path_unlink);
                        Foto::where('urlfoto',$value)->where('idkdrn',$request->kendaraan_id)->delete();
                }
            }
            Kendaraan::where('id', $request->kendaraan_id)->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'code' => 200,
                'message' => 'Sucessfully Delete'
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