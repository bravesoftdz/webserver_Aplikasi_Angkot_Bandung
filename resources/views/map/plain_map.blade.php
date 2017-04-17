<!DOCTYPE html>
<html>
  <head>
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
  </head>
  <body>
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

      function webserverangkot(lat = "-6.914242477573626,107.60831594467163",lon = "-6.914242477573626,107.60831594467163" ) {
        var result=""; // lat lon diatas sudah diganti, itu hanya penamaan saja.

        //var lat = -6.9016497;//-6.9025157; -6.8984257601323,107.59760856628418
        //var lon = 107.620514;//107.618782; -6.9039576,107.5801608 -> bandara // -6.914242477573626,107.60831594467163
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/djikstra_cepat" , //bisa diganti ganti
        method: "GET",
        data: "start=" + lat + "&finish=" + lon,
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

        function get_location(location) {
        var result="";
      //  var location = document.getElementById('location').value;
      //  var shape_id = location;
        
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_position" ,
        method: "GET",
        data: "location=" + location ,
        async: false,
        success:function(data) {
        result = data; 
              }
           });
         //console.log(result);
         
         return result;
         

        }


    </script>

    <script>

     var markers = [];

     function setMapOnAll(map) 
      {
        for (var i = 0; i < markers.length; i++) 
        {
          markers[i].setMap(map);

        }
      }

      function clearMarkers() 
      {
        setMapOnAll(null);
      }

      function initMap() 
      { 

        var location = document.getElementById('location').value;

        var lokasi = get_location(location);
        var lat = lokasi.lat;
        var lng = lokasi.lng;
        console.log(lat);
        console.log(lng);
        
        jalan(lat+","+lng , lat+","+lng);
        $('#loading').hide(); 
      }

      function submit()
      { 
        var a = document.getElementById('start').value;
        var b = document.getElementById('end').value;
       /* b = a.split(",");
        lat = b[0];
        lon = b[1];
        console.log(lat); */

        $('#loading').show();
        jalan(a, b);
        $('#loading').hide();
      }

      function initMap_klik() 
      { 

        var location = document.getElementById('location').value;

        var lokasi = get_location(location);
        var lat = lokasi.lat;
        var lng = lokasi.lng;
        console.log(lat);
        console.log(lng);
        
        jalan(lat, lng); 
        $('#loading').hide();
      }

      function clear_value()
    {
      var a = document.getElementById('start');
      a.value = '';

    }

    function hapus(input)
  {
   var a = document.getElementById(input);
   a.value = '';
   clearMarkers();
   console.log(a);
   
  }

    function jalan(lat = '-6.914242477573626,107.60831594467163', lon = '-6.914242477573626,107.60831594467163')
    {  

      $('#loading').show();
      if(lat == null & lon == null )
      {
          var a = get_location();//document.getElementById('location').value;
          var lat = a.lat;
          var lon = a.lon;
          console.log(lat);
          console.log(a);
      }
       

      var obj = webserverangkot(lat, lon);

      var data = obj;//JSON.parse(obj);
   
      var status = data.start.status;
      var radius = data.start.radius;
      var angkot = data.start.angkot;
      var angkot2 = data.finish.angkot;
      //var trayek = angkot.trayek;
      var start_lat = Number(data.start.start_position.lat);
      var start_lon = Number(data.start.start_position.lng);
      var start = {lat: start_lat, lng: start_lon };

      var start_lat = Number(data.finish.start_position.lat);
      var start_lon = Number(data.finish.start_position.lng);
      var finish = {lat: start_lat, lng: start_lon }; ; 

      
      console.log(data);
      console.log(status); 
      
      console.log(start);

      

        var uluru = {lat: -6.8999246, lng: 107.6229656};
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: uluru
        });

       for (var i = 0; i < angkot.length; i++) { // for jalur pejalan kaki start
        
        if(data.start.angkot[i].pickup_point.walk_route.status == "OK")
        {
        var garis = data.start.angkot[i].pickup_point.walk_route.routes[0].overview_polyline.points;
        var encodeString = google.maps.geometry.encoding.decodePath(garis);
        

        var polyline = new google.maps.Polyline({  //deklarasi polyline. (belum eksekusi)
         path:  encodeString ,
         geodesic: true,
         strokeColor: '#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 2
          });

        polyline.setMap(map);
        }
        else
        {
          var lat = Number( data.start.angkot[i].pickup_point.titik_terdekat.shape_pt_lat);
          var lon = Number (data.start.angkot[i].pickup_point.titik_terdekat.shape_pt_lon );
          var titik_terdekat = {lat:lat, lng:lon} ;
          var encodeString = [start, titik_terdekat ];
         var polyline = new google.maps.Polyline({  //deklarasi polyline. (belum eksekusi)
         path:  encodeString , // harus dibuat array titik awal , titik terdekat.
         geodesic: true,
         strokeColor: '#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 2
          });

        polyline.setMap(map); 
        }

        }

        for (var i = 0; i < angkot2.length; i++) { // for jalur pejalan kaki start
        if(data.finish.angkot[i].pickup_point.walk_route.status == "OK" )
        {
        var garis = data.finish.angkot[i].pickup_point.walk_route.routes[0].overview_polyline.points;
        var encodeString = google.maps.geometry.encoding.decodePath(garis);
        

        var polyline = new google.maps.Polyline({  //deklarasi polyline. (belum eksekusi)
         path:  encodeString ,
         geodesic: true,
         strokeColor: '#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 2
          });

        polyline.setMap(map);
        }
        else
        {
              var lat = Number( data.finish.angkot[i].pickup_point.titik_terdekat.shape_pt_lat);
          var lon = Number( data.finish.angkot[i].pickup_point.titik_terdekat.shape_pt_lon);
          var titik_terdekat = {lat:lat, lng:lon} ;
          console.log(titik_terdekat, start);
          var encodeString = [finish, titik_terdekat ];

          var polyline = new google.maps.Polyline({  //deklarasi polyline. (belum eksekusi)
         path:  encodeString , // harus dibuat array titik awal , titik terdekat.
         geodesic: true,
         strokeColor: '#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 2
          });

        polyline.setMap(map);

        }

        } 
        



       var make_marker = function (location = {lat: -6.9025157, lng: 107.618782} ){
        var marker = new google.maps.Marker({
          position: location,
         // icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
          map: map
        });
        
        }

        var make_marker_awal = function (location = {lat: -6.9025157, lng: 107.618782} ){
        var marker = new google.maps.Marker({
          position: location,
          icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
          map: map
        });
        
        }

        var make_marker_klik = function (location = {lat: -6.9025157, lng: 107.618782} ){
        var marker = new google.maps.Marker({
          position: location,
          map: map
        });
        markers.push(marker);
        }

        make_marker_awal(start); //make marker posisi awal
        make_marker_awal(finish);

        for (var i = 0; i < angkot.length; i++) 
        {
        var latitude = Number(data.start.angkot[i].pickup_point.titik_terdekat.shape_pt_lat) ;
        var longitude = Number(data.start.angkot[i].pickup_point.titik_terdekat.shape_pt_lon);
        objek = {lat:latitude, lng:longitude};
        
        make_marker(objek); //make marker titik terdekat untuk start
        }

         for (var i = 0; i < angkot2.length; i++) 
        {
        var latitude = Number(data.finish.angkot[i].pickup_point.titik_terdekat.shape_pt_lat) ;
        var longitude = Number(data.finish.angkot[i].pickup_point.titik_terdekat.shape_pt_lon);
        objek = {lat:latitude, lng:longitude};
        
        make_marker(objek); //make marker titik terdekat untuk finish
        }



        var arraykoordinat = [];
        var gabungarraykoordinat = [];
        var array = [];

        function getRandomColor() 
        {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++ ) 
          {        color += letters[Math.floor(Math.random() * 16)]; }
        return color;
        } //akhir function getRandomColor();


        for (var i = 0; i < angkot.length; i++) // penggambaran trayek angkot start
        {
          
          for (var j = 0; j < data.start.angkot[i].trayek.length; j++)
          {
            var route_id = data.start.angkot[i].trayek[j].route_id;
            if(route_id==41)
              {continue;}

            var koordinat = get_koordinat(route_id);
                console.log(route_id);
                console.log(koordinat);
                
              gabungarraykoordinat[j] = koordinat ;
             // console.log(gabungarraykoordinat[j]);
              var titik_koordinat = [];
              for (var w = 0; w < gabungarraykoordinat[j].length; w++) 
              {

                  var koordinat_latitude = Number(gabungarraykoordinat[j][w].shape_pt_lat) ;
                  var koordinat_longitude = Number(gabungarraykoordinat[j][w].shape_pt_lon);
                  titik_koordinat[w] = {lat:koordinat_latitude, lng:koordinat_longitude}; 
                  
              }
              array[j] = titik_koordinat;
              console.log(array[j]);
              var warna = [];
             // warna.push(getRandomColor());
             warna[j] = getRandomColor();//data.angkot[i].color[j].route_color;//getRandomColor();
             console.log(warna[j]);         
                      //console.log(array[i]);
                      for (var n = 0; n < array[j].length; n++) 
                      {     
                           
                            
                            var b = array[j][n];
                            var c = array[j][n+1];
                            var a = [ b , c ]; // bener, bisa tampil, tapi jalurnya jadi acak acakan
                            if(c == null)
                            {continue;}
                           // console.log(a);
                            var polyline = new google.maps.Polyline({
                                       path:  a ,
                                       geodesic: true,
                                       strokeColor: warna[j],
                                       strokeOpacity: 1.0,
                                       strokeWeight: 2
                                        });

                            polyline.setMap(map);  
                            
                      } //akhir for

          }     //akhir for    
       
        } // akhir for start

        for (var i = 0; i < angkot2.length; i++) // penggambaran trayek angkot finish
        {
          
          for (var j = 0; j < data.finish.angkot[i].trayek.length; j++)
          {
            var route_id = data.finish.angkot[i].trayek[j].route_id;
            if(route_id==41)
              {continue;}

            var koordinat = get_koordinat(route_id);
                console.log(route_id);
                console.log(koordinat);
                
              gabungarraykoordinat[j] = koordinat ;
             // console.log(gabungarraykoordinat[j]);
              var titik_koordinat = [];
              for (var w = 0; w < gabungarraykoordinat[j].length; w++) 
              {

                  var koordinat_latitude = Number(gabungarraykoordinat[j][w].shape_pt_lat) ;
                  var koordinat_longitude = Number(gabungarraykoordinat[j][w].shape_pt_lon);
                  titik_koordinat[w] = {lat:koordinat_latitude, lng:koordinat_longitude}; 
                  
              }
              array[j] = titik_koordinat;
              console.log(array[j]);
              var warna = [];
             // warna.push(getRandomColor());
             warna[j] = getRandomColor();//data.angkot[i].color[j].route_color;//getRandomColor();
             console.log(warna[j]);         
                      //console.log(array[i]);
                      for (var n = 0; n < array[j].length; n++) 
                      {     
                           
                            
                            var b = array[j][n];
                            var c = array[j][n+1];
                            var a = [ b , c ]; // bener, bisa tampil, tapi jalurnya jadi acak acakan
                            if(c == null)
                            {continue;}
                           // console.log(a);
                            var polyline = new google.maps.Polyline({
                                       path:  a ,
                                       geodesic: true,
                                       strokeColor: warna[j],
                                       strokeOpacity: 1.0,
                                       strokeWeight: 2
                                        });

                            polyline.setMap(map);  
                            
                      } //akhir for

          }     //akhir for    
       
        } // akhir for finish

        var koordinat = [];
        google.maps.event.addListener(map, 'click', function(event) {

          if(koordinat.length < 2)
          {
           make_marker_klik(event.latLng);

           var lat = event.latLng.lat();
           var lon = event.latLng.lng();
           var a = {lat: lat, lng: lon};

           var latlon = [lat,lon];
           
           koordinat.push(latlon);
          // obj = webserverangkot(lat, lon);
           var latlon = [lat,lon];
           console.log(latlon);
           console.log(koordinat);
           console.log(event.latLng);
           
           document.getElementById('start').value = koordinat[0];//latlon;
          // if(koordinat[1] = 'undefined')
          //  {document.getElementById('end').value = ''}
           document.getElementById('end').value = koordinat[1];
           }
           
        });

     



        google.maps.event.addListener(map, 'rightclick', function(){
          clearMarkers();
          hapus('start');
          hapus('end');
          koordinat = [];
          
        });
          
    }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry&callback=initMap">
    </script>


<div id="floating-panel">
    
    <b>Start: </b>
    <input type="input" name="start" id="start">
    
    <b>End: </b>
    <input type="input" name="end" id="end">
    <span>
    <b><input type="submit" name="submit" value="submit" onclick="submit()"></b>
    <span id='loading' style='display:none'>membuat rute ..</span>
    </span>
    <b><input type="submit" name="clear" value="clear" onclick="hapus('start')"></b> 
    </div>
    <div id="map"></div>

    <div align="center" id="map"></div>

<div align="center" id="floating-panel2">

<input type="input" name="location" id="location" value="buah batu">
<input type="submit" name="submit" value="get lokasi" onclick="initMap()">

</div>

<div id="judul"> Angkot Fujicon Apps</div>

  </body>
</html>