<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\trip; 
use App\trip_new;
use App\shape;
use App\shapes_new;
use DB;

class ApiControl extends Controller
{ 

  public function get_jalur_terdekat($lat = -6.924318036348017 , $lon = 107.60681390762329)
  {

    ini_set('max_execution_time', 180);

      if(empty($_GET['lat']) or empty($_GET['lon']) )
      {
        $lat;
        $lon;
        
        $origin = [$lat, $lon];
        $start = ['lat'=>$lat, 'lng'=>$lon];
        $awal = $lat.",".$lon;
      } 
      else
      {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $origin = [$lat, $lon]; 
        $start = ['lat'=>$lat, 'lng'=>$lon];
        $awal = $lat.",".$lon;
      }                
      $radius = 0.25;   
      // perbaiki nearest ini, jangan pakai * biar bisa dirubah pake as shape_pt_lat, shape_pt_lon nya.
      a:
     $nearest = DB::select("SELECT id,shape_pt_lat as lat, shape_pt_lon as lng, shape_id, shape_pt_sequence, place_info, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < ".$radius." ORDER BY distance  ;");
      //filtering

     if(empty($nearest))
      {
        //return "tidak ada angkot";
        $radius = $radius+0.25;
        if($radius > 2)
        {
          $angkot = [];
          $arraytrip = [];
          goto b;

        }
        goto  a;

      }


      $data = [];
             
    $known = array();

    $filtered = array_filter($nearest, function ($val) use (&$known) {
        $unique = !in_array($val->shape_id, $known);
        $known[] = $val->shape_id;
        return $unique;
    });  // filter supaya yang di return base on shape_id

    for ($n=0; $n < sizeof($filtered)  ; $n++) { 
      # code...
      if(isset($filtered[$n]))
      {
      $data[] = $filtered[$n];
      }
    } // mengalokasikan hasil filter ke index yang berurutan.
    
    for ($i=0; $i < sizeof($data); $i++) 
    { 

      $akhirlat = $data[$i]->lat;
      $akhirlon = $data[$i]->lng;

      $akhir = $akhirlat.",".$akhirlon;
      $filtered = ["status"=> "unavailable" ]; //$this->get_walking_route($awal, $akhir);
      $distance = $filtered;//json_decode($filtered);
     /* if($distance->status == "OVER_QUERY_LIMIT")
      {
        return "google api OVER_QUERY_LIMIT";
      } */
     // $obj = $distance->routes[0]->legs[0]->distance->value;
      $obj = $data[$i]->distance;
      $hasil[$i] = [ "jarak"=>$obj, "titik_terdekat"=>$data[$i], "walk_route"=> $filtered];
    } 


      $arraytrip = [];
      $result_filtered_trayek = [];
      $a = [];

      for ($i=0; $i < sizeof($data) ; $i++) 
      {     
        $hai = $data[$i]->shape_id;
        $trip2 = DB::select("select trips.route_id,trip_short_name, trip_headsign,shape_id, route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai.",%' union 
                select trips.route_id,trip_short_name, trip_headsign,shape_id,route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id = ".$hai." union
                select trips.route_id,trip_short_name, trip_headsign,shape_id,route.route_color,  fare_attributes.price,route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai."' ");
        //DB::select("select * from trips where shape_id like '% ".$hai.",%' union select * from trips where shape_id = ".$hai." union select * from trips where shape_id like '% ".$hai."' ");

       
        $a[$i] = $trip2;
        $b[$i] = $trip2;

         /*$filtered_trayek_data[$i] = array_filter( $b[$i] , function ($val) use (&$known) {
          $unique = !in_array($val->trip_short_name, $known);
          $known[] = $val->trip_short_name;
          return $unique;
          });*/
 

         $filtered_trayek_data[$i] = array_filter($b[$i], function ($val) use (&$known) {
          $unique = !in_array($val->route_id, $known);
          $known[] = $val->route_id;
          return $unique;
          })  ; 

         //return $filtered_trayek_data;

          if(empty($filtered_trayek_data[$i]))
          {
            continue;
          } 
          else
          {
              $new[$i] = array_values($filtered_trayek_data[$i]);
          }
          
          $route_id = $new[$i];
          $warna = [];
          for ($n=0; $n < sizeof($route_id) ; $n++) { 
            $route = $route_id[$n]->route_id;
            $warna[$n] = DB::select("select route_color from route where route_id =".$route);
           
          }
          
          $route_color =[];
          for ($m=0; $m <sizeof($warna) ; $m++) { 
            if(!empty($warna[$m]))
            {
            $route_color[$m] = $warna[$m][0];
            }
            else
            {
              continue;
            }
          }

          $color[$i] = $route_color;


           $arraytrip[] = [ "pickup_point"=> $hasil[$i] , "trayek"=>$new[$i] ];//,"color"=>$color[$i] ]; 

      } //
       
      
     //  $arraytrip = array("titik_terdekat"=> $data, "trayek"=>$filtered_trayek_data );
      b:
      if(!empty($nearest))
      {
        $status = "OK";
      }
      else
      {
        $status = "null";
      }
      $result = ['status'=> $status ,'start_position'=>$start , 'angkot'=> $arraytrip];
      //return json_encode([ $status , $arraytrip ], true);//, $data];
       return json_encode($result, true);    

  }

  public function get_angkot_start_finish($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782") 
  {  
    
      if(!empty($_GET['start']) && !empty($_GET['finish']))
      {
        $a_start = explode(',', $_GET['start']);
        $a_finish = explode(',', $_GET['finish']); 

      }
      else
      {
       $a_start = explode(',', $start);
       $a_finish = explode(',', $finish);
      }

      $awal= $this->get_jalur_terdekat($a_start[0], $a_start[1]);
      $akhir = $this->get_jalur_terdekat($a_finish[0], $a_finish[1]);

      return ['start'=> json_decode($awal),'finish'=> json_decode($akhir)];

  }

  public function get_trayek($kirim = 1)
  {   
      if(isset($_GET['kirim']))
      {$route_id = $_GET['kirim'];}  
      else {
        $route_id = $kirim;
      }
      
      $index = ($route_id - 1);
      $trip = trip::all()->where('route_id', '=', $route_id);
      $shape_id = $trip[$index]['shape_id'];
      $array = explode(",", $shape_id);
     
   
      $shape = shape::select(array('id','shape_pt_lat as lat','shape_pt_lon as lng','shape_id','shape_pt_sequence','place_info'))
                           ->whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();

        return $shape;
  }

  public function distanceTo($lat1, $lng1, $lat2, $lng2) 
  {
    $earthRadius = 3958.75;

    $dLat = deg2rad($lat2-$lat1);
    $dLng = deg2rad($lng2-$lng1);

    $a =  sin($dLat/2) * sin($dLat/2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $dist = $earthRadius * $c;

    // from miles
    $meterConversion = 1609;
    $geopointDistance = $dist * $meterConversion;

    return $geopointDistance;
  }

  public function get_distance_route($route_id = 1)
  {
      $data = $this->get_trayek($route_id);
      // return $data
      foreach ($data as $j => $value) 
      {
        
        if(!isset($data[$j]->lat) || !isset($data[$j]->lng) )
          {continue;}
        $lat1 = (float) $data[$j]->lat  ;
        $lng1 = (float) $data[$j]->lng  ;
        //return [$lat1,$lng1];
        if(!isset($data[$j+1]) || !isset($data[$j+1]) )
          {continue;}
        $lat2 = (float) $data[$j+1]->lat;
        $lng2 = (float) $data[$j+1]->lng;
        
        $jarak[] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);


      }
      return array_sum( $jarak );
  }

  public function get_walking_route($awal='gedung sate', $akhir='gasibu')
  {

    if(isset($awal) and isset($destination))
    {
    $awal = $_GET['origin']; // get dari adddress bar
    $origin = rawurlencode($awal);
    $mode = 'walking';
    $destination = rawurlencode($_GET['destination']); 
    }
    else
    {
      if(!is_object($awal))
      {
        $awal = (object) $awal; //json_encode($awal);

      }
      $awal = $awal->lat.",".$awal->lng;
      //return $awal;
      $origin = rawurlencode($awal);
      $mode = 'walking';
      if(!is_object($akhir))
      {
        $akhir  = (object) $akhir; //json_encode($akhir) ;
      }
      $akhir = $akhir->lat.",".$akhir->lng;;
      
      $destination = rawurlencode($akhir);
    }

    $arrContextOptions = array( //kalau ga pake ini error
    "ssl"=>array(
    "verify_peer"=>false,
    "verify_peer_name"=>false,
        ),
    );  


    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin."&destination=".$destination."&mode=".$mode."&key=AIzaSyBuM6Zuy4Zr3SDn_X6nmUvTQn64l944btk";//AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w";

   $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
   
   return json_decode( $json);

  }

  public function get_trayek_potong($kirim = 1, $start_key, $finish_key)
  {   
      if(isset($_GET['kirim']))
      {$route_id = $_GET['kirim'];}  
      else {
        $route_id = $kirim;
      }
      
      $index = ($route_id - 1);
      $trip = trip::all()->where('route_id', '=', $route_id);
      $shape_id = $trip[$index]['shape_id'];
      $array = explode(",", $shape_id);
     
   
      $data_shapes = shape::select(array('id','shape_pt_lat as lat','shape_pt_lon as lng','shape_id','shape_pt_sequence', 'place_info'))
                           ->whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();

        //return $shape;
      $ketemu = false;
          foreach ($data_shapes as $key => $value) {
            # code...
            if($value->id == $start_key )
            {
              $ketemu = true;
              break;
            }
          }

          $kunci1 = $key;

      $ketemu = false;
          foreach ($data_shapes as $key => $value) {
            # code...
            if($value->id == $finish_key )
            {
              $ketemu = true;
              break;
            }
          }

          $kunci2 = $key;

         // return [$kunci1, $kunci2];

      if($kunci1 < $kunci2)
        {  

           if($ketemu)
           {
              for ($kj=0; $kj < $kunci1 ; $kj++) { 
                # code...
                $unset[] = $data_shapes[$kj];
                unset($data_shapes[$kj] );  
              }
              //return $unset;
              foreach ($data_shapes as $key => $value) {
                $data_shapes_filtered[] = $value;
              }
              $data_shapes = $data_shapes_filtered;
           }

           $data_shapes_filtered = []; 

           if($ketemu)
           {
              for ($m=sizeof($data_shapes) -1; $m > $kunci2 - $kunci1 ; $m--) { 
                # code...
                $unset[] = $data_shapes[$m];
                //$perulangan[] = $m;
                unset($data_shapes[$m] );
              }
             // return $unset;
              foreach ($data_shapes as $kunci => $value) 
              {
                $data_shapes_filtered[] = $value;
              }
              $data_shapes = $data_shapes_filtered;
           }   
           $data_shapes_filtered = [];
        }
        else
        { 
          if($ketemu)
           {
              for ($m=sizeof($data_shapes) -1; $m > $kunci1 ; $m--) { 
                # code...
                $unset[] = $data_shapes[$m];
                //$perulangan[] = $m;
                unset($data_shapes[$m] );
              }
             // return $unset;
              foreach ($data_shapes as $key => $value) 
              {
                $data_shapes_filtered[] = $value;
              }
              $data_shapes = $data_shapes_filtered;
           }   
           $data_shapes_filtered = [];

          if($ketemu)
           {
              for ($kj=0; $kj < $kunci2 ; $kj++) { 
                # code...
                unset($data_shapes[$kj] );  
              }
              foreach ($data_shapes as $key => $value) {
                $data_shapes_filtered[] = $value;
              }
              $data_shapes = $data_shapes_filtered;
           } 

             $data_shapes_filtered = []; 

        }
        /*{
          $data_shapes = [];
        }*/
        return $data_shapes;
  }

  public function getLocInfo($latlng = '44.4647452,7.3553838' )
  { 
    $arrContextOptions = array( //kalau ga pake ini error
      "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
          ),
    );  
    //"AIzaSyAI5gLAuxEzfYY-d-Et4VszYLi9qtv8ZSs"; //teguh
    //"AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE"; //teguh
    //
    $key = "AIzaSyCKpPFPiinrY1wJHeMD92jJzz5B1r9OGgI"; //Dama
    $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latlng."&sensor=true&key=".$key;

   $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
   $obj =  json_decode($json);
   return $obj;//$obj->results[0]->formatted_address
  
  }

