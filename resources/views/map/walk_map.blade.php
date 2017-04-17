<!DOCTYPE html>
<html>
  <head>
    <style>
       #map {
        height: 400px;
        width: 100%;
       }
    </style>
  </head>
  <body>
    <h3>My Google Maps Demo</h3>
    <div id="map"></div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <!--  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry"></script> -->


    <script type="text/javascript"> // ambil data dari database
        function testAjax() {
        var result="";
        //var kirim = terima;
        //var host = window.location.origin;
        $.ajax({
        url: "https://maps.googleapis.com/maps/api/directions/json?origin=cicaheum,bandung&destination=gedung%20sate,%20bandung&waypoints=-6.915000681505,107.66569577157|-6.9158483127425,107.66547666415|-6.9163812988777,107.66482463256&key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w",  //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
        method: "GET",
        //data: "kirim=" + kirim,
        //async: false,  
        success:function(data) {
        result = data; 
              }
           });
         return result;
        }

      function webserverangkot() {
        var result="";
        var lat = -6.9025157;
        var lon = 107.618782;
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/trips/get_walking_route" ,
        method: "GET",
        data: "lat=" + lat + "&lon=" + lon,
        async: false,
        success:function(data) {
        result = data; 
              }
           });
         return result;

        }

        function get_koordinat(kirim) {
        var result="";
        var shape_id = kirim;
        
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_koordinat" ,
        method: "GET",
        data: "kirim=" + kirim ,
        async: false,
        success:function(data) {
        result = data; 
              }
           });
         return result;

        } 


    </script>

    <script>

      function initMap() {
        
      var obj = webserverangkot();
      var data = JSON.parse(obj);
   
      var start_lat = data.routes[0].legs[0].start_location.lat;
      var start_lon = data.routes[0].legs[0].start_location.lng;
      var latlngstart = {lat:start_lat, lng:start_lon};

      var endlat = data.routes[0].legs[0].end_location.lat;
      var endlon = data.routes[0].legs[0].end_location.lng;
      var latlngend = {lat:endlat, lng:endlon};
      var steps = data.routes[0].legs[0].steps;
      var overview_polyline = data.routes[0].overview_polyline.points;
      
      var koordinat = get_koordinat(5);

    //  console.log(latlngstart);
    //  console.log(latlngend);
    //  console.log(overview_polyline);
      console.log(koordinat);
      //console.log(points);


        var uluru = {lat: -6.8999246, lng: 107.6229656};
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: uluru
        });

       var encodeString = google.maps.geometry.encoding.decodePath(overview_polyline);
       
       
        console.log(encodeString);


      for (i=0;i<data.routes[0].legs[0].steps.length;i++) {
        var points = data.routes[0].legs[0].steps[i].polyline.points;
        var decodedpoints = google.maps.geometry.encoding.decodePath(points);

        var polyline = new google.maps.Polyline({
         path:  decodedpoints ,
         //path: google.maps.geometry.encoding.decodePath(overview_polyline) ,
         geodesic: true,
         strokeColor: '#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 2
          });
      
       var bounds = new google.maps.LatLngBounds(latlngstart);
       polyline.setMap(map);
        map.fitBounds(bounds);  
       } //akhir for

      

       var make_marker = function (location = {lat: -6.9025157, lng: 107.618782} ){
        var marker = new google.maps.Marker({
          position: location,
          map: map
        });
        }

        make_marker(latlngstart);
        make_marker(latlngend);

     /*   for (var i = 0; i < angkot.length; i++) {
          for (var j = 0; j < data.angkot[i].length; j++) {
                var shape_id =+ data.angkot[i].trayek[j].shape_id;

          } //for dalem    
         }  //for luar
        var data_koordinate = get_koordinat(shape_id);
        console.log(shape_id);
        console.log(data_koordinate);

        for (var i = 0; i < data_koordinate.length; i++) {
          var shape_lat = Number(data_koordinate[i].shape_pt_lat) ;
          var shape_lon = Number(data_koordinate[i].shape_pt_lon) ;
         var shape_lat_lon = {lat:shape_lat, lng:shape_lon};

          make_marker(shape_lat_lon);

        } */

       
        
        
      }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry&callback=initMap">
    </script>
  </body>
</html>