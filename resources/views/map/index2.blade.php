@extends('layouts.app2')

@section('content')




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <!--  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry"></script> -->
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>

    <script type="text/javascript"> // ambil data dari database

      

      function webserverangkot(lat = "-6.914242477573626,107.60831594467163",lon = "-6.914242477573626,107.60831594467163" ) {
        var result=""; // lat lon diatas sudah diganti, itu hanya penamaan saja.

        //var lat = -6.9016497;//-6.9025157; -6.8984257601323,107.59760856628418
        //var lon = 107.620514;//107.618782; -6.9039576,107.5801608 -> bandara // -6.914242477573626,107.60831594467163
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/djikstra" , //bisa diganti ganti
        method: "GET",
        data: "start=" + lat + "&finish=" + lon,
        async: false,
        success:function(data) {
        result = data; 
              }
           });
         return result;

        }

        function get_fastest_route3(lat = "-6.914242477573626,107.60831594467163",lon = "-6.914242477573626,107.60831594467163" ) {
        var result=""; // lat lon diatas sudah diganti, itu hanya penamaan saja.

        //var lat = -6.9016497;//-6.9025157; -6.8984257601323,107.59760856628418
        //var lon = 107.620514;//107.618782; -6.9039576,107.5801608 -> bandara // -6.914242477573626,107.60831594467163
        $.ajax({
        url: "http://localhost/webserverangkot/public/api/cetak_jalur" , //bisa diganti ganti
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

<script type="text/javascript">   
   $(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");;
  });
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
        $(".se-pre-con").show();;
        if(typeof document.getElementById('start_location').value !== 'undefined' || typeof document.getElementById('finish_location').value !== 'undefined')
          {
            var start_location = document.getElementById('start_location').value;
             var finish_location = document.getElementById('finish_location').value;

            var lokasi_awal = get_location(start_location);
            var lokasi_tujuan = get_location(finish_location);

            var lat1 = lokasi_awal.searchresult[0].location.lat;
            var lng1 = lokasi_awal.searchresult[0].location.lng;

            var lat2 = lokasi_tujuan.searchresult[0].location.lat;
            var lng2 = lokasi_tujuan.searchresult[0].location.lng;
            $("#select_start").hide();
            $("#select_finish").hide();


            jalan(lat1+","+lng1 , lat2+","+lng2);
            
          }

        else
        {
          jalan();
          //var select_finish = document.getElementById('select_start');
          //var select_start = document.getElementById('select_start');
          $("select_start").hide();
          $("select_finish").hide();
        }
          
      }



      function submit()
      { 
        var a = document.getElementById('start').value;
        var b = document.getElementById('end').value;
        var c = document.getElementById('start_location').value;
        var d = document.getElementById('finish_location').value;
        if(c || d)
        {
          initMap();
          hapus('start_location');
          hapus('finish_location');
        }
        else if(a == "undefined" || b == "undefined"  )
        {
          alert("you need to fullfill start and finish first");
        }
        else if(!a  || !b )
        {
          alert("you need to fullfill start and finish first");
        }
       /* else if(a !== Number || b !== Number)
        {
          alert('bukan nomor');
        } */
        else
        {
        jalan(a, b);
        }
        
      }

     function clear_klik() 
      {
          clearMarkers();
          hapus('start');
          hapus('end');
          koordinat = [];
      }

    function initMap_klik() 
      { 
         $(".se-pre-con").show();;
        if(typeof document.getElementById('start_location').value !== 'undefined' || typeof document.getElementById('finish_location').value !== 'undefined')
          {
            var start_location = document.getElementById('start_location').value;
             var finish_location = document.getElementById('finish_location').value;

            var lokasi_awal = get_location(start_location);
            var lokasi_tujuan = get_location(finish_location);

            var lat1 = lokasi_awal.searchresult[0].location.lat;
            var lng1 = lokasi_awal.searchresult[0].location.lng;

            var lat2 = lokasi_tujuan.searchresult[0].location.lat;
            var lng2 = lokasi_tujuan.searchresult[0].location.lng;
            
            jalan(lat1+","+lng1 , lat2+","+lng2);
            $("#select_start").show();
            $("#select_finish").show();

            // PEER DISINI
          }

        else
        {
          jalan();
        } 
        
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

   
  }

function jalan(lat = '-6.914242477573626,107.60831594467163', lon = '-6.914242477573626,107.60831594467163')
{  
       $(".se-pre-con").show();;
 
      
      if(lat == null & lon == null )
      {
          var a = get_location();//document.getElementById('location').value;
          var lat = a.lat;
          var lon = a.lon;
         
      }
       

     // var obj = webserverangkot(lat, lon);
      var jalur = get_fastest_route3(lat, lon); // masuk ke fungsi cetak_jalur

      var uluru = {lat: -6.914838922559386, lng: 107.60765075683594};
      var map = new google.maps.Map(document.getElementById('map'), {
      zoom: 13,
      center: uluru
      });

       var make_marker = function (location = {lat: -6.9025157, lng: 107.618782} )
       {
       var marker = new google.maps.Marker({
       position: location,
       // icon:'http://www.webserverangkot.fujicon-japan.com/webserverangkot/public/images/abdul_muis_cicaheum_via_aceh.png',
       icon: 'http://localhost/webserverangkot/public/png_2017/Aset-01.png' ,
       map: map  });
        }

        if(jalur.data.start.status !== "OK" || jalur.data.finish.status !== "OK" )
        {
            alert("sorry, we can't find public transfortasion for you arround here.");
            jalan();
        }   

      var titik_start = { lat: Number( jalur.data.start.angkot[0].pickup_point.titik_terdekat.shape_pt_lat) , lng: Number( jalur.data.start.angkot[0].pickup_point.titik_terdekat.shape_pt_lon) };

      var titik_finish = { lat: Number( jalur.data.finish.angkot[0].pickup_point.titik_terdekat.shape_pt_lat) , lng: Number( jalur.data.finish.angkot[0].pickup_point.titik_terdekat.shape_pt_lon) };

      make_marker(titik_start);
      make_marker(titik_finish);

        var make_marker_awal = function (location = {lat: -6.9025157, lng: 107.618782} ){
        var marker = new google.maps.Marker({
          position: location,
          icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
          map: map
        });
        }

        var start = { lat: Number( jalur.data.start.start_position.lat) , lng: Number( jalur.data.start.start_position.lng) };
        var finish = { lat: Number( jalur.data.finish.start_position.lat) , lng: Number( jalur.data.finish.start_position.lng) };
        make_marker_awal(start);
        make_marker_awal(finish);
       

        var make_marker_klik = function (location = {lat: -6.9025157, lng: 107.618782} ){ // deklarasi klik
        var marker = new google.maps.Marker({
          position: location,
        //  icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
          map: map
        });
        markers.push(marker);
        }

        var arraykoordinat = [];
        var gabungarraykoordinat = [];
        var array = [];

       var a = [];
       var color = [];
       var obj = "" ;
       for (var i = 0; i < jalur.step[0].length; i++) {  //perulangan titik titik jalur terdekat.

       var htung = [];
       var b = [];
       var warna = [];
            for (var m = 0; m < jalur.step[0][i].length; m++) {

             var lat = Number(jalur.step[0][i][m].shape_pt_lat);
             var lon = Number(jalur.step[0][i][m].shape_pt_lon);
             if(typeof jalur.step[2][i][0][0].route_color !== 'undefined' && jalur.step[2][i][0] !== '')
             {
             warna = jalur.step[2][i][0][0].route_color; //masih ambil warna pertama aja
           
             }
             else
             {
                warna = "#ff0000";
             }
             
             obj = {lat:lat, lng:lon} ;
             htung.push(obj);
             b.push(warna);

            }
             
              a[i] = htung;
              color[i] = b;
       }

  
      for (var i = 0; i < a.length; i++) {  //perulangan titik titik jalur terdekat.

            
             var polyline = new google.maps.Polyline({
                                       path:  a[i] ,
                                       geodesic: true,
                                       strokeColor: color[i][0] ,//'#43378e' ,
                                       strokeOpacity: 2.0,
                                       strokeWeight: 2
                                        });  
                                         polyline.setMap(map);
      

          } 

  

      // garis jalur djikstra. kalau mau beda warna, coding line nya juga harus masuk for ini PR aja. coding set warna nya = polyline.setOptions({strokeColor:'red'});

      var line_pejalan_kaki = new google.maps.Polyline({  // ini PR
                                       //path:  a ,
                                       geodesic: true,
                                       strokeColor: '#b22007' ,
                                       strokeOpacity: 1.0,
                                       strokeWeight: 2
                                        });
       var line_pejalan_kaki2 = new google.maps.Polyline({  // ini PR
                                       //path:  a ,
                                       geodesic: true,
                                       strokeColor: '#b22007' ,
                                       strokeOpacity: 1.0,
                                       strokeWeight: 2
                                        });

      line_pejalan_kaki.setPath([start, titik_start ]);
      line_pejalan_kaki.setMap(map);
     
      line_pejalan_kaki2.setPath([finish, titik_finish ]);
      line_pejalan_kaki2.setMap(map);

        var koordinat = [];  // array penampung marker. max 2.
        google.maps.event.addListener(map, 'click', function(event) {  // fungsi klik, tambah marker

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
        
           
           document.getElementById('start').value = koordinat[0];//latlon;
          // if(koordinat[1] = 'undefined')
          //  {document.getElementById('end').value = ''}
           document.getElementById('end').value = koordinat[1];
           }
           
        });


        google.maps.event.addListener(map, 'rightclick', function(){  //fungsi klik kanan, hapus marker.
          clearMarkers();
          hapus('start');
          hapus('end');
          koordinat = [];
          
        });

        $(".se-pre-con").fadeOut("slow");;
}
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry&callback=initMap">
</script>

@endsection