  public function get_last_shapes_id()
  {
    $data = shape::select(['shape_id','id'])->orderby('shape_id', 'DESC')->get();
    return $data[0];
  }

  public function cetak_jalur($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265", $walk_route='yes')
  {
    if(empty($_GET['walk_route']))
    {

      $walk_route = $walk_route;
    }
    else
    {
      $walk_route = $_GET['walk_route'] ;
      //return $walk_route;
    }

    $data = $this->get_angkot_start_finish($start, $finish);
    //return $data;
    if($data['start']->status == "OK" and $data['finish']->status == "OK" )
    {
      $status = 'OK';
    }
    
    if($data['start']->status !== "OK" || $data['finish']->status !== "OK")
    {
      $status = 'Bad';
      return ["status"=>$status];
    }
    if(empty( $data['start']->angkot) || empty( $data['finish']->angkot) )
    {

      return  ['status'=>"bad", 'note'=> 'tidak ada angkot di titik start atau titik finish'];
    }

    //deklarasi angkot start.
    for ($i=0; $i < sizeof($data['start']->angkot) ; $i++) { 
      # code...
      for ($j=0; $j < sizeof($data['start']->angkot[$i]->trayek) ; $j++) { 
        # code...
        $angkot_start[] = $data['start']->angkot[$i]->trayek[$j];
        $pickup_point_start[] = $data['start']->angkot[$i]->pickup_point;
      }    
    }
    //deklarasi angkot finish.
    for ($i=0; $i < sizeof($data['finish']->angkot) ; $i++) { 
      # code...
      for ($j=0; $j < sizeof($data['finish']->angkot[$i]->trayek) ; $j++) { 
        # code...
        $angkot_finish[] = $data['finish']->angkot[$i]->trayek[$j];
        $pickup_point_finish[] = $data['finish']->angkot[$i]->pickup_point;
      }    
    }
    
    $intersec = array_map("unserialize", array_intersect(array_map("serialize", $angkot_start) , array_map("serialize", $angkot_finish ))) ;

    //deklarasi gabung start & finish
    for ($i=0; $i < sizeof($angkot_start) ; $i++) { 
       # code...
       $gabung_start[] = [$angkot_start[$i], $pickup_point_start[$i]];
    }
      //return $gabung_start;
    for ($i=0; $i < sizeof($angkot_finish) ; $i++) { 
        # code...
        $gabung_finish[] = [$angkot_finish[$i], $pickup_point_finish[$i] ]; 
    }

    //cek ada angkot yang sama atau tidak di start dan Finish.
    if(!empty($intersec)) //fungsi satu angkot // fungsi 1 angkot
    { 
      

      //return [$gabung_start, $gabung_finish] ;

      $intersec2 = array_map("unserialize", array_intersect(array_map("serialize", $angkot_finish) , array_map("serialize", $angkot_start ))) ;
      
      foreach ($intersec as $key => $value) { // pemindahan numerical array ke array biasa.
        # code...
        $intersection[] = $value;
        $key1[] = $key;
      }

      //$pairKey = array_combine (array_keys($intersec),array_keys($intersec2) );
      
      foreach ($intersec as $key => $value) {
        # code...
        $pairKey[$key] = array_search($value, $intersec2);
      }

      //return [$gabung_start, $gabung_finish ,$pairKey ];
      foreach ($pairKey as $key => $value) {
        # code...
        $sid = $angkot_start[ $key ]->shape_id ;
        $tmp = explode(", ", $sid) ;
        $potong_awal = array_keys($tmp, $gabung_start[$key][1]->titik_terdekat->shape_id );
        $potong_akhir = array_keys($tmp, $gabung_finish[$value][1]->titik_terdekat->shape_id );
        if($potong_awal[0] > $potong_akhir[0]){
          //return "hapus intersec dengan key ini";
          unset($intersec[$key] );
        }
      }
       
      if(empty($intersec))
       {goto duaAngkot;}
      //return [$intersec ,$potong_awal, $potong_akhir];


      foreach ($intersec as $key => $value) {
        # code...
        // $i = counter perulangan.
        
        $titik_terdekat = $gabung_start[$key][1]->titik_terdekat; // $pickup_point_start[$key]->titik_terdekat *sama saja.
        $start_position = $data['start']->start_position;//json_decode(json_encode($data['start']->start_position),true) ;
        //$posisi_awal = titik_Terdekat
        if($walk_route == "no")
        {

          $walking_path =  [ "status"=>"unavailable"];
        }
        else
        {

          $walking_path = $this->get_walking_route($start_position, $titik_terdekat);
        }

        $jlnkaki = (object) ["route_id" =>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#000000", "price"=>0, "image"=>"public/images/walk.png" ];

        $step[] = [ "angkot"=>[ $jlnkaki ], "jalur"=> [  $start_position  ,  $titik_terdekat, $walking_path ] ];
        //step pertama jalan kaki.

        $angkot = [  $value ]; // angkot yang dinaiki di step angkot. //titik potong awal = titik_terdekat ;
        
        $keyPotongAkhir = $gabung_finish[ $pairKey[$key] ][1]->titik_terdekat ;
        $jalur = $this->get_trayek_potong($value->route_id, $titik_terdekat->id, $keyPotongAkhir->id  );
        
        $step[] = [ "angkot"=> $angkot , "jalur"=> $jalur ];

        $titik_terdekat_finish = $keyPotongAkhir;//$gabung_finish[ $pairKey[$key] ][1]->titik_terdekat; 
        $finish_position = $data['finish']->start_position;//json_decode(json_encode($data['start']->start_position),true) ;
        //$posisi_awal = titik_Terdekat
        if($walk_route == "no")
        {

          $walking_path =  [ "status"=>"unavailable"];
        }
        else
        {

          $walking_path = $this->get_walking_route($titik_terdekat_finish, $finish_position);
        }
        
        $step[] = [ "angkot"=>[ $jlnkaki ], "jalur"=> [ $titik_terdekat_finish, $finish_position  , $walking_path ] ];

        //perubahan jadi object
        foreach ($step as $ii => $value) {
          foreach ($step[$ii]['jalur'] as $j => $value) {
            $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j]; 
          }
        }
        
        //penambahan distance  
        foreach ($step as $ii => $value) {
          $jarak = [];
          foreach ($step[$ii]['jalur'] as $j => $value) {
          
            if(!isset($step[$ii]['jalur'][$j]->lat) || !isset($step[$ii]['jalur'][$j]->lng) )
              {continue;}
            $lat1 = (float) $step[$ii]['jalur'][$j]->lat  ;
            $lng1 = (float) $step[$ii]['jalur'][$j]->lng  ;
        
            if(!isset($step[$ii]['jalur'][$j+1]->lat) || !isset($step[$ii]['jalur'][$j+1]->lng) )
              {continue;}
            $lat2 = (float) $step[$ii]['jalur'][$j+1]->lat;
            $lng2 = (float) $step[$ii]['jalur'][$j+1]->lng;
            
            
            $jarak[$ii][] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);

          }

          $distance = array_sum( $jarak[$ii] ) ;
          $step[$ii]['distance'] = $distance;
        }//*/

        //manipulating price inside angkot.
        foreach ($step as $key => $value) {
          # code...
          
          if($value['angkot'][0]->price == 0)
          {
            continue;
          }
          else
          { 
            $route_id = $value['angkot'][0]->route_id; 
            $total_jarak = $this->get_distance_route($route_id);

            if($value['distance'] <= 1000 ) // satu kilo pertama
            {
              $value['angkot'][0]->price = 1500;  
            }
            elseif ($value['distance'] > 1000 and $value['distance'] <= ( (1/3) * $total_jarak) ) // 1/3 pertama
            {
              $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) / 3 ))  ; //pembulatan
              //$temp = substr($value['angkot'][0]->price, -2);
              $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
              if(substr( $value['angkot'][0]->price , -3) < 499 )
              {
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
              }
              else
              {
                $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
              }                  
            }
            elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 2/3 pertama
            {
              # code...
              $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
              //$temp = substr($value['angkot'][0]->price, -2);
              $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
              if(substr( $value['angkot'][0]->price , -3) < 499 )
              {
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
              }
              else
              {
                $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
              }                  
            }
            else
            {
              $value['angkot'][0]->price = $value['angkot'][0]->price;
            }
          
          }

        }
        
        $total_cost = 0;
        foreach ($step as $key => $value) {
          # code...
          $total_cost = $total_cost + $value['angkot'][0]->price;
        }

