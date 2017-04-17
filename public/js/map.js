var points = [];
var koordinat = []; 
var arrPolyline = [];
var jalur;
var walk_route = "no";
var lokasi_awal;
var lokasi_tujuan;
var marker_intersection = [];
var map;
var arrName;
var nameplace = [];
var tdPoints = {};
var iconBase = "http://localhost/webserverangkot/" ;
function initMap() {

  var lineSymbol = {
          path: google.maps.SymbolPath.CIRCLE,//'M 0,-1 0,1',
          strokeOpacity: 1,
          scale: 4
          };
          

  var hide = function(){
    $("#select_routingresult").hide();
    $("#select_start").hide();
    $("#select_finish").hide();
    $("#tabs").hide();
    $("#isiTabs").hide();
    $("#start_location_name").hide();
    $("#finish_location_name").hide();
    $(".myProgressBar").hide();
  }

  hide();

  map = new google.maps.Map( document.getElementById('map') , {
          center: {lat: -6.914838922559386, lng: 107.60765075683594},
          zoom: 13,
          title: "map bandung"
        });

  google.maps.event.addDomListener(window, "resize", function() {
   var center = map.getCenter();
   google.maps.event.trigger(map, "resize");
   map.setCenter(center); 
  });

  map.addListener('click', function(event){   
    if(points.length < 2)
    {
      var marker = new google.maps.Marker({
            position: event.latLng,
            map: map
        });
      points.push(marker);
      
      if( $("#start_location").val() == ''  )
      {
        var lat = points[0].position.lat();
        var lng = points[0].position.lng();
        
        $("#start").val( lat+","+lng );
        $("#start_location").val( lat+","+lng );
        $("#finish_location").focus(); 

        var placeName = getlocinfo(lat+","+lng) ;
        $.when(placeName).done(function(){
          $("#start_location").hide();
          var placename = placeName.responseJSON.results[0].formatted_address;
          placename = placename.split("No."+placeName.responseJSON.results[0].address_components[0].long_name+",");
          $("#start_location_name").val( placename );
          $("#start_location_name").show();
        });

      }
      //else if( $("#start_location").val() !== '' )
      else
      {
        if(points.length == 1)
        {
          var lat = points[0].position.lat();
          var lng = points[0].position.lng();  
        }
        else{
          var lat = points[1].position.lat();
          var lng = points[1].position.lng();
        }
        $("#end").val( lat+","+lng );
        $("#finish_location").val( lat+","+lng );
        var placeName = getlocinfo(lat+","+lng) ;
        $.when(placeName).done(function(){
          $("#finish_location").hide();
          var placename = placeName.responseJSON.results[0].formatted_address;
          placename = placename.split("No."+placeName.responseJSON.results[0].address_components[0].long_name+",");
          $("#finish_location_name").val( placename );
          $("#finish_location_name").show();
          setTimeout( function(){$("#submit").trigger("click")} , 1000 );

        });

      }
    
    }
  });


  
  $('#finish_location').on("keypress", function(e) {
        if (e.keyCode == 13) {
            //alert("Enter pressed");
            $("#submit").trigger("click");
            //return false; // prevent the button click from happening
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

  function setMapOnAll(gg) 
  {
    for (var i = 0; i < points.length; i++) 
    {
      points[i].setMap(gg);
    }
  }

  $("#button_clear").on('click', function(){
      //console.log("ganti ");
      hide();
      $("#tabs").empty();
      $("#isiTabs").empty();
      $("#start").val("");
      $("#end").val("");
      $("#start_location").val("");
      $("#finish_location").val("");
      
      $("#start_location").show();
      $("#finish_location").show();

      setMapOnAll(null);
      del_polyline(arrPolyline); 
      points = [];
      koordinat = [];
      marker_intersection = [];

      map.setCenter({lat: -6.914838922559386, lng: 107.60765075683594});
      map.setZoom(13);
  })

  
  $("#start_location").autocomplete({
  source : function( request, response ) {
      $.ajax({
          url: "http://localhost/webserverangkot/public/api/get_position" ,
          //method: "GET",
          dataType: "json",
          data: "location=" + request.term ,
          success: function( data ) {
            response( $.map(data.searchresult, function (value, key) {
              return {
                  label: value.placename
                  //value: value.id
              } ;
          }));
        } });
      //console.log(request);

      }
  });

  $("#finish_location").autocomplete({
    source : function( request, response ) {
      $.ajax({
          url: "http://localhost/webserverangkot/public/api/get_position" ,
          //method: "GET",
          dataType: "json",
          data: "location=" + request.term ,
          success: function( data ) {
            response( $.map(data.searchresult, function (value, key) {
              return {
                  label: value.placename
                  //value: value.id
              } ;
          }));
        } });
      //console.log(request);
      }
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

  //$("#menu-toggle").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );
  $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );

  $("#submit").on("click", function(){
      
    
    var a = document.getElementById('start_location').value;
    var b = document.getElementById('finish_location').value;
    a = a.split(",");
    b = b.split(",");
    if( !isNaN(a[0]) && !isNaN(a[1]) && !isNaN(b[0]) && !isNaN(b[1]) ){
        
      console.log("run jalan");
      jalan($("#start_location").val(),$("#finish_location").val(), walk_route );
    }
    else if(!isNaN(a[0]) && !isNaN(a[1]) && isNaN(b[0]) && isNaN(b[1]) ) // angka & tulisan
    {
      var start = $("#start_location_name").val();
      start = start.split(",");
      console.log(start[0]);
      jalanWithApi( start[0] ,$("#finish_location").val());
       $("#start_location").hide();
       $("#finish_location").hide();
       $("#start_location_name").hide();

    }
    else if( isNaN(a[0]) && isNaN(a[1]) && !isNaN(b[0]) && !isNaN(b[1])) //tulisan dan angka
    {
      var finish = $("#finish_location_name").val();
      finish = finish.split(",");
      
      jalanWithApi($("#start_location").val(), finish[0] );
      $("#start_location").hide();
      $("#finish_location").hide();
      $("#finish_location_name").hide();
    }
    else
    {
      console.log("run jalanWithApi");
       jalanWithApi($("#start_location").val(),$("#finish_location").val());
       $("#start_location").hide();
       $("#finish_location").hide();
    }
  
  })

  $("#reverse").on("click", function(){
    
    var a = document.getElementById('start_location').value;
    var b = document.getElementById('finish_location').value;
    $("#start_location").val(b);
    $("#finish_location").val(a);

    $("#submit").trigger("click");

  })

  $("#select_routingresult_select").on('change',function(){
      var index =  $("#select_routingresult_select").val() ;
      //console.log(index);
      change_step(index);
  });



  $(document).on('shown.bs.tab', function (e){ 
    //console.log('ae'); 
    var target = $(e.target).attr("href");
    var index = target.substr(target.length - 1);
    console.log(index);
    change_step(index);
    
  });

  $("#select_start_select").on("change",function(){
      var index1 = $("#select_start_select").val();
      var index2 = $("#select_finish_select").val();
      koordinat = [];
      change_select_start(index1, index2);
  })

  $("#select_finish_select").on("change",function(){
      var index1 = $("#select_start_select").val();
      var index2 = $("#select_finish_select").val();
      koordinat = [];
      change_select_start(index1, index2);
  })  


  var draw_polyline = function(rute, color, map, icon='',strokeOpacity=1){
    var polyline = new google.maps.Polyline({
                 path:  rute ,
                 icons : icon,
                 strokeColor: color ,//'#43378e' ,
                 strokeOpacity: strokeOpacity,
                 strokeWeight: 3
                 
        });  
    polyline.setMap(map);
      return polyline;
  }

  var make_marker = function(a,icon){
    var marker = new google.maps.Marker({
            position: a,
            map: map,
            icon: icon
        });
    points.push(marker);
  }

  var del_polyline = function(ggg){
    for (var i = 0; i < ggg.length; i++) {
          ggg[i].setMap(null);      
        }
        arrPolyline = [];
  }

  
  

  var jalan = function (lat = '-6.9334,107.6279', lon='-6.9328,107.6346', walk_route=walk_route)//(lat = '-6.934776792662195,107.64575958251953', lon = '-6.906999871255851,107.60250091552734')
  {  
      //$(".se-pre-con").show();
      $(".myProgressBar").show();
      
      //$("#button_clear").trigger("click");
      if(lat == null & lon == null )
      {
          var a = get_location();//document.getElementById('location').value;
          var lat = a.lat;
          var lon = a.lon;
         
      }
       
       

     jalur = get_jalur2(lat, lon, walk_route); // masuk ke fungsi cetak_jalur
     
     del_polyline(arrPolyline);
     
     $.when(jalur).done(function(){
        jalur = jalur.responseJSON;
        if(jalur.status !== "OK"  )
        {
            alert("sorry, we can't find public transfortasion for you arround here.");
            hide();
            $("#button_clear").trigger("click");
            //$(".se-pre-con").fadeOut("slow");
            return ;

        }
        $("#tabs").empty();
        $("#isiTabs").empty();
        $("#select_routingresult_select").text("--Pilih--");
        var routingresult = [];
        for (var key in jalur.routingresult ) //key jadi index nya
        {
          
          routingresult.push( jalur.routingresult[key] );
          var result = jalur.routingresult;
          //$("#select_routingresult_select").append('<option value="' + key + '">' + result[key].total_cost + '</option>' );  
          if(key == 0)
          {
            $("#tabs").append('<li role="presentation" class="active" id="'+key+'"  ><a data-toggle="tab" href="#tabpane'+ key +'" >Rute '+ (Number(key)+1) +'  Rp. '+ result[key].total_cost  +' </a></li>');
            
            $("#isiTabs").append('<div class="tab-pane active" id="tabpane'+ key +'">'+                      
                        '<table  class="table" id="tableKet'+key+'">'+
                        '</table>'+
                      '</div>'); 
            
            var trHandler = [];
            var angka = {};               
            for (var j in jalur.routingresult[key].step){
              var ket = jalur.routingresult[key].step[j].ket ;
              var gambar = jalur.routingresult[key].step[j].angkot[0].image;
              $("#tableKet"+key).append("<tr id='tr"+key+"-"+j+"'><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+gambar+"'></td><td  width='70%'>"+ket+"</td></tr>");
              
              
              var lat = jalur.routingresult[key].step[j].jalur[0].lat ;
              var lng = jalur.routingresult[key].step[j].jalur[0].lng ;
              var latLng = {lat: Number(lat) ,lng:Number(lng) };
              
              angka[j] = latLng;
              
            }
            tdPoints[key] = angka;

            $("#tableKet"+key).on("click", "tr", function(){
              console.log($(this).attr('id'));
              var tmp = $(this).attr('id');
              var keytmp = tmp.split("tr");
              var keytmp = keytmp[1].split("-");
              var key = keytmp[0];
              var j = keytmp[1];
              console.log(key+""+j);
              
              console.log(tdPoints[key][j]);
              map.setCenter(tdPoints[key][j]);
              map.setZoom(15);
            });

            
           
          }
          else
          {
           $("#tabs").append('<li role="presentation" id="'+key+'"  ><a data-toggle="tab" href="#tabpane'+ key +'">Rute '+ (Number(key)+1) +' Rp. '+ result[key].total_cost  +' </a></li>');
           $("#isiTabs").append('<div class="tab-pane" id="tabpane'+ key +'">'+                      
                        '<table  class="table" id="tableKet'+key+'">'+
                        '</table>'+
                      '</div>');

            var angka = {};
            for (var j in jalur.routingresult[key].step){
              var ket = jalur.routingresult[key].step[j].ket ;
              var gambar = jalur.routingresult[key].step[j].angkot[0].image;
              $("#tableKet"+key).append("<tr id='tr"+key+"-"+j+"'><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+gambar+"'></td><td width='70%'>"+ket+"</td></tr>");

              var lat = jalur.routingresult[key].step[j].jalur[0].lat ;
              var lng = jalur.routingresult[key].step[j].jalur[0].lng ;
              var latLng = {lat: Number(lat) ,lng:Number(lng) };
              
              angka[j] = latLng;
              
            }
            tdPoints[key] = angka;

            $("#tableKet"+key).on("click", "tr", function(){
              console.log($(this).attr('id'));
              var tmp = $(this).attr('id');
              var keytmp = tmp.split("tr");
              var keytmp = keytmp[1].split("-");
              var key = keytmp[0];
              var j = keytmp[1];
              console.log(key+""+j);
              console.log(tdPoints[key][j]);
              map.setCenter(tdPoints[key][j]);
              map.setZoom(15);
            });

             
          }


        }
        $("#tabs").show()
        $("#isiTabs").show();
        //$("#select_routingresult").show();  
        
        console.log(jalur);


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
        
        var icon = []; 
        icon.push({ icon: lineSymbol,
                    offset: '0',
                    repeat: '20px'
                  }) ;
        var opacity=0;
        if(walk_route=="no")
        {
          
          for (var i = 0; i < rute.length; i++) {

            if(i == 0)
            {
              arrPolyline.push( draw_polyline(rute[i],color[i],map,icon,opacity)  );   
            }
            else if(i == (rute.length-1) )
            {
             
              arrPolyline.push( draw_polyline(rute[i],color[i],map,icon,opacity)  ); 
            }
            else      
            {
              arrPolyline.push(  draw_polyline(rute[i],color[i],map,'') );  
            }    
          }
          //console.log(arrPolyline);
        }
        else
        {
          
          for (var i = 0; i < rute.length; i++) {
            if(i == 0)
            {
              var garis = jalur.routingresult[0].step[i].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              arrPolyline.push( draw_polyline(encodeString,color[i],map, icon,opacity)  );   
            }
            else if(i == (rute.length-1) )
            {
              var garis = jalur.routingresult[0].step[ (rute.length-1) ].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
             
              arrPolyline.push( draw_polyline(encodeString,color[i],map, icon,opacity)  ); 
            }
            else      
            {
              arrPolyline.push(  draw_polyline(rute[i],color[i],map) );  
            }    
          }    

        }
        
        //add marker intersection
        marker_intersection = [];
        markerIcon = [];
        setMapOnAll(null);
        for (var i = 0; i < jalur.routingresult[0].step.length; i++) {
          var tmpIcon = {url: iconBase+jalur.routingresult[0].step[i].angkot[0].image, scaledSize: new google.maps.Size(50, 40)}
          if (i == 0) {
            var lat1 = Number( jalur.routingresult[0].step[i].jalur[0].lat ) ;
            var lng1 = Number(jalur.routingresult[0].step[i].jalur[0].lng) ;          
            marker_intersection.push( {lat:lat1,lng:lng1}  );

            var lat = Number( jalur.routingresult[0].step[i].jalur[1].lat ) ;
            var lng = Number(jalur.routingresult[0].step[i].jalur[1].lng) ;
            
            marker_intersection.push( {lat:lat,lng:lng}  );
            markerIcon.push( tmpIcon );
          } 
          else if(i == jalur.routingresult[0].step.length - 1 ){
            var lat = Number(jalur.routingresult[0].step[i].jalur[1].lat) ;
            var lng = Number(jalur.routingresult[0].step[i].jalur[1].lng) ;
            marker_intersection.push( {lat:lat,lng:lng}  );
            markerIcon.push(tmpIcon);
          } 
          else{

            var lat = Number(jalur.routingresult[0].step[i].jalur[jalur.routingresult[0].step[i].jalur.length-1].lat) ;
            var lng = Number(jalur.routingresult[0].step[i].jalur[jalur.routingresult[0].step[i].jalur.length-1].lng) ;
            marker_intersection.push( {lat:lat,lng:lng}  );
            markerIcon.push(tmpIcon);
          
          }
        }
       

        for (var i = 0; i < marker_intersection.length; i++) {

          make_marker( marker_intersection[i], markerIcon[i] );
          console.log(marker_intersection[i]);
        }

        var bound =  new google.maps.LatLngBounds();
        for (var i = 0; i < points.length; i++) {
          //points[i]
          bound.extend(points[i].position);
        }
        
        map.fitBounds(bound);
        //$(".se-pre-con").fadeOut("slow");
        $(".myProgressBar").fadeOut("slow");
       // $("#progressBar").progressbar("disable");
     })
     //$(document).ajaxComplete(function(){});
             
  }

  var change_step = function(index){
    //console.log(jalur);
    //setMapOnAll(null);
    del_polyline(arrPolyline);
    var trayek = [];
    var color = [];
    var rute = [];
    for (var j = 0; j < jalur.routingresult[index].step.length; j++) {
       if(undefined !== jalur.routingresult[index].step[j].angkot[0].route_color)
       {

          var warna = jalur.routingresult[index].step[j].angkot[0].route_color;
          var nama_trayek = jalur.routingresult[index].step[j].angkot[0].trip_short_name;
          trayek.push(nama_trayek);
          color.push(warna);
       }
       else
       {

          var warna = "#FF0000";
          color.push(warna);
       } 
        obj = [];
       
        for (var k = 0; k < jalur.routingresult[index].step[j].jalur.length; k++) {

          var lat =  Number(jalur.routingresult[index].step[j].jalur[k].lat);
          var lng =  Number(jalur.routingresult[index].step[j].jalur[k].lng);
          obj.push( {lat:lat, lng:lng} ) ;

        }
        rute.push(obj);
    }

    var icon = []; 
    icon.push({ icon: lineSymbol,
                offset: '0',
                repeat: '20px'
              }) ;
    var opacity=0;
    if(walk_route=="no")
    {
      for (var i = 0; i < rute.length; i++) {
            if(i == 0 || i == (rute.length-1) )
            {
              arrPolyline.push(  draw_polyline(rute[i],color[i],map, icon, opacity) );
            }
            else{
              arrPolyline.push(  draw_polyline(rute[i],color[i],map ) );
            }

          }
      //console.log(arrPolyline);
    }
    else
    {
      for (var i = 0; i < rute.length; i++) 
      {

        if(i == 0)
        {
          var garis = jalur.routingresult[index].step[i].jalur[2].routes[0].overview_polyline.points;
          var encodeString = google.maps.geometry.encoding.decodePath(garis);
          
          arrPolyline.push( draw_polyline(encodeString,color[i],map, icon, opacity)  );   
        }
        else if(i == (rute.length-1) )
        {
          var garis = jalur.routingresult[index].step[ (rute.length-1) ].jalur[2].routes[0].overview_polyline.points;
          var encodeString = google.maps.geometry.encoding.decodePath(garis);
          
          arrPolyline.push( draw_polyline(encodeString,color[i],map, icon, opacity)  ); 
        }
        else      
        {

          arrPolyline.push(  draw_polyline(rute[i],color[i],map) );  
        }
      
      }

    }

    marker_intersection = [];
    markerIcon = [];
    setMapOnAll(null);
    for (var i = 0; i < jalur.routingresult[index].step.length; i++) {
      var tmpIcon = {url: iconBase+jalur.routingresult[index].step[i].angkot[0].image, scaledSize: new google.maps.Size(50, 40)} ;
        if (i == 0) {
          var lat1 = Number( jalur.routingresult[index].step[i].jalur[0].lat ) ;
          var lng1 = Number(jalur.routingresult[index].step[i].jalur[0].lng) ;
          marker_intersection.push( {lat:lat1,lng:lng1}  );

          var lat = Number( jalur.routingresult[index].step[i].jalur[1].lat ) ;
          var lng = Number(jalur.routingresult[index].step[i].jalur[1].lng) ;
          marker_intersection.push( {lat:lat,lng:lng}  );

          markerIcon.push(tmpIcon);
        } 
        else if(i == jalur.routingresult[index].step.length - 1 ){
          var lat = Number(jalur.routingresult[index].step[i].jalur[1].lat) ;
          var lng = Number(jalur.routingresult[index].step[i].jalur[1].lng) ;
          marker_intersection.push( {lat:lat,lng:lng}  );

          markerIcon.push(tmpIcon);
        } 
        else{

          var lat = Number(jalur.routingresult[index].step[i].jalur[jalur.routingresult[index].step[i].jalur.length-1].lat) ;
          var lng = Number(jalur.routingresult[index].step[i].jalur[jalur.routingresult[index].step[i].jalur.length-1].lng) ;
          marker_intersection.push( {lat:lat,lng:lng}  );
          markerIcon.push(tmpIcon);
        
        }

        $("#tableKet"+index+" tr").on("click", "tr", function(){
              console.log($("#tableKet"+index).attr('id'));
              var tmp = $("#tableKet"+index).attr('id');
              var keytmp = tmp.split("tr");
              var keytmp = keytmp[1].split("-");
              var key = keytmp[0];
              var j = keytmp[1];
              console.log(key+""+j);
              console.log(tdPoints[key][j]);
              map.setCenter(tdPoints[key][j]);
              map.setZoom(17);
            });

    }
     
        
      for (var i = 0; i < marker_intersection.length; i++) {

        make_marker( marker_intersection[i], markerIcon[i] );
        //console.log(marker_intersection[i]);
      }

      var bound =  new google.maps.LatLngBounds();
      for (var i = 0; i < points.length; i++) {
        //points[i]
        bound.extend(points[i].position);
      }
      
      map.fitBounds(bound); 

  }

  var jalanWithApi = function(start, finish){
    $(".myProgressBar").show();
    lokasi_awal = '';
    lokasi_tujuan = '';
    $("#select_start_select").empty();
    $("#select_finish_select").empty();
    
    lokasi_awal = get_location(start);
    lokasi_tujuan = get_location(finish);

    $.when(lokasi_awal, lokasi_tujuan ).done(function(){

          lokasi_awal = lokasi_awal.responseJSON;
          console.log(lokasi_awal);
          lokasi_tujuan = lokasi_tujuan.responseJSON;
          console.log("run setelah if");
          for (var key in lokasi_awal.searchresult ) //key jadi index nya
          {
           
            var result = lokasi_awal.searchresult;
            $("#select_start_select").append('<option value="' + key + '">' + result[key].placename + '</option>' );
          }
          $("#select_start").show();

          for (var key in lokasi_tujuan.searchresult ) //key jadi index nya
          {
            var result = lokasi_tujuan.searchresult;
            $("#select_finish_select").append('<option value="' + key + '">' + result[key].placename + '</option>' );
          }

          $("#select_finish").show();
          console.log(lokasi_awal);
          change_select_start(0,0);
        
         $(".myProgressBar").fadeOut();
    });
    
  }

  
  var change_select_start = function(index1, index2){
    del_polyline(arrPolyline);
    setMapOnAll(null);
    //if(lokasi_awal.searchresult[index1].location && lokasi_tujuan.searchresult[index2].location)
    //{


    var start = lokasi_awal.searchresult[index1].location;
    var finish = lokasi_tujuan.searchresult[index2].location;
    koordinat.push(start);
    koordinat.push(finish);
    for (var i = 0; i < koordinat.length; i++) {
      make_marker(koordinat[i]);
    }
    
    var start2 = start.lat + "," + start.lng ;
    var finish2 = finish.lat + "," + finish.lng;

    jalur = get_jalur2(start2, finish2, walk_route);
    $.when(jalur).done(function(){
        jalur = jalur.responseJSON;

        //console.log(" Run change_select_start")
        console.log(jalur);
        if(jalur.status !== "OK"  )
        {   
            alert("sorry, we can't find public transfortasion for you arround here.");
            $("#button_clear").trigger("click");
            $(".myProgressBar").fadeOut("slow");
            return ;

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
        
        var icon = []; 
        icon.push({ icon: lineSymbol,
                    offset: '0',
                    repeat: '20px'
                  }) ;
        var opacity=0;
  
        if(walk_route=="no")
        {
          for (var i = 0; i < rute.length; i++) {
            //arrPolyline.push(  draw_polyline(rute[i],color[i],map) );
            if(i == 0)
            {
              arrPolyline.push( draw_polyline(rute[i],color[i],map,icon,opacity)  );   
            }
            else if(i == (rute.length-1) )
            {
             
              arrPolyline.push( draw_polyline(rute[i],color[i],map,icon,opacity)  ); 
            }
            else      
            {
              arrPolyline.push(  draw_polyline(rute[i],color[i],map,'') );  
            }    
          }
          
        }
        else
        {
          
          for (var i = 0; i < rute.length; i++) {
            if(i == 0)
            {
              var garis = jalur.routingresult[index1].step[i].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              
              arrPolyline.push( draw_polyline(encodeString,color[i],map)  );  
            }
            else if(i == (rute.length-1) )
            {
              var garis = jalur.routingresult[index2].step[ (rute.length-1) ].jalur[2].routes[0].overview_polyline.points;
              var encodeString = google.maps.geometry.encoding.decodePath(garis);
              
              arrPolyline.push( draw_polyline(encodeString,color[i],map)  );  
            }
            else      
            {
              
              arrPolyline.push(  draw_polyline(rute[i],color[i],map) ); 
            }    
          }    
  
        }
        
        $("#tabs").empty();
        $("#isiTabs").empty();
        var routingresult = [];
        for (var key in jalur.routingresult ) //key jadi index nya
        {
          
          routingresult.push( jalur.routingresult[key] );
          var result = jalur.routingresult;
          $("#select_routingresult_select").append('<option value="' + key + '">' + result[key].total_cost + '</option>' );
          
          if(key == 0)
          {
            $("#tabs").append('<li role="presentation" class="active" id="'+key+'"  ><a data-toggle="tab" href="#tabpane'+ key +'" >Rute '+ (Number(key)+1) +'  Rp. '+ result[key].total_cost  +' </a></li>');
            
            $("#isiTabs").append('<div class="tab-pane active" id="tabpane'+ key +'">'+                      
                        '<table  class="table" id="tableKet'+key+'">'+
                        '</table>'+
                      '</div>'); 
            
            var trHandler = [];
            var angka = {};               
            for (var j in jalur.routingresult[key].step){
              var ket = jalur.routingresult[key].step[j].ket ;
              var gambar = jalur.routingresult[key].step[j].angkot[0].image;
              $("#tableKet"+key).append("<tr id='tr"+key+"-"+j+"'><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+gambar+"'></td><td  width='70%'>"+ket+"</td></tr>");
              
              
              var lat = jalur.routingresult[key].step[j].jalur[0].lat ;
              var lng = jalur.routingresult[key].step[j].jalur[0].lng ;
              var latLng = {lat: Number(lat) ,lng:Number(lng) };
              
              angka[j] = latLng;
              
            }
            tdPoints[key] = angka;

            $("#tableKet"+key).on("click", "tr", function(){
              console.log($(this).attr('id'));
              var tmp = $(this).attr('id');
              var keytmp = tmp.split("tr");
              var keytmp = keytmp[1].split("-");
              var key = keytmp[0];
              var j = keytmp[1];
              console.log(key+""+j);
              
              console.log(tdPoints[key][j]);
              map.setCenter(tdPoints[key][j]);
              map.setZoom(15);
            });

            
           
          }
          else
          {
           $("#tabs").append('<li role="presentation" id="'+key+'"  ><a data-toggle="tab" href="#tabpane'+ key +'">Rute '+ (Number(key)+1) +' Rp. '+ result[key].total_cost  +' </a></li>');
           $("#isiTabs").append('<div class="tab-pane" id="tabpane'+ key +'">'+                      
                        '<table  class="table" id="tableKet'+key+'">'+
                        '</table>'+
                      '</div>');

            var angka = {};
            for (var j in jalur.routingresult[key].step){
              var ket = jalur.routingresult[key].step[j].ket ;
              var gambar = jalur.routingresult[key].step[j].angkot[0].image;
              $("#tableKet"+key).append("<tr id='tr"+key+"-"+j+"'><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+gambar+"'></td><td width='70%'>"+ket+"</td></tr>");

              var lat = jalur.routingresult[key].step[j].jalur[0].lat ;
              var lng = jalur.routingresult[key].step[j].jalur[0].lng ;
              var latLng = {lat: Number(lat) ,lng:Number(lng) };
              
              angka[j] = latLng;
              
            }
            tdPoints[key] = angka;

            $("#tableKet"+key).on("click", "tr", function(){
              console.log($(this).attr('id'));
              var tmp = $(this).attr('id');
              var keytmp = tmp.split("tr");
              var keytmp = keytmp[1].split("-");
              var key = keytmp[0];
              var j = keytmp[1];
              console.log(key+""+j);
              console.log(tdPoints[key][j]);
              map.setCenter(tdPoints[key][j]);
              map.setZoom(15);
            });

             
          }
        }
        //$("#select_routingresult").show();
        marker_intersection = [];
        markerIcon = [];
        setMapOnAll(null);
        index = 0;
        for (var i = 0; i < jalur.routingresult[index].step.length; i++) {
          var tmpIcon = {url: iconBase+jalur.routingresult[index].step[i].angkot[0].image, scaledSize: new google.maps.Size(50, 40)} ;
            if (i == 0) {
              var lat1 = Number( jalur.routingresult[index].step[i].jalur[0].lat ) ;
              var lng1 = Number(jalur.routingresult[index].step[i].jalur[0].lng) ;
              marker_intersection.push( {lat:lat1,lng:lng1}  );

              var lat = Number( jalur.routingresult[index].step[i].jalur[1].lat ) ;
              var lng = Number(jalur.routingresult[index].step[i].jalur[1].lng) ;
              marker_intersection.push( {lat:lat,lng:lng}  );

              markerIcon.push(tmpIcon);
            } 
            else if(i == jalur.routingresult[index].step.length - 1 ){
              var lat = Number(jalur.routingresult[index].step[i].jalur[1].lat) ;
              var lng = Number(jalur.routingresult[index].step[i].jalur[1].lng) ;
              marker_intersection.push( {lat:lat,lng:lng}  );

              markerIcon.push(tmpIcon);
            } 
            else{

              var lat = Number(jalur.routingresult[index].step[i].jalur[jalur.routingresult[index].step[i].jalur.length-1].lat) ;
              var lng = Number(jalur.routingresult[index].step[i].jalur[jalur.routingresult[index].step[i].jalur.length-1].lng) ;
              marker_intersection.push( {lat:lat,lng:lng}  );
              markerIcon.push(tmpIcon);
            
            }

            $("#tableKet"+index+" tr").on("click", "tr", function(){
                  console.log($("#tableKet"+index).attr('id'));
                  var tmp = $("#tableKet"+index).attr('id');
                  var keytmp = tmp.split("tr");
                  var keytmp = keytmp[1].split("-");
                  var key = keytmp[0];
                  var j = keytmp[1];
                  console.log(key+""+j);
                  console.log(tdPoints[key][j]);
                  map.setCenter(tdPoints[key][j]);
                  map.setZoom(17);
                });

        }


        for (var i = 0; i < marker_intersection.length; i++) {
          make_marker( marker_intersection[i], markerIcon[i] );
        }

        var bound =  new google.maps.LatLngBounds();
        for (var i = 0; i < points.length; i++) {
          //points[i]
              bound.extend(points[i].position);
        }
        
        map.fitBounds(bound);

        $("#tabs").show();
        $("#isiTabs").show();
        $(".myProgressBar").fadeOut("slow");
    
    });
    //$(document).ajaxComplete(function(){});
 
    //}

  }

      
} // tutup initMap()

$(window).load(function() {
    // Animate loader off screen
    /*function get_placeinfo (){
      return $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_placeinfo",//efisiensi2",//cetak_jalur2" , //bisa diganti ganti
        method: "GET",
        //dataType: "json"         
      });
    }*/

    $(".se-pre-con").fadeOut("slow");

});



function get_jalur(lat = "-6.942870986409176,107.65348434448242",lon = "-6.907425909992761,107.6220703125", walk_route="no" ) 
{
  console.log("ajax berjalan"); 
  $.ajax({
    url: "http://localhost/webserverangkot/public/api/cetak_jalur",//efisiensi2",//cetak_jalur2" , //bisa diganti ganti
    method: "GET",
    data: "start=" + lat + "&finish=" + lon + "&walk_route=" + walk_route ,
    //async: false,
    success:function(data) 
    {
      jalur = data; 
    },
    error:function(){
      $(".se-pre-con").fadeOut("slow");
      alert("Sorry Error has occured, please refresh");
      //location.reload();
    }

  });
  //return result;
  
}

function get_jalur2(lat = "-6.942870986409176,107.65348434448242",lon = "-6.907425909992761,107.6220703125", walk_route="no" ) 
{ 
  
  return $.ajax({
         url: "http://localhost/webserverangkot/public/api/cetak_jalur",//efisiensi2",//cetak_jalur2" , //bisa diganti ganti
         method: "GET",
         data: "start=" + lat + "&finish=" + lon + "&walk_route=" + walk_route
         });
  
}

function get_koordinat(kirim) 
{
  var result="";
  var shape_id = kirim;
  
  $.ajax({
  url: "http://localhost/webserverangkot/public/api/get_koordinat" ,
  method: "GET",
  data: "kirim=" + kirim ,
  //async: false,
  success:function(data) {
  result = data; 
        }
     });
   return result;

}

function getlocinfo(latlng){
  return  $.ajax({
          url: "http://localhost/webserverangkot/public/api/getlocinfo" ,
          method: "GET",
          dataType: "json",
          data: "latlng=" + latlng
                
        });
}



function get_location(location) 
{
  console.log("ajax get_location berjalan");
  //var result;
  return  $.ajax({
    url: "http://localhost/webserverangkot/public/api/get_position" ,
    method: "GET",
    dataType: "json",
    data: "location=" + location
          
       });
}



