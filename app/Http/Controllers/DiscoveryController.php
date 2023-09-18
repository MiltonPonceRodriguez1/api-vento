<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use App\Models\DiscoveryMotorcycle;
use App\Models\WoocomerceMotorcycle;
use Hamcrest\Type\IsObject;

class DiscoveryController extends Controller
{
    private $tableWoocomers = 'wpsgshcm_wc_product_meta_lookup';
    private $tableSpecs = 'wpsgshcm_specs';

    private $data_error = array(
        "code" => 500,
        "status" => "error",
        "message" => "Faltan Datos por Enviar"
    );

    public function index() {
        $motorcycles = DB::connection('mysql')->table($this->tableWoocomers)
            ->where($this->tableSpecs.'.active', '=', true)
            ->join( $this->tableSpecs, $this->tableWoocomers.'.product_id', '=', $this->tableSpecs.'.motorcycle_id')
            ->select( $this->tableWoocomers.'.min_price as price', $this->tableSpecs.'.*')
            ->get();

        foreach ($motorcycles as $motorcycle) {
            $motorcycle->msi_plans = $this->MSI_plans($motorcycle->price);
            $motorcycle->vc_plans = $this->VC_plans($motorcycle->price);
            $motorcycle->deposit = $motorcycle->price < 99 ? round(($motorcycle->price * 0.15),2) : round($motorcycle->price * 0.15);
            $motorcycle->specs = explode(",", str_replace(" ", "", $motorcycle->specs));
            $motorcycle->price = round($motorcycle->price);
        }

        return response()->json([
            "code" => 200,
            "status" => "success",
            "total" => DB::table('wpsgshcm_specs')->where($this->tableSpecs.'.active', '=', true)->count(),
            "motorcycles" => $motorcycles
        ], 200);
    }

    public function getSkusWoocomerce() {
        // ! SELECT * FROM `wpsgshcm_wc_product_meta_lookup` as t1 LEFT JOIN `wpsgshcm_specs` as t2 ON t1.product_id = t2.motorcycle_id WHERE t2.motorcycle_id Is Null AND t1.sku <> "";
        $skus = DB::connection('mysql')->table( $this->tableWoocomers )
            ->leftJoin( $this->tableSpecs, $this->tableWoocomers.'.product_id', '=', $this->tableSpecs.'.motorcycle_id' )
            ->whereNull( $this->tableSpecs.'.id' )
            ->where( $this->tableWoocomers.'.sku', '<>', '')
            ->select( $this->tableWoocomers.'.product_id', $this->tableWoocomers.'.sku')
            ->get();

        return response()->json([
            'status' => 'success',
            'skus' => $skus,
        ], 200); 
    }

    public function getDiscoveryMotorcycles() {
        $motorcycles = DiscoveryMotorcycle::all();

        foreach ($motorcycles as $motorcycle) {
            $motorcycle->specs = explode(",", str_replace(" ", "", $motorcycle->specs));
            $motorcycle->active = (int) $motorcycle->active;
        }

        return response()->json([
            'status' => 'success',
            'motorcycles' => $motorcycles,
        ], 200);
    }

