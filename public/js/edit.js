var data = [];
var angkot;
var array_polyline = [];
var array_marker = [];
var addMarker = [];
var cekAddMarker = "no";
var cekAddLine = "no";
var array_LinePath = [];
var array_shape_id = [];
var sum_shape_id = [];
var toBesorted = [] ; 
var points;
var pointsLokal = [];
var array_Line;
var tmp = '';//[]; // tmp adalah tempat menyimpan polyline changes
var addLine = false;
var new_shapes = [];
var last_shape_id = '';
var arrShapeId = [];
var map;
var Polyline;
var last_id;
var hasAddListener = false;

function initMap() 
{   
  //new google.maps.MVCArray() ;
  map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -6.914838922559386, lng: 107.60765075683594},
        zoom: 13
      });
  array_Line = new google.maps.MVCArray() ;
  //hide();
  $("#file").hide();
  var koordinat = [];  
  points = new google.maps.MVCArray() ;


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
    hasAddListener = false;
    new_shapes = [] ;
    array_Line.clear();  //new google.maps.MVCArray() ;
    array_shape_id = [];
    tmp = '';
    $("#query").empty();
    $("#trips").empty();
    map.setCenter({lat: -6.914838922559386, lng: 107.60765075683594});
    map.setZoom(13);
  
    $("#namaTrayek").val('');
    $("#route_id").val('');
    $('#colorSelector div').css('backgroundColor', '#ffffff' );
    $("#price").val('');
    $("#image").val('');
    $("#keterangan").val('');
    $("#shape_id").val('');
    $("#image_place").empty();
    $("#colorText").text('Change Color');
    $("#trip_headsign").val('');    

  })

  $("#file").on('change',function(){
     //console.log( $("#file").val() );
     var namaGambar = $("#file").val() ; 
     namaGambar = namaGambar.split('fakepath\\');
     $("#image").val('public/images/'+namaGambar[1] ) ;
     $("#image_place").empty();
     $("#image_place").append("<img class='img-responsive' src='"+ $("#file").val() +"'>");

  });

  $("#button_save").on('click', function(e){   
    //hapus isi textarea dulu
    // e.preventDefault();
    if($("#route_id").val() == '')
    {
      alert('pilih trayek angkot dulu ');
      return ;
    }

    for (i in data){
      var lat = points.getAt(i).lat();
      var lng = points.getAt(i).lng()
      data[i].shape_pt_lat = lat.toString();
      data[i].shape_pt_lon = lng.toString() ;
    }

    var objectData = {
      '_token': $('meta[name=csrf-token]').attr('content'),
      data: data,
      route_id: $("#route_id").val()
    }
    $.ajax({
      type: "POST",
      url: "http://localhost/webserverangkot/public/update_points",
      data: objectData,
      success: function(data){
        console.log(data);
      },
      error:function(jqXHR, textStatus, errorThrown) {
           console.log(textStatus, errorThrown);
        }
    });
    
    /*
      console.log("save clicked ");
      var namaTrayek =  $("#namaTrayek").val(); ; 
      var route_id = $("#route_id").val(); 
      var route_color = $("#colorText").text()  ;
      var price = $("#price").val();
      var fare_id = $("#fare_id").val();
      var image =  $("#image").val()  ; // path image
      var file = document.getElementById('file'); //$("#file").val();//.attr('name');
      //file = file.files;
      var shape_id = $("#shape_id").val();
      var keterangan = $("#keterangan").val();
      var token = $('meta[name=csrf-token]').attr('content') ;
      var dataObject = { '_token': token , namaTrayek: namaTrayek, route_id: route_id, route_color: route_color, price: price, fare_id: fare_id, image: image, file:file, shape_id: shape_id, keterangan: keterangan };
    
      var formdata = new FormData();
      formdata.append('_token', token );
      formdata.append('namaTrayek', namaTrayek );
      formdata.append('route_id' , route_id );
      formdata.append('route_color', route_color );
      formdata.append('price', price );
      formdata.append('fare_id', fare_id );
      formdata.append('image', image );
      formdata.append('file', file );
      formdata.append('shape_id', shape_id );
      formdata.append('keterangan', keterangan );
       
      // var frm = $("form");
      // var formdata = new FormData(frm);
      // console.log(formdata);  
      
      $.ajax({
        type: "POST",
        url: "http://localhost/webserverangkot/public/update",
        contentType: false,
        cache:false,
        processData: false,
        data: formdata,//JSON.stringify( dataObject ),
        success: function(data){
          alert("data terupdate " + data );
          console.log(data);
        },
        error:function(jqXHR, textStatus, errorThrown) {
             console.log(textStatus, errorThrown);
          }
      });
      console.log(data);
    */
  })

  

  $("#fare_id").on('change', function(){
    var fare_id = $("#fare_id").val();
    $.ajax({
      type: "POST",
      url: "http://localhost/webserverangkot/public/api/get_fare_rule",
      data: "fare_id="+fare_id,
      success: function(data){
        //console.log(data);
        data = data[0] ;
        //console.log(data);
        $("#price").val(data.price);
      },
      error:function(jqXHR, textStatus, errorThrown) {
           alert(textStatus, errorThrown);
        }
    });
  
  });

  function setMapOnAll(gg) 
  {
    for (var i = 0; i < addMarker.length; i++) 
    {
      addMarker[i].setMap(gg);
      //linePath.setMap(gg);

    }
  }

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

  $('#colorSelector').ColorPicker({
    color: '#0000ff',
    onShow: function (colpkr) {
      $(colpkr).fadeIn(500);
      return false;
    },
    onHide: function (colpkr) {
      $(colpkr).fadeOut(500);
      return false;
    },
    onChange: function (hsb, hex, rgb) {
      $('#colorSelector div').css('backgroundColor', '#' + hex);
      $('#colorText').text( '#' + hex);
      $('#route_color').val('#' + hex );
    }
  });
    
     
} // end initMap