        $routingresult[] = ['step'=>$step, 'total_cost'=>$total_cost ];
        $step = [];
      }
      
      //return $routingresult;

      usort($routingresult, function($a, $b) {return $a['total_cost'] - $b['total_cost']; }); //sorting by total cost
      $routingresult = array_slice($routingresult, 0,3) ;



      //perbaiki kalau jalur salah.
      foreach ($routingresult as $i => $value) {
        # code...
        foreach ($routingresult[$i]['step'] as $j => $value) {
          if($j == 0)
          {
            $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
            $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
          
            if($jalur[1]->id !== $jalur2[0]->id){
              $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse($jalur2);
            }
          }
          else if($j == sizeof($routingresult[$i]['step'])-1 ){
            //$jalur = $routingresult[$i]['step'][$j]['jalur'] ;
            //$jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
            continue;
          }
          else{
            $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
            $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
            //return $jalur2;
            if($jalur[sizeof($jalur)-1]->id !== $jalur2[0]->id){
              $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse($jalur2);

            }

          }

        }
      }

      //penambahan keterangan.
      foreach ($routingresult as $i => $value) {
          # code...
          foreach ($routingresult[$i]['step'] as $j => $value) {
            # code...
            if($j == 0)
            {
              if($walk_route=="no")
              {
                $a = $routingresult[$i]['step'][$j]['jalur'][0];
                $b = $routingresult[$i]['step'][$j]['jalur'][1];
                  if(empty($b->place_info))              
                  {
                    if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                                  continue;
                                }
                
                    $param1 = $a->lat .",". $a->lng;
                    $param2 = $b->lat .",". $b->lng;
                    $turun = $this->getLocInfo($param2);

                    $turun = $turun->results[0]->formatted_address;
                    
                    $turun = explode(", ", $turun);
                    $jarak = $routingresult[$i]['step'][$j]['distance'];
                    $jarak = explode(".", $jarak);
                    //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                  }
                  else
                  {
                    if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                      continue;
                    }
                
                    $param1 = $a->lat .",". $a->lng;
                    $param2 = $b->lat .",". $b->lng;
                    
                    $turun = $b->place_info;
                    $turun = explode(", ", $turun);
                    $jarak = $routingresult[$i]['step'][$j]['distance'];
                    $jarak = explode(".", $jarak);
                    //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                  }
              }
              else
              {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                $turun = explode(", ", $turun);
                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);
                //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
              }
            }
            else if($j == sizeof($routingresult[$i]['step'])-1 )
            {
              if($walk_route=="no")
              { 
                $a = $routingresult[$i]['step'][$j]['jalur'][0];
                $b = $routingresult[$i]['step'][$j]['jalur'][1];
                  
                if(empty($a->place_info)) 
                {
                  
                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                   $naik = $this->getLocInfo($param1); 
    
                  //$turun = $this->getLocInfo($param1);
                  $naik = $naik->results[0]->formatted_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                }
                else
                {

                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                  //$naik = $this->getLocInfo($param1); 
    
                  //$turun = $this->getLocInfo($param1);
                  $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak); 
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                }
             }
             else
             {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                
                  $naik = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->start_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);

                  //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";

             }
            }
            else
            {
              $a = $routingresult[$i]['step'][$j]['jalur'][0];
              $b = $routingresult[$i]['step'][$j]['jalur'][sizeof($routingresult[$i]['step'][$j]['jalur']) - 1];
              //return (array) $a;
              //ada yang salah dari jalur terakhir.
              if(empty($a)||empty($b))
              {
                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                  $naik = $this->getLocInfo($param1); 
                  $turun = $this->getLocInfo($param2);
                  $naik = $naik->results[0]->formatted_address;
                  $turun = $turun->results[0]->formatted_address;
                  $naik = explode(", ", $naik);
                  $turun = explode(", ", $turun);
                  


                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);
                  $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );
    
                  //$angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                  $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name .". ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign ;
                  $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot ".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
              }
              else
              {
                if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) )
                {
                  continue;
                }
                $param1 = $a->lat .",". $a->lng;
                $param2 = $b->lat .",". $b->lng;
                //$naik = $this->getLocInfo($param1); 
                //$turun = $this->getLocInfo($param2);
                $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                $turun = $b->place_info;
                $naik = explode(", ", $naik);
                $turun = explode(", ", $turun);

                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);

                $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );

                $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ." ( ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign." )" ;
                //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                //$routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
                $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot No.".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
              }
            }

          }
      }

      return [ "status"=>$status, "routingresult"=> $routingresult ];//json_decode(json_encode($shapes), true);
    }
    else
    { 
      duaAngkot:
      //deklarasi shape_id_start
      for ($i=0; $i < sizeof($angkot_start) ; $i++) { 
        # code...
        $shape_id_start[] = explode(", ", $angkot_start[$i]->shape_id) ;
      }
      //deklarasi shape_id finish;
      for ($i=0; $i < sizeof($angkot_finish) ; $i++) { 
        # code...
        $shape_id_finish[] = explode(", ", $angkot_finish[$i]->shape_id) ;
      }
      
      //cek ada shape id yg sama di shape id angkot start dan shape id angkot finish.
      for ($i=0; $i < sizeof($shape_id_start) ; $i++) { 
        # code...
        for ($j=0; $j < sizeof($shape_id_finish) ; $j++) { 
          # code...
          $intersec_shape_id[$i][$j] = array_map("unserialize", array_intersect(array_map("serialize", $shape_id_start[$i]) , array_map("serialize", $shape_id_finish[$j] ))) ;
        }
      }

     
     //deklarasi no intersec, array penentu angkot mana berhubungan dengan angkot mana di titik finish.
      for ($i=0; $i < sizeof($intersec_shape_id) ; $i++) { 
        # code...
        for ($j=0; $j < sizeof($intersec_shape_id[$i]) ; $j++) { 
          # code...
          if(!empty( $intersec_shape_id[$i][$j]) )
          { 
            $intersection_shape_id[] = $intersec_shape_id[$i][$j];
            $no_intersec[] = ["start"=>$i,"finish"=> $j];
          }
        }
      }

      //return $no_intersec;
      //jika ada shape id yang bersimpangan, maka ambil sebagai tempat persinggungan.
      //return $intersection_shape_id ;
      if(!empty( $intersection_shape_id) ) //fungsi 2 angkot // fungsi dua angkot
      {
        //return $intersection_shape_id_numeric; //"ada shape yang bersimpangan.";\
        //pemindahan associative array to numeric array
        for ($i=0; $i < sizeof($intersection_shape_id) ; $i++) { 
          # code...
          $a = [];
          foreach ($intersection_shape_id[$i] as $key => $value) {
            # code...
            $a[] = $value;
          }
          $intersection_shape_id_numeric[] = $a;
        }

        // return ['no_intersec'=>$no_intersec , '$intersection_shape_id_numeric[$key]'=> $intersection_shape_id_numeric, 'angkot_start'=>$gabung_start, 'angkot_finish'=>$gabung_finish ];

        //hapus yang salah jalur, dari angkot pertama
        foreach ($no_intersec as $key => $value) {
          # code...
          //return [ $gabung_start[ $no_intersec[1]['start'] ], $gabung_finish[ $no_intersec[1]['finish'] ] ];
          $sid = $angkot_start[ $no_intersec[$key]['start'] ]->shape_id ;
          $tmp = explode(", ", $sid) ;
          $potong_awal = array_keys($tmp, $gabung_start[ $no_intersec[$key]['start'] ][1]->titik_terdekat->shape_id );
          $potong_akhir = array_keys($tmp, $intersection_shape_id_numeric[$key][0] )  ;
          if($potong_awal[0] >= $potong_akhir[0]){
            //return "hapus intersec dengan key ini";
            unset($no_intersec[$key] );
            unset( $intersection_shape_id_numeric[$key] ); 
          }
        }
        
        //hapus yang salah jalur, dari angkot kedua
        foreach ($no_intersec as $key => $value) {
          # code...
          //return [ $gabung_start[ $no_intersec[1]['start'] ], $gabung_finish[ $no_intersec[1]['finish'] ] ];
          $sid = $angkot_finish[ $no_intersec[$key]['finish'] ]->shape_id ;
          $tmp = explode(", ", $sid) ;
          $potong_awal = array_keys($tmp, $intersection_shape_id_numeric[$key][0] )  ;
          $potong_akhir = array_keys($tmp, $gabung_finish[ $no_intersec[$key]['finish'] ][1]->titik_terdekat->shape_id );
          
          if($potong_awal[0] >= $potong_akhir[0]){
            //return "hapus intersec dengan key ini";
            //return [$potong_awal, $potong_akhir, $tmp, $intersection_shape_id_numeric[$key][0], $gabung_finish[ $no_intersec[$key]['finish'] ][1]->titik_terdekat->shape_id  ] ;
            unset($no_intersec[$key] );
            unset( $intersection_shape_id_numeric[$key] );  
          }
        }
        // return [$intersection_shape_id_numeric, $no_intersec];
        //return $intersection_shape_id_numeric; #shapeid yang berintersection
        //pemindahan array associative ke array numeric
        if(empty($no_intersec)){
          
          return ['status'=>'Bad', 'note'=>'tidak ditemukan angkot untuk jalur ini']; 
          //goto tigaAngkot;
        } 
        foreach ($no_intersec as $key => $value) {
          # code...
          $tmpno_intersec[] = $value;
          $tmpIntersection_shape_id_numeric[] = $intersection_shape_id_numeric[$key ] ;
        }
        $no_intersec = $tmpno_intersec; 
        $intersection_shape_id_numeric = $tmpIntersection_shape_id_numeric ; 
        $tmpno_intersec =[];
        $tmpIntersection_shape_id_numeric = [];

        //return [ $no_intersec, $intersection_shape_id_numeric ];

        for ($i=0; $i < sizeof($no_intersec) ; $i++) { 
          # code...
          $step = [];
          $index_terakhir = sizeof($intersection_shape_id_numeric[$i]) -1 ;
          $index_pertama = 0;
          $potong_akhir_start = DB::select("select id from shapes where shape_id =".$intersection_shape_id_numeric[$i][$index_terakhir]." and shape_pt_sequence = 0 ");
          //return $intersection_shape_id_numeric[10/*$i*/][0] ;
          //return $potong_akhir_start;

          if($walk_route == 'no')
          {

            $walking_path = 'unavailable';
          }
          else
          { 

            $walking_path = $this->get_walking_route($data['start']->start_position , $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat);
          }

          $step[] = ["angkot"=>[(object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ]] , "jalur"=>[ (object) $data['start']->start_position , (object) $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat, $walking_path ]   ];
          //return  $potong_akhir_start[0]->id;

          $angkot_start_intersec = $angkot_start[$no_intersec[$i]['start']];
          $data_shapes = $this->get_trayek_potong($angkot_start_intersec->route_id ,  $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat->id, $potong_akhir_start[0]->id );
          
          //return $data_shapes;
          

          $step[] = ["angkot"=>[$angkot_start_intersec ], "jalur" => $data_shapes ];
          
          $angkot_finish_intersec = $angkot_finish[$no_intersec[$i]['finish']];
          $data_shapes2 = $this->get_trayek_potong($angkot_finish_intersec->route_id ,  $potong_akhir_start[0]->id , $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat->id );

          $step[] = ["angkot"=>[$angkot_finish_intersec] , "jalur" => $data_shapes2 ];
          

          if($walk_route == 'no')
          {

            $walking_path = 'unavailable';

          }
          else
          {

            $walking_path = $this->get_walking_route($pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat , $data['finish']->start_position);

          }

          $step[] = ["angkot"=>[(object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ] ], "jalur"=>[ (object) $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat ,(object) $data['finish']->start_position, $walking_path ]  ];

          //perubahan jadi object semua
          /*foreach ($step as $ii => $value) {
            # code...
            foreach ($step[$ii]['jalur'] as $j => $value) {
              # code...
              $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j] ;
              
            }
          }*/
          //return $step;
          //penambahan distance 
          foreach ($step as $ii => $value) {
            # code...
            $jarak = [];
            foreach ($step[$ii]['jalur'] as $j => $value) {
              # code...

              if(!is_object($step[$ii]['jalur'][$j]) || !is_object($step[$ii]['jalur'][$j] ) )
              {
               $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
               $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
              }
              if(!isset($step[$ii]['jalur'][$j]->lat) || !isset($step[$ii]['jalur'][$j]->lng) )
                {continue;}
              $lat1 = (float) $step[$ii]['jalur'][$j]->lat  ;
              $lng1 = (float) $step[$ii]['jalur'][$j]->lng  ;
              /*if($j == 133){
                if( !isset($step[$ii]['jalur'][$j+1]->lat ) ){
                  return $step ;
                }
              }*/
              if( !isset($step[$ii]['jalur'][$j+1] ) )
              {
                continue;
              }
              if( !isset($step[$ii]['jalur'][$j+1]->lat) || !isset($step[$ii]['jalur'][$j+1]->lng  ) )
                {continue;}
              $lat2 = (float) $step[$ii]['jalur'][$j+1]->lat;
              $lng2 = (float) $step[$ii]['jalur'][$j+1]->lng;
              
              
              $jarak[$ii][] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);

            }
            $distance = array_sum( $jarak[$ii] ) ;
            $step[$ii]['distance'] = $distance;
          }//*/

          //manipulating price inside angkot.
          foreach ($step as $key => $value) {
            # code...
            
            if($value['angkot'][0]->price == 0)
            {
              continue;
            }
            else
            { 
              $route_id = $value['angkot'][0]->route_id; 
              $total_jarak = $this->get_distance_route($route_id);

              if($value['distance'] <= 1000 ) // satu kilo pertama
              {
                $value['angkot'][0]->price = 1500;  
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (1/3) * $total_jarak) ) // 1/3 pertama
              {
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) / 3 ))  ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                if(substr( $value['angkot'][0]->price , -3) < 499 )
                {
                  $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                }
                else
                {
                  $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                }                  
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 2/3 pertama
              {
                # code...
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                if(substr( $value['angkot'][0]->price , -3) < 499 )
                {
                  $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                }
                else
                {
                  $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                }                  
              }
              else
              {
                $value['angkot'][0]->price = $value['angkot'][0]->price;
              }
            
            }

          }

          //Coding untuk logika harga
          $total_cost = 0;   
          foreach ($step as $key => $value) {
            # code... sedang mengerjakan price ini
            $temp = (object) $value['angkot'][0];
            $total_cost = $total_cost + $temp->price;//$total_cost + $value['angkot'][0]->price; 
          }
          
           $routingresult[] = [ "step"=> $step, 'total_cost'=>$total_cost ];
        } //ini ambil intersec terakhir

        for ($i=0; $i < sizeof($no_intersec) ; $i++) { 
          # code...
          $step = [];
          $index_terakhir = sizeof($intersection_shape_id_numeric[$i]) -1 ;
          $index_pertama = 0;
          $potong_akhir_start = DB::select("select id from shapes where shape_id =".$intersection_shape_id_numeric[$i][$index_pertama]." and shape_pt_sequence = 0 ");
          
         
          //if( empty( $potong_akhir_start) ){
          //  return $intersection_shape_id_numeric[$i][$index_pertama] ;
          //}

          if($walk_route == 'no')
          {

            $walking_path = 'unavailable';
          }
          else
          { 

            $walking_path = $this->get_walking_route($data['start']->start_position , $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat);
          }

          $step[] = ["angkot"=>[(object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ]] , "jalur"=>[ (object) $data['start']->start_position , (object) $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat, $walking_path ]   ];
          //return  $potong_akhir_start[0]->id;

          $angkot_start_intersec = $angkot_start[$no_intersec[$i]['start']];
          $data_shapes = $this->get_trayek_potong($angkot_start_intersec->route_id ,  $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat->id, $potong_akhir_start[0]->id );
          
          //return $data_shapes;
          

          $step[] = ["angkot"=>[$angkot_start_intersec ], "jalur" => $data_shapes ];
          
          $angkot_finish_intersec = $angkot_finish[$no_intersec[$i]['finish']];
          $data_shapes2 = $this->get_trayek_potong($angkot_finish_intersec->route_id ,  $potong_akhir_start[0]->id , $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat->id );

          $step[] = ["angkot"=>[$angkot_finish_intersec] , "jalur" => $data_shapes2 ];
          

          if($walk_route == 'no')
          {

            $walking_path = 'unavailable';

          }
          else
          {

            $walking_path = $this->get_walking_route($pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat , $data['finish']->start_position);

          }

          $step[] = ["angkot"=>[(object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ] ], "jalur"=>[ (object) $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat ,(object) $data['finish']->start_position, $walking_path ]  ];

          //perubahan jadi object
          /*foreach ($step as $ii => $value) {
            # code...
            foreach ($step[$ii]['jalur'] as $j => $value) {
              # code...
              $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j] ;
              
            }
          }*/

          //penambahan distance  
          foreach ($step as $ii => $value) {
            # code...
            $jarak = [];
            foreach ($step[$ii]['jalur'] as $j => $value) {
              # code...

              if(!is_object($step[$ii]['jalur'][$j]) || !is_object($step[$ii]['jalur'][$j] ) )
              {
               $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
               $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
              }
              if(!isset($step[$ii]['jalur'][$j]->lat) || !isset($step[$ii]['jalur'][$j]->lng) )
                {continue;}
              $lat1 = (float) $step[$ii]['jalur'][$j]->lat  ;
              $lng1 = (float) $step[$ii]['jalur'][$j]->lng  ;
              //return [$lat1,$lng1];
              /*if( !isset($step[$ii]['jalur'][$j+1]) ){
                continue;
              }*/
              if(!isset($step[$ii]['jalur'][$j+1]->lat) || !isset($step[$ii]['jalur'][$j+1]->lng) )
                {continue;}
              $lat2 = (float) $step[$ii]['jalur'][$j+1]->lat;
              $lng2 = (float) $step[$ii]['jalur'][$j+1]->lng;
              
              
              $jarak[$ii][] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);

            }
            $distance = array_sum( $jarak[$ii] ) ;
            $step[$ii]['distance'] = $distance;
          }//

          //manipulating price inside angkot.
          foreach ($step as $key => $value) {
            # code...
            
            if($value['angkot'][0]->price == 0)
            {
              continue;
            }
            else
            { 
              $route_id = $value['angkot'][0]->route_id; 
              $total_jarak = $this->get_distance_route($route_id);

              if($value['distance'] <= 1000 ) // satu kilo pertama
              {
                $value['angkot'][0]->price = 1500;  
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (1/3) * $total_jarak) ) // 1/3 pertama
              {
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) / 3 ))  ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                if(substr( $value['angkot'][0]->price , -3) < 499 )
                {
                  $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                }
                else
                {
                  $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                }                  
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 2/3 pertama
              {
                # code...
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                if(substr( $value['angkot'][0]->price , -3) < 499 )
                {
                  $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                }
                else
                {
                  $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                }                  
              }
              else
              {
                $value['angkot'][0]->price = $value['angkot'][0]->price;
              }
            
            }

          }

          //Coding untuk logika harga
          $total_cost = 0;   
          foreach ($step as $key => $value) {
            # code... sedang mengerjakan price ini
            $temp = (object) $value['angkot'][0];
            $total_cost = $total_cost + $temp->price;

            }
          
           $routingresult[] = [ "step"=> $step, 'total_cost'=>$total_cost ];
           /*if($i == 1){
              return $routingresult;
            }*/
        } // ini ambil intersec pertama */
        
        //penambahan total_distance di step
        foreach ($routingresult as $i => $value) {
          # code...
          $total_distance = 0;
          foreach ($routingresult[$i]['step'] as $j => $value) {
            # code...
            $tmp = $value['distance'];

            $total_distance = $total_distance + $tmp;
          }
          $routingresult[$i]['total_distance'] = $total_distance;
          
        }

        //return $routingresult[0];

        usort($routingresult, function($a, $b) {return $a['total_cost'] - $b['total_cost']; }); //sorting by total cost
        $routingresult = array_slice($routingresult, 0,5) ; //return cuman 3 kombinasi
        

        usort($routingresult, function($a, $b) {return $a['total_distance'] - $b['total_distance']; }); //sorting by total_distance
        $routingresult = array_slice($routingresult, 0,3) ; //return cuman 3 kombinasi


        
        //perbaiki kalau jalur salah.
        foreach ($routingresult as $i => $value) {
          # code...
          foreach ($routingresult[$i]['step'] as $j => $value) {
            if($j == 0)
            {
              $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
              $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
              //$jalur2 = json_decode(json_encode($jalur2),true );
              if($jalur[1]->id !== $jalur2[0]->id){
                $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse( (array) $jalur2);
              }
            }
            else if($j == sizeof($routingresult[$i]['step'])-1 ){
              //$jalur = $routingresult[$i]['step'][$j]['jalur'] ;
              //$jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
              continue;
            }
            else
            {
              $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
              $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
              if(!isset($jalur2[0]->id )){
                return $jalur2;
              }
              if($jalur[sizeof($jalur)-1]->id !== $jalur2[0]->id){
                $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse( $jalur2);
                //return [$jalur, $jalur2];
            }

            }

          }
        }

        
        //penambahan logika ket // penambahan keterangan        
        foreach ($routingresult as $i => $value) {
          # code...
          foreach ($routingresult[$i]['step'] as $j => $value) {
            # code...
            if($j == 0)
            {
              if($walk_route=="no")
              {
                $a = $routingresult[$i]['step'][$j]['jalur'][0];
                $b = $routingresult[$i]['step'][$j]['jalur'][1];
                  if(empty($b->place_info))              
                  {
                    if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                                  continue;
                                }
                
                    $param1 = $a->lat .",". $a->lng;
                    $param2 = $b->lat .",". $b->lng;
                    $turun = $this->getLocInfo($param2);

                    $turun = $turun->results[0]->formatted_address;
                    
                    $turun = explode(", ", $turun);
                    $jarak = $routingresult[$i]['step'][$j]['distance'];
                    $jarak = explode(".", $jarak);
                    //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                  }
                  else
                  {
                    if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                      continue;
                    }
                
                    $param1 = $a->lat .",". $a->lng;
                    $param2 = $b->lat .",". $b->lng;
                    
                    $turun = $b->place_info;
                    $turun = explode(", ", $turun);
                    $jarak = $routingresult[$i]['step'][$j]['distance'];
                    $jarak = explode(".", $jarak);
                    //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                  }
              }
              else
              {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                $turun = explode(", ", $turun);
                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);
                //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
              }
            }
            else if($j == sizeof($routingresult[$i]['step'])-1 )
            {
              if($walk_route=="no")
              { 
                $a = $routingresult[$i]['step'][$j]['jalur'][0];
                $b = $routingresult[$i]['step'][$j]['jalur'][1];
                  
                if(empty($a->place_info)) 
                {
                  
                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                   $naik = $this->getLocInfo($param1); 
    
                  //$turun = $this->getLocInfo($param1);
                  $naik = $naik->results[0]->formatted_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                }
                else
                {

                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                  //$naik = $this->getLocInfo($param1); 
    
                  //$turun = $this->getLocInfo($param1);
                  $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak); 
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                }
             }
             else
             {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                
                  $naik = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->start_address;
                  $naik = explode(", ", $naik);

                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);

                  //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";

             }
            }
            else
            {
              $a = $routingresult[$i]['step'][$j]['jalur'][0];
              $b = $routingresult[$i]['step'][$j]['jalur'][sizeof($routingresult[$i]['step'][$j]['jalur']) - 1];
              //return (array) $a;
              //ada yang salah dari jalur terakhir.
              if(empty($a)||empty($b))
              {
                  if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                    continue;
                  }
                  $param1 = $a->lat .",". $a->lng;
                  $param2 = $b->lat .",". $b->lng;
                  $naik = $this->getLocInfo($param1); 
                  $turun = $this->getLocInfo($param2);
                  $naik = $naik->results[0]->formatted_address;
                  $turun = $turun->results[0]->formatted_address;
                  $naik = explode(", ", $naik);
                  $turun = explode(", ", $turun);
                  


                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);
                  $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );
    
                  //$angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                  $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name .". ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign ;
                  $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot ".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
              }
              else
              {
                if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) )
                {
                  continue;
                }
                $param1 = $a->lat .",". $a->lng;
                $param2 = $b->lat .",". $b->lng;
                //$naik = $this->getLocInfo($param1); 
                //$turun = $this->getLocInfo($param2);
                $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                $turun = $b->place_info;
                $naik = explode(", ", $naik);
                $turun = explode(", ", $turun);

                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);

                $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );

                $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ." ( ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign." )" ;
                //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                //$routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
                $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot No.".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
              }
            }

          }
        }

        return [ "status"=>$status, "routingresult"=> $routingresult ]; //[ $angkot_start_intersec, $angkot_finish_intersec ];
      }
      else // fungsi 3 angkot.
      {
         tigaAngkot:
         //ambil all angkot.
          $all_angkot = DB::select("select trips.route_id,trip_short_name,trip_headsign,shape_id, route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id");
          
          //deklarasi shape_id $all_angkot,
          for ($i=0; $i < sizeof($all_angkot) ; $i++) { 

             $shape_id_all[] = explode(", ", $all_angkot[$i]->shape_id) ;
          }
          
          //cari yang bersinggungan shape_id nya dengan angkot_finish.
          awal:
          for ($i=0; $i < sizeof($shape_id_finish) ; $i++) {

            for ($j=0; $j < sizeof($shape_id_all) ; $j++) { 
              $intersec_shape_id_finish_all[$i][$j] = array_map("unserialize", array_intersect(array_map("serialize", $shape_id_finish[$i]) , array_map("serialize", $shape_id_all[$j] ))) ;
            }
          }

          
         //return $angkot_finish;
          $no_intersec_finish_all = [];
          for ($i=0; $i < sizeof($intersec_shape_id_finish_all) ; $i++) { 
           # code...
            for ($j=0; $j < sizeof($intersec_shape_id_finish_all[$i]) ; $j++) { 
              # code...
              if(!empty( $intersec_shape_id_finish_all[$i][$j] ) ) // ga ada yang masuk kesini.
              { 

                if( $intersec_shape_id_finish_all[$i][$j] === $shape_id_finish[$i] )
                {
                  continue;
                }

                $intersection_shape_id_finish_all[] = $intersec_shape_id_finish_all[$i][$j];
                $no_intersec_finish_all[] = ["finish"=>$i,"all"=> $j];
              }

            }
          }
          
      
          //perubahan hasil intersec dari array associative ke array numeric
          for ($i=0; $i < sizeof($intersection_shape_id_finish_all) ; $i++) { 
            # code...
            $temp = array();
            foreach ($intersection_shape_id_finish_all[$i] as $key => $value) {
              # code...
              $temp[] = $value;
            }
            $intersection_shape_id_finish_all_numeric[] = $temp;
          }

          for ($i=0; $i < sizeof($intersection_shape_id_finish_all_numeric) ; $i++) { 
            # code...
            for ($j=0; $j < sizeof($shape_id_start) ; $j++) { 
              # code...
              $intersec_shape_id_all_start[$i][$j] = array_map("unserialize", array_intersect(array_map("serialize", $intersection_shape_id_finish_all_numeric[$i]) , array_map("serialize", $shape_id_start[$j] ))) ;
            }
          }
          
         // print_r($intersec_shape_id_all_start);
          $final = [];
          $no_final = [];
          for ($i=0; $i < sizeof($intersec_shape_id_all_start) ; $i++) { 
           # code...
            for ($j=0; $j < sizeof($intersec_shape_id_all_start[$i]) ; $j++) { 
              # code...
              if(!empty( $intersec_shape_id_all_start[$i][$j] ) ) // ga ada yang masuk kesini.
              { 
               /* if($intersec_shape_id_finish_all[$i][$j] === $shape_id_finish[$i]){
                  continue;
                }*/
                $final[] = $intersec_shape_id_all_start[$i][$j];
                $no_intersec_finish_all[] = ["finish"=>$i,"all"=> $j];
                $no_final[] =["all"=>$i,"start"=> $j];
              }


            }
          }
          $final_result = $final;
          //$no_final_result = $no_intersec_finish_all ;

          if(empty( $final_result))
          { 
            //pengambilan angkot dari $intersection_shape_id_finish_all
            //$step[] = $shape_id_finish;

            
            $intersection_angkot_finish_dengan_all = [];
            $jejak_no_intersec_finish_all[] = $no_intersec_finish_all;
            for ($i=0; $i < sizeof($no_intersec_finish_all) ; $i++) { 
              # code...
              $intersection_angkot_finish_dengan_all[] = $all_angkot[$no_intersec_finish_all[$i]['all']];
            }

            $no_intersec_finish_all = [];
            $jejak_angkot_dari_awal[] = $intersection_angkot_finish_dengan_all;
            $shape_id_finish =[];

            for ($i=0; $i < sizeof($intersection_angkot_finish_dengan_all) ; $i++) { 
             $shape_id_finish[] = explode(", ", $intersection_angkot_finish_dengan_all[$i]->shape_id) ;
            }
          
            goto awal;
          }
          else
          {
            //$final_result;
           //return $no_intersec_finish_all;
            //return $no_final;
            
            $jejak_no_intersec_finish_all[] = $no_intersec_finish_all;

            for ($i=0; $i < sizeof($no_intersec_finish_all) ; $i++) { 
              # code...
              $intersection_angkot_finish_dengan_all[] = $all_angkot[$no_intersec_finish_all[$i]['all']];
            }
            $no_intersec_finish_all = [];
            $jejak_angkot_dari_awal[] = $intersection_angkot_finish_dengan_all;
           
           //return sizeof( $jejak_angkot_dari_awal[0] ) ;
            //return $jejak_angkot_dari_awal;

            $step[] = $angkot_finish;

            for ($i=0; $i < sizeof($jejak_angkot_dari_awal) ; $i++) {  // supaya urutan step nya jelas.
              # code...
              $step[] = $jejak_angkot_dari_awal[$i];
            }
            

            
            $step[] = $angkot_start;



            $reverse = array_reverse($step); //reverse order
            //return $reverse;

            for ($i=0; $i < sizeof($reverse) ; $i++) // penghapusan duplicate
            { 
              
              $_tmp = array();
              foreach($reverse[$i] as $key => $value) {
                  if(!array_key_exists($value->route_id , $_tmp) ) {
                      $_tmp [$value->route_id] = $value;
                  }
              }
              $reverse_unduplicated[$i] = array_values($_tmp);
            } 
            
           // return $reverse_unduplicated;   
           
            //cari persimpangan untuk keperluan mencari kombinasi yang sampai.
            for ($i=0; $i < sizeof($reverse_unduplicated) ; $i++) { 
              
              $tmp_shape_id_reverse = [];
              for ($j=0; $j < sizeof($reverse_unduplicated[$i]) ; $j++) { 
                # code...
                
                $tmp_shape_id_reverse[] = explode(", ", $reverse_unduplicated[$i][$j]->shape_id) ;
                
              }
              $shape_id_reverse[] = $tmp_shape_id_reverse;

            }
            
            

            $index = 0;
            cari_jalur_step_by_step:
            $key = [];
            // pencarian intersection angkot dari step 0 ke angkot setelahnya. 
            foreach ($shape_id_reverse[$index] as $i => $value) {
              # code...
              foreach ($shape_id_reverse[$index+1] as $j => $value) {
                # code...
                $intersec_to_next_step[$i][$j] = array_map("unserialize", array_intersect(array_map("serialize",$shape_id_reverse[$index][$i]) , array_map("serialize",  $shape_id_reverse[$index+1][$j] ))) ; //ambil intersection
                if(!empty($intersec_to_next_step[$i][$j]))
                {
                  $intersec_to_next_step_full[$i][$j] = $intersec_to_next_step[$i][$j] ;
                  $key[] = ["index_satu"=>$i, "index_dua"=>$j];
                  //$result_shape_id_reverse[$i][$j] = $shape_id_reverse[$index+1][$j] ; #bisa dibenerin
                  $result_shape_id_reverse[$index+1][$j] = $shape_id_reverse[$index+1][$j] ; // ini yang benar.

                  $result_angkot_reverse[$i][$j] = $reverse_unduplicated[$index+1][$j];
                }  

              }
            }

            $temp = []; // hapus temp
            $temp = $intersec_to_next_step_full;//$result_shape_id_reverse;
            $jejak_result_shape_id_reverse[] = $temp;
            $temp =[]; // hapus lagi.

            //return $reverse_unduplicated;
            //return $shape_id_reverse;

            $jejak_result_angkot_reverse[] = $result_angkot_reverse;
            $result_angkot_reverse = [];

            //return $result_angkot_reverse;
            //return $result_shape_id_reverse ; 
            //return $intersec_to_next_step_full;

            foreach ( $shape_id_reverse[count($shape_id_reverse) - 1] as $i => $value) { //$shape_id_reverse_index terakhir, artinya angkot finish.
              # code...
              foreach ($result_shape_id_reverse[$index+1] as $j => $value) { // index pertamanya harus di revisi
                # code...
                  $intersec_to_next_step2[$i][$j] = array_map("unserialize", array_intersect(array_map("serialize",$shape_id_reverse[count($shape_id_reverse)-1][$i]) , array_map("serialize",  $result_shape_id_reverse[$index+1][$j] ))) ; //ambil

                  //$jejak_shape_id_reverse_from_start_to_end[$index+1][$j] = $result_shape_id_reverse[$index+1][$j] ; 
                  
                  if(!empty($intersec_to_next_step2[$i][$j]))
                  {
                    $intersec_to_next_step_to_step_akhir_full[$i][$j] = $intersec_to_next_step2[$i][$j];
                    //return $intersec_to_next_step_to_step_akhir_full ;
                  } 

                
              }
            }

            $result_shape_id_reverse = [] ; //penghapusan agar yang sebelumnya tidak masuk ke berikutnya.

            if(!isset($intersec_to_next_step_to_step_akhir_full))
            {
              //return '$intersec_to_next_step_to_step_akhir_full undefined' ;
              
              $index = $index+1;
              goto cari_jalur_step_by_step;
            }
            else
            {
              $jejak_result_shape_id_reverse[] = $intersec_to_next_step_to_step_akhir_full ;
              //return $jejak_result_shape_id_reverse ;
              
              $jejak_result_angkot_reverse[] = $intersec_to_next_step_to_step_akhir_full ;
              //return $jejak_result_shape_id_reverse ;
              

              if(sizeof($jejak_result_shape_id_reverse) == 2)
              {
                //return $jejak_result_angkot_reverse;
                //return $jejak_result_shape_id_reverse;

                for ($i=sizeof($jejak_result_shape_id_reverse)-1; $i >= 0 ; $i--) { 
                  # code...
                  //$berapa[] = $i;
                  foreach ($jejak_result_shape_id_reverse[$i] as $j => $value) {
                    # code...
                    foreach ($jejak_result_shape_id_reverse[$i][$j] as $k => $value) {
                      # code...
                      if($i ==sizeof($jejak_result_shape_id_reverse)-1 )
                      {
                        $look[$i][] = $j."-".$k ;
                        $path_searcher[$i][$j][$k] = $jejak_result_shape_id_reverse[$i][$j][$k] ;
                        $angkot_searcher[$i][$j][$k] = $jejak_result_angkot_reverse[$i][$j][$k] ;
                      }
                      else
                      {
                        $look[$i][] = $k."-".$j ;
                        $path_searcher[$i][$k][$j] = $jejak_result_shape_id_reverse[$i][$j][$k] ;//$j ;
                        $angkot_searcher[$i][$k][$j] = $jejak_result_angkot_reverse[$i][$j][$k] ;
                      }

                    }
                  }
                }
                //return $path_searcher;
                $temp =[];
                $temp2 =[];
                foreach ($path_searcher as $key => $value) {
                  # code...
                  $temp[] = $value; // jejak shape id;
                  $temp2[] = $angkot_searcher[$key]; // jejak angkot
                }
                
                $path_searcher = $temp;
                $angkot_searcher = $temp2;
                $temp2 =[];
                $temp =[];

                //return $path_searcher;
                //return $angkot_searcher;

                
                proses_path_search:
                $jalan = [];
                foreach ($path_searcher as $i => $value) {
                  # code...
                  $plus_i = $i+1 ;
                  foreach ($path_searcher[$i] as $j => $value) {
                    # code...
                    foreach ($path_searcher[$i][$j] as $k => $value) {
                      # code...
                      if(!isset($path_searcher[$plus_i]))
                        {break;}
                      foreach ($path_searcher[$plus_i] as $l => $value) {
                        # code...
                        foreach ($path_searcher[$plus_i][$l] as $m => $value) {
                          # code...
                          if($k !== $l)
                            {continue;}
                          $path_result[$i][$j][$k][$m] = $path_searcher[$plus_i][$l][$m] ;
                          $path_key[$i][] = $j."-".$k."-".$m ;
                          $path_key2[$i][] = $j."-".$k."-".$l."-".$m ;
                          $jalan[] = $j."-".$k.",".$l."-".$m ;
                        }
                      }
                      
                    }
                  }
                }

                //return $angkot_searcher;
                $angkot = [];
                $jalur = [];
                $step = [];
                $routingresult =[];
                
                $index = [];
                foreach ($path_key2 as $i => $value) {
                  # code...
                  foreach ($path_key2[$i] as $j => $valuej) {
                    # code...
                    $index[] = explode("-", $valuej) ;

                  }
                }

                //return $index;
               // return $path_searcher;
               $new_reverse_unduplicated = array_reverse($reverse_unduplicated);
               //return $new_reverse_unduplicated;
                //return $index ;
                #pencarian Angkot.
                $angkot = [];
                //penghapusan index kedua;
                /*foreach ($index as $i => $value) {
                   
                   foreach ($index[$i] as $j => $value) {
                     # code...
                     if($j == 2)
                     {
                      unset( $index[$i][$j] );
                      //unset( $angkot[$i][$j] );
                     } 
                     // $angkot[$i][] = $new_reverse_unduplicated[$j][$value];
                     // $index_pickup_points[$i][] = $value;
                   }

                }

                foreach ($index as $i => $value) {
                   
                  foreach ($index[$i] as $j => $value) {
                   # code...
                   $tmpindex[] = $value;
                  }
                  $index[$i] = $tmpindex ;
                  $tmpindex = []; 
                }*/
                //return $new_reverse_unduplicated ; 
                //2return $index;
                foreach ($index as $i => $value) {
                   
                   foreach ($index[$i] as $j => $value) {
                     # code...
                     if($j == 2){
                      continue;
                     }
                     $angkot[$i][] = $new_reverse_unduplicated[$j][$value];
                     $index_pickup_points[$i][] = $value;
                   }

                }

                //return [$angkot, $index];
                //return $index;
                //return $index_pickup_points;
                #pencarian potong
                foreach ($jalan as $i => $value) {
                  # code...
                  $jalan2[] = explode(",", $value); 
                }
                $jalan =[];
                foreach ($jalan2 as $i => $value) {
                  # code...
                  foreach ($jalan2[$i] as $j => $value) {
                    # code...
                    $jalan[$i][] = explode("-", $value);
                  }
                }

                foreach ($jalan as $i => $value) {
                  # code...
                  foreach ($jalan[$i] as $j => $value) {
                    # code...
                    $potong[$i][] = $path_searcher[$j][ $jalan[$i][$j][0] ][ $jalan[$i][$j][1]];
                  }
                }

                foreach ($potong as $i => $value) {
                  # code...
                  foreach ($potong[$i] as $j => $value) {
                    # code...
                    if($j == 0)
                    {
                      $potong1[] = $value;
                    }
                    else
                    {
                      $potong2[] = $value;
                    }
                  }
                }

                #Pencarian Jalur
                //$pickup_point_finish = array_reverse($pickup_point_finish);
                //$pickup_point_start = array_reverse($pickup_point_start);
                $jalur = [];
                $array =[];
                //return $angkot_start;
                //  return $potong1[7];

                //seleksi jalur pulang pergi
                //hapus yang salah jalur, dari angkot pertama
                foreach ($angkot as $i => $value) {
                  # code...
                  //return [ $gabung_start[ $no_intersec[1]['start'] ], $gabung_finish[ $no_intersec[1]['finish'] ] ];
                  $angkot_ke = 0;
                  $sid = $angkot[$i][$angkot_ke]->shape_id;//$angkot_start[ $no_intersec[$key]['start'] ]->shape_id ;
                  $tmp = explode(", ", $sid) ;
                  $potong_awal = array_keys($tmp, $gabung_finish[ $index[$i][ $angkot_ke ] ][1]->titik_terdekat->shape_id );
                  $potong_akhir = array_keys($tmp, end($potong1[$i] ) )  ;

                  
                  if($potong_awal[0] >= $potong_akhir[0]){
                    //return "hapus intersec dengan key ini";
                    unset($angkot[$i] );
                    unset( $potong1[$i] );
                    unset( $potong2[$i] );
                    unset($index[$i] );
                    unset($index_pickup_points[$i] );
                  }
                }

                //hapus salah jalur dari angkot kedua
                foreach ($angkot as $i => $value) {
                  # code...
                  
                  $sid = $angkot[$i][1]->shape_id;//$angkot_start[ $no_intersec[$key]['start'] ]->shape_id ;
                  $tmp = explode(", ", $sid) ;
                  $potong_awal = array_keys($tmp, end($potong1[$i] ) )  ;
                  $potong_akhir = array_keys($tmp, end($potong2[$i] )  );
                  
                  //return [ $potong_awal, $potong_akhir, $angkot[$i] ];
                  if($potong_awal[0] >= $potong_akhir[0]){
                    //return "hapus intersec dengan key ini";
                    unset($angkot[$i] );
                    unset( $potong1[$i] );
                    unset( $potong2[$i] );
                    unset($index[$i] ); 
                    unset($index_pickup_points[$i] );
                  }
                }

                //return [$angkot,$index, $gabung_start, $index_pickup_points];
                //hapus salah jalur dari angkot ketiga;
                foreach ($angkot as $i => $value) {
                  # code...
                  $angkot_ke = 3;
                  $sid = $angkot[$i][2]->shape_id;//$angkot_start[ $no_intersec[$key]['start'] ]->shape_id ;
                  $tmp = explode(", ", $sid) ;
                  $potong_awal = array_keys($tmp, end($potong2[$i] ) )  ;
                  $potong_akhir = array_keys($tmp, $gabung_start[ $index[$i][ $angkot_ke ] ] [1]->titik_terdekat->shape_id );
                  
                  // if(!isset($potong_awal[0]) || !isset($potong_akhir[0] ) ){
                  //   return [$potong_awal, $potong_akhir, $angkot[$i][2] , $gabung_start[ $index[$i][ $angkot_ke ] ][0] , $gabung_finish[ $index[$i][ $angkot_ke ] ][0] ] ;
                  // }
                  
                  if($potong_awal[0] >= $potong_akhir[0]){
                    //return "hapus intersec dengan key ini";
                    unset($angkot[$i] );
                    unset( $potong1[$i] );
                    unset( $potong2[$i] );
                    unset($index[$i] ); 
                    unset($index_pickup_points[$i] );
                  }
                }
                //return [$angkot, $potong1, $potong2, $index ];
                
                

                //masukin jalur
                foreach ($angkot as $i => $value) {
                  # code...
                  foreach ($angkot[$i] as $j => $value) {
                    # code...
                    if($j == 0)
                    { 
                      
                      $tmp_potong = DB::select("select id from shapes where shape_id =".end($potong1[$i])." and shape_pt_sequence = 0 ");
                      $tmp_potong = $tmp_potong[0]->id;
                      
                      //return $pickup_point_finish[$j]->titik_terdekat->id;

                      $jalur[$i][] = $this->get_trayek_potong($value->route_id, $pickup_point_finish[ $index[$i][0] ]->titik_terdekat->id, $tmp_potong  );

                      $array[$i][] = [$value->route_id, $pickup_point_finish[ $index[$i][0] ]->titik_terdekat->id, $tmp_potong];
                    }
                    elseif ($j == 1) {
                      $tmp_potong = DB::select("select id from shapes where shape_id =".end($potong1[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong = $tmp_potong[0]->id;
                      $tmp_potong2 = DB::select("select id from shapes where shape_id =".end($potong2[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong2 = $tmp_potong2[0]->id;
                      
                      $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong, $tmp_potong2  );
                      // $array[$i][]=[$value->route_id, $tmp_potong, $tmp_potong2];
                    }
                    elseif ($j == 2) {
                      $tmp_potong2 = DB::select("select id from shapes where shape_id =".end($potong2[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong2 = $tmp_potong2[0]->id;

                     $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong2 , $pickup_point_start[ $index[$i][3]  ]->titik_terdekat->id  ); // /*index terakhir na, index array angkot_start

                     // $array[$i][] = [$value->route_id, end($potong2[$i]) , $pickup_point_start[$index[$i][3]]->titik_terdekat->id ];
                    }
                  }
                } // jalur intersec terakhir. */

                //gabung antara angkot dan jalur
                foreach ($angkot as $i => $value) {
                  # code...
                  $step = [];

                  foreach ($angkot[$i] as $j => $value) {
                    # code...
                    if($j == 0)
                    {
                      if($walk_route == 'no')
                      {

                        $walking_path = 'unavailable';
                      }
                      else
                      {

                        $walking_path = $this->get_walking_route($pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position );
                      }

                      $step[] = ["angkot"=>[ (object) ["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ]] , 'jalur'=>[ $pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position, $walking_path ] ];
                      
                    }
                    if($j == 3){
                      return [$angkot[$i] , $jalur[$i] ] ; 
                    }
                    $step[] = ["angkot"=>[$value], "jalur"=>array_reverse( json_decode(json_encode($jalur[$i][$j]),true)  ) ] ;

                    if($j == sizeof($angkot[$i])-1 )
                    { 
                      if($walk_route == 'no')
                      {

                        $walking_path = 'unavailable';
                      }
                      else
                      {

                        $walking_path = $this->get_walking_route($pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position );
                      }

                      $step[sizeof($step)] = ["angkot"=> [ (object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ] ] , 'jalur'=>[ $data['start']->start_position , $pickup_point_start[ $index[$i][3] ]->titik_terdekat, $walking_path ]  ];
                    }

                  }

                   
                  //perubahan jadi object semua.
                  foreach ($step as $ii => $value) {
                    # code...
                    foreach ($step[$ii]['jalur'] as $j => $value) {
                      # code...
                      $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j] ;
                      
                    }
                  }

                  //penambahan distance 
                  //penambahan logika jarak
                  //return $step;
                  /*if($i == 48){
                    return $step;
                  }*/

                  foreach ($step as $ii => $value) {
                    # code...
                    $jarak = [];
                    foreach ($step[$ii]['jalur'] as $j => $value) {
                      # code...

                      if(!is_object($step[$ii]['jalur'][$j]) || !is_object($step[$ii]['jalur'][$j] ) )
                      {
                       $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                       $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                      }
                      if(!isset($step[$ii]['jalur'][$j]->lat) || !isset($step[$ii]['jalur'][$j]->lng) )
                        {continue;}
                      $lat1 = (float) $step[$ii]['jalur'][$j]->lat  ;
                      $lng1 = (float) $step[$ii]['jalur'][$j]->lng  ;
                      //return [$lat1,$lng1];
                      if(!isset($step[$ii]['jalur'][$j+1]->lat) || !isset($step[$ii]['jalur'][$j+1]->lng) )
                        {continue;}
                      $lat2 = (float) $step[$ii]['jalur'][$j+1]->lat;
                      $lng2 = (float) $step[$ii]['jalur'][$j+1]->lng;
                      
                      
                      $jarak[$ii][] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);

                    }
                    /*if(!isset($jarak[$ii])){
                      return $i;
                    }*/

                    $distance = array_sum( $jarak[$ii] ) ;
                    $step[$ii]['distance'] = $distance;
                  }

                                  
                  //manipulating price  inside angkot.
                  foreach ($step as $key => $value) {
                    # code...
                    
                    if($value['angkot'][0]->price == 0)
                    {
                      continue;
                    }
                    else
                    { 
                      $route_id = $value['angkot'][0]->route_id; 
                      $total_jarak = $this->get_distance_route($route_id);

                      if($value['distance'] <= 1000 ) // satu kilo pertama
                      {
                        $value['angkot'][0]->price = 1500;  
                      }
                      elseif ($value['distance'] > 1000 and $value['distance'] <= ( (1/3) * $total_jarak) ) // 1/3 pertama
                      {
                        $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) / 3 ))  ; //pembulatan
                        //$temp = substr($value['angkot'][0]->price, -2);
                        $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                        if(substr( $value['angkot'][0]->price , -3) < 499 )
                        {
                          $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                        }
                        else
                        {
                          $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                        }                  
                      }
                      elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 2/3 pertama
                      {
                        # code...
                        $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                        //$temp = substr($value['angkot'][0]->price, -2);
                        $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                        if(substr( $value['angkot'][0]->price , -3) < 499 )
                        {
                          $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                        }
                        else
                        {
                          $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                        }                  
                      }
                      else
                      {
                        $value['angkot'][0]->price = $value['angkot'][0]->price;
                      }
                    
                    }

                  }

                  //coding total harga
                  $total_cost = 0;
                  foreach ($step as $key => $value) {
                    # code...
                    $temp = (object) $value['angkot'][0] ;
                    $total_cost = $total_cost + $temp->price;
                  }

                  $routingresult[] = ["step"=>array_reverse( $step) , "total_cost"=> $total_cost  ];
                  }

                  //penambahan total_distance di step
                  foreach ($routingresult as $i => $value) {
                  # code...
                  $total_distance = 0;
                  foreach ($routingresult[$i]['step'] as $j => $value) {
                    # code...
                    $tmp = $value['distance'];

                    $total_distance = $total_distance + $tmp;
                  }
                  $routingresult[$i]['total_distance'] = $total_distance;
                  
                }//*/
                //return "cek hasil";
                //masukin jalur lg
                foreach ($angkot as $i => $value) {
                  # code...
                  foreach ($angkot[$i] as $j => $value) {
                    # code...
                    if($j == 0)
                    { 
                      
                      $tmp_potong = DB::select("select id from shapes where shape_id =".reset($potong1[$i])." and shape_pt_sequence = 0 ");
                      $tmp_potong = $tmp_potong[0]->id;
                      
                      //return $pickup_point_finish[$j]->titik_terdekat->id;

                      $jalur[$i][] = $this->get_trayek_potong($value->route_id, $pickup_point_finish[ $index[$i][0] ]->titik_terdekat->id, $tmp_potong  );

                      $array[$i][] = [$value->route_id, $pickup_point_finish[$index[$i][0] ]->titik_terdekat->id, $tmp_potong];
                    }
                    elseif ($j == 1) {
                      $tmp_potong = DB::select("select id from shapes where shape_id =".reset($potong1[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong = $tmp_potong[0]->id;
                      $tmp_potong2 = DB::select("select id from shapes where shape_id =".reset($potong2[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong2 = $tmp_potong2[0]->id;
                      
                      $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong, $tmp_potong2  );
                      $array[$i][]=[$value->route_id, $tmp_potong, $tmp_potong2];
                    }
                    elseif ($j == 2) {
                      $tmp_potong2 = DB::select("select id from shapes where shape_id =".reset($potong2[$i])." and shape_pt_sequence = 0 ") ;
                      $tmp_potong2 = $tmp_potong2[0]->id;

                     $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong2 , $pickup_point_start[ $index[$i][3]  ]->titik_terdekat->id  ); //index terakhir na, index array angkot_start //

                     // $array[$i][] = [$value->route_id, reset($potong2[$i]) , $pickup_point_start[$index[$i][3]]->titik_terdekat->id ];
                    }
                  }
                } // jalur intersec pertama. */
                
                
                //gabung antara angkot dan jalur
                foreach ($angkot as $i => $value) {
                  # code...
                  $step = [];

                  foreach ($angkot[$i] as $j => $value) {
                    # code...
                    if($j == 0)
                    {
                      if($walk_route == 'no')
                      {

                        $walking_path = 'unavailable';
                      }
                      else
                      {

                        $walking_path = $this->get_walking_route($pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position );
                      }

                      $step[] = ["angkot"=>[ (object) ["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ]] , 'jalur'=>[ $pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position, $walking_path ] ];
                      
                    }

                    $step[] = ["angkot"=>[$value], "jalur"=>array_reverse( json_decode(json_encode($jalur[$i][$j]),true)  ) ] ;

                    if($j == sizeof($angkot[$i])-1 )
                    { 
                      if($walk_route == 'no')
                      {

                        $walking_path = 'unavailable';
                      }
                      else
                      {

                        $walking_path = $this->get_walking_route($pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position );
                      }

                      $step[sizeof($step)] = ["angkot"=> [ (object)["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#000000","price"=> 0, "image"=>"public/images/walk.png" ] ] , 'jalur'=>[ $data['start']->start_position , $pickup_point_start[ $index[$i][3] ]->titik_terdekat, $walking_path ]  ];
                    }

                  }

                   
                  //perubahan jadi object semua.
                  foreach ($step as $ii => $value) {
                    # code...
                    foreach ($step[$ii]['jalur'] as $j => $value) {
                      # code...
                      $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j] ;
                      
                    }
                  }

                  //penambahan distance 
                  //penambahan logika jarak
                  foreach ($step as $ii => $value) {
                    # code...
                    $jarak = [];
                    foreach ($step[$ii]['jalur'] as $j => $value) {
                      # code...

                      if(!is_object($step[$ii]['jalur'][$j]) || !is_object($step[$ii]['jalur'][$j] ) )
                      {
                       $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                       $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                      }
                      if(!isset($step[$ii]['jalur'][$j]->lat) || !isset($step[$ii]['jalur'][$j]->lng) )
                        {continue;}
                      $lat1 = (float) $step[$ii]['jalur'][$j]->lat  ;
                      $lng1 = (float) $step[$ii]['jalur'][$j]->lng  ;
                      //return [$lat1,$lng1];
                      if(!isset($step[$ii]['jalur'][$j+1]->lat) || !isset($step[$ii]['jalur'][$j+1]->lng) )
                        {continue;}
                      $lat2 = (float) $step[$ii]['jalur'][$j+1]->lat;
                      $lng2 = (float) $step[$ii]['jalur'][$j+1]->lng;
                      
                      
                      $jarak[$ii][] =  $this->distanceTo($lat1,$lng1,$lat2,$lng2);

                    }
                    $distance = array_sum( $jarak[$ii] ) ;
                    $step[$ii]['distance'] = $distance;
                  }
                  
                  //manipulating price  inside angkot.
                  foreach ($step as $key => $value) {
                    # code...
                    
                    if($value['angkot'][0]->price == 0)
                    {
                      continue;
                    }
                    else
                    { 
                      $route_id = $value['angkot'][0]->route_id; 
                      $total_jarak = $this->get_distance_route($route_id);

                      if($value['distance'] <= 1000 ) // satu kilo pertama
                      {
                        $value['angkot'][0]->price = 1500;  
                      }
                      elseif ($value['distance'] > 1000 and $value['distance'] <= ( (1/3) * $total_jarak) ) // 1/3 pertama
                      {
                        $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) / 3 ))  ; //pembulatan
                        //$temp = substr($value['angkot'][0]->price, -2);
                        $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                        if(substr( $value['angkot'][0]->price , -3) < 499 )
                        {
                          $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                        }
                        else
                        {
                          $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                        }                  
                      }
                      elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 2/3 pertama
                      {
                        # code...
                        $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                        //$temp = substr($value['angkot'][0]->price, -2);
                        $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);
                        if(substr( $value['angkot'][0]->price , -3) < 499 )
                        {
                          $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '500', -3);
                        }
                        else
                        {
                          $value['angkot'][0]->price = $value['angkot'][0]->price + (1000 - substr( $value['angkot'][0]->price , -3) ) ;
                        }                  
                      }
                      else
                      {
                        $value['angkot'][0]->price = $value['angkot'][0]->price;
                      }
                    
                    }

                  }

                  //coding total harga
                  $total_cost = 0;
                  foreach ($step as $key => $value) {
                    # code...
                    $temp = (object) $value['angkot'][0] ;
                    $total_cost = $total_cost + $temp->price;
                  }

                  $routingresult[] = ["step"=>array_reverse( $step) , "total_cost"=> $total_cost  ];
                  }

                  //penambahan total_distance di step
                  foreach ($routingresult as $i => $value) {
                  # code...
                  $total_distance = 0;
                  foreach ($routingresult[$i]['step'] as $j => $value) {
                    # code...
                    $tmp = $value['distance'];

                    $total_distance = $total_distance + $tmp;
                  }
                  $routingresult[$i]['total_distance'] = $total_distance;  
                }

                usort($routingresult, function($a, $b) {return $a['total_distance'] - $b['total_distance']; }); //sorting by total cost
                $routingresult = array_slice($routingresult, 0,5) ;

                usort($routingresult, function($a, $b) {return $a['total_cost'] - $b['total_cost']; }); //sorting by total cost
                $routingresult = array_slice($routingresult, 0,3) ;
                
                //perbaiki kalau jalur salah.
                foreach ($routingresult as $i => $value) {
                  # code...
                  foreach ($routingresult[$i]['step'] as $j => $value) {
                    if($j == 0)
                    {
                      $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
                      $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
                    
                      if($jalur[1]->id !== $jalur2[0]->id){
                        $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse($jalur2);
                      }
                    }
                    else if($j == sizeof($routingresult[$i]['step'])-1 ){
                      //$jalur = $routingresult[$i]['step'][$j]['jalur'] ;
                      //$jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;
                      continue;
                    }
                    else
                    {
                      $jalur = $routingresult[$i]['step'][$j]['jalur'] ;
                      $jalur2 = $routingresult[$i]['step'][$j+1]['jalur'] ;

                      if($jalur[sizeof($jalur)-1]->id !== $jalur2[0]->id){
                        $routingresult[$i]['step'][$j+1]['jalur'] = array_reverse($jalur2);
                        //return [$jalur, $jalur2];
                      }

                    }

                  }
                }

                //return $routingresult;
                //penambahan logika ket //peenambahan keterangan
                foreach ($routingresult as $i => $value) {
                  # code...
                  foreach ($routingresult[$i]['step'] as $j => $value) {
                    # code...
                    if($j == 0)
                    {
                      if($walk_route=="no")
                      {
                        $a = $routingresult[$i]['step'][$j]['jalur'][0];
                        $b = $routingresult[$i]['step'][$j]['jalur'][1];
                          if(empty($b->place_info))              
                          {
                            if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                                          continue;
                                        }
                        
                            $param1 = $a->lat .",". $a->lng;
                            $param2 = $b->lat .",". $b->lng;
                            $turun = $this->getLocInfo($param2);

                            $turun = $turun->results[0]->formatted_address;
                            
                            $turun = explode(", ", $turun);
                            $jarak = $routingresult[$i]['step'][$j]['distance'];
                            $jarak = explode(".", $jarak);
                            //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                            $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                          }
                          else
                          {
                            if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                              continue;
                            }
                        
                            $param1 = $a->lat .",". $a->lng;
                            $param2 = $b->lat .",". $b->lng;
                            
                            $turun = $b->place_info;
                            $turun = explode(", ", $turun);
                            $jarak = $routingresult[$i]['step'][$j]['distance'];
                            $jarak = explode(".", $jarak);
                            //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                            $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                          }
                      }
                      else
                      {
                        //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                        $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                        $turun = explode(", ", $turun);
                        $jarak = $routingresult[$i]['step'][$j]['distance'];
                        $jarak = explode(".", $jarak);
                        //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                        $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh ".$jarak[0]." meter dari posisi anda menuju <strong>".$turun[0]."</strong>.";
                      }
                    }
                    else if($j == sizeof($routingresult[$i]['step'])-1 )
                    {
                      if($walk_route=="no")
                      { 
                        $a = $routingresult[$i]['step'][$j]['jalur'][0];
                        $b = $routingresult[$i]['step'][$j]['jalur'][1];
                          
                        if(empty($a->place_info)) 
                        {
                          
                          if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                            continue;
                          }
                          $param1 = $a->lat .",". $a->lng;
                          $param2 = $b->lat .",". $b->lng;
                           $naik = $this->getLocInfo($param1); 
            
                          //$turun = $this->getLocInfo($param1);
                          $naik = $naik->results[0]->formatted_address;
                          $naik = explode(", ", $naik);

                          $jarak = $routingresult[$i]['step'][$j]['distance'];
                          $jarak = explode(".", $jarak);
                          //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                          $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                        }
                        else
                        {

                          if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                            continue;
                          }
                          $param1 = $a->lat .",". $a->lng;
                          $param2 = $b->lat .",". $b->lng;
                          //$naik = $this->getLocInfo($param1); 
            
                          //$turun = $this->getLocInfo($param1);
                          $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                          $naik = explode(", ", $naik);

                          $jarak = $routingresult[$i]['step'][$j]['distance'];
                          $jarak = explode(".", $jarak); 
                          //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                          $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";
                        }
                     }
                     else
                     {
                        //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                        
                          $naik = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->start_address;
                          $naik = explode(", ", $naik);

                          $jarak = $routingresult[$i]['step'][$j]['distance'];
                          $jarak = explode(".", $jarak);

                          //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                          //$routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
                          $routingresult[$i]['step'][$j]['ket'] = "Jalan kaki sejauh <strong>".$jarak[0]."</strong> meter sampai tujuan anda.";

                     }
                    }
                    else
                    {
                      $a = $routingresult[$i]['step'][$j]['jalur'][0];
                      $b = $routingresult[$i]['step'][$j]['jalur'][sizeof($routingresult[$i]['step'][$j]['jalur']) - 1];
                      //return (array) $a;
                      //ada yang salah dari jalur terakhir.
                      if(empty($a)||empty($b))
                      {
                          if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) ){
                            continue;
                          }
                          $param1 = $a->lat .",". $a->lng;
                          $param2 = $b->lat .",". $b->lng;
                          $naik = $this->getLocInfo($param1); 
                          $turun = $this->getLocInfo($param2);
                          $naik = $naik->results[0]->formatted_address;
                          $turun = $turun->results[0]->formatted_address;
                          $naik = explode(", ", $naik);
                          $turun = explode(", ", $turun);
                          


                          $jarak = $routingresult[$i]['step'][$j]['distance'];
                          $jarak = explode(".", $jarak);
                          $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );
            
                          //$angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                          $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name .". ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign ;
                          $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot ".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
                      }
                      else
                      {
                        if(!isset($a->lat) || !isset($a->lng) || !isset($b->lat) || !isset($b->lng) )
                        {
                          continue;
                        }
                        $param1 = $a->lat .",". $a->lng;
                        $param2 = $b->lat .",". $b->lng;
                        //$naik = $this->getLocInfo($param1); 
                        //$turun = $this->getLocInfo($param2);
                        $naik =  $a->place_info;//$naik->results[0]->formatted_address;
                        $turun = $b->place_info;
                        $naik = explode(", ", $naik);
                        $turun = explode(", ", $turun);

                        $jarak = $routingresult[$i]['step'][$j]['distance'];
                        $jarak = explode(".", $jarak);

                        $jarak[0] = number_format($jarak[0]/1000, 1, '.', '');//ceil( $jarak[0] / 1000 );

                        $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ." ( ".$routingresult[$i]['step'][$j]['angkot'][0]->trip_headsign." )" ;
                        //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                        //$routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
                        $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>Angkot No.".$angkot."</strong> sampai <strong>".$turun[0]."</strong> sejauh <strong>".$jarak[0]." Km</strong>. <br> <i>* biasanya ongkos : Rp. ".$routingresult[$i]['step'][$j]['angkot'][0]->price.". </i>" ;
                      }
                    }

                  }
                }
                return ["status"=>$status, "routingresult"=>$routingresult ];

                
              } //tutup if 3 angkot saja.
              elseif( sizeof($jejak_result_shape_id_reverse) == 3 ) //jika harus naik 4 angkot //fungsi 4 angkot
              {
                //return $jejak_result_shape_id_reverse;

                for ($i=sizeof($jejak_result_shape_id_reverse)-1; $i >= 0 ; $i--) { 
                  # code...
                  //$berapa[] = $i;
                  foreach ($jejak_result_shape_id_reverse[$i] as $j => $value) {
                    # code...
                    foreach ($jejak_result_shape_id_reverse[$i][$j] as $k => $value) {
                      # code...
                      if($i ==sizeof($jejak_result_shape_id_reverse)-1 )
                      {
                        $look[$i][] = $j."-".$k ;
                        $path_searcher[$i][$j][$k] = $jejak_result_shape_id_reverse[$i][$j][$k] ;
                        $angkot_searcher[$i][$j][$k] = $jejak_result_angkot_reverse[$i][$j][$k] ;
                      }
                      else
                      {
                        $look[$i][] = $k."-".$j ;
                        $path_searcher[$i][$k][$j] = $jejak_result_shape_id_reverse[$i][$j][$k] ;//$j ;
                        //$angkot_searcher[$i][$k][$j] = $jejak_result_angkot_reverse[$i][$j][$k] ;
                      }

                    }
                  }
                }
                //return $path_searcher;
                $temp =[];
                $temp2 =[];
                foreach ($path_searcher as $key => $value) {
                  # code...
                  $temp[] = $value; // jejak shape id;
                  //$temp2[] = $angkot_searcher[$key]; // jejak angkot
                }
                
                $path_searcher = $temp;
                $angkot_searcher = $temp2;
                $temp2 =[];
                $temp =[];

                //return $path_searcher;
                $path_key = [];
                $path_key2 = [];
                $jalan = [];
                foreach ($path_searcher[0] as $i => $value) {
                  # code...
                  if(!isset($path_searcher[0][$i]))
                    {continue;}
                  foreach ($path_searcher[0][$i] as $j => $value) {
                    # code...
                    if(!isset($path_searcher[1][$j]))
                    {continue;}
                    foreach ($path_searcher[1][$j] as $k => $value) {
                      # code...
                      if(!isset($path_searcher[2][$k]))
                    {continue;}
                      foreach ($path_searcher[2][$k] as $l => $value) {
                        # code...
                        $path_key[] = $i."-".$j."-".$k."-".$l ;
                        $path_key2[] = $i."-".$j."-".$j."-".$k."-".$k."-".$l ;
                        $jalan[] = $i."-".$j.",".$j."-".$k.",".$k."-".$l ;
                      }

                    }
                  }
                }
              
                //return $reverse_unduplicated;
                $new_reverse_unduplicated = array_reverse( $reverse_unduplicated );
                //return $new_reverse_unduplicated;
                $temp =[];
                foreach ($path_key as $i => $value) {
                  # code...
                  $temp[] = explode("-", $value) ;
                }

                $path_key = $temp;
                $temp = [];

                #pencarian angkot
                $angkot = [];
                foreach ($path_key as $i => $value) {
                  # code...
                  foreach ($path_key[$i] as $j => $value) {
                    # code...
                    $angkot[$i][] = $new_reverse_unduplicated[$j][$value];
                  }
                }

                return $angkot ;

              } //tutup naik 4 angkot.
            } // tutup else dalem.

          } // tutup else,


      }

    }
  }

  public function get_fare_rule(Request $request){
    //$fare_id = $_POST['fare_id'];
    $fare_id = $request->fare_id;
    $data = DB::select('select price from fare_attributes where fare_id='.$fare_id);  
    return $data;
  }

  //for checking
  public function all_angkot()
  {
    $angkot = trip::select(['route_id','trip_short_name','shape_id'])->where('shape_id', '!=', '' )->get();  
    return $angkot;
  }
  //for checking
  public function get_shapes_id($shape_idA)
  {
    //$shape_idA = $valuei['shape_id'];
    $arrayA = explode(", ", $shape_idA);
    //$nama_angkot1 = $valuei['trip_short_name'];
    $data_shapesA = shape::select(array('id','shape_pt_lat as lat','shape_pt_lon as lng','shape_id','shape_pt_sequence'))
                         ->whereIn('shape_id', $arrayA )
                         ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_idA." )"))
                         ->orderByRaw('shape_pt_sequence', 'Asc')
                         ->get();
    return $data_shapesA;
  }
  //for checking
  public function check()
  {
    ini_set('max_execution_time', 300);
    $angkotA = $this->all_angkot();
    $angkotB = $this->all_angkot();
    
    foreach ($angkotA as $i => $valuei) {
      $shape_idA = $valuei['shape_id'];  
      $data_shapesA = $this->get_shapes_id($shape_idA);
      $nama_angkot1 = $valuei['trip_short_name'];

      foreach ($angkotB as $j => $valuej) {
        if($i == $j){
          continue;
        }
        $shape_idB = $valuej['shape_id'];
        $data_shapesB = $this->get_shapes_id($shape_idB);
        $nama_angkot2 = $valuej['trip_short_name'];
        $array_penampung = [];
        $array_penampung2 = [];
        //return [$data_shapesA, $data_shapesB];
        /*foreach ($data_shapesA as $key => $value) {

          $shape_id1 = $value['shape_id'] ;
          foreach ($data_shapesB as $key => $value) {
            # code...
            $shape_id2 = $value['shape_id'] ;
            $array_penampung[] = $shape_id2 ;
            $array_penampung2[] = $shape_id1 ;
          }
        }*/
        foreach ($data_shapesA as $key => $value) {
          # code...
          $shape_id1 = $value['shape_id'] ;
          $array_penampung2[] = $shape_id1 ;
        }
        //$intersection = array_intersect(array_map("serialize", $data_shapesA) , array_map("serialize", $data_shapesB ) ) ;
        foreach ($data_shapesA as $k => $value) {
          # code...
          
          $lat1 = $value['lat'];
          $lng1 = $value['lng'];
          $shape_id1 = $value['shape_id'] ;
          $array_penampung2[] = $shape_id1 ;
          
          foreach ($data_shapesB as $l => $value) {
            # code...
            
            $lat2 = $value['lat'];
            $lng2 = $value['lng'];
            $shape_id2 = $value['shape_id'] ;
            $array_penampung[] = $shape_id2 ;
            //return [$value, json_decode($data_shapesA, true)];
            if( $shape_id1 == $shape_id2 )
            { 
              continue;
            }
            if(in_array($shape_id1, $array_penampung ) || in_array($shape_id2, $array_penampung2 )  )
            {
              continue;
            }
            $jarak = $this->distanceTo($lat1,$lng1,$lat2,$lng2) ;

            if ( $i != $j && $shape_id1 != $shape_id2 && $jarak <= 100 ) {
              $dekat[] = [ $nama_angkot1, $nama_angkot2 ,$shape_id1, $shape_id2, $jarak ];
            }
          }
        }

        return $dekat;
        
      }
      

    }

    
    
  }
  //for checking
  public function check2()
  {
    ini_set('max_execution_time', 300);
    $angkotA = $this->all_angkot();
    $angkotB = $this->all_angkot();
    
    foreach ($angkotA as $i => $valuei) {
      $shape_idA = $valuei['shape_id'];  
      $data_shapesA = $this->get_shapes_id($shape_idA);
      $nama_angkot1 = $valuei['trip_short_name'];
      $array_penampung2 = [];
        
      foreach ($data_shapesA as $key => $value) {
        # code...
        $shape_id1 = $value['shape_id'] ;
        $array_penampung2[] = $shape_id1 ;
      }

      for ($j=$i+1; $j < $i ; $j++)  {
        if($i == $j){
          continue;
        }
        $shape_idB = $angkotB[$j]['shape_id'];
        $data_shapesB = $this->get_shapes_id($shape_idB);
        $nama_angkot2 = $angkotB[$j]['trip_short_name'];
        $array_penampung = [];
        
        //$intersection = array_intersect(array_map("serialize", $data_shapesA) , array_map("serialize", $data_shapesB ) ) ;
        foreach ($data_shapesA as $k => $value) {
          # code...
          
          $lat1 = $value['lat'];
          $lng1 = $value['lng'];
          $shape_id1 = $value['shape_id'] ;
          $array_penampung2[] = $shape_id1 ;
          
          /*foreach ($data_shapesB as $l => $value)*/
          for ($l=$k+1; $l < $k ; $l++)
          {
            # code...
            
            $lat2 = $data_shapesB[$l]['lat'];
            $lng2 = $data_shapesB[$l]['lng'];
            $shape_id2 = $data_shapesB[$l]['shape_id'] ;
            $array_penampung[] = $shape_id2 ;
            //return [$data_shapesB[$l], json_decode($data_shapesA, true)];
            if( $shape_id1 == $shape_id2 )
            { 
              continue;
            }
            if(in_array($shape_id1, $array_penampung ) || in_array($shape_id2, $array_penampung2 )  )
            {
              continue;
            }
            $jarak = $this->distanceTo($lat1,$lng1,$lat2,$lng2) ;

            if ( $i != $j && $shape_id1 != $shape_id2 && $jarak <= 100 ) {
              $dekat[] = [ $nama_angkot1, $nama_angkot2 ,$shape_id1, $shape_id2, $jarak ];
            }
          }

          return $dekat;
        }


        
      }
      

    }
  }  

  
  


} // tutup class
