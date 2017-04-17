<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Complex icons</title>
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 5;
      }
      #map {
        align-content: center;
        align-self: center;
        height: 80%;
        width: 100%;
      }

       #floating-panel {
        position: absolute;
        top: 10px;
        left: 25%;
        z-index: 5;
        background-color: #fff;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #judul {
        position: absolute;
        top: 10%;
        left: 5%;
        z-index: 5;
        background-color: #ccg;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      #floating-panel2 {
        position: absolute;

        top: 50px;
        left: 35%;
        z-index: 5;
        background-color: #ccc;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }
    </style>
   <!-- <script src="https://maps.googleapis.com/maps/api/js?libraries=drawing,places"></script> -->
    
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

  </head>

  <body>
  
  <div id="floating-panel">
    <b>Start: </b>
    <input type="input" name="start" id="start">
    <b>End: </b>
    <input type="input" name="end" id="end">
    <b><input type="submit" name="submit" value="clear" onclick="clearMarkers()"></b> 
    </div>
    <div id="map"></div>

    <div align="center" id="map"></div>

<script type="text/javascript"> // ambil data dari database
        
  function testAjax(terima=1) {
  var result="";
  var kirim = terima;
  var host = window.location.origin;
  
  $.ajax({
    url: "http://localhost/webserverangkot/public/api/trips/get_trayek_akbar", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
    method: "GET",
    data: "kirim=" + kirim,
    async: false,  
    success:function(data) {
    result = data; 
          }
  });
 
   var b = result;//JSON.parse(result);
   var gabung= new Array() ;
   for (var i = 0; i < b.length; i++) {
   var c = b[i].shape_pt_lat;
   var d = b[i].shape_pt_lon;
   gabung =[c,d];
   
   return b;
   

   }          
       
  }
</script>


    <script>




function initMap() {
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 13,
    center: {lat: -6.8999246, lng: 107.6229656}
  });

  setMarkers(map);
  
}


