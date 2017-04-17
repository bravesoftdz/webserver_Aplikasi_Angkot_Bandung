@extends('layouts.check_html')

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>

<script src="js/jquery.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/scripts.js"></script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry&callback=initMap">
</script>

<script type="text/javascript">

  var data = [];
  var data2 = [];

  var array_polyline = [];
  var array_polyline2 = [];
  
  var array_marker = [];
  var dataAngkot;
  var dataAngkot2 ;
  var addMarker=[];
  var addMarker2 = [];

  function initMap() 
  { 
      map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -6.914838922559386, lng: 107.60765075683594},
            zoom: 13
          });
        
      //hide();

      var koordinat = [];  

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
       })
   
       

      $("#pilih").on("change", function(){
        var select_pilih = $("#pilih").val();
        pilih_change(select_pilih);

      });

      $("#pilih2").on("change", function(){
        var select_pilih = $("#pilih2").val();
        pilih_change2(select_pilih);

      });

      $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );

      $(".se-pre-con").fadeOut("slow");
       
  }



  var make_marker = function(a,icons='',content='start'){
      var marker = new google.maps.Marker({
              position: a,
              map: map,
              icon: icons
          });
      var infowindow = new google.maps.InfoWindow();

      google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
        return function() {
            infowindow.setContent(""+content);
            infowindow.open(map,this);
            //console.log( this );
        };
      })(marker,content,infowindow));

      return marker;
    }

  function pilih_change(route_id = 1){
    
    data = get_point(route_id);

    dataAngkot = get_angkot(route_id);
    //console.log(dataAngkot);
    

    $.when(data, dataAngkot).done(function(){
      data = data.responseJSON;
      dataAngkot = dataAngkot.responseJSON;
      var points = [];
      var array_info = [];

      for (var i = 0; i < data.length; i++) {
        var lat = Number( data[i].shape_pt_lat );
        var lng = Number( data[i].shape_pt_lon );
        points.push( {lat:lat, lng:lng} );
      }
      $("#tableInfo").empty();
      //$("#tableInfo").append("<tr><td></td> <td>Informasi </td> </tr>"); 
      //console.log(dataAngkot[0].image); 
      $("#tableInfo").append("<tr><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+dataAngkot[0].image+"'></td> <td width=70%>"+dataAngkot[0].ket +"</td> </tr>");
      $("#tableInfo").append("<tr> <td>Harga </td> <td>Rp. "+dataAngkot[0].price+"</td> </tr>");
      //console.log(dataAngkot);
      var color = dataAngkot[0].route_color ;
      var Polyline = new google.maps.Polyline({
         path: points ,
         geodesic: true,
         strokeColor: color, //'#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 3
      });

      if(array_polyline.length > 0)
      { 
        array_polyline[0].setMap(null);
        array_polyline.splice(0,1);
        array_polyline.push(Polyline);

      }
      else{
        
        array_polyline.push(Polyline);
      }
      //hapus marker sebelumnya.
      for (var i = 0; i < addMarker.length; i++) {
        addMarker[i].setMap(null);
      }

      
      for (var i = 0; i < points.length; i++) {
        var info = "shape_id : " +data[i].shape_id+ "<br> shape_pt_lat : " +data[i].shape_pt_lat+ "<br> shape_pt_lon : "+data[i].shape_pt_lon+" <br> ";
        addMarker.push( make_marker(points[i] , '' , info ) );
      }
      

      array_polyline[0].setMap(map);
      var bound =  new  google.maps.LatLngBounds();
      for (var i = 0; i < points.length; i++) {
        //array_marker[i]
        bound.extend(points[i]);
      }
        
        map.fitBounds(bound);
    
    });
  }

  function pilih_change2(route_id = 1){
    
    data2 = get_point(route_id);

    dataAngkot2 = get_angkot(route_id);
    //console.log(dataAngkot);
    

    $.when(data2, dataAngkot2).done(function(){
      data2 = data2.responseJSON;
      dataAngkot2 = dataAngkot2.responseJSON;
      var points2 = [];
      var array_info = [];

      for (var i = 0; i < data2.length; i++) {
        var lat = Number( data2[i].shape_pt_lat );
        var lng = Number( data2[i].shape_pt_lon );
        points2.push( {lat:lat, lng:lng} );
      }
      console.log(points2) ;
      $("#tableInfo").empty();
      //$("#tableInfo").append("<tr><td></td> <td>Informasi </td> </tr>"); 
      //console.log(dataAngkot[0].image); 
      $("#tableInfo").append("<tr><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+dataAngkot2[0].image+"'></td> <td width=70%>"+dataAngkot2[0].ket +"</td> </tr>");
      $("#tableInfo").append("<tr> <td>Harga </td> <td>Rp. "+dataAngkot2[0].price+"</td> </tr>");
      //console.log(dataAngkot);
      var warna = dataAngkot2[0].route_color ; 
      var Polyline = new google.maps.Polyline({
         path: points2 ,
         geodesic: true,
         strokeColor: warna, //'#FF0000',
         strokeOpacity: 1.0,
         strokeWeight: 3
      });

      if(array_polyline2.length > 0)
      { 
        array_polyline2[0].setMap(null);
        array_polyline2.splice(0,1);
        array_polyline2.push(Polyline);

      }
      else{
        
        array_polyline2.push(Polyline);
      }
      //hapus marker sebelumnya.
      for (var i = 0; i < addMarker2.length; i++) {
        addMarker2[i].setMap(null);
      }

      
      /*addMarker.push( make_marker(points2[0], "http://maps.google.com/mapfiles/ms/icons/green-dot.png", 'start' ) );

      addMarker.push ( make_marker( points2[ points2.length - 1 ] , "http://maps.google.com/mapfiles/ms/icons/green-dot.png", 'finish' ) );*/
      for (var i = 0; i < points2.length; i++) {
        var info = "shape_id : " +data2[i].shape_id+ "<br> shape_pt_lat : " +data2[i].shape_pt_lat+ "<br> shape_pt_lon : "+data2[i].shape_pt_lon+" <br> ";
        addMarker2.push( make_marker(points2[i] , "http://maps.google.com/mapfiles/ms/icons/green-dot.png" , info ) );
      }
      
      

      array_polyline2[0].setMap(map);
      var bound =  new  google.maps.LatLngBounds();
      for (var i = 0; i < points2.length; i++) {
        //array_marker[i]
        bound.extend(points2[i]);
      }
        
        map.fitBounds(bound);
    
    });
  }




  function get_point(terima=1) {
      
    var kirim = terima;
      
    return $.ajax({
        url: "http://localhost/webserverangkot/public/api/trips/get_trayek_akbar", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
        method: "GET",
        data: "kirim=" + kirim,
        
      });
     
       
  }

  function get_angkot(route_id){
    
    return $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_angkot_info",
        method: "GET",
        data: "route_id=" + route_id,  
        
    });

  }      
       


</script>


@endsection