<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailerController extends Controller
{

    public function getUser(Request $request) {

        $table = $request->input('table');

        try {
            
            $users = DB::connection('mysql_mailing')->table($table)
            ->select('NOMBRE', 'EMAIL')
            ->get();

            if ($users != null) {
                return response()->json(['status' => 'success','users' => $users], 200);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No results found'], 500);
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
}