function setMarkers(map) {

 

  var terima = pilih.value;
  var cor = testAjax(terima); // variabel cor menampung ajax 

var mapCoordinates = new Array();
var beaches = new Array();
var gabung= new Array();

for (var i = 0; i < cor.length; i++) 
{
      var lat = cor[i].shape_pt_lat;
      var lon = cor[i].shape_pt_lon;
      var jalur = cor[i].jalur;
      var shape_point_sequence = cor[i].shape_pt_sequence;
      var shape_id = cor[i].shape_id;
      //console.log(shape_id);

	function pilih_warna() // ga kepake, bisa dihapus
    {
	   if ( shape_point_sequence == 0)
	   {
	     alert(shape_point_sequence);
	     return color = '#0xfff1b7c3'; 
	   }

	    else
	    {   
        console.log(shape_point_sequence);
	        return color = '#FF0000';  
	    }
	} // akhir function pilih_warna()

      beaches[i] = ['shape id : '+ shape_id +'\n lat : '+ lat + '\n lon : '+ lon +'\n jalur : '+ jalur + '\n shape_point_sequence : '+ shape_point_sequence , parseFloat(lat), parseFloat(lon) , i];
        mapCoordinates[i] = new google.maps.LatLng( cor[i].shape_pt_lat, cor[i].shape_pt_lon) ;
      
}   


function getRandomColor() 
{
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6 ; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;

}



  // Adds markers to the map.

  // Marker sizes are expressed as a Size of X,Y where the origin of the image
  // (0,0) is located in the top left of the image.
var host = window.location.origin;
  // Origins, anchor positions and coordinates of the marker increase in the X
  // direction to the right and in the Y direction down.
  var image = {
    url: host + "/dbangkot3/assets/images/dot.png"  /*'images/beachflag.png'*/ ,
    // This marker is 20 pixels wide by 32 pixels high.
    size: new google.maps.Size(20, 32),
    // The origin for this image is (0, 0).
    origin: new google.maps.Point(0, 0),
    // The anchor for this image is the base of the flagpole at (0, 32).
    anchor: new google.maps.Point(0, 0)
  };

  
  // Shapes define the clickable region of the icon. The type defines an HTML
  // <area> element 'poly' which traces out a polygon as a series of X,Y points.
  // The final coordinate closes the poly by connecting to the first coordinate.
  var shape = {
    coords: [1, 1, 1, 20, 18, 20, 18, 1],
    type: 'poly'
  };

  for (var i = 0; i < beaches.length; i++) {

   var beach = beaches[i]; // coding marker
  // console.log(beach);
   var marker = new google.maps.Marker({
      position: {lat: Number( beach[1]), lng: Number( beach[2])  },
      map: map,
      //icon : image, // mengganti icon marker, jika tidak ada, pakai icon bawaan google
      shape: shape,
      title: beach[0],
      zIndex: beach[3]

    }) ; // akhir coding marker


  
   //console.log(position);
   var titik = {lat: beach[1], lng: beach[2] };
  // console.log(titik);
   var make_marker = function(){
    var tanda = new google.maps.Marker({
      position: titik,
      map: map,
    })
   }
   make_marker();
  // console.log(titik);

    var contentString = new Array(); // mulai coding infowindow
    contentString[i] = beaches[i];//beach[0]; //isi contentString
    var konten = beach[0];

    var infowindow = new google.maps.InfoWindow({
          content: konten
         });

  /* google.maps.event.addListener(marker,'click', function(marker,konten, infowindow ) { // multiple infowindows
      return function(){
          infowindow.setContent(konten);
          infowindow.open(map, marker);
          console.log(konten);
        };
        }(marker, konten,infowindow) ); // akhir coding infowindow*/

 

  

    var b = mapCoordinates[i];
    var c = mapCoordinates[i+1];

    var a = [ b , c ];

    var mapPath = new google.maps.Polyline({
       path: a ,
       geodesic: true,
       strokeColor: getRandomColor() , //'#FF0000',
       strokeOpacity: 1.0,
       strokeWeight: 2
        });
  mapPath.setMap(map);    //akhir coding line       
  //road(mapPath);  
  } // akhir for

/* var polylines = [];
function road(poly) {
    var path = poly.getPath();
    polylines.push(poly);
    placeIdArray = [];
    runSnapToRoad(path);
  }

 function runSnapToRoad(path) {
  var pathValues = [];
  for (var i = 0; i < path.getLength(); i++) {
    pathValues.push(path.getAt(i).toUrlValue());
  } 
} */

           google.maps.event.addListener(map, 'click', function(event) {

           placeMarker(event.latLng);
           AddCircle(event.latLng);
          


           var lat = event.latLng.lat();
           var lon = event.latLng.lng();
           var latlon = [lat,lon];
          // console.log(latlon);
           document.getElementById('start').value = latlon;

        }); // akhir google.maps.addListener

        function placeMarker(location) {
            var marker = new google.maps.Marker({
                position: location, 
                map: map,
                draggable:true
            });
            //console.log(location); // test buat tahu posisi marker
          }

function AddCircle(location){

        var cityCircle = new google.maps.Circle({
            strokeColor: '#FF0000',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#FF0000',
            fillOpacity: 0.35,
            map: map,
            center: location,
            radius: 500
          });

var bound = function(loc){
 var bounds = cityCircle.getBounds();

  var noteA = bounds.contains( {lat: beach[1], lng: beach[2]} );    
      

     //console.log(noteA);
     if(noteA == true)
     {
        console.log({lat: beach[1], lng: beach[2]});
        console.log(beach[0]);
       // var nearest = google.maps.geometry.spherical.computeDistanceBetween( placeMarker , {lat: beach[1], lng: beach[2]});
       // console.log(nearest);
     }

}

 for (var i = beaches.length - 1; i >= 0; i--) {
   beach = beaches[i];
   bound(beach); // perulangan mengeksekusi function bound untuk kemudian dievaluasi apakah true (didalam circle), atau false (diluar)
 }

} // akhir function AddCircle()



 google.maps.event.addListener(marker,'click', 

function(marker,konten, infowindow ) { // multiple infowindows
      return function(){
          infowindow.setContent(konten);
          infowindow.open(map, marker);
        };

        }(marker, konten,infowindow) ); 

} // akhir function

 function clearMarkers() 
        {
          initMap(null );
        }

    </script>

<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&callback=initMap">
          
        </script>

<div align="center" id="floating-panel2">
<select id="pilih" name="pilih">
    <?php foreach ($trip as $a): ?>
    <option value= <?php echo $a['route_id']; ?> > <?php echo $a['trip_short_name']; ?> </option>  
    <?php endforeach ?> 
</select>

<input type="submit" name="submit" onclick="initMap()">
</div>

<div id="judul"> Angkot Fujicon Apps</div>

  </body>
</html>