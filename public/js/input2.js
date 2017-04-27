var data = [];
var array_polyline = [];
var array_marker = [];
var addMarker = [];
var cekAddMarker = "no";
var cekAddLine = "no";
var array_LinePath = [];
var array_shape_id = [];
var sum_shape_id = [];
var toBesorted = [] ; 

var array_Line;
var tmp = '';//[]; // tmp adalah tempat menyimpan polyline changes
var addLine = false;
var new_shapes = [];
var last_shape_id = '';
var arrShapeId = [];
var map;

function initMap() 
{   
  //new google.maps.MVCArray() ;
  map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -6.914838922559386, lng: 107.60765075683594},
        zoom: 13
      });
  array_Line = new google.maps.MVCArray() ;
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

  $("#button_clear").on('click', function(){
    
    
    setMapOnAll(null);
    del_polyline();
    $("#textarea").val('');
    $('input[type=radio][name=add]').prop("checked", false);
    addMarker = [];
    data = []
    new_shapes = [] ;
    array_Line.clear();  //new google.maps.MVCArray() ;
    array_shape_id = [];
    tmp = '';
    $("#query").empty();
    $("#trips").empty();
    map.setCenter({lat: -6.914838922559386, lng: 107.60765075683594});
    map.setZoom(13);
  })

  $("#button_save").on('click', function(){   
    //hapus isi textarea dulu
    $("#query").empty();
   
    var temp = array_Line.getArray();
    var temp2 = [];
    new_shapes = [];
    for (var i = 0; i < temp.length; i++) {
      temp2[i] = temp[i].toUrlValue(6) ;
      if(jQuery.inArray(temp2[i], new_shapes ) == -1 && tmp[i] !== '' ){
         
          new_shapes.push( [temp2[i] , array_shape_id[i][1] ] ) ;

        }
    }

    setMapOnAll(null);
    del_polyline();
    $('input[type=radio][name=add]').prop("checked", false);
    $("#trips").empty();
    //array_Line.clear();

    for (var i = 0; i < new_shapes.length; i++) {
      if(new_shapes[i][1] !== null ){
        var shapes_id = new_shapes[i][1] ; //Number( last_shape_id ) + ( i+1 ) ;
      }else{
        var shape_id = Number( last_shape_id ) + ( i+1 ) ;
      }
      var tmp2 = new_shapes[i][0].split(",");
      var lat = Number( tmp2[0] ) ;
      var lng = Number( tmp2[1] ) ;
      toBesorted.push( { lat:lat, lng:lng, shape_id:shapes_id } ) ;
      var position = { lat:lat, lng:lng } ;
      
      make_marker( position, shapes_id );
      var query  = "INSERT INTO shapes "+
      " VALUES ('',"+shapes_id+","+lat+","+lng+",0,'','','') " // jalur dan place_info masih dikosongkan
      $("#trips").append(shapes_id+", ");
      $("#query").append(query+"<br>");
    }

    
    
  })

  function setMapOnAll(gg) 
  {
    for (var i = 0; i < addMarker.length; i++) 
    {
      addMarker[i].setMap(gg);
      //linePath.setMap(gg);

    }
  }

  $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );
  
  function del_polyline(){
    for (var i = 0; i < array_LinePath.length; i++) {
      array_LinePath[i].setMap(null);
    }
  }

  
    $('input:radio').change(function () {
      // body...
      if (this.value == 'addMarker') {
          console.log("addMarker selected");
      }
      else if(this.value == 'addLine'){
        

      }
    });


    

    map.addListener("click", function(event){

      if($("#addMarker").is(':checked'))
      {
        make_marker(event.latLng);
        
      }

    });  
      


    

     
    
     
} // end initMap


$(window).load(function() {
  last_shape_id = $.ajax({
      url: "http://localhost/webserverangkot/public/api/get_last_shapes_id", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
      method: "GET",
  });

  $.when(last_shape_id).done( function(){
    last_shape_id = last_shape_id.responseText ;
    $(".se-pre-con").fadeOut("slow");
  });


    
});


