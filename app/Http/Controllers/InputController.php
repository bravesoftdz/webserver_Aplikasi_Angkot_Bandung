<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Http\Requests\UploadRequest;
use App\trip;
use App\trip_new;
use Fpdf;
use Auth;
use Session;
use DB;

class InputController extends Controller
{
    //
    public function insert(Request $request)
    {
	     $trip_short_name = $request->noTrayek ; // kalau dari ajax, namaTrayek teh dari JSON yang dikirim.
	     $route_id = $request->route_id ; //trip::find($id);
	     $fare_id = $request->fare_id;
	     $route_color = $request->route_color;
	     $price = $request->price;
	     $file = $request->file('file');
	     $image = $request->image;
	     $shape_id = $request->shape_id;
	     $keterangan = $request->keterangan;
	     $trip_headsign = $request->namaTrayek;

	     if( $request->hasFile('file') && !file_exists( public_path('images/'.$file->getClientOriginalName() ) )){
	        $imageName = $file->getClientOriginalName();
	        $file->storeAs('images', $imageName);
	     }
	     //route_id = auto_increment
	     DB::select("INSERT INTO trips VALUES ('".$route_id."','0','".$trip_headsign."','".$trip_short_name."','','','".$shape_id."','','".$keterangan."' )");
	     DB::select("INSERT INTO route VALUES ('".$route_id."','".$trip_headsign."','','','0','','".$route_color."','','1','".$image."')");
	     DB::select("INSERT INTO fare_rule VALUES ('','".$fare_id."','".$route_id."','','')");
	     
	     
	     return back();

    }

    public function insert_points(Request $request)
    {
    	$data = $request->data;
    	$route_id = $request->route_id;

	      if(!empty($data)){

		        foreach ($data as $key => $value) {
		          
		          $gabungShapeId[] = $value['shape_id'];
		          if( $value['id'] == '' ){
		            DB::select("INSERT INTO shapes value ('','".$value['shape_id']."','".$value['shape_pt_lat']."','".$value['shape_pt_lon']."','0','','','')");
		            //$result[] = "INSERT INTO shapes value ('','".$value['shape_id']."','".$value['shape_pt_lat']."','".$value['shape_pt_lon']."','0','','','')";
		          }
		          
		        }

		        $implode = implode(", ", $gabungShapeId ) ;
		        //$result[] = "UPDATE trips SET shape_id = '".$implode."' where route_id ='".$route_id."' ";
		        DB::select("UPDATE trips SET shape_id = '".$implode."' where route_id ='".$route_id."' ");
		        return $result = "OK";
	      }
	      else
	      {
	        return $result = 'empty';
	      }
	}
}
