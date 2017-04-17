@extends('layouts.trayek_html')

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
  var array_polyline = [];
  var array_marker = [];
  var dataAngkot;
  var addMarker=[];
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
    
    var points = [];
    var array_info = [];

    for (var i = 0; i < data.length; i++) {
      var lat = Number( data[i].shape_pt_lat );
      var lng = Number( data[i].shape_pt_lon );
      points.push( {lat:lat, lng:lng} );
    }

    get_angkot(route_id);
    //console.log(dataAngkot);
    

    $(document).ajaxComplete(function(){
      $("#tableInfo").empty();
      //$("#tableInfo").append("<tr><td></td> <td>Informasi </td> </tr>"); 
      //console.log(dataAngkot[0].image); 
      $("#tableInfo").append("<tr><td valign='middle' width='30%'><img class='img-responsive' src='http://localhost/webserverangkot/"+dataAngkot[0].image+"'></td> <td width=70%>"+dataAngkot[0].ket +"</td> </tr>");
      $("#tableInfo").append("<tr> <td>Harga </td> <td>Rp. "+dataAngkot[0].price+"</td> </tr>");
      //console.log(dataAngkot);

      var Polyline = new google.maps.Polyline({
         path: points ,
         geodesic: true,
         strokeColor: dataAngkot[0].route_color, //'#FF0000',
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

      
      addMarker.push( make_marker(points[0] ) );

      addMarker.push ( make_marker( points[ points.length - 1 ] , "http://maps.google.com/mapfiles/ms/icons/green-dot.png", 'finish' ) );
      
      

      array_polyline[0].setMap(map);
      var bound =  new  google.maps.LatLngBounds();
      for (var i = 0; i < points.length; i++) {
        //array_marker[i]
        bound.extend(points[i]);
      }
        
        map.fitBounds(bound);
    
    });

    


     

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