    public function store( Request $request ) {
        $validate = \Validator::make( $request->all(), [
            'motorcycle_id' => 'required',
            'name' => 'required',
            'bodywork' => 'required',
            'transmission' => 'required',
            'highways' => 'required',
            'long_trips' => 'required',
            'experience' => 'required',
            'specs' => 'required',
            'endpoint' => 'required',
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if ( $validate->fails() ) return response([ 'status' => 'error', 'message' => 'Faltan enviar datos' ,'errors' => $validate->errors() ], 500);

        $image = $request->file('file0');
        $extension = explode('/', $image->getMimeType());
        $imageName = str_replace(" ", "", $request->input('name'))."_".time().'.'.$extension[1];

        \Storage::disk('motorcycles')->put($imageName, \File::get($image));


        $motorcycle = DiscoveryMotorcycle::create([
            'motorcycle_id' => (int) $request->input('motorcycle_id'),
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'bodywork' => $request->input('bodywork'),
            'description' => $request->input('description'),
            'transmission' => $request->input('transmission'),
            'highways' => $request->input('highways'),
            'long_trips' => $request->input('long_trips'),
            'experience' => $request->input('experience'),
            'endpoint' => $request->input('endpoint'),
            'specs' => $request->input('specs'),
            'image' => $imageName,
        ]);

        if ( !Is_object($motorcycle) ) return response([ 'status' => 'error', 'message' => 'Error de servidor, intente más tarde' ], 500);

        return response()->json([
            'status' => 'success',
            'message' => $request->input('name').' Almacenada Correctamente',
            'motorcycle' => $motorcycle
        ], 200);
    }

    public function update(Request $request) {
        $id = $request->input('id');
        $motorcycleId = $request->input('motorcycle_id');

        $result = DiscoveryMotorcycle::where('id', '=', $id)
        ->where('motorcycle_id', '=', $motorcycleId)
        ->update([
            'name' => $request->input('name'),
            'year' => $request->input('year'),
            'description' => $request->input('description'),
            'bodywork' => $request->input('bodywork'),
            'transmission' => $request->input('transmission'),
            'highways' => $request->input('highways'),
            'long_trips' => $request->input('long_trips'),
            'experience' => $request->input('experience'),
            'endpoint' => $request->input('endpoint'),
            'specs' => implode(', ', $request->input('specs')),
        ]);

        if ( $result === 0 ) return response()->json([ 'status' => 'error', 'message' => 'Error al intentar actualizar la motocicletea!' ], 500);

        return response()->json([
            'status' => 'success',
            'message' => $request->input('name').', actualizada correctamente!',
        ], 200);
        
    }

    public function upload( Request $request ) {
        $validate = \Validator::make($request->all(),[
            'id' => 'required',
            'motorcycle_id' => 'required',
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if ($validate->fails()) return response()->json(['status' => 'error', 'message' => 'No se envio una imagen con un formato valido'], 500);

        $id = (int) $request->input('id');
        $motorcycleId = (int) $request->input('motorcycle_id');
        $name = $request->input('name');

        $image = $request->file('file0');
        $extension = explode('/', $image->getMimeType());
        $imageName = $id.'_'.str_replace(" ", "",$name)."_".time().'.'.$extension[1];

        \Storage::disk('motorcycles')->put($imageName, \File::get($image));

        $result = DiscoveryMotorcycle::where('id', '=', $id)
            ->where('motorcycle_id', '=', $motorcycleId)
            ->update([
                'image' => $imageName,
            ]);

        if ( $result === 0 ) return response()->json([ 'status' => 'error', 'message' => 'Error al intentar actualizar la imagen!' ], 500);

        return response()->json([
            'status' => 'success',
            'message' => 'Imagen actualizada correctamente!',
            'image' => $imageName
        ], 200);
    }

    public function onChageActive(Request $request) {
        $id = $request->input('id');
        $motorcycleId = $request->input('motorcycle_id');
        $active = $request->input('active');
        $name = $request->input('name');

        $message = ($active) ? $name.', Se podrá visualizar ' : $name.', No se podrá visualizar';
        $status = ($active) ? 'success' : 'warning';

        $result = DiscoveryMotorcycle::where('id', '=', $id)
            ->where('motorcycle_id', '=', $motorcycleId)
            ->update(['active' => $active]);

        if ( $result === 0 ) return response()->json(['status' => 'error', 'message' => 'Error al actualizar el campo active, intente más tarde'], 500);

        return response()->json([
            'status' => $status,
            'message' => $message
        ], 200);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('motorcycles')->exists($filename);

        if ($isset) {
            $file = \Storage::disk('motorcycles')->get($filename);

            return new Response($file, 200);
        }

        $this->data_error['message'] =  "La imagen no existe!";
        return response()->json($this->data_error, $this->data_error['code']);
    }

    private function PMT($price, $temp) {
        $pv = $price - ($price * 0.15);
        $r = (0.58/52);
        $pmt = $pv / ((1 - (1 / pow((1 + $r), $temp)))/ $r);

        return $pmt;
    }

    private function MSI_plans($price) {
        $plans = array(
            'msi_12' => $price < 99 ? round(($price/12),2) : round($price/12),
            'msi_6' => $price < 99 ? round(($price/6),2) : round($price/6),
            'msi_3' => $price < 99 ? round(($price/3),2) : round($price/3)
        );

        return $plans;
    }

    private function VC_plans($price) {
        $plans = array(
            'vc_52' => $price < 99 ? round($this->PMT($price, 52),2) : round($this->PMT($price, 52)),
            'vc_104' => $price < 99 ? round($this->PMT($price, 104),2) : round($this->PMT($price, 104))
        );

        return $plans;
    }

}
