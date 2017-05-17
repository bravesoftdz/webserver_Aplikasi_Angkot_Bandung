<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\trip;
use App\trip_new;
use App\shape;
use App\shapes_new;
use DB;

class ApiController extends Controller
{
   
    public function get_jalur_terdekat($lat = -6.924318036348017 , $lon = 107.60681390762329)//($route_id = 1) //ini ambil dlm radius 2 km, di filter base on shape id, trus tanya google api soal jarak dan walking route.
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
        
        $nearest = DB::select("SELECT *, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < 2 ORDER BY distance  ;"); // limitnya di hilangkan dulu. LIMIT 0 , 20
        
        //filtering
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

        $akhirlat = $data[$i]->shape_pt_lat;
        $akhirlon = $data[$i]->shape_pt_lon;

        $akhir = $akhirlat.",".$akhirlon;
        $filtered = $this->get_walking_route($awal, $akhir);
        $distance = json_decode($filtered);
        if($distance->status == "OVER_QUERY_LIMIT")
        {
          return "google api OVER_QUERY_LIMIT";
        }
        $obj = $distance->routes[0]->legs[0]->distance->value;
        $hasil[$i] = [ "jarak"=>$obj, "titik_terdekat"=>$data[$i], "walk_route"=>json_decode($filtered) ];
        } 


        $arraytrip = [];
        $result_filtered_trayek = [];
        $a = [];

        for ($i=0; $i < sizeof($data) ; $i++) 
        {     
          $hai = $data[$i]->shape_id;
          $trip2 = DB::select("select * from trips where shape_id like '% ".$hai.",%' union select * from trips where shape_id = ".$hai." union select * from trips where shape_id like '% ".$hai."' ");

         
          $a[$i] = $trip2;
          $b[$i] = $trip2;

           $filtered_trayek_data[$i] = array_filter( $b[$i] , function ($val) use (&$known) {
            $unique = !in_array($val->trip_short_name, $known);
            $known[] = $val->trip_short_name;
            return $unique;
            });
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


             $arraytrip[] = [ "pickup_point"=> $hasil[$i] , "trayek"=>$new[$i],"color"=>$color[$i] ]; 

         } //
         
        
       //  $arraytrip = array("titik_terdekat"=> $data, "trayek"=>$filtered_trayek_data );

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

    public function get_jalur_terdekat_cepat($lat = -6.924318036348017 , $lon = 107.60681390762329)
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
        // perbaiki nearest ini, jangan pakai * biar bisa dirubah pake as shape_pt_lat, shape_pt_lon nya.
        $nearest = DB::select("SELECT *, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < 0.5 ORDER BY distance  ;"); // limitnya di hilangkan dulu. LIMIT 0 , 20
       // $nearest = DB::select("SELECT id,shape_pt_lat as lat, shape_pt_lon as lng, shape_id, shape_pt_sequence , ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < 2 ORDER BY distance  ;");
        //filtering
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

        $akhirlat = $data[$i]->shape_pt_lat;
        $akhirlon = $data[$i]->shape_pt_lon;

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
          $trip2 = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai.",%' union 
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id = ".$hai." union
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai."' ");
          //DB::select("select * from trips where shape_id like '% ".$hai.",%' union select * from trips where shape_id = ".$hai." union select * from trips where shape_id like '% ".$hai."' ");

         
          $a[$i] = $trip2;
          $b[$i] = $trip2;

           $filtered_trayek_data[$i] = array_filter( $b[$i] , function ($val) use (&$known) {
            $unique = !in_array($val->trip_short_name, $known);
            $known[] = $val->trip_short_name;
            return $unique;
            });
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

    public function get_jalur_terdekat_cepat_new($lat = -6.924318036348017 , $lon = 107.60681390762329)
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
        $radius = 0.5;   
        // perbaiki nearest ini, jangan pakai * biar bisa dirubah pake as shape_pt_lat, shape_pt_lon nya.
        a:
       $nearest = DB::select("SELECT id,shape_pt_lat as lat, shape_pt_lon as lng, shape_id, shape_pt_sequence, place_info, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < ".$radius." ORDER BY distance  ;");
        //filtering

       if(empty($nearest))
        {
          //return "tidak ada angkot";
          $radius = $radius+0.5;
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
          $trip2 = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai.",%' union 
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id = ".$hai." union
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price,route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai."' ");
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

    public function get_jalur_terdekat_cepat2($lat = -6.924318036348017 , $lon = 107.60681390762329) //revisi perampingan return
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
        
        $nearest = DB::select("SELECT *, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < 2 ORDER BY distance  ;"); // limitnya di hilangkan dulu. LIMIT 0 , 20
        
        //filtering
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

        $akhirlat = $data[$i]->shape_pt_lat;
        $akhirlon = $data[$i]->shape_pt_lon;

        $akhir = $akhirlat.",".$akhirlon;
        $filtered = ["status"=> "unavailable" ]; //$this->get_walking_route($awal, $akhir);
        $distance = $filtered;//json_decode($filtered);
       
        $obj = $data[$i]->distance;
        $hasil[$i] = [ "jarak"=>$obj, "titik_terdekat"=>$data[$i], "walk_route"=> $filtered];
        } 


        $arraytrip = [];
        $result_filtered_trayek = [];
        $a = [];

        for ($i=0; $i < sizeof($data) ; $i++) 
        {     
          $hai = $data[$i]->shape_id;
          $trip2 = DB::select("select * from trips where shape_id like '% ".$hai.",%' union select * from trips where shape_id = ".$hai." union select * from trips where shape_id like '% ".$hai."' ");

         
          $a[$i] = $trip2;
          $b[$i] = $trip2;

           $filtered_trayek_data[$i] = array_filter( $b[$i] , function ($val) use (&$known) {
            $unique = !in_array($val->trip_short_name, $known);
            $known[] = $val->trip_short_name;
            return $unique;
            });
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


             $arraytrip[] = [ "pickup_point"=> $hasil[$i] , "trayek"=>$new[$i],"color"=>$color[$i] ]; 

         } //
         
        
       //  $arraytrip = array("titik_terdekat"=> $data, "trayek"=>$filtered_trayek_data );

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

    public function get_jalur_terdekat_baru($lat = -6.9006744, $lon = 107.6186616)//($route_id = 1)
    //ambil dalam radius 500, jika tidak ada radius naik 500 s/d 2 km. jarak di tentukan oleh google api.
    {
        ini_set('max_execution_time', 180);
       
        if(empty($_GET['lat']) or empty($_GET['lon']) )
        {
          $lat;
          $lon;
          $origin = [$lat, $lon];
          $start = ['lat'=>$lat, 'lng'=>$lon];
          $awal = $lat.",".$lon;
          $radius = 0.5;
        } 
        else
        {
        $lat = $_GET['lat'];
        $lon = $_GET['lon'];
        $origin = [$lat, $lon];
        $start = ['lat'=>$lat, 'lng'=>$lon];
        $awal = $lat.",".$lon;
        $radius = 0.5; 
        }                  
        a:
        $nearest = DB::select("SELECT *, ( 6371 * acos( cos( radians(".$origin[0].") ) * cos( radians( shape_pt_lat ) ) * cos( radians( shape_pt_lon ) - radians(".$origin[1].") ) + sin( radians(".$origin[0].") ) * sin( radians( shape_pt_lat ) ) ) ) AS distance FROM shapes  HAVING distance < ".$radius." ORDER BY distance ;"); // limitnya di hilangkan dulu. LIMIT 0 , 20   
        
        //filtering
        if(empty($nearest))
        {
          //return "tidak ada angkot";
          $radius = $radius+0.5;
          if($radius > 2)
          {
            $angkot = [];
            goto b;

          }
          goto  a;

        }


       // $filterednearest = array_count_values($nearest->shape_id);

        
        for ($i=0; $i < sizeof($nearest); $i++) 
        { 

        $akhirlat = $nearest[$i]->shape_pt_lat;
        $akhirlon = $nearest[$i]->shape_pt_lon;

        $akhir = $akhirlat.",".$akhirlon;
        $filtered = $this->get_walking_route($awal, $akhir);
        $distance = json_decode($filtered);
        $obj = $distance->routes[0]->legs[0]->distance->value;
        $hasil[$i] = [ "jarak"=>$obj, "titik_terdekat"=>$nearest[$i], "walk_route"=>json_decode($filtered) ];
        } 
        
        $sorted = array();
        foreach ($hasil as $key) {
          $sorted[] = $key;
        }
        array_multisort($sorted, SORT_ASC, $hasil);

        $data = [];
        $known = array();

        $eliminated = array_filter($sorted, function ($val) use (&$known) {
            $unique = !in_array($val['titik_terdekat']->shape_id, $known);
            $known[] = $val['titik_terdekat']->shape_id;
            return $unique;
        });  // filter supaya yang di return base on shape_id

        for ($n=0; $n < sizeof($eliminated)  ; $n++)
        { 
          # code...
          if(isset($eliminated[$n]))
          {
          $data[] = $eliminated[$n];
          }
        } 

         for ($i=0; $i < sizeof($data); $i++) 
         { 
            $waduk = $data[$i]['titik_terdekat']->shape_id; //ininya salah kayaknya.
            $bungkus[] = $waduk; 
         }

           for ($i=0; $i < sizeof($data) ; $i++) { 
             # code...
            $database[$i] = DB::select("select * from trips where shape_id like '% ".$bungkus[$i].",%' union select * from trips where shape_id = ".$bungkus[$i]." union select * from trips where shape_id like '% ".$waduk."' ");
           }

           for ($i=0; $i < sizeof($database); $i++) { 
             # code...

            $filtered_database[] = array_filter( $database[$i] , function ($val) use (&$known) {
            $unique = !in_array($val->trip_short_name, $known);
            $known[] = $val->trip_short_name;
            return $unique;
            });

           }

      
           for ($i=0; $i < sizeof($data) ; $i++)
          { 
             # code...
            if(empty($filtered_database[$i]))
            {
              continue;
            }
            else
            {
              $sortered_filtered_database = array_values($filtered_database[$i]) ;
             // $jalur[] = $this->get_koordinat($filtered_database[$i]->route_id);
            }

             $route_id = $sortered_filtered_database;
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


         //    $arraytrip[] = [ "pickup_point"=> $hasil[$i] , "trayek"=>$new[$i],"color"=>$color[$i] ]; 

           

           $angkot[] = [ "pickup_point"=>$data[$i], "trayek"=>$sortered_filtered_database, "color"=>$color[$i] ];//, "jalur"=>$jalur[$i] ];
          }

        b:
        if(!empty($nearest))
        {
          $status = "OK";
        }
        else
        {
          $status = "null";
        }




        $result = [ "status"=>$status, 'start_position'=>$start , 'angkot'=>$angkot, 'radius'=>$radius];
        return json_encode($result, true);
        
     } //akhir function 

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
        $key = "AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE"; //teguh //"AIzaSyCKpPFPiinrY1wJHeMD92jJzz5B1r9OGgI"; //Dama
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latlng."&sensor=true&key=".$key;

       $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
       $obj =  json_decode($json);
       return $obj;//$obj->results[0]->formatted_address
    
    } 

    public function getLocInfo2($latlng = '44.4647452,7.3553838' )
    { 
      if( isset($_GET['latlng']) ){
        $latlng = $_GET['latlng'] ;

      }
      $arrContextOptions = array( //kalau ga pake ini error
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
            ),
        );  
        //"AIzaSyAI5gLAuxEzfYY-d-Et4VszYLi9qtv8ZSs"; //teguh
        //"AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE"; //teguh
        //
        $key = "AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE"; //"AIzaSyCKpPFPiinrY1wJHeMD92jJzz5B1r9OGgI"; //Dama
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=".$latlng."&sensor=true&key=".$key;

       $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
       //$obj =  json_decode($json);
       return $json; //$obj->results[0]->formatted_address;
    
    } 

    public function get_placeInfo(){
      
      $data = DB::select("select stop_name as name from stops");
      return $data;
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

    public function get_walking_route_backup($awal='gedung sate', $akhir='gasibu')
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
        $awal ;
        $origin = rawurlencode($awal);
        $mode = 'walking';
        $akhir = json_decode(json_encode($akhir),true);
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

        $url = "https://maps.googleapis.com/maps/api/directions/json?origin=".$origin."&destination=".$destination."&waypoints=".$waypoints."&key=AIzaSyBuM6Zuy4Zr3SDn_X6nmUvTQn64l944btk";

       $json = file_get_contents($url, false, stream_context_create($arrContextOptions));
       

       return $json; 
       
    }
    
    public function get_angkot_info($route_id=1){
      if( isset($_GET['route_id']) ){
        $route_id = $_GET['route_id'];  
      }
      //$data = DB::select("select a.route_id, a.trip_headsign, a.trip_short_name, a.shape_id,a.ket, b.image, b.route_color from trips a inner join route b on a.route_id = b.route_id where a.route_id =".$route_id);
      $data = DB::select("select trips.route_id,trip_short_name, trip_headsign ,shape_id, route.route_color,route.image,trips.ket,  fare_attributes.price, fare_rule.fare_id from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where trips.route_id =".$route_id);
      return $data;
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

          return $data_shapes;
    }

     public function get_trayek_akbar($kirim = 1)
    {
        if(isset($_GET['kirim']))
        {
          $route_id = $_GET['kirim'];
        }
        else
        {
          $route_id = $kirim;
        }

        if($route_id == 99){
          $shape = shape::all();
          return $shape;
        }

        $index = 0;//($route_id - 1);
        $trip = trip::where('route_id', '=', $route_id)->get();
        
        
        if( !isset($trip[0]) )
        {
          return "there's no route with route_id = ".$route_id;
        }

        $shape_id = $trip[$index]['shape_id'];
        
        $array = explode(",", $shape_id);
       
     
        $shape = shape::select(['id','shape_id','shape_pt_lat','shape_pt_lon','shape_pt_sequence','jalur','place_info'])->whereIn('shape_id', $array )
                             ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                             ->orderByRaw('shape_pt_sequence', 'Asc')
                             ->get();

        return $shape;  
    }


    public function get_trayek_akbar2()
    {
        $route_id = $_GET['kirim'];
        $index = ($route_id - 1);
        $trip = trip::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[$index]['shape_id'];
        $array = explode(",", $shape_id);
      
        $shape = DB::select("select * from shapes where shape_pt_sequence = 0 and shape_id in (".$shape_id.") ");

        return $shape;
    }

    public function get_trayek_akbar3($kirim = 1) //dipakai untuk test jalur kukuh
    {
        if(isset($_GET['kirim'])){
        $route_id = $_GET['kirim'];
        }
        else
        {
          $route_id = $kirim;
        }
        $index = ($route_id - 1);
        $trip = trip_new::all()->where('route_id', '=', $route_id);
        //return $trip;
        $shape_id = $trip[$index]['shape_id'];
        $array = explode(",", $shape_id);
       
     
        $shape = shapes_new::/*select(['id','shape_pt_lat','shape_pt_lon'])->*/whereIn('shape_id', $array )
                             ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                             ->orderByRaw('shape_pt_sequence', 'Asc')
                             ->get();

          return $shape;  
    }

     public function get_trayek_in()
    {
        $route_id = $_GET['kirim'];
        $index = ($route_id - 1);
        $trip = trip::all()->where('route_id', '=', $route_id);
        $shape_id = $trip[$index]['shape_id'];
        $array = explode(",", $shape_id);
      
        $shape = DB::select("select * from shapes shape_id in (".$shape_id.") ");

        return $shape;
    }

    public function get_koordinat($kirim = 1)
    {   
        if( isset($_GET['kirim']))
        {
          if($_GET['kirim'] == 41)
            {continue;}
          $route_id = $_GET['kirim'];
        } 
        else
          { $route_id = $kirim; }
        


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

    public function get_angkot()
    {
      $trip = trip::all();//->where('route_id', '=', $route_id); 
        $shape_id = $trip[0]['shape_id'];
        $array = explode(",", $shape_id);
       
     
      $shape = shape::whereIn('shape_id', $array )
                           ->orderByRaw(DB::raw("FIELD( shape_id, ".$shape_id." )"))
                           ->orderByRaw('shape_pt_sequence', 'Asc')
                           ->get();
      $gabung = [$trip, $shape]; 

      return $gabung  ;
    }

    public function get_jalur($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782" )
    {   
        $latlngstart = explode(",", $start);
        $latlngfinish = explode(",", $finish);
        $naik = $this->get_jalur_terdekat_baru($latlngstart[0],$latlngstart[1]);
        $turun = $this->get_jalur_terdekat_baru($latlngfinish[0],$latlngfinish[1]);
        $hasil = ["start"=>json_decode($naik), "finish"=>json_decode($turun)];
        return json_encode($hasil);
    }

    public function test($lat = -6.924318036348017 , $lon = 107.60681390762329)
    { 
      return $this->get_distance_route();
    }

    public function object()
    {
      $a= ["nama"=>"teguh", "kuku"=>"panjang"] ;
      $b = ['teguh', 'panjang'];
      $c = json_decode(json_encode($a));


      print_r($a);
     // return [$a,$b,$c];
    }

    public function get_position($location='gedung sate')
    { 
      if(isset($_GET['location'])){
        $location = $_GET['location'];
      }
      // cari dulu dari database, kalau tidak ada, baru ke api.
      //stop_id, stop_name, stop_lat, stop_lon where stop_name LIKE '%".$location."%'
      $data = DB::select("select stop_id, stop_name, stop_lat, stop_lon from stops where stop_name LIKE '%".$location."%' " );
      //$data = "select stop_id, stop_name, stop_lat, stop_lon from stops where stop_name LIKE '%".$location."%' ";
     
      if(!empty($data)){
        $status = "OK";
        foreach ($data as $key => $value) {
          # code...
          $placename = $data[$key]->stop_name;
          $lat = $data[$key]->stop_lat ;
          $lng = $data[$key]->stop_lon ;
          $location = ["lat"=>(float) $lat , "lng"=>(float) $lng ] ;
          $result[] = ["placename"=>$placename, "location"=>$location ] ;

        }
        
        return ["status"=>$status, "searchresult"=> $result];

      }
      else
      {

        $arrContextOptions = array( //kalau ga pake ini error
              "ssl"=>array(
              "verify_peer"=>false,
              "verify_peer_name"=>false,
                  ),
              );  
  
        if(!empty($_GET['location']))
        {
          $lokasi = $_GET['location'];
        }
        else
        {
          $lokasi = $location;
          
        }
  
        $url_autocomplete = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=".urlencode($lokasi)."&location=-6.914838922559386,107.60765075683594&radius=100000&key=AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE";
  
        $result_url_autocomplete = file_get_contents($url_autocomplete);
        $arrAutocomplete = json_decode($result_url_autocomplete, true);
        
        //return $arrAutocomplete; 
        
        if($arrAutocomplete['status'] == 'OK')

        {
  
          $place = $arrAutocomplete['predictions'];
          for ($i=0; $i < sizeof($place) ; $i++) { 
            $tempat = $place[$i]['description'];
  
            $maps_url = 'https://' .
              'maps.googleapis.com/' .
              'maps/api/geocode/json' .
              '?address=' . urlencode($tempat)."&key=AIzaSyACC2VXsDvq7g3fRrIIZKx0mWTMrgJGVVE";
  
             $maps_json = file_get_contents($maps_url);
  
             $maps_array = json_decode($maps_json, true);
             
             //return $maps_array;
  
            if($maps_array['status'] == "OK")
            {
  
            ini_set('max_execution_time', 180);
            $lat_result = $maps_array['results'][0]['geometry']['location']['lat'];
            $lon_result = $maps_array['results'][0]['geometry']['location']['lng'];
            $position = $maps_array['results'][0]['formatted_address'];
            $json[$i] = ['lat'=>$lat_result , 'lng'=>$lon_result];
            $place_result[$i] = $tempat;
            $result[$i] = ['placename'=>$place_result[$i], 'location'=> $json[$i]];
            }
            else
            {
            $lat_result = "lat_tidak_ditemukan";
            $lon_result = "lon_tidak_ditemukan";
            $position  = $_GET['location'];
            $json[$i] = ['lat'=>$lat_result , 'lng'=>$lon_result];
  
            }
              
          } // tutup for
  
          $status = "OK";
        }
        else{
          $status = "unavailable";
          $result = [];
        }  
        
        foreach ($result as $key => $value) {
          # code...
          $stop_name = $result[$key]['placename'];
          $lat =  $result[$key]['location']['lat'] ;
          $lng =  $result[$key]['location']['lng'] ;
          $query = DB::select("insert into stops values ('','','".$stop_name."','','".$lat."','".$lng."','') ");
        }
        return ["status"=>$status, "searchresult"=> $result];
      }
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

    public function test_distanceto()
    {
     // [-6.89819735094127, 107.62962579718078];
     // [-6.888653805329838, 107.64627695074523];
      $lat1 = -6.89819735094127;
      $lng1 = 107.62962579718078;
      $lat2 = -6.888653805329838 ;
      $lng2 = 107.64627695074523;

      $a = $this->distanceTo($lat1,$lng1,$lat2,$lng2);

      return $a;

    }

    public function djikstra($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782")
    {  
      if( isset($_GET['start']) and isset($_GET['finish']) )
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

    public function djikstra_cepat($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782")
    {  
      // if( isset($_GET['start']) and isset($_GET['finish']) )
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

        $awal= $this->get_jalur_terdekat_cepat($a_start[0], $a_start[1]);
        $akhir = $this->get_jalur_terdekat_cepat($a_finish[0], $a_finish[1]);

        return ['start'=> json_decode($awal),'finish'=> json_decode($akhir)];

    }

    public function djikstra_cepat_new($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782") //dipakai di fungsi baru, pakai djikstra_cepat_new, dan get_jalur_terdekat_cepat_new
    {  
      // if( isset($_GET['start']) and isset($_GET['finish']) )
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

        $awal= $this->get_jalur_terdekat_cepat_new($a_start[0], $a_start[1]);
        $akhir = $this->get_jalur_terdekat_cepat_new($a_finish[0], $a_finish[1]);

        return ['start'=> json_decode($awal),'finish'=> json_decode($akhir)];

    }

    public function djikstra_cepat2($start="-6.9006744,107.6186616", $finish="-6.9025157,107.618782") //revisi, perampingan return
    {  
      // if( isset($_GET['start']) and isset($_GET['finish']) )
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

      $awal= $this->get_jalur_terdekat_cepat2($a_start[0], $a_start[1]);
      $akhir = $this->get_jalur_terdekat_cepat2($a_finish[0], $a_finish[1]);

      return ['start'=> json_decode($awal),'finish'=> json_decode($akhir)];

    } // akhir function

    public function get_fastest_route($a = 33, $b = 55)
    {

      if(isset($_GET['a']) and isset($_GET['b']) )
      {
      

        $a = $_GET['a'];
        $b = $_GET['b'];

      }
      //set the distance array
        $_distArr = array();

        $data = DB::select("select * from transfer");

        foreach ($data as $value) {
          $from = $value->from_stop_id;
          $to = $value->to_stop_id;
          $bobot = $value->distance;

          $_distArr[$from][$to] = $bobot;
        }

        $S = array();//the nearest path with its parent and weight
        $Q = array();//the left nodes without the nearest path
        foreach(array_keys($_distArr) as $val) $Q[$val] = 99999;
        $Q[$a] = 0;

         

        //start calculating
        hitung:
        while(!empty($Q)){
            $min = array_search(min($Q), $Q);//the most min weight
            if($min == $b) break;
            foreach($_distArr[$min] as $key=>$val) if(!empty($Q[$key]) && $Q[$min] + $val < $Q[$key]) {
                $Q[$key] = $Q[$min] + $val;
                $S[$key] = array($min, $Q[$key]);
            }
            unset($Q[$min]);
        }

         if (!array_key_exists($b, $S)) {
           
          $status = "no path found";
          $result = $S ;
          goto hasil;
        }

        //list the path
        $path = array();
        $pos = $b;
        while($pos != $a){
            $path[] = $pos;
            $pos = $S[$pos][0];
        }
        $path[] = $a;
        $path = array_reverse($path);


        for ($i=0; $i < sizeof($path); $i++) 
        {
          if(!isset($path[$i+1]))
          {
            continue;
          } 
          $result[] = $path[$i]."-".$path[$i+1];
        }

        $status = "OK";

        hasil:
          return ["status"=>$status,"step"=> $result];
    }

    public function get_titik_based_on_jalur($a = "1-2")
    { 
      //if(isset($_GET['a']) )
      //return $this->DB:select("");
       $shape = shape::all()->where('jalur','=', $a )
                            //->orderBy('shape_pt_sequence', 'Asc')
                            ->sortBy('shape_pt_sequence')
                            ;//->get();
       return $shape;
    }

    public function get_transfer($a)
    {
        $transfer = DB::select("select * from transfer where from_stop_id =".$a);
        return $transfer;
    }



    public function get_fastest_route3($a = 33, $b = 55)
    {
      if(isset($_GET['a']) and isset($_GET['b']) )
      {
      

        $a = $_GET['a'];
        $b = $_GET['b'];

      }
      //set the distance array
        $_distArr = array();

        $data = DB::select("select * from transfer");

        foreach ($data as $value) {
          $from = $value->from_stop_id;
          $to = $value->to_stop_id;
          $bobot = $value->distance;

          $_distArr[$from][$to] = $bobot;
        }

        $S = array();//the nearest path with its parent and weight
        $Q = array();//the left nodes without the nearest path
        foreach(array_keys($_distArr) as $val) $Q[$val] = 99999;
        $Q[$a] = 0;

         

        //start calculating
        hitung:
        while(!empty($Q)){
            $min = array_search(min($Q), $Q);//the most min weight
            if($min == $b) break;
            foreach($_distArr[$min] as $key=>$val) if(!empty($Q[$key]) && $Q[$min] + $val < $Q[$key]) {
                $Q[$key] = $Q[$min] + $val;
                $S[$key] = array($min, $Q[$key]);
            }
            unset($Q[$min]);
        }

         if (!array_key_exists($b, $S)) {
           
          $status = "no path found";
          $result = $S ;
          goto hasil;
        }

        //list the path
        $path = array();
        $pos = $b;
        while($pos != $a){
            $path[] = $pos;
            $pos = $S[$pos][0];
        }
        $path[] = $a;
        $path = array_reverse($path);


        for ($i=0; $i < sizeof($path); $i++) 
        {
          if(!isset($path[$i+1]))
          {
            continue;
          } 
          $result[] = $path[$i]."-".$path[$i+1];
        }

        $status = "OK";

        hasil:
          return ["status"=>$status,"step"=> $result];

    }

    public function cetak_jalur($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.59803771972656")
    {
      $data = $this->djikstra_cepat($start, $finish);
      if($data['start']->status !== "OK")
      {   
          $data_shapes = [];
          $jalur_angkot = [];
          goto hasil;
      }
      $awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $sequence_awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;
      $sequence_finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;
      
      $a = explode("-", $awal);
      $b = explode("-", $finish);
      $berangkat = $a[1] ;
      $sampai = $b[0] ;

      carijalur:  
      $fastest_route = $this->get_fastest_route3( $berangkat, $sampai);  // diambil jalur awal explode kedua karena dari awal ke ahirnya sudah diambil langsung dari $awalnya.
    
        if($fastest_route['status'] == "OK")
        { 

          $data_shapes[] = DB::select("select * from shapes where jalur LIKE '".$awal."' and shape_pt_sequence >= ".$sequence_awal); // step jalur pertama.

          for ($i=0; $i < sizeof($fastest_route['step']) ; $i++) 
          {  
             $data_shapes[] = DB::select("select * from shapes where jalur LIKE '".$fastest_route['step'][$i]."'");               
          }

          $dtakhir = end($data_shapes);
          $f = explode("-", $finish);
          $s = explode("-", $start);


       /*   if($data_shapes[1][0]->jalur == $start or $data_shapes[1][0] == $s[1]."-".$s[0] )
          {
            $data_shapes = [];  //untuk mengatasi jalur ga rapi.
            $berangkat = $a[0];
            goto carijalur;
          }

      if( $dtakhir[0]->jalur == $finish or $dtakhir[0]->jalur == $f[1]."-".$f[0] ) // PR----->
          {
            $data_shapes = []; //untuk mengatasi jalur ga rapi.
            $berangkat = $a[1];
            goto carijalur;
          }  */

          $data_shapes[] = DB::select("select * from shapes where jalur LIKE '".$finish."' and shape_pt_sequence <= ".$sequence_finish); // step jalur terakhir

          for ($i=0; $i < sizeof($data_shapes) ; $i++) // convert dari array 2 dimensi jadi array 1 dimensi.
          { 
              $hai = $data_shapes[$i][0]->shape_id;
              $jalur_angkot[] = DB::select("select * from trips where shape_id like '% ".$hai.",%' union select * from trips where shape_id = ".$hai." union select * from trips where shape_id like '% ".$hai."' ");
            /*  for ($m=0; $m < sizeof($data_shapes[$i]) ; $m++) 
              { 
                $shapes[] = $data_shapes[$i][$m]; // pemindahan array dua dimensi, ke satu dimensi. // tidak jadi dipakai.
              } */
          }

          for ($i=0; $i < sizeof($jalur_angkot) ; $i++) { 
            $color = [];
            for ($m=0; $m < sizeof($jalur_angkot[$i]) ; $m++) {

              $route_id = $jalur_angkot[$i][$m]->route_id;
              if($route_id !== 41)
              {
              $color[] = DB::select("select route_color from route where route_id =".$route_id);
              }
              else
              {continue;}   
            }
           if (!empty($color))
            {
            $warna[] = $color;
            }
            else
              {
                $color[] = [["route_color"=>"#FF0000" ]];
                $warna[] = $color;
              } 
          }
        
        }
        else
          {
            $data_shapes[] = 'unavailable';
            $jalur_angkot[] = 'unavailable';
          }  

          hasil:
          return [ "data"=>$data, "step"=> [ $data_shapes, $jalur_angkot , $warna] ];//json_decode(json_encode($shapes), true);

    } // tutup function

    public function cetak_jalur2($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265")
    {
      $data = $this->djikstra_cepat($start, $finish);
      if($data['start']->status !== "OK")
      {   
          $data_shapes = [];
          $jalur_angkot = [];
          $status = 'unavailable';
          $step = [];
          goto hasil;
      }

      

      $awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $sequence_awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;
      $sequence_finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;

      //$step[] = ["walk", "walk", [] ];
      
      $a = explode("-", $awal);
      $b = explode("-", $finish);
      $berangkat = $a[1] ;
      $sampai = $b[0] ;

      
     
      //deklarasi angkot start
      for ($i=0; $i < sizeof($data['start']->angkot) ; $i++) { 
        # code...
        
        for ($m=0; $m < sizeof($data['start']->angkot[$i]->trayek) ; $m++) { 
          # code...
          $angkot_start[] = json_decode(json_encode($data['start']->angkot[$i]->trayek[$m]),true); //   
          $pickup_point_start[] = json_decode(json_encode($data['start']->angkot[$i]->pickup_point->titik_terdekat),true);
          //$gabung_start[$m] = [$angkot_start[$m], $pickup_point_start[$m]];
        }
        
      }
      
      for ($i=0; $i < sizeof($angkot_start) ; $i++) { 
         # code...
         $gabung_start[] = [$angkot_start[$i], $pickup_point_start[$i]];
       } 
      
      //deklarasi angkot finish.
      for ($i=0; $i < sizeof($data['finish']->angkot) ; $i++) { 
        # code...
        for ($m=0; $m < sizeof($data['finish']->angkot[$i]->trayek) ; $m++) { 
          # code...
          $angkot_finish[] = json_decode(json_encode($data['finish']->angkot[$i]->trayek[$m]),true); //
          $pickup_point_finish[] = json_decode(json_encode($data['finish']->angkot[$i]->pickup_point->titik_terdekat),true);
          //$gabung_finish[$m] = [$angkot_finish[$m], $pickup_point_finish[$m]];    
        }          
      }

      for ($i=0; $i < sizeof($angkot_finish) ; $i++) { 
        # code...
        $gabung_finish[] = [$angkot_finish[$i], $pickup_point_finish[$i] ]; 
      }
      
      usort($gabung_finish, function($a, $b) {return $a[0]['route_id'] - $b[0]['route_id']; });
      usort($gabung_start, function($a, $b) {return $a[0]['route_id'] - $b[0]['route_id']; });
      usort($angkot_start, function($a, $b) {return $a['route_id'] - $b['route_id']; });
      usort($angkot_finish, function($a, $b) {return $a['route_id'] - $b['route_id']; });
         
      //return $sorted_gabung_finish;
     //return $gabung_finish;
      //jika angkot di start ada di angkot finish, maka return trayek angkot tersebut.
      for ($i=0; $i < sizeof($gabung_finish) ; $i++) { 
            # code...
            $gabung_finish1[] = $gabung_finish[$i][0]; 
          }

          for ($i=0; $i < sizeof($gabung_start) ; $i++) { 
            # code...
            $gabung_start1[] = $gabung_start[$i][0]; 
          }

      $intersec = array_map("unserialize", array_intersect( array_map("serialize", $gabung_start1) , array_map("serialize",$gabung_finish1) )) ; 
     


      foreach ($intersec as $key => $value) { // pemindahan numerical array ke array biasa.
        # code...
        $intersection[] = $value;
        $key1[] = $key;
      }
     
      

      if( !empty( $intersec ) ) // cek apakah ada angkot start di angkot finish.
      { 
        for ($i=0; $i < sizeof($intersection) ; $i++)  //untuk memaximalkan kalau ada lebih dari satu angkot yg sama.
        { 
          # code...
          $data_shapes_filtered = [];
          //step pertama
          $jlnawal = $gabung_start[$key1[$i]][1]; //$pickup_point_start[$key1[$i]];
          $start_position = json_decode(json_encode($data['start']->start_position),true) ;
          
          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [ (object) $start_position  ,$posisiawal ] ] ;  //step awal

          $status = "OK"; 
          $filtered_jalur = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where trips.route_id =".$intersection[$i]['route_id']); // ini bisa dimaksimalkan. pakai for saja.

          $data_shapes = $this->get_trayek($intersection[$i]['route_id']); // kita selection lagi aja hasil ini. 
          
          
            $ketemu = false;
            foreach ($data_shapes as $key => $value) {
              # code...
              if($value->id == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci1 = $key;

           


          //agar tahu ttitik turun yang ada angkot yang sama.
          $intersec2 = array_map("unserialize", array_intersect( array_map("serialize", $gabung_finish1) , array_map("serialize",$gabung_start1) )) ;

          foreach ($intersec2 as $key => $value) { // pemindahan numerical array ke array biasa.
          # code...
          $key2[] = $key;//$value['route_id'];
          }

          $jlnawal =    $gabung_finish[$key2[$i]][1];

          $start_position = json_decode(json_encode($data['finish']->start_position),true) ;
          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];

          $data_shapes_filtered = [];        //hapus $data_shape_filtered; 

          $data_shapes0 = $data_shapes;

          $ketemu2 = false;
            foreach ($data_shapes as $key => $value) 
            {
              # code...
             $jumlah[] = $value->id;
              if($value->id  == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci2 = $key;

           // return [$data_shapes[$kunci1],$data_shapes[$kunci2] ];

           // return [$kunci1, $kunci2];
          if($kunci1 < $kunci2)
          {  
            //return [$kunci1, $kunci2];
             if($ketemu)
             {
                for ($kj=0; $kj < $kunci1 ; $kj++) { 
                  # code...
                  unset($data_shapes[$kj] );  
                }
                foreach ($data_shapes as $key => $value) {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
                //$sizeof = 
             }
            
             $data_shapes_filtered = []; 

             if($ketemu)
             {  
                for ($m=sizeof($data_shapes) -1; $m > ($kunci2-$kunci1) ; $m--) { 
                  # code...
                  $unset[] = $data_shapes[$m];
                  //$perulangan[] = $m;
                  unset($data_shapes[$m] );
                }
                //return $unset;
                foreach ($data_shapes as $kunci => $value) 
                {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
             //   return [$unset, $kunci2, $m, sizeof($data_shapes)];
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
           
            //return sizeof($data_shapes);
             

          $step[] = ["angkot"=> $filtered_jalur, "jalur"=> $data_shapes];

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [ (object) $posisiawal,$start_position ] ] ;  //step awal

          $routingresult[] = ['step'=>$step];
          $step = [];
        } // akhir for
        
        
        goto hasil;

      }

      #step jalan pertama.
      $jlnawal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat),true) ;
      $start_position = json_decode(json_encode($data['start']->start_position),true) ;
      $posisiawal = (object) ["lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];

      $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [ (object) $start_position  ,$posisiawal ] ] ;  //step awal
      

      carijalur:  
      $fastest_route = $this->get_fastest_route3( $berangkat, $sampai);  // diambil jalur awal explode kedua karena dari awal ke ahirnya sudah diambil langsung dari $awalnya.
    
      if($fastest_route['status'] == "OK")
      { 
          $status = "OK";
          $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$awal."' and shape_pt_sequence >= ".$sequence_awal); // step jalur pertama.

          for ($i=0; $i < sizeof($fastest_route['step']) ; $i++) 
          {  
             $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$fastest_route['step'][$i]."'");               
          }

          $dtakhir = end($data_shapes);
          $f = explode("-", $finish);
          $s = explode("-", $start);

            $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng ,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$finish."' and shape_pt_sequence <= ".$sequence_finish); // step jalur terakhir

            for ($i=0; $i < sizeof($data_shapes) ; $i++) // pencarian trayek dari jalur yg dibuat dari shape id
            { 
                $hai = $data_shapes[$i][0]->shape_id;

               $jalur_angkot[] = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai.",%' union 
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id = ".$hai." union
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai."' ");
              
            }

            

            for ($i=0; $i < sizeof($jalur_angkot) ; $i++) {  // pencariaon angkot antar node
              # code...
              $filtered_jalur =[];
              for ($m=0; $m < sizeof($jalur_angkot[$i]) ; $m++) { 
                # code...
                $filtered_jalur[] = $jalur_angkot[$i][$m];
              }
              if(empty($filtered_jalur)){
                $filtered_jalur = [ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]];
              }
              $step [] = [ "angkot"=> $filtered_jalur, "jalur"=> $data_shapes[$i]];
              
            }

            $jlnawal = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat),true) ;
            $start_position = json_decode(json_encode($data['finish']->start_position),true) ;
            $posisiawal = (object) ["lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];
            //step terakhir. dari turun angkot ke titik tujuan.
            $step[] =  ["angkot"=> [(object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [  $posisiawal, (object) $start_position ] ] ;
            
            $routingresult[] = ['step'=>$step];
            
      }
      else
      {
        $status = 'unavailable';
        $step[] = 'unavailable';
        $routingresult[] = ['step'=>$step];
      }  

      hasil:
      return [ "status"=>$status, "routingresult"=> $routingresult ];//json_decode(json_encode($shapes), true);
      
    }

    public function cetak_jalur2_api($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265", $walk_route="no")
    {
      $data = $this->djikstra_cepat($start, $finish);

      if($data['start']->status !== "OK")
      {    

          $data_shapes = [];
          $jalur_angkot = [];
          $status = 'unavailable';
          $step = [];
          goto hasil;
      }

      return $data;

      $awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->jalur),true) ;
      $sequence_awal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;
      $sequence_finish = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat->shape_pt_sequence),true) ;

      //$step[] = ["walk", "walk", [] ];
      
      $a = explode("-", $awal);
      $b = explode("-", $finish);
      $berangkat = $a[1] ;
      $sampai = $b[0] ;

      
     
      //deklarasi angkot start
      for ($i=0; $i < sizeof($data['start']->angkot) ; $i++) { 
        # code...
        
        for ($m=0; $m < sizeof($data['start']->angkot[$i]->trayek) ; $m++) { 
          # code...
          $angkot_start[] = json_decode(json_encode($data['start']->angkot[$i]->trayek[$m]),true); //   
          $pickup_point_start[] = json_decode(json_encode($data['start']->angkot[$i]->pickup_point->titik_terdekat),true);
          //$gabung_start[$m] = [$angkot_start[$m], $pickup_point_start[$m]];
        }
        
      }
      
      for ($i=0; $i < sizeof($angkot_start) ; $i++) { 
         # code...
         $gabung_start[] = [$angkot_start[$i], $pickup_point_start[$i]];
       } 
      
      //deklarasi angkot finish.
      for ($i=0; $i < sizeof($data['finish']->angkot) ; $i++) { 
        # code...
        for ($m=0; $m < sizeof($data['finish']->angkot[$i]->trayek) ; $m++) { 
          # code...
          $angkot_finish[] = json_decode(json_encode($data['finish']->angkot[$i]->trayek[$m]),true); //
          $pickup_point_finish[] = json_decode(json_encode($data['finish']->angkot[$i]->pickup_point->titik_terdekat),true);
          //$gabung_finish[$m] = [$angkot_finish[$m], $pickup_point_finish[$m]];    
        }          
      }

      for ($i=0; $i < sizeof($angkot_finish) ; $i++) { 
        # code...
        $gabung_finish[] = [$angkot_finish[$i], $pickup_point_finish[$i] ]; 
      }
      
      usort($gabung_finish, function($a, $b) {return $a[0]['route_id'] - $b[0]['route_id']; });
      usort($gabung_start, function($a, $b) {return $a[0]['route_id'] - $b[0]['route_id']; });
      usort($angkot_start, function($a, $b) {return $a['route_id'] - $b['route_id']; });
      usort($angkot_finish, function($a, $b) {return $a['route_id'] - $b['route_id']; });
         
      //return $sorted_gabung_finish;
     //return $gabung_finish;
      //jika angkot di start ada di angkot finish, maka return trayek angkot tersebut.
      for ($i=0; $i < sizeof($gabung_finish) ; $i++) { 
            # code...
            $gabung_finish1[] = $gabung_finish[$i][0]; 
          }

      for ($i=0; $i < sizeof($gabung_start) ; $i++) { 
        # code...
        $gabung_start1[] = $gabung_start[$i][0]; 
      }

      $intersec = array_map("unserialize", array_intersect( array_map("serialize", $gabung_start1) , array_map("serialize",$gabung_finish1) )) ; 
     


      foreach ($intersec as $key => $value) { // pemindahan numerical array ke array biasa.
        # code...
        $intersection[] = $value;
        $key1[] = $key;
      }
     
      

      if( !empty( $intersec ) ) // cek apakah ada angkot start di angkot finish.
      { 
        //return "fungsi satu angkot";
        for ($i=0; $i < sizeof($intersection) ; $i++)  //untuk memaximalkan kalau ada lebih dari satu angkot yg sama.
        { 
          # code...
          $data_shapes_filtered = [];
          //step pertama
          $jlnawal = $gabung_start[$key1[$i]][1]; //$pickup_point_start[$key1[$i]];
          $start_position = json_decode(json_encode($data['start']->start_position),true) ;

          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'], "place_info"=>$jlnawal['place_info'] ];

          if($walk_route == "no")
          {

            $walking_path =  [ "status"=>"unavailable"];
          }
          else
          {

            $walking_path = $this->get_walking_route($start_position, $posisiawal);
          }

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#000000", "price"=>0, "image"=>"public/images/walk.png" ]] , "jalur"=> [ (object) $start_position  , (object) $posisiawal, $walking_path ] ] ;  //step awal

          $status = "OK"; 
          $filtered_jalur = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price, route.image  from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where trips.route_id =".$intersection[$i]['route_id']); // ini bisa dimaksimalkan. pakai for saja.

          $data_shapes = $this->get_trayek($intersection[$i]['route_id']); // kita selection lagi aja hasil ini. 
          
          
            $ketemu = false;
            foreach ($data_shapes as $key => $value) {
              # code...
              if($value->id == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci1 = $key;

           


          //agar tahu ttitik turun yang ada angkot yang sama.
          $intersec2 = array_map("unserialize", array_intersect( array_map("serialize", $gabung_finish1) , array_map("serialize",$gabung_start1) )) ;

          foreach ($intersec2 as $key => $value) { // pemindahan associative array ke array numeric.
            # code...
            $key2[] = $key;//$value['route_id'];
          }

          $jlnawal =    $gabung_finish[$key2[$i]][1];

          $start_position = json_decode(json_encode($data['finish']->start_position),true) ;
          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] , "place_info"=>$jlnawal['place_info'] ];

          $data_shapes_filtered = [];        //hapus $data_shape_filtered; 

          $data_shapes0 = $data_shapes;

          $ketemu2 = false;
            foreach ($data_shapes as $key => $value) 
            {
              # code...
             $jumlah[] = $value->id;
              if($value->id  == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci2 = $key;

           // return [$data_shapes[$kunci1],$data_shapes[$kunci2] ];

           // return [$kunci1, $kunci2];
          if($kunci1 < $kunci2)
          {  
            //return [$kunci1, $kunci2];
             if($ketemu)
             {
                for ($kj=0; $kj < $kunci1 ; $kj++) { 
                  # code...
                  unset($data_shapes[$kj] );  
                }
                foreach ($data_shapes as $key => $value) {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
                //$sizeof = 
             }
            
             $data_shapes_filtered = []; 

             if($ketemu)
             {  
                for ($m=sizeof($data_shapes) -1; $m > ($kunci2-$kunci1) ; $m--) { 
                  # code...
                  $unset[] = $data_shapes[$m];
                  //$perulangan[] = $m;
                  unset($data_shapes[$m] );
                }
                //return $unset;
                foreach ($data_shapes as $kunci => $value) 
                {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
             //   return [$unset, $kunci2, $m, sizeof($data_shapes)];
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
           
            //return sizeof($data_shapes);
             
          $step[] = ["angkot"=> $filtered_jalur, "jalur"=> $data_shapes]; // step angkot, step kedua.

          if($walk_route == "no")
          {
            $walking_path = [ "status"=>"unavailable"];
          }
          else
          {
            $walking_path = $this->get_walking_route($posisiawal,$start_position);
          }

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#000000", "price"=>0, "image"=>"public/images/walk.png" ] ] , "jalur"=> [ (object) $posisiawal, (object) $start_position, $walking_path ] ] ;  //step awal
          //perubahan jadi object
          foreach ($step as $ii => $value) {
            foreach ($step[$ii]['jalur'] as $j => $value) {
              $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j]; 
            }
          }
          //return $step;
          //return  $step[]['jalur'][0]->lat ;
          //penambahan distance  
          foreach ($step as $ii => $value) {
           
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
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 1/3 pertama
              {
                # code...
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);                  
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
        } // akhir for
        
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
                    //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                    //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                  }
              }
              else
              {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                $turun = explode(", ", $turun);
                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);
                //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                  //$turun = $turun->results[0]->formatted_address;
    
                 //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                  //$turun = $turun->results[0]->formatted_address;
    
                 //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";

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
    
                  $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                  //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                  $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>angkot ".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
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
  
                $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                $routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
              }
            }

          }
        } 

        goto hasil;

      }

      #step jalan pertama.
      $jlnawal = json_decode(json_encode($data['start']->angkot[0]->pickup_point->titik_terdekat),true) ;
      $start_position = json_decode(json_encode($data['start']->start_position),true) ;
      $posisiawal = (object) ["lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];

      $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [ (object) $start_position , (object) $posisiawal ] ] ;  //step awal
      

      carijalur:  
      $fastest_route = $this->get_fastest_route3( $berangkat, $sampai);  // diambil jalur awal explode kedua karena dari awal ke ahirnya sudah diambil langsung dari $awalnya.
    
      if($fastest_route['status'] == "OK")
      { 
          $status = "OK";
          $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$awal."' and shape_pt_sequence >= ".$sequence_awal); // step jalur pertama.

          for ($i=0; $i < sizeof($fastest_route['step']) ; $i++) 
          {  
             $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$fastest_route['step'][$i]."'");               
          }

          $dtakhir = end($data_shapes);
          $f = explode("-", $finish);
          $s = explode("-", $start);

            $data_shapes[] = DB::select("select id, shape_pt_lat as lat,shape_pt_lon as lng ,shape_pt_sequence,shape_id from shapes where jalur LIKE '".$finish."' and shape_pt_sequence <= ".$sequence_finish); // step jalur terakhir

            for ($i=0; $i < sizeof($data_shapes) ; $i++) // pencarian trayek dari jalur yg dibuat dari shape id
            { 
                $hai = $data_shapes[$i][0]->shape_id;

               $jalur_angkot[] = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai.",%' union 
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id = ".$hai." union
                  select trips.route_id,trip_short_name,shape_id,route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where shape_id like '% ".$hai."' ");
              
            }

            

            for ($i=0; $i < sizeof($jalur_angkot) ; $i++) {  // pencariaon angkot antar node
              # code...
              $filtered_jalur =[];
              for ($m=0; $m < sizeof($jalur_angkot[$i]) ; $m++) { 
                # code...
                $filtered_jalur[] = $jalur_angkot[$i][$m];
              }
              if(empty($filtered_jalur)){
                $filtered_jalur = [ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]];
              }
              $step [] = [ "angkot"=> $filtered_jalur, "jalur"=> $data_shapes[$i]];
              
            }

            $jlnawal = json_decode(json_encode($data['finish']->angkot[0]->pickup_point->titik_terdekat),true) ;
            $start_position = json_decode(json_encode($data['finish']->start_position),true) ;
            $posisiawal = (object) ["lat"=> $jlnawal['shape_pt_lat'] , "lng"=>$jlnawal['shape_pt_lon'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] ];
            //step terakhir. dari turun angkot ke titik tujuan.
            $step[] =  ["angkot"=> [(object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#FF0000", "price"=>0]] , "jalur"=> [  $posisiawal, (object) $start_position ] ] ;
            
            $routingresult[] = ['step'=>$step];
            
      }
      else
      {
        $status = 'unavailable';
        $step[] = 'unavailable';
        $routingresult[] = ['step'=>$step];
      }  

      hasil:
      return [ "status"=>$status, "routingresult"=> $routingresult ];//json_decode(json_encode($shapes), true);
      
    } // akhir function 

    public function cetak_jalur3($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265")
    {
      $data = $this->djikstra_cepat_new($start, $finish);
      //return $data;
      if($data['start']->status == "OK" and $data['finish']->status == "OK" )
      {
        $status = 'OK';
      }
      
      if($data['start']->status !== "OK" || $data['finish']->status !== "OK")
      {
        $status = 'Bad';
        return ["status"=>"bad"];
      }
      if(empty( $data['start']->angkot) || empty( $data['finish']->angkot) )
      {
        return $data;//'tidak ada angkot di titik start atau titik finish';
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
      //return $intersec;

      //cek ada angkot yang sama atau tidak di start dan Finish.
      if(!empty($intersec))
      {
        $cetak_jalur2 = $this->cetak_jalur2($start, $finish);
        return $cetak_jalur2;
      }
      else
      {
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

       // return $intersec_shape_id;
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
         
        
        
        //return $intersection_shape_id_numeric;
        //jika ada shape id yang bersimpangan, maka ambil sebagai tempat persinggungan.
        if(!empty( $intersection_shape_id) )
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


          for ($i=0; $i < sizeof($no_intersec) ; $i++) { 
            # code...
            $step = [];
            $potong_akhir_start = DB::select("select id from shapes where shape_id =".$intersection_shape_id_numeric[$i][0]." and shape_pt_sequence = 0 ");
            $step[] = ["angkot"=>[["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#FF0000","price"=> 0 ]] , "jalur"=>[ $data['start']->start_position , $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat ]   ];
            //return  $potong_akhir_start[0]->id;

            $angkot_start_intersec = $angkot_start[$no_intersec[$i]['start']];
            $data_shapes = $this->get_trayek_potong($angkot_start_intersec->route_id ,  $pickup_point_start[$no_intersec[$i]['start']]->titik_terdekat->id, $potong_akhir_start[0]->id );
            
            //return $data_shapes;
            

            $step[] = ["angkot"=>[$angkot_start_intersec ], "jalur" => $data_shapes ];
            
            $angkot_finish_intersec = $angkot_finish[$no_intersec[$i]['finish']];
            $data_shapes2 = $this->get_trayek_potong($angkot_finish_intersec->route_id ,  $potong_akhir_start[0]->id , $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat->id );
            $step[] = ["angkot"=>[$angkot_finish_intersec] , "jalur" => $data_shapes2 ];
            
            $step[] = ["angkot"=>[["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#FF0000","price"=> 0 ] ], "jalur"=>[ $pickup_point_finish[ $no_intersec[$i]['finish']]->titik_terdekat , $data['finish']->start_position ]  ];
            $routingresult[] = [ "step"=> $step ];
          }
          

          return [ "status"=>$status, "routingresult"=> $routingresult ]; //[ $angkot_start_intersec, $angkot_finish_intersec ];
        }
        else
        {
          //return "ini coding untuk lebih dari dua angkot";  
           //ambil all angkot.
            $all_angkot = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id");
            
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

            //pemindahan array dua dimensi ke satu dimensi & pencarian nomor index untuk keperluan pencarian angkot.
            //return $shape_id_finish;
           /*if( !empty($intersec_shape_id_finish_all[1][1]) )
           {return "true" ;}
           else
           {return "false";}
            */
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
                  #pencarian Angkot.
                  $angkot = [];
                  foreach ($index as $i => $value) {
                     
                     foreach ($index[$i] as $j => $value) {
                       # code...
                      if($j == 2)
                       {continue;} 
                       $angkot[$i][] = $new_reverse_unduplicated[$j][$value];
                       $index_pickup_points[$i][] = $value;
                     }

                  }
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
                  $pickup_point_finish = array_reverse($pickup_point_finish);
                  $pickup_point_start = array_reverse($pickup_point_start);
                  $jalur = [];
                  $array =[];
                  //return $angkot_start;
                  //  return $potong1[7];
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

                       $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong2 , $pickup_point_start[ $index[$i][3] /*index terakhir na, index array angkot_start*/ ]->titik_terdekat->id  );

                       $array[$i][] = [$value->route_id, reset($potong2[$i]) , $pickup_point_start[$index[$i][3]]->titik_terdekat->id ];
                      }
                    }
                  }
                  //return $array_i;
                  //return $jalur;
                  //return $array;

                  foreach ($angkot as $i => $value) {
                    # code...
                    $step = [];

                    foreach ($angkot[$i] as $j => $value) {
                      # code...
                      if($j == 0)
                      {
                        
                        $step[] = ["angkot"=>[["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#FF0000","price"=> 0 ]] , 'jalur'=>[ $pickup_point_finish[ $index[$i][0] ]->titik_terdekat , $data['finish']->start_position ] ];
                        
                      }

                      $step[] = ["angkot"=>[$value], "jalur"=>array_reverse( json_decode(json_encode($jalur[$i][$j]),true)  ) ] ;

                      if($j == sizeof($angkot[$i])-1 )
                      {  
                        $step[sizeof($step)] = ["angkot"=> [ ["route_id"=>99 , "trip_short_name"=>'walking' , "shape_id"=>"" , "route_color"=>"#FF0000","price"=> 0 ] ] , 'jalur'=>[ $data['start']->start_position , $pickup_point_start[ $index[$i][3] ]->titik_terdekat ]  ];
                      }

                      

                    }
                    
                    $routingresult[] = ["step"=>array_reverse( $step) ];
                  }
                  
                  return ["status"=>$status, "routingresult"=>$routingresult ];

                  
                } //tutup if 3 angkot saja.
                elseif( sizeof($jejak_result_shape_id_reverse) == 3 ) //jika harus naik 4 angkot
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

    public function cetak_jalur3_api($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265", $walk_route='no')
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

      $data = $this->djikstra_cepat_new($start, $finish);
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
      //return $intersec;

      //cek ada angkot yang sama atau tidak di start dan Finish.
      if(!empty($intersec))
      { 
        for ($i=0; $i < sizeof($angkot_start) ; $i++) { 
         # code...
         $gabung_start[] = [$angkot_start[$i], $pickup_point_start[$i]];
        }
        //return $gabung_start;
        for ($i=0; $i < sizeof($angkot_finish) ; $i++) { 
          # code...
          $gabung_finish[] = [$angkot_finish[$i], $pickup_point_finish[$i] ]; 
        }

        for ($i=0; $i < sizeof($gabung_finish) ; $i++) { 
            # code...
            $gabung_finish1[] = $gabung_finish[$i][0]; 
          }

        for ($i=0; $i < sizeof($gabung_start) ; $i++) { 
          # code...
          $gabung_start1[] = $gabung_start[$i][0]; 
        }

        foreach ($intersec as $key => $value) { // pemindahan numerical array ke array biasa.
          # code...
          $intersection[] = $value;
          $key1[] = $key;
        }

        for ($i=0; $i < sizeof($intersection) ; $i++)  //untuk memaximalkan kalau ada lebih dari satu angkot yg sama.
        { 
          # code...
          $data_shapes_filtered = [];
          //step pertama
          $jlnawal = $gabung_start[$key1[$i]][1]; //$pickup_point_start[$key1[$i]];
          $jlnawal = $jlnawal->titik_terdekat;
          $jlnawal = json_decode(json_encode($jlnawal),true);
          //return  $gabung_start;
          //return $jlnawal;
          $start_position = json_decode(json_encode($data['start']->start_position),true) ;

          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['lat'] , "lng"=>$jlnawal['lng'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'], "place_info"=>$jlnawal['place_info'] ];

          if($walk_route == "no")
          {

            $walking_path =  [ "status"=>"unavailable"];
          }
          else
          {

            $walking_path = $this->get_walking_route($start_position, $posisiawal);
          }

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#000000", "price"=>0, "image"=>"public/images/walk.png" ]] , "jalur"=> [ (object) $start_position  , (object) $posisiawal, $walking_path ] ] ;  //step awal

          $status = "OK"; 
          $intersection = json_decode(json_encode($intersection),true);
          $filtered_jalur = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price, route.image  from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id where trips.route_id =".$intersection[$i]['route_id']); // ini bisa dimaksimalkan. pakai for saja.

          $data_shapes = $this->get_trayek($intersection[$i]['route_id']); // kita selection lagi aja hasil ini. 
          
          
            $ketemu = false;
            foreach ($data_shapes as $key => $value) {
              # code...
              if($value->id == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci1 = $key;

           


          //agar tahu ttitik turun yang ada angkot yang sama.
          $intersec2 = array_map("unserialize", array_intersect( array_map("serialize", $gabung_finish1) , array_map("serialize",$gabung_start1) )) ;

          foreach ($intersec2 as $key => $value) { // pemindahan associative array ke array numeric.
            # code...
            $key2[] = $key;//$value['route_id'];
          }

          $jlnawal =    $gabung_finish[$key2[$i]][1];
          $jlnawal = $jlnawal->titik_terdekat;
          $jlnawal = json_decode(json_encode($jlnawal),true);

          $start_position = json_decode(json_encode($data['finish']->start_position),true) ;
          $posisiawal = (object) ["id"=>$jlnawal['id'], "lat"=> $jlnawal['lat'] , "lng"=>$jlnawal['lng'] ,"shape_id"=>$jlnawal['shape_id'] ,  "shape_pt_sequence"=>$jlnawal['shape_pt_sequence'] , "place_info"=>$jlnawal['place_info'] ];

          $data_shapes_filtered = [];        //hapus $data_shape_filtered; 

          $data_shapes0 = $data_shapes;

          $ketemu2 = false;
            foreach ($data_shapes as $key => $value) 
            {
              # code...
             $jumlah[] = $value->id;
              if($value->id  == $posisiawal->id )
              {
                $ketemu = true;
                break;
              }
            }

            $kunci2 = $key;

           // return [$data_shapes[$kunci1],$data_shapes[$kunci2] ];

           // return [$kunci1, $kunci2];
          if($kunci1 < $kunci2)
          {  
            //return [$kunci1, $kunci2];
             if($ketemu)
             {
                for ($kj=0; $kj < $kunci1 ; $kj++) { 
                  # code...
                  unset($data_shapes[$kj] );  
                }
                foreach ($data_shapes as $key => $value) {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
                //$sizeof = 
             }
            
             $data_shapes_filtered = []; 

             if($ketemu)
             {  
                for ($m=sizeof($data_shapes) -1; $m > ($kunci2-$kunci1) ; $m--) { 
                  # code...
                  $unset[] = $data_shapes[$m];
                  //$perulangan[] = $m;
                  unset($data_shapes[$m] );
                }
                //return $unset;
                foreach ($data_shapes as $kunci => $value) 
                {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
             //   return [$unset, $kunci2, $m, sizeof($data_shapes)];
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
           
            //return sizeof($data_shapes);
             
          $step[] = ["angkot"=> $filtered_jalur, "jalur"=> $data_shapes]; // step angkot, step kedua.

          if($walk_route == "no")
          {
            $walking_path = [ "status"=>"unavailable"];
          }
          else
          {
            $walking_path = $this->get_walking_route($posisiawal,$start_position);
          }

          $step[] = ["angkot"=>[ (object) ["route_id"=>99, "trip_short_name"=>'walking', "shape_id"=>'', "route_color"=> "#000000", "price"=>0, "image"=>"public/images/walk.png" ] ] , "jalur"=> [ (object) $posisiawal, (object) $start_position, $walking_path ] ] ;  //step awal
          //perubahan jadi object
          foreach ($step as $ii => $value) {
            foreach ($step[$ii]['jalur'] as $j => $value) {
              $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j]; 
            }
          }
          //return $step;
          //return  $step[]['jalur'][0]->lat ;
          //penambahan distance  
          foreach ($step as $ii => $value) {
           
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
              }
              elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 1/3 pertama
              {
                # code...
                $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                //$temp = substr($value['angkot'][0]->price, -2);
                $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);                  
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
        } // akhir for
        
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
                    //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                    //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                  }
              }
              else
              {
                //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                $turun = explode(", ", $turun);
                $jarak = $routingresult[$i]['step'][$j]['distance'];
                $jarak = explode(".", $jarak);
                //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                  //$turun = $turun->results[0]->formatted_address;
    
                 //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                  //$turun = $turun->results[0]->formatted_address;
    
                 //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";

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
    
                  $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                  //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                  $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>angkot ".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
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
  
                $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                $routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
              }
            }

          }
        }

        return [ "status"=>$status, "routingresult"=> $routingresult ];//json_decode(json_encode($shapes), true);
      }
      else
      {
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

       // return $intersec_shape_id;
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
           
        //return $intersection_shape_id_numeric;
        //jika ada shape id yang bersimpangan, maka ambil sebagai tempat persinggungan.

        if(!empty( $intersection_shape_id) ) //fungsi 2 angkot
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

          //return $intersection_shape_id_numeric; #shapeid yang berintersection

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

            //penambahan distance  
            foreach ($step as $ii => $value) {
              # code...
              //$jarak = [];
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
                }
                elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 1/3 pertama
                {
                  # code...
                  $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                  //$temp = substr($value['angkot'][0]->price, -2);
                  $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);                  
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
          }
          
          usort($routingresult, function($a, $b) {return $a['total_cost'] - $b['total_cost']; }); //sorting by total cost
          $routingresult = array_slice($routingresult, 0,3) ; //return cuman 3 kombinasi
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
          //penambahan logika ket
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
                      //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                      $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                      //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                      $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                    }
                }
                else
                {
                  //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                  $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                  $turun = explode(", ", $turun);
                  $jarak = $routingresult[$i]['step'][$j]['distance'];
                  $jarak = explode(".", $jarak);
                  //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                  $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                    //$turun = $turun->results[0]->formatted_address;
      
                   //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                    //$turun = $turun->results[0]->formatted_address;
      
                   //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                    $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";

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
      
                    $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                    //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                    $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>angkot ".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
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
    
                  $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                  //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                  $routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
                }
              }


            }
          }

          return [ "status"=>$status, "routingresult"=> $routingresult ]; //[ $angkot_start_intersec, $angkot_finish_intersec ];
        }
        else // fungsi 3 angkot.
        {
           
           //ambil all angkot.
            $all_angkot = DB::select("select trips.route_id,trip_short_name,shape_id, route.route_color,  fare_attributes.price, route.image from trips left join route on trips.route_id = route.route_id left join fare_rule on trips.route_id = fare_rule.route_id left join fare_attributes on fare_rule.fare_id = fare_attributes.fare_id");
            
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

            //pemindahan array dua dimensi ke satu dimensi & pencarian nomor index untuk keperluan pencarian angkot.
            //return $shape_id_finish;
           /*if( !empty($intersec_shape_id_finish_all[1][1]) )
           {return "true" ;}
           else
           {return "false";}
            */
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
                  #pencarian Angkot.
                  $angkot = [];
                  foreach ($index as $i => $value) {
                     
                     foreach ($index[$i] as $j => $value) {
                       # code...
                      if($j == 2)
                       {continue;} 
                       $angkot[$i][] = $new_reverse_unduplicated[$j][$value];
                       $index_pickup_points[$i][] = $value;
                     }

                  }


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

                        $array[$i][] = [$value->route_id, $pickup_point_finish[$index[$i][0] ]->titik_terdekat->id, $tmp_potong];
                      }
                      elseif ($j == 1) {
                        $tmp_potong = DB::select("select id from shapes where shape_id =".end($potong1[$i])." and shape_pt_sequence = 0 ") ;
                        $tmp_potong = $tmp_potong[0]->id;
                        $tmp_potong2 = DB::select("select id from shapes where shape_id =".end($potong2[$i])." and shape_pt_sequence = 0 ") ;
                        $tmp_potong2 = $tmp_potong2[0]->id;
                        
                        $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong, $tmp_potong2  );
                        $array[$i][]=[$value->route_id, $tmp_potong, $tmp_potong2];
                      }
                      elseif ($j == 2) {
                        $tmp_potong2 = DB::select("select id from shapes where shape_id =".end($potong2[$i])." and shape_pt_sequence = 0 ") ;
                        $tmp_potong2 = $tmp_potong2[0]->id;

                       $jalur[$i][] = $this->get_trayek_potong($value->route_id, $tmp_potong2 , $pickup_point_start[ $index[$i][3] /*index terakhir na, index array angkot_start*/ ]->titik_terdekat->id  );

                       $array[$i][] = [$value->route_id, reset($potong2[$i]) , $pickup_point_start[$index[$i][3]]->titik_terdekat->id ];
                      }
                    }
                  }
                  //return $array_i;
                  //return $jalur;
                  //return $array;

                 
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

                     //penambahan distance 
                    //perubahan jadi object semua.
                    foreach ($step as $ii => $value) {
                      # code...
                      foreach ($step[$ii]['jalur'] as $key => $value) {
                        # code...
                        $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j] ;
                      }
                    }
                    //penambahan logika jarak
                    foreach ($step as $ii => $value) {
                      # code...
                      //$jarak = [];

                      foreach ($step[$ii]['jalur'] as $j => $value) {
                        # code...
                        if(!is_object($step[$ii]['jalur'][$j]) || !is_object($step[$ii]['jalur'][$j] ) )
                        {
                         $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                         $step[$ii]['jalur'][$j] = (object) $step[$ii]['jalur'][$j];
                        }

                        //$step[1]['jalur'][$j]->lat ;
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
                        }
                        elseif ($value['distance'] > 1000 and $value['distance'] <= ( (2/3) * $total_jarak) ) // 1/3 pertama
                        {
                          # code...
                          $value['angkot'][0]->price = round(1500 + ( ($value['angkot'][0]->price - 1500) * (2/3) )) ; //pembulatan
                          //$temp = substr($value['angkot'][0]->price, -2);
                          $value['angkot'][0]->price = substr_replace($value['angkot'][0]->price , '00', -2);                  
                        }
                        else
                        {
                          $value['angkot'][0]->price = $value['angkot'][0]->price;
                        }
                      
                      }

                    }// */

                    //coding total harga
                    $total_cost = 0;
                    foreach ($step as $key => $value) {
                      # code...
                      $temp = (object) $value['angkot'][0] ;
                      $total_cost = $total_cost + $temp->price;
                    }

                    $routingresult[] = ["step"=>array_reverse( $step) , "total_cost"=> $total_cost  ];
                  }

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
                  //fungsi tambah ket
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
                              //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                              $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                              //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                              $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
                            }
                        }
                        else
                        {
                          //$turun = $routingresult[$i]['step'][$j]['jalur'][2]['routes'][0]['legs'][0]['end_address'];
                          $turun = $routingresult[$i]['step'][$j]['jalur'][2]->routes[0]->legs[0]->end_address;
                          $turun = explode(", ", $turun);
                          $jarak = $routingresult[$i]['step'][$j]['distance'];
                          $jarak = explode(".", $jarak);
                          //$routingresult[$i]['step'][$j]['ket'] = "walk from your position to ".$turun[0]." for about ".$routingresult[$i]['step'][$j]['distance'] ." meter" ;
                          $routingresult[$i]['step'][$j]['ket'] = "Jalan dari posisi anda menuju <strong>".$turun[0]."</strong> kurang lebih ".$jarak[0]." meter" ;
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
                            //$turun = $turun->results[0]->formatted_address;
              
                           //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                            $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                            //$turun = $turun->results[0]->formatted_address;
              
                           //$routingresult[$i]['step'][$j]['ket'] = "walk from ".$naik[0]." to your destination " ; 
                            $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";
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
                            $routingresult[$i]['step'][$j]['ket'] = "Jalan dari <strong>".$naik[0]."</strong> ke tujuan anda kurang lebih ".$jarak[0]." meter";

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
              
                            $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                            //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                            $routingresult[$i]['step'][$j]['ket'] = "Naik <strong>angkot ".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
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
            
                          $angkot = $routingresult[$i]['step'][$j]['angkot'][0]->trip_short_name ;
                          //$routingresult[$i]['step'][$j]['ket'] = "take angkot ".$angkot." from ".$naik[0]." to ".$turun[0] ;
                          $routingresult[$i]['step'][$j]['ket'] = "Naik angkot <strong>".$angkot."</strong> dari <strong>".$naik[0]."</strong> ke <strong>".$turun[0]."</strong>" ;
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

    public function efisiensi($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265")
    {
        $cetak_jalur2 = $this->cetak_jalur2($start, $finish);
        $status = $cetak_jalur2['status'];
        //deklarasi angkot
        for ($n=0; $n < sizeof($cetak_jalur2['routingresult']) ; $n++) { 
          # code...
          for ($i=0; $i < sizeof($cetak_jalur2['routingresult'][$n]['step']) ; $i++) { 
            # code...
            $a = array();
            $isi = array();
            $jlr = array();
            for ($m=0; $m < sizeof($cetak_jalur2['routingresult'][$n]['step'][$i]['angkot']) ; $m++) { 
              # code...
              $a[] = $cetak_jalur2['routingresult'][$n]['step'][$i]['angkot'][$m]->route_id;
              $isi[] = $cetak_jalur2['routingresult'][$n]['step'][$i]['angkot'][$m];
            }
            for ($s=0; $s < sizeof($cetak_jalur2['routingresult'][$n]['step'][$i]['jalur']) ; $s++) { 
              # code...
            $jlr[] = $cetak_jalur2['routingresult'][$n]['step'][$i]['jalur'][$s];
            }
            $d[] = $a;
            $angkot[] = $isi;
            $jalur[] = $jlr; 
          }
        }
          
        

        for ($i=0; $i < sizeof($angkot) ; $i++) { 
          # code...
          $pertama[] = array_map("serialize", $angkot[$i]) ;
           if(empty($angkot[$i+1]))
            {continue;}
          $kedua[] = array_map("serialize", $angkot[$i+1]) ;//$d[$i+1];
          
          if(isset($pertama) && isset($kedua))
          {  
            $intersec = array_map('unserialize', array_intersect( array_map("serialize", $angkot[$i]) , array_map("serialize", $angkot[$i+1]) ));//($pertama[0], $kedua[0]) ) ;
            
            if(!empty($intersec))
            {
              
              $temp_jalur[] = $jalur[$i] ;

                //continue;
            }
            else
            { 
            
              if(!isset($temp_jalur))
              {
                $temp_jalur[] = $jalur[$i];
              }
              else
              {
                $temp_jalur[] = $jalur[$i];//array_push($temp_jalur, $jalur[$i]);
              }

              for ($rs=0; $rs < sizeof($temp_jalur) ; $rs++) { 
                # code...
                for ($mn=0; $mn < sizeof($temp_jalur[$rs]) ; $mn++) { 
                  # code...
                  $result_temp_jalur[] = $temp_jalur[$rs][$mn];
                }
              }

              $hasil[]=   [ "angkot"=> $angkot[$i], "jalur"=>$result_temp_jalur ] ;
              
              $temp_jalur = [];
              $result_temp_jalur = [];  
            }
          }



          $pertama = [];
          $kedua = []; 
        } 
       

        $hasil[]=   [ "angkot"=> end($angkot) , "jalur"=>end($jalur)  ] ; 
        $routingresult[] = ['step'=>$hasil];
        return [ "status"=>$status, "routingresult"=> $routingresult  ];

    }

    public function efisiensi2($start="-6.897286083979936,107.64301300048828", $finish="-6.900524035220587,107.5980377197265")
    {
      $cetak_jalur2 = $this->cetak_jalur2($start, $finish);
      if($cetak_jalur2['status'] == 'OK')
      {
        $status = 'OK';
      }
      if( sizeof($cetak_jalur2['routingresult']) > 1 )//harus diperbaiki. belum tentu hasil routing result cuman 
      {
        $routingresult = $cetak_jalur2['routingresult'];
        goto step;
      }

      //deklarasi angkot.
      for ($i=0; $i < sizeof($cetak_jalur2['routingresult']) ; $i++) 
      { 
        # code...
        for ($j=0; $j < sizeof($cetak_jalur2['routingresult'][$i]['step']) ; $j++) { 
          # code...
          $temp_angkot = [];
          for ($k=0; $k <sizeof($cetak_jalur2['routingresult'][$i]['step'][$j]['angkot']) ; $k++) { 
            # code...
            $temp_angkot[] = $cetak_jalur2['routingresult'][$i]['step'][$j]['angkot'][$k];
          }
          $angkot[] = $temp_angkot;
          $temp_jalur = [];
          for ($l=0; $l < sizeof($cetak_jalur2['routingresult'][$i]['step'][$j]['jalur']) ; $l++) { 
            # code...
            $temp_jalur[] = $cetak_jalur2['routingresult'][$i]['step'][$j]['jalur'][$l];
          }
          $jalur[] = $temp_jalur;
          
        }
      }

      for ($i=0; $i < sizeof($angkot); $i++) { 
        # code...
        $gabung[] = ["angkot"=>$angkot[$i], "jalur"=>$jalur[$i]];
      }
      
     // return $gabung;
      
      $akhir = $angkot[count($angkot) - 2];
      for ($j=0; $j < sizeof($angkot) ; $j++) { 
        # code...
        $awal = $angkot[$j];
        $intersec = array_map("unserialize", array_intersect(array_map("serialize", $awal) ,array_map("serialize", $akhir))) ;
        
        if(!empty($intersec)){
          $temporary_angkot[$j] = $intersec;
          // pemindahan array associative ke numeric
          foreach ($intersec as $key => $value) {
            $intersection[] = $value;
          }

          for ($k=0; $k < sizeof($intersec) ; $k++) { 
              # code...
            
             $data_shapes = $this->get_trayek($intersection[$k]->route_id);
             $temporary_jalur[$j] = $jalur[$j];
             
             $id = $temporary_jalur[$j][0]->id; //masih ngambil id yg pertama saja.
            
             $ketemu = false;
             foreach ($data_shapes as $key => $value) 
             {
                # code...
                if($value->id == $id )
                {
                  $ketemu = true;
                  //return $value->id;
                  break;
                }
             }

             if($ketemu)
             {
                for ($kj=0; $kj < $key ; $kj++) { 
                  # code...
                  unset($data_shapes[$kj] );  
                }
                foreach ($data_shapes as $key => $value) {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
             }
             // return $data_shapes;
             $data_shapes_filtered = []; 
             $end_jalur = $jalur[count($jalur) - 2];
             
             $ketemu = false;
             foreach ($data_shapes as $key => $value) 
             {
                # code...
                if($value->id == $end_jalur[count($end_jalur) - 1]->id )
                {
                  $ketemu = true;
                  //return $value->id;
                  break;
                }
             }

             if($ketemu)
             {
                for ($m=sizeof($data_shapes) -1; $m > $key ; $m--) { 
                  
                  unset($data_shapes[$m] );
                }
                foreach ($data_shapes as $kunci => $value) 
                {
                  $data_shapes_filtered[] = $value;
                }
                $data_shapes = $data_shapes_filtered;
             }
             
             $data_shapes_filtered = [];

              $temporary_jalur[$j] = $data_shapes;
              //return $temporary_angkot;
              $hasil[] = [ "angkot"=> $temporary_angkot[$j], "jalur"=>$temporary_jalur[$j] ] ;
          }  
            break;
          //goto out;
        }
        else {
          $temporary_angkot[] = $angkot[$j]; // lanjut disini.
          $temporary_jalur[] = $jalur[$j];
        }

        $hasil[] = [ "angkot"=> $temporary_angkot[$j], "jalur"=>$temporary_jalur[$j] ] ;

      }
      out:

      $hasil[] = [ "angkot"=>end($angkot), "jalur"=>end($jalur) ] ;
      $step = $hasil;
      
      $routingresult[] = ["step"=>$step];
      step:
      return [ "status"=>$status, "routingresult"=> $routingresult  ];
     // return [$angkot, $akhir, $intersec];   
    }

    public function import_placeName() // pak andhi's task
    {
      ini_set('max_execution_time', 300);
      $points = DB::select("select * from shapes");
      //$points2 = DB::select("select * from shapes_back_up");
      foreach ($points as $key => $value) {
        if(!empty($points[$key]->place_info ) ){
          continue;
        }
        //return json_decode( json_encode($data),true );//json_decode( json_encode($data), true );
        //return "UPDATE shapes SET place_info = ".$place_info." WHERE `id` =".$id ;
        $cari = DB::select("SELECT * FROM `shapes_back_up` WHERE `shape_pt_lat` = '".$points[$key]->shape_pt_lat."' AND `shape_pt_lon` = '".$points[$key]->shape_pt_lon."' AND shape_id = '".$points[$key]->shape_id."' AND place_info !='' ");
        //return $cari;
        if(!empty($cari) ){
          $place_info = $cari[0]->place_info ;
          $id = $points[$key]->id;

          echo "UPDATE shapes SET place_info = ".$place_info." WHERE `id` =".$id."<br>" ;
          $query = DB::select("UPDATE shapes SET place_info = '".$place_info."' WHERE id =".$id);

        }
        else
        {
          
          $id = $points[$key]->id;
          $parameter = $points[$key]->shape_pt_lat.",".$points[$key]->shape_pt_lon;
          //return $id;
          $data = $this->getLocInfo($parameter);
          //return $place_info = $data->status;
          if($data->status != "OK"){
            $place_info="";
          }
          else
          {
            $place_info = $data->results[0]->formatted_address;
          }//return $place_info;
          //return "UPDATE shapes SET place_info = ".$place_info." WHERE `id` =".$id."<br>" ;
          echo "UPDATE shapes SET place_info = ".$place_info." WHERE `id` =".$id."<br>" ;
          $query = DB::select("UPDATE shapes SET place_info = '".$place_info."' WHERE id =".$id);
        }
      
      }  
      
    }

    public function jajal()
    {
      /*$data = DB::select("select route_id,image from route");
      foreach ($data as $i => $value) {
        # code...
        if(empty($value->image)){
          continue;
        }
        $id = $value->route_id;
        $image = $value->image;
        $image = substr($image, 16);
        $query = DB::select("UPDATE route SET image ='".$image."' where route_id = ".$id);
        if($query)
        {echo "UPDATE route SET image ='".$image."' where route_id = ".$id." <br>";} 
      }
      $a = ['a','b','c','d','e','f'];
      $b = ['d','e','f'];
      $c = array_intersect($a, $b);
      $d = array_intersect($b,$a);
      $e = array_combine($c, $d);

      return $e;*/

      /*$a = ['a', 'b', 'c', 'd', 'e', 'f'];
      $b = ['d', 'e', 'm', 'f'];
      $intersect = array_intersect($a, $b);
      $key_intersect = [];
      foreach ($intersect as $key => $value) {
          $key_intersect[$key] = array_search($value, $b);
      }
      //var_dump($key_intersect);
      return $key_intersect;*/
      $data = DB::select("select trip_short_name, shape_id from trips where route_id = 35 ");
      //return $data;
      $shape_id = $data[0]->shape_id;
      $shape_id = explode(", ", $shape_id );
      
      $reverse = array_reverse($shape_id);
      //return $reverse;
      $implode = implode(", ", $reverse);
      return $implode;
    }

    public function export2($trayek=39,$filename = 'file.csv')
    {/* 
      if(isset($_GET['trayek']))
      {
        $trayek = $_GET['trayek'];
      }
      $data = $this->get_trayek_akbar($trayek);//, $filename = 'file.csv');

      header('Content-Type: application/csv');
      header('Content-Disposition: attachment; filename="'.$filename.'";');

      $array = json_decode($data, true);
      //return $array;
      
      $path = 'D:/file.csv' ;
      //$fp = fopen('file.csv', 'w');
      $fp = fopen('php://output', 'w');
      foreach ($array as $key => $value) {
        # code...
        fputcsv($fp, $value);
      }
      fclose($fp);
      //$hasil = fclose($fp);
      echo $fp;
      //$hasil = fread($fp, filesize('file.csv'));
       */
       return "unused"; 
    }

    public function export()
    {
      //return "unused";
      $data = $this->getLocInfo();
      return json_decode( json_encode($data),true );
    }


}