var make_marker = function(a, shape_id ='', icon ){
  var marker = new google.maps.Marker({
          position: a,
          map: map,
          icon: icon
          //draggable:true
      });
  addMarker.push(marker);
  

  var infowindow = new google.maps.InfoWindow();
  if(shape_id == ''){
    var shape_id = Number(last_shape_id) + addMarker.length ;
  }
  else{
    shape_id = shape_id;
  }
  var content = "no : "+ addMarker.length + "<br> shape_id : "+shape_id+" <br> latLng :" +marker.position;



  google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
        return function() {
            infowindow.setContent(""+content);
            infowindow.open(map,this);
            //console.log( this );
        };
  })(marker,content,infowindow));

  google.maps.event.addListener(marker,'rightclick', (function(marker,content,infowindow){ 
        return function() {
            
            addMarker.splice(this, 1);
            this.setMap(null);

        };
  })(marker,content,infowindow));

  var polylineChanged = function(evt){
    //document.getElementById('info').innerHTML = '';//"polyline points:" + "<br>";
    for (var i = 0; i < this.getLength(); i++) {
      //document.getElementById('info').innerHTML += this.getAt(i).toUrlValue(6) + "<br>";
      tmp = tmp + this.getAt(i).toUrlValue(6) + " / " ;
      if(jQuery.inArray(this.getAt(i).toUrlValue(6) , array_shape_id ) == -1 ){
        array_shape_id.splice( i, i,  [ this.getAt(i).toUrlValue(6) , shape_id ] ) ;
        console.log(array_shape_id) ; 
      }
    }
    
  }
   
  google.maps.event.addListener(marker,'click', function(){ 
    if($("#addLine").is(":checked") )
    {
      var originPoint = this.getPosition();
      var oLat = parseFloat(this.getPosition().lat().toFixed(4));
      var oLng = parseFloat(this.getPosition().lng().toFixed(4));
      var object = new google.maps.LatLng(oLat,oLng);

      
      if(jQuery.inArray(oLat+","+oLng, array_shape_id ) == -1  ){ //kalau sudah di klik, g bs d klik lagi
          array_Line.push(object);
          array_shape_id.push([oLat+","+oLng, shape_id ]) ;
          //new_shapes.push( oLat+","+oLng ) ;
          $("#trips").append(shape_id +", ");
          //console.log(array_shape_id, shape_id);
      }
      
      

      
        var linePath = new google.maps.Polyline({
          path: array_Line ,
          strokeColor: '#4A484D',
          strokeOpacity: 1.0,
          strokeWeight: 2,
          geodesic: true,
          map: map,
          draggable:true,
          editable:true
        });
        google.maps.event.addListener(linePath.getPath() , "insert_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "remove_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "set_at" , polylineChanged );
        array_LinePath.push(linePath); //tempat polyline.
      //   array_shape_id = [];
      //   array_Line = [];

      /*if(array_shape_id.length == 2)
      {
        var linePath = new google.maps.Polyline({
          path: array_Line ,
          strokeColor: '#4A484D',
          strokeOpacity: 1.0,
          strokeWeight: 2,
          geodesic: true,
          map: map,
          draggable:true,
          editable:true
        });
        google.maps.event.addListener(linePath.getPath() , "insert_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "remove_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "set_at" , polylineChanged );
        
        //sum_shape_id.push(array_shape_id);
        array_LinePath.push(linePath); //tempat polyline.
        array_shape_id = [];
        array_Line = [];
        alert("marker berhasil tersambung");
  
      } */ 
    }  
    
  });
        
  
  

  return marker;    
}

function pilih_change(route_id = 1){
  
  data = get_point(route_id);
  
  var icons = {
    path: google.maps.SymbolPath.CIRCLE,
    scale: 5
  };

  var points = [];
  var array_info = [];

  for (var i = 0; i < data.length; i++) {
    var lat = Number( data[i].shape_pt_lat );
    var lng = Number( data[i].shape_pt_lon );
    points.push( {lat:lat, lng:lng} );
    

  }

  //hapus marker sebelumnya
  for (var i = 0; i < array_marker.length; i++) {
    
    array_marker[i].setMap(null) ;
  }

  array_marker = [];
  //buat marker baru
  for (var i = 0; i < points.length; i++) {
    
      array_marker.push( make_marker( points[i], data[i].shape_id, icons  ) );

      /*var infowindow = new google.maps.InfoWindow();

      var content = "shape_id : "+ data[i].shape_id + "<br> shape_pt_sequence : " + data[i].shape_pt_sequence ;
      google.maps.event.addListener(array_marker[i],'click', (function(marker,content,infowindow){ 
            return function() {
                infowindow.setContent(""+content);
                infowindow.open(map,this);
            };
          })(array_marker[i],content,infowindow));*/
  }

  var Polyline = new google.maps.Polyline({
       path: points ,
       geodesic: true,
       strokeColor:'#FF0000',
       strokeOpacity: 1.0,
       strokeWeight: 2
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
       
