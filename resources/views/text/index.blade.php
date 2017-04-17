<!DOCTYPE html>
<html>
<head>
	<title>test API</title>
	<style>	
        html, body {
                background-color: #ffc; 
                color: #636b6E;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;

            }

            .full-height {
                height: 100vh;
            }

            .table {
                align-self: center;
            	align-content: center;
            	text-align: left;
            	position: relative;
            	
                left: 10%;
                
            }
      
	</style>
</head>
<body>
<?php
 ini_set('max_execution_time', 180);
$arrContextOptions = array( //kalau ga pake ini error
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
            ),
        );  
if(!empty($_GET['location']))
{
$maps_url = 'https://' .
        'maps.googleapis.com/' .
        'maps/api/geocode/json' .
        '?address=' . urlencode($_GET['location']);
    $maps_json = file_get_contents($maps_url);
     $maps_array = json_decode($maps_json, true);

    if($maps_array['status'] == "OK")
    {
    ini_set('max_execution_time', 180);
    $lat_result = $maps_array['results'][0]['geometry']['location']['lat'];
    $lon_result = $maps_array['results'][0]['geometry']['location']['lng'];
    $position = $maps_array['results'][0]['formatted_address'];

    $url = "http://localhost/webserverangkot/public/api/trips/get_jalur_terdekat_baru?lat=".$lat_result."&lon=".$lon_result;
    $json = file_get_contents($url,false, stream_context_create($arrContextOptions));
    $data = json_decode($json);

    }
    else
    {
    $lat_result = "lat_tidak_ditemukan";
    $lon_result = "lon_tidak_ditemukan";
    $position  = $_GET['location'];

    }
}

if(!empty($_GET['lat']) and !empty($_GET['lon']) ){
    ini_set('max_execution_time', 180);
$lat = $_GET['lat'];
$lon = $_GET['lon'];

$url = "http://localhost/webserverangkot/public/api/trips/get_jalur_terdekat_baru?lat=".$lat."&lon=".$lon;
$json = file_get_contents($url);//,false, stream_context_create($arrContextOptions));
$data = json_decode($json);
}

?> 
<h1 align="center">Test API</h1>
<div class="table">
<form>
<pre> masukan lokasi : <input type="input" name="location"> <input type="submit" name="" value="submit"> </pre>
</form>
<form action="http://localhost/webserverangkot/public/test" name="data">
<pre> masukan lat    : <input  type="input" name="lat" value=<?php if(!empty($lat_result)){ echo $lat_result; } ?> > </pre>
<pre> masukan lon    : <input  type="input" name="lon" value=<?php if(!empty($lon_result)){ echo $lon_result; } ?> > </pre>
<input type="submit" name="" value="submit">
</form>
</div>
<br>

<table  class="table" border="1" >
	<tr>
		<td>Saya sedang berada di </td>
		<td>:</td>
		<td> <?php 
        if(!empty($position)){ 
        echo $position;
        }
                        ?> </td>
	</tr>
	
	<tr>
		<td>dari sini saya bisa naik angkot </td>
		<td>:</td>
		<td >
		<?php 
      

        if(!empty($data) ){
            $array = get_object_vars($data);

            if($data->status == "OK")
            {   
                for ($i=0; $i < sizeof($data->angkot); $i++) 
                {   
                    $lat = $data->angkot[$i]->pickup_point->titik_terdekat->shape_pt_lat;
                    $lon = $data->angkot[$i]->pickup_point->titik_terdekat->shape_pt_lon;
                    $titik = "( lat: ".$lat." lon: ".$lon.")";
                    for ($n=0; $n < sizeof($data->angkot[$i]->trayek); $n++) { 
                        # code...
                        echo $data->angkot[$i]->trayek[$n]->trip_short_name."<br>";
                        //echo $titik."<br>";
                    }
                    
                }               
                                   
            } 
  
            else
            {
                echo "maaf, tidak ada angkot Bandung dalam radius 2Km";
            }
        }



        ?>
        
		</td>
	</tr>
	<tr>
		<td>titik terdekat dari masing masing angkot adalah</td>
		<td>:</td>
		<td>
      <?php 
    if(!empty($data) ){
            $array = get_object_vars($data);

            if($data->status == "OK")
            {   
                for ($i=0; $i < sizeof($data->angkot); $i++) 
                {   
                    $lat = $data->angkot[$i]->pickup_point->titik_terdekat->shape_pt_lat;
                    $lon = $data->angkot[$i]->pickup_point  ->titik_terdekat->shape_pt_lon;
                    $titik = "( lat: ".$lat." lon: ".$lon.")";
                    for ($n=0; $n < sizeof($data->angkot[$i]->trayek); $n++) { 
                        # code...
                        echo $data->angkot[$i]->trayek[$n]->trip_short_name." --> ";
                        echo $titik."<br>";
                    }
                    
                }               
                                   
            } 
  
            else
            {
                echo " - " ;
            }
        } 
         ?>      
        </td>
	</tr>
</table>



</body>
</html>