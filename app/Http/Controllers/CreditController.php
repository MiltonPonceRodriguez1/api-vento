<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    private $table_specs = 'wpsgshcm_specs';
    private $table_products = 'wpsgshcm_wc_product_meta_lookup';

    // Obtener el catalogo con todas las motocicletas
    public function motorcycleCatalogue()
    {
        try {
            $catalogue = DB::table($this->table_products)
                ->join($this->table_specs, $this->table_products . '.product_id', '=', $this->table_specs . '.motorcycle_id')
                ->select($this->table_products . '.sku', $this->table_products . '.stock_status', $this->table_products . '.min_price', $this->table_specs . '.name')
                ->get();

            return response()->json($catalogue, 200);
        } catch (\Throwable $th) {
            $data = [
                'status' => $th,
                'message' => 'error query',
                'code' => 500
            ];
            return response()->json($data, $data['code']);
        }
    }

    // Obtener una motocicleta por medio de su SKU
    public function motorcycleSku(Request $request)
    {
        $sku = $request->input('SKU');
      
        try {
            $motorcycle = DB::table($this->table_products)
                ->where($this->table_products . '.sku', '=', $sku)
                ->join($this->table_specs, $this->table_products . '.product_id', '=', $this->table_specs . '.motorcycle_id')
                ->select($this->table_products . '.sku', $this->table_products . '.stock_status', $this->table_products . '.min_price',  $this->table_specs . '.name')
                ->first();
		
            if ($motorcycle != null) {
                return response()->json(['status' => 'success','motorcycle' => $motorcycle], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No se encontr贸 ning煤n resultado con ese SKU.'], 500);
            }
        } catch (\Throwable $th) {
            $data = [
                'status' => 'error',
                'message' => $th,
                'code' => 500
            ];
            return response()->json($data, $data['code']);
        }
    }

    // Obtener motocicletas que solo tengan informacion
    public function motorcycleCatalogueInfo()
    {
        try {
           $motorcycles = DB::table('wpsgshcm_wc_product_meta_lookup')
	        ->where('wpsgshcm_specs.image', '<>', 'default.png')
       		->join('wpsgshcm_specs', 'wpsgshcm_wc_product_meta_lookup.product_id', '=', 'wpsgshcm_specs.motorcycle_id')
	        ->select('wpsgshcm_wc_product_meta_lookup.min_price as price', 'wpsgshcm_specs.*')
	        ->get();

	        foreach ($motorcycles as $motorcycle) {
	           
        	    $motorcycle->msi_plans = $this->MSI_plans($motorcycle->price);
	            $motorcycle->vc_plans = $this->VC_plans($motorcycle->price);
	            $motorcycle->deposit = $motorcycle->price < 99 ? round(($motorcycle->price * 0.15),2) : round($motorcycle->price * 0.15);
        	    $motorcycle->specs = explode(",", str_replace(" ", "", $motorcycle->specs));
	            $motorcycle->price = round($motorcycle->price);
	        }

	        $data = array(
        	    "code" => 200,
	            "status" => "success",
        	    "total" => DB::table('wpsgshcm_specs')->count(),
	            "motorcycles" => $motorcycles
	        );

	        return response()->json($data, $data['code']);
        } catch (\Throwable $th) {
            $data = [
                'status' => 'error',
                'message' => $th,
                'code' => 500
            ];
            return response()->json($data, $data['code']);
        }
    }
}