$(window).load(function() {
  last_shape_id = $.ajax({
      url: "http://localhost/webserverangkot/public/api/get_last_shapes_id", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
      method: "GET",
  });

  $.when(last_shape_id).done( function(){
    last_shape_id = last_shape_id.responseJSON ;
    last_id = last_shape_id.id;
    last_shape_id = last_shape_id.shape_id;
    
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
          //draggable:true,
          editable:true
        });
        google.maps.event.addListener(linePath.getPath() , "insert_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "remove_at" , polylineChanged );
        google.maps.event.addListener(linePath.getPath() , "set_at" , polylineChanged );
        array_LinePath.push(linePath); //tempat polyline.
      
    }  
    
  });
        
  
  

  return marker;    
}

function pilih_change(route_id = 1){
  
  data = get_point(route_id);
  angkot = get_angkot( route_id );
  for (var i = 0; i < array_LinePath.length; i++) {
      array_LinePath[i].setMap(null);
    }

  if(hasAddListener == true){
    google.maps.event.clearListeners(Polyline.getPath() , "set_at");
    google.maps.event.clearListeners(Polyline.getPath() , "insert_at");
    google.maps.event.clearListeners(Polyline.getPath() , "remove_at");
  }


  $.when(data, angkot).done( function()
  { 
    
    data = data.responseJSON ;
    angkot = angkot.responseJSON ;
    var icons = {
      path: google.maps.SymbolPath.CIRCLE,
      scale: 5
    };
  
    //var points =  ;
    pointsLokal = [];
    points.clear() ; 
    var array_info = [];
  
    for (var i = 0; i < data.length; i++) {
      var olat = Number( data[i].shape_pt_lat );
      var olng = Number( data[i].shape_pt_lon );
      var ob = new google.maps.LatLng(olat,olng);
      pointsLokal.push( {lat:olat, lng:olng} );
      points.push( ob );
      //points.push( {lat:lat, lng:lng} );
      
  
    }
  
    Polyline = new google.maps.Polyline({
         path: points ,
         geodesic: true,
         strokeColor: angkot[0].route_color,
         strokeOpacity: 1.0,
         strokeWeight: 2,
         editable:true
    });
    Polyline.setMap(map) ;
    array_LinePath.push(Polyline); //tempat untuk hapus array
    
    var tmpposisi;
    var urutan;
    var tempData;
    google.maps.event.clearListeners(Polyline.getPath() , "set_at");
    google.maps.event.clearListeners(Polyline.getPath() , "insert_at");
    google.maps.event.clearListeners(Polyline.getPath() , "remove_at");
    
    hasAddListener = true; // sudah punya add listener
    
    google.maps.event.addListener(Polyline.getPath() , "set_at" , function( urutan){
      
      function index(urutan){
        for (var i = 0; i < this.getLength(); i++)
        { 
          urutan = i;
          //tmpposisi = this.getAt(urutan).toUrlValue(6);
        }
        
      }
      //console.log(tmpposisi,urutan.toUrlValue(4), data[ Number( tmpposisi) ] );
      console.log(urutan);
      data[urutan].shape_pt_lat = points.getAt(urutan).lat();
      data[urutan].shape_pt_lon = points.getAt(urutan).lng();
      
        
    });

    google.maps.event.addListener(Polyline.getPath() , "insert_at" , function(urutan){

      function index(tmpposisi, urutan){
        for (var i = 0; i < this.getLength(); i++)
        { 
          urutan = i;
        }
        
      }
      console.log(urutan);
      last_shape_id = ++last_shape_id;
      var item = {
        id: '', // id gausah di set, auto increment
        jalur:'',
        place_info:'',
        shape_dist_travel:'',
        shape_id: last_shape_id,
        shape_pt_lat: points.getAt(urutan).lat(),
        shape_pt_lon: points.getAt(urutan).lng(),
        shape_pt_sequence: 0
      } ;
      data.splice(urutan, 0, item);
      
              
    });

    

    /*for (var i = 0; i < data.length; i++) {
      var infowindow = new google.maps.InfoWindow();
      var content = data[i].shape_id;
      google.maps.event.addListener(points.getAt(i),'click', (function(points,content,infowindow){ 
              return function() {
                  infowindow.setContent(""+content);
                  infowindow.open(map,this);
                  //console.log( this );
              };
        })(points.getAt(i),content,infowindow));
  
    }*/

    //Isi form dengan data angkot
    $("#namaTrayek").val(""+angkot[0].trip_short_name);
    $("#route_id").val(""+angkot[0].route_id);
    $('#colorSelector div').css('backgroundColor', angkot[0].route_color );
    $('#colorText').empty();
    if(angkot[0].route_color == '' || angkot[0].route_color == null ){
      angkot[0].route_color = 'Change Color';
    }
    $('#colorText').text(angkot[0].route_color);
    
    $('#route_color').empty();
    $('#route_color').val(angkot[0].route_color);
    $("#price").val(""+angkot[0].price);
    $("#fare_id").val(angkot[0].fare_id);
    $("#image").val(""+angkot[0].image);
    $("#keterangan").val(""+angkot[0].ket);
    $("#shape_id").val(""+angkot[0].shape_id);
    $("#image_place").empty();
    $("#image_place").append("<img class='img-responsive' src='http://localhost/webserverangkot/"+angkot[0].image+"'>")
    $("#trip_headsign").val(angkot[0].trip_headsign);
    var bound =  new google.maps.LatLngBounds();
    for (var i = 0; i < pointsLokal.length; i++) {
      bound.extend(pointsLokal[i]);
    }
    map.fitBounds(bound);
    map.setZoom(13);
    map.setCenter( pointsLokal[pointsLokal.length / 2] ); // setCenter di tengah Array pointsLokal

    

  }) ;
}



function get_point(terima=1) {
 
  return  $.ajax({
      url: "http://localhost/webserverangkot/public/api/trips/get_trayek_akbar", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
      method: "GET",
      data: "kirim=" + terima
    });
 
}          
       
function get_angkot(route_id){
  return $.ajax({
              url: "http://localhost/webserverangkot/public/api/get_angkot_info",
              method: "GET",
              data: "route_id=" + route_id
         });
}