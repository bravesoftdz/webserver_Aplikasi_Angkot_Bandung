@extends('layouts.app')

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>

<script type="text/javascript"> // ambil data dari database

  function webserverangkot(lat = "-6.914242477573626,107.60831594467163",lon = "-6.914242477573626,107.60831594467163" ) 
  {
    var result=""; // lat lon diatas sudah diganti, itu hanya penamaan saja.
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

  function cetak_jalur2(lat = "-6.942870986409176,107.65348434448242",lon = "-6.907425909992761,107.6220703125" ) 
  {
    var result="";
    $.ajax({
    url: "http://localhost/webserverangkot/public/api/cetak_jalur3_api",//efisiensi2",//cetak_jalur2" , //bisa diganti ganti
    method: "GET",
    data: "start=" + lat + "&finish=" + lon,
    async: false,
    success:function(data) {
    result = data; 
          }
       });
     return result;

  }

  function get_koordinat(kirim) 
  {
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

  function get_location(location) 
  {
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
    $(".se-pre-con").show();
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

  function jalan(lat = '-6.9334,107.6279', lon='-6.9328,107.6346')//(lat = '-6.934776792662195,107.64575958251953', lon = '-6.906999871255851,107.60250091552734')
  {  
      $(".se-pre-con").show();;
 
      
      if(lat == null & lon == null )
      {
          var a = get_location();//document.getElementById('location').value;
          var lat = a.lat;
          var lon = a.lon;
         
      }
       

     // var obj = webserverangkot(lat, lon);
      var jalur = cetak_jalur2(lat, lon); // masuk ke fungsi cetak_jalur

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
       //  icon: 'http://localhost/webserverangkot/public/png_2017/Aset-01.png' ,
         map: map  });
       }

        if(jalur.status !== "OK"  )
        {
            alert("sorry, we can't find public transfortasion for you arround here.");
            jalan();
        }   

        var titik_start = {lat: Number(jalur.routingresult[0].step[0].jalur[0].lat) , lng: Number(jalur.routingresult[0].step[0].jalur[0].lng) };

        var last_element = jalur.routingresult[0].step.length;

        var titik_finish = {lat: Number(jalur.routingresult[0].step[last_element - 1].jalur[1].lat) , lng: Number(jalur.routingresult[0].step[last_element - 1].jalur[1].lng) };

        make_marker(titik_start);
        make_marker(titik_finish);

        var make_marker_awal = function (location = {lat: -6.9025157, lng: 107.618782} ){
          var marker = new google.maps.Marker({
            position: location,
           // icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
            map: map
          });
        }

        var start ={lat: Number(jalur.routingresult[0].step[0].jalur[1].lat) , lng: Number(jalur.routingresult[0].step[0].jalur[1].lng) };
        var finish = {lat: Number(jalur.routingresult[0].step[last_element - 1].jalur[0].lat) , lng: Number(jalur.routingresult[0].step[last_element - 1].jalur[0].lng) };
        
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
       var obj = [] ;
       var rute = [];
       var trayek = [];
     

       //  for (var i = 0; i < jalur.routingresult.length; i++) {

        for (var j = 0; j < jalur.routingresult[0].step.length; j++) {
         if(undefined !== jalur.routingresult[0].step[j].angkot[0].route_color)
         {
          var warna = jalur.routingresult[0].step[j].angkot[0].route_color;
          var nama_trayek = jalur.routingresult[0].step[j].angkot[0].trip_short_name;
          trayek.push(nama_trayek);
          color.push(warna);
         }
         else
         {
          var warna = "#FF0000";
          color.push(warna);
         } 
          obj = [];
         
          for (var k = 0; k < jalur.routingresult[0].step[j].jalur.length; k++) {

            var lat =  Number(jalur.routingresult[0].step[j].jalur[k].lat);
            var lng =  Number(jalur.routingresult[0].step[j].jalur[k].lng);
            obj.push( {lat:lat, lng:lng} ) ;

          }
          rute.push(obj);
        }
        
         //  }
        console.log(trayek);
        console.log(color);
        

        for (var i = 0; i < rute.length; i++) {
          var polyline = new google.maps.Polyline({
                                       path:  rute[i] ,
                                       geodesic: true,
                                       strokeColor: color[i] ,//'#43378e' ,
                                       strokeOpacity: 2.0,
                                       strokeWeight: 2
                                        });  
                                       polyline.setMap(map);  
        }
    
     
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