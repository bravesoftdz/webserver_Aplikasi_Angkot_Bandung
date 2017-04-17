<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\trip;
use App\trip_new;
use App\shape;
use App\shapes_new;
use DB;

class TripsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function get_jalur_terdekat($route_id = 1)
    {
        

        $trip = trip::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[0]['shape_id'];
        $array = explode(",", $shape_id);
       
     
      $shape = shape::whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get(); 
       //return $shape;
        $origin = [-6.974309216722413,107.5920295715332]; //ini bisa diganti ganti
        $nearest = DB::select("SELECT *, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes HAVING distance < 2 ORDER BY distance LIMIT 0 , 20;"); //-->lanjut disini. ini baru mengambil titik terdekat dari semua titik yang ada di database. untuk menguji kebenarannya, harus di lokalisasi dulu di salah satu jalur, kemudian di bandingkan dengan map yang pernah di buat.
        $nearest_jalur = $nearest; 
        return $nearest_jalur;
    }

    public function get_walking_route()
    {
        $awal = $_GET['origin']; // get dari adddress bar
        $origin = rawurlencode($awal);
        $mode = 'walking';
        $destination = rawurlencode($_GET['destination']); 

        $arrContextOptions = array( //kalau ga pake ini error
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
            ),
        );  


        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin."&destination=".$destination."&mode=".$mode."&key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w";

       $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
       

       return $json;

    }

    public function get_car_route()
    {   
         $route_id = 40;
        $trip = trip::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[39]['shape_id'];
        $array = explode(",", $shape_id);
       //return $trip;
     
     $shape = shape::whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();
                           //->paginate(10);

         $waypoints = '';                  
        for ($i=0; $i < sizeof($shape) ; $i++) { 
             # code...
            $lat = $shape[$i]['shape_pt_lat'];
            $lon = $shape[$i]['shape_pt_lon'];
            $location[$i] = $lat.",".$lon."|";
            
            $waypoints .= $location[$i];
         } 

         //return $waypoints;
       
        $awal = $_GET['origin']; // get dari adddress bar
        $origin = rawurlencode($awal);
        $mode = 'driving';
        $destination = rawurlencode($_GET['destination']);  //get dari address bar
        //$waypoints = "Charlestown,MA|Lexington,MA"; //dari database
        $arrContextOptions = array( //kalau ga pake ini error
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
            ),
        );  

        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin."&destination=".$destination."&waypoints=".$waypoints."&key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w";

       $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
       

       return $json; 
       
    }

    public function get_trayek($kirim = 1)
    {
        $route_id = $_GET['kirim'];
        $index = ($route_id - 1);
        $trip = trip::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[$index]['shape_id'];
        $array = explode(",", $shape_id);
       
     
      $shape = shape::whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();

        return $shape;
    }

     public function get_trayek_akbar($kirim = 1)
    {   
        
        $route_id = $_GET['kirim'];
        
        $index = ($route_id - 1);
        $trip = trip_new::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[$index]['shape_id'];
        $array = explode(",", $shape_id);
       
     
      $shape = shapes_new::whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();

        return $shape;
    }
}
