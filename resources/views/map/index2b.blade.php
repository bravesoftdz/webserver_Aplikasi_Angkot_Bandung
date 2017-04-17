@extends('layouts.app')

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
<script src="js/map.js"></script>
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

  function cetak_jalur2(lat = "-6.942870986409176,107.65348434448242",lon = "-6.907425909992761,107.6220703125", walk_route="no" ) 
  {
    var result="";
    $.ajax({
      url: "http://localhost/webserverangkot/public/api/cetak_jalur3_api",//efisiensi2",//cetak_jalur2" , //bisa diganti ganti
      method: "GET",
      data: "start=" + lat + "&finish=" + lon + "&walk_route=" + walk_route ,
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
    map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -6.914838922559386, lng: 107.60765075683594},
          zoom: 13
        });
      
    hide();

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
       document.getElementById('end').value = koordinat[1];
       }
       
    });

    $("#walk_route_check").on("click", function(){
        
        if($("#walk_route_check").is(":checked") ){
          
          walk_route = "yes";
          console.log(walk_route);
        }
        else
        {
          walk_route = "no"; 
          console.log(walk_route);
        } 
        //console.log("checked");
     }); 

    $("#menu-toggle").on('click',function(){
      if($("#navbar").is(":visible") ){
        $("#navbar").fadeOut();
        //$("#navbar").attr("visibility", "hidden");
        console.log("hide");
      }
      else
      {
        $("#navbar").fadeIn();
        //$("#navbar").attr("visibility", "visible");
        console.log("show");  
      }
    });

    google.maps.event.addListener(map, 'rightclick', function(){  //fungsi klik kanan, hapus marker.
      clearMarkers();
      hapus('start');
      hapus('end');
      koordinat = [];
      
    });

    
    this.klik_kanan = function(){
      clearMarkers();
      hapus('start');
      hapus('end');
      koordinat = [];
      
    }

    var make_marker_klik = function (location = {lat: -6.9025157, lng: 107.618782} ){ // deklarasi klik
        var marker = new google.maps.Marker({
          position: location,
        //  icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
          map: map
        });
        markers.push(marker);
    }  

    hide();
   // clear_klik();
    $(".se-pre-con").fadeOut("slow");
     
  }

  function hide()
  {
    $("#select_start").hide();
    $("#select_finish").hide();
    $("#select_routingresult").hide();
     
  }

  function show()
  {
    $("#select_start").show();
    $("#select_finish").show();
    $("#select_routingresult").show();
  }



  function submit()
  { 
    var a = document.getElementById('start').value;
    var b = document.getElementById('end').value;
    var c = document.getElementById('start_location').value;
    var d = document.getElementById('finish_location').value;
    if(c || d)
    {
      initMap_klik();
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
   
    else
    {
      if($("#walk_route_check").is(":checked") )
      {
        walk_route = "yes";
        console.log(walk_route);
      }
      else
      {
        walk_route = "no"; 
        console.log(walk_route);
      }
      jalan(a, b, walk_route);
    }
    
  }

  function clear_klik() 
  {
      /*clearMarkers();
      hapus('start');v
      hapus('end');
      koordinat = [];*/
      var kj = new initMap().klik_kanan();


  }

  function initMap_klik() 
  { 
     $(".se-pre-con").show();
     hide();
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

         
        $("#select_start_select").text("--Pilih--");
        for (var key in lokasi_awal.searchresult ) //key jadi index nya
        {
          var array = [];
          var result = lokasi_awal.searchresult;
          $("#select_start_select").append('<option value="' + key + '">' + result[key].placename + '</option>' );
        }

        $("#select_finish_select").text("--Pilih--");
        for (var key in lokasi_tujuan.searchresult ) //key jadi index nya
        {
          var array = [];
          var result = lokasi_tujuan.searchresult;
          $("#select_finish_select").append('<option value="' + key + '">' + result[key].placename + '</option>' );
        }
        
        $("#select_start_select").on('change', function(){
            var index =  $("#select_start_select").val() ;
            var index2 = $("#select_finish_select").val() ;
            pilih_select_start(index,index2, lokasi_awal, lokasi_tujuan);
          //console.log('start changed');
        } );

        $("#select_finish_select").on('change', function(){
            var index =  $("#select_start_select").val() ;
            var index2 = $("#select_finish_select").val() ;
            pilih_select_start(index,index2, lokasi_awal, lokasi_tujuan);
          //console.log('start changed');
        } );

        $("#select_start").show();
        $("#select_finish").show();

        // PEER DISINI
      }

      else
      {
        jalan();
      } 
    
  }



  function pilih_select_start(index,index2, lokasi_awal, lokasi_tujuan){
    //console.log("changed");
    var start_location = document.getElementById('start_location').value;
    var finish_location = document.getElementById('finish_location').value;
    console.log(index, index2 );
    //var lokasi_awal = get_location(start_location);
    //var lokasi_tujuan = get_location(finish_location);

    var lat1 = lokasi_awal.searchresult[index].location.lat;
    var lng1 = lokasi_awal.searchresult[index].location.lng;  

    var lat2 = lokasi_tujuan.searchresult[index2].location.lat;
    var lng2 = lokasi_tujuan.searchresult[index2].location.lng;
    
    jalan(lat1+","+lng1 , lat2+","+lng2);


    $("#select_start").show();
    $("#select_finish").show();

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

       

  /*$("#select_finish_select").on('change', function(){
    pilih_select_finish();
  } );*/
      

  function jalan(lat = '-6.9334,107.6279', lon='-6.9328,107.6346', walk_route="no")//(lat = '-6.934776792662195,107.64575958251953', lon = '-6.906999871255851,107.60250091552734')
  {  
      $(".se-pre-con").show();
      

      hide();

      if(lat == null & lon == null )
      {
          var a = get_location();//document.getElementById('location').value;
          var lat = a.lat;
          var lon = a.lon;
         
      }
       

     // var obj = webserverangkot(lat, lon);
     $("#walk_route_check").on("click", function(){
        
        if($("#walk_route_check").is(":checked") ){
          
          walk_route = "yes";
          console.log(walk_route);
        }
        else
        {
          walk_route = "no"; 
          console.log(walk_route);
        } 
     });     

     

     var jalur = cetak_jalur2(lat, lon, walk_route); // masuk ke fungsi cetak_jalur
    
      var uluru = {lat: -6.914838922559386, lng: 107.60765075683594};
      var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13,
        center: uluru
      });

      $("#select_routingresult_select").text("--Pilih--");
      var routingresult = [];
      for (var key in jalur.routingresult ) //key jadi index nya
      {
        
        routingresult.push( jalur.routingresult[key] );
        var result = jalur.routingresult;
        $("#select_routingresult_select").append('<option value="' + key + '">' + result[key].total_cost + '</option>' );

      }
      $("#select_routingresult").show();  
      console.log(jalur);

      $("#select_routingresult_select").on('change',function(){
          var index =  $("#select_routingresult_select").val() ;
          //console.log(index);
          //var pram = jalur;
          console.log(routingresult);
          pilih_kombinasi(index, routingresult, walk_route );
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
            hapus('start');
            hapus('end');
            //initMap();
            location.reload();
            hide();

            return ;

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
        
        if(walk_route=="no")
        {
          for (var i = 0; i < rute.length; i++) {
                  var polyline = new google.maps.Polyline({
                       path:  rute[i] ,
                       //geodesic: true,
                       strokeColor: color[i] ,//'#43378e' ,
                       strokeOpacity: 2.0,
                       strokeWeight: 2
                  });  
                  polyline.setMap(map);  
                }
        }
        else
        {
          //tulis pernyataan disini
          //console.log(rute.length);
          for (var i = 0; i < rute.length; i++) {
            if(i == 0)
            {
              var garis = jalur.routingresult[0].step[i].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              //console.log(garis);
              var polyline = new google.maps.Polyline({
                                 path:  encodeString ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }
            else if(i == (rute.length-1) )
            {
              var garis = jalur.routingresult[0].step[ (rute.length-1) ].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              //console.log(garis);
              var polyline = new google.maps.Polyline({
                                 path:  encodeString ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }
            else      
            {
              var polyline = new google.maps.Polyline({
                                 path:  rute[i] ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }    
          }    

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
        
           
           document.getElementById('start').value = koordinat[0];
           document.getElementById('end').value = koordinat[1];
           }
           
        });


        var klik_kanan = google.maps.event.addListener(map, 'rightclick', function(){  //fungsi klik kanan, hapus marker.
          //clear_klik();
          clearMarkers();
          hapus('start');
          hapus('end');
          koordinat = [];
        });

        
        
        $(".se-pre-con").fadeOut("slow");;
  }

  function pilih_kombinasi(index_step, routingresult, walk_route)
  { 
    //$(".se-pre-con").show();; 
    hide();
    

    console.log(routingresult);
    console.log(index_step);

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
         // icon: 'http://localhost/webserverangkot/public/png_2017/Aset-01.png' ,
         map: map  });
       }

    /*if(jalur.status !== "OK"  )
    {
      alert("sorry, we can't find public transfortasion for you arround here.");
      console.log(jalur);
      initMap();
      return;
    } */    

    /*$("#select_routingresult_select").text("--Pilih--");
    for (var key in jalur.routingresult ) //key jadi index nya
    {
      
      var array = [];
      //var option = '<option value="">--Pilih--</option>';
      var result = jalur.routingresult;
      $("#select_routingresult_select").append('<option value="' + key + '">' + result[key].total_cost + '</option>' );

    }**/
    $("#select_routingresult").show();  

    var titik_start = {lat: Number(routingresult[index_step].step[0].jalur[0].lat) , lng: Number(routingresult[index_step].step[0].jalur[0].lng) };

    var last_element = routingresult[index_step].step.length;

    var titik_finish = {lat: Number(routingresult[index_step].step[last_element - 1].jalur[1].lat) , lng: Number(routingresult[index_step].step[last_element - 1].jalur[1].lng) };

    make_marker(titik_start);
    make_marker(titik_finish);

    var make_marker_awal = function (location = {lat: -6.9025157, lng: 107.618782} ){
      var marker = new google.maps.Marker({
        position: location,
       // icon:'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png',
        map: map
      });
    }

    var start ={lat: Number(routingresult[index_step].step[0].jalur[1].lat) , lng: Number(routingresult[index_step].step[0].jalur[1].lng) };
    var finish = {lat: Number(routingresult[index_step].step[last_element - 1].jalur[0].lat) , lng: Number(routingresult[index_step].step[last_element - 1].jalur[0].lng) };
    
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

    for (var j = 0; j < routingresult[index_step].step.length; j++) {
     if(undefined !== routingresult[index_step].step[j].angkot[0].route_color)
     {
      var warna = routingresult[index_step].step[j].angkot[0].route_color;
      var nama_trayek = routingresult[index_step].step[j].angkot[0].trip_short_name;
      trayek.push(nama_trayek);
      color.push(warna);
     }
     else
     {
      var warna = "#FF0000";
      color.push(warna);
     } 
      obj = [];
     
      for (var k = 0; k < routingresult[index_step].step[j].jalur.length; k++) {

        var lat =  Number(routingresult[index_step].step[j].jalur[k].lat);
        var lng =  Number(routingresult[index_step].step[j].jalur[k].lng);
        obj.push( {lat:lat, lng:lng} ) ;

      }
      rute.push(obj);
    }
    
     //  }
    console.log(trayek);
    console.log(color);
    

   
     if(walk_route=="no")
        {
          for (var i = 0; i < rute.length; i++) {
                  var polyline = new google.maps.Polyline({
                       path:  rute[i] ,
                       //geodesic: true,
                       strokeColor: color[i] ,//'#43378e' ,
                       strokeOpacity: 2.0,
                       strokeWeight: 2
                  });  
                  polyline.setMap(map);  
                }
        }
        else
        {
          //tulis pernyataan disini
          //console.log(rute.length);
          for (var i = 0; i < rute.length; i++) {
            if(i == 0)
            {
              var garis = routingresult[index_step].step[i].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              //console.log(garis);
              var polyline = new google.maps.Polyline({
                                 path:  encodeString ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }
            else if(i == (rute.length-1) )
            {
              var garis = routingresult[index_step].step[ (rute.length-1) ].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              //console.log(garis);
              var polyline = new google.maps.Polyline({
                                 path:  encodeString ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }
            else      
            {
              var polyline = new google.maps.Polyline({
                                 path:  rute[i] ,
                                 //geodesic: true,
                                 strokeColor: color[i] ,//'#43378e' ,
                                 strokeOpacity: 2.0,
                                 strokeWeight: 2
              });  
              polyline.setMap(map);  
            }    
          }    

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
    
       
       document.getElementById('start').value = koordinat[0];
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