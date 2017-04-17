@extends('layouts.trayek_html')

@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/scripts.js"></script>

<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC3y8eFg4RsElrdt7Gw-qAj78dWrQ4N21w&libraries=geometry&callback=initMap">
</script>

<script type="text/javascript">

var data = [];
var array_polyline = [];
var array_marker = [];
var dataAngkot;
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

    $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );

    $(".se-pre-con").fadeOut("slow");
     
}



var make_marker = function(a){
    var marker = new google.maps.Marker({
            position: a,
            map: map
        });
    array_marker.push(marker);
    return marker;
  }

var del_marker = function(){
  for (var i = 0; i < array_marker.length; i++) {
    array_marker[i].setMap(null);
  }
}

function pilih_change(route_id = 1){
  
  data = get_point(route_id);
  //console.log(data);
  var points = [];
  var array_info = [];
  del_marker();
  array_marker = [];
  for (var i = 0; i < data.length; i++) {
    var lat = Number( data[i].shape_pt_lat );
    var lng = Number( data[i].shape_pt_lon );
    points.push( {lat:lat, lng:lng} );
    

  }

  //get_angkot(route_id);
  //console.log(dataAngkot);
  

  /*$(document).ajaxComplete(function(){
    $("#tableInfo").empty();
    //$("#tableInfo").append("<tr><td></td> <td>Informasi </td> </tr>"); 
   // console.log(dataAngkot[0].image); 
    $("#tableInfo").append("<tr><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/"+dataAngkot[0].image+"'></td> <td width=70%>"+dataAngkot[0].ket +"</td> </tr>");
   // console.log(dataAngkot);
  
  });*/

  var Polyline = new google.maps.Polyline({
       path: points ,
       geodesic: true,
       strokeColor:'#FF0000',
       strokeOpacity: 1.0,
       strokeWeight: 2
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

  
  array_polyline[0].setMap(map);
  var bound =  new  google.maps.LatLngBounds();
  
  for (var i = 0; i < points.length; i++) {
    
    bound.extend(points[i]);
          
    make_marker(points[i]);
  }

  for (var i = 0; i < array_marker.length; i++) {
    
    var infowindow = new google.maps.InfoWindow();

    
    var content = "shape_id : "+ data[i].shape_id + "<br> latLng :" +array_marker[i].position+"<br> sequence : "+data[i].shape_pt_sequence;

    google.maps.event.addListener(array_marker[i],'click', (function(marker,content,infowindow){ 
          return function() {
              infowindow.setContent(""+content);
              infowindow.open(map,this);
              
          };
    })(array_marker[i],content,infowindow));

  }
      
      map.fitBounds(bound); 

}




function get_point(terima=1) {
    var result="";
    var kirim = terima;
    
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
     return b;
}

function get_angkot(route_id){
  
  $.ajax({
      url: "http://localhost/webserverangkot/public/api/get_angkot_info",
      method: "GET",
      data: "route_id=" + route_id,  
      success:gotData
  });

}

function gotData(data){
  dataAngkot = data;
}          
       


</script>


@endsection