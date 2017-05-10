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
var cekIdMarker = [];

function initMap() 
{   
  //new google.maps.MVCArray() ;
  $(".table").hide();
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

  $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );

  $("#pilih").on("change", function(){
    var select_pilih = $("#pilih").val();
    pilih_change(select_pilih);
    $(".table").show();

  });

  $("#pilihImport").on("change", function(){
    var select_pilih = $("#pilihImport").val();
    $.ajax({
      url: "http://localhost/webserverangkot/public/api/trips/get_trayek_akbar", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
      method: "GET",
      data: "kirim=" + select_pilih,
      success:function(data) {

        //window.data = data; //ga tau ini buat apa yaa
        clear_marker();
        for (var i = 0; i < data.length; i++) {
          var lat = data[i].shape_pt_lat;
          var lng = data[i].shape_pt_lon;
          var id = data[i].id;
          var LatLng = new google.maps.LatLng(lat,lng);
          
          make_marker(LatLng, data[i].shape_id, '', id );
        }
      }
    });

  });

  $("#button_clear").on('click', function(){
    
    var q = confirm('Batal Edit Data ??');
    if(q == true)
    {
        setMapOnAll(null);
        del_polyline();
        $(".table").hide();
        $("#textarea").val('');
        $('input[type=radio][name=add]').prop("checked", false);
        addMarker = [];
        //hasAddListener = false;
        new_shapes = [] ;
        array_Line.clear();  //new google.maps.MVCArray() ;
        array_shape_id = [];
        cekIdMarker = [];
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
    }
  })

  $("#file").on('change',function(e){
     //console.log( $("#file").val() );
     var isValid = false;
     var diperbolehkan = [".jpg", ".jpeg", ".bmp", ".gif", ".png"];
     var namaGambar = $("#file").val() ; 
     namaGambar = namaGambar.split('fakepath\\');
     namaGambar = namaGambar[1];
     for(i in diperbolehkan){
        if(namaGambar.substr( namaGambar.length - diperbolehkan[i].length , diperbolehkan[i].length ).toLowerCase() == diperbolehkan[i].toLowerCase() && $("#file")[0].files[0].size < 5000  )
        {     
          isValid = true;
        }  
     }

      if( isValid == true){
         $("#image").val('public/images/'+namaGambar ) ;
         $("#image_place").empty();
         //$("#image_place").append("<img class='img-responsive' src='"+ $("#file").val() +"'>");
      }
      else{
          alert('Gambar Harus berformat ".jpg", ".jpeg", ".bmp", ".gif", ".png" dan dibawah 5KB ');
          
          return false;
      }
  });

  $("#button_save").on('click', function(e){   
    //hapus isi textarea dulu
    e.preventDefault();
    var q = confirm('Yakin Akan Memperbarui Data ?')
    if(q == true){
        if($("#route_id").val() == '')
        {
          alert('pilih trayek angkot dulu ');
          $("#button_clear").trigger('click');
          return false;
    
        }

        var isValid = false;
        if( $("#file").val() !== ""  ){ //cek tombol browse menampung file.

          // cek extention file yang ditampung.
          var diperbolehkan = [".jpg", ".jpeg", ".bmp", ".gif", ".png"];
          
          var namaGambar = $("#file").val() ; 
          namaGambar = namaGambar.split('fakepath\\');
          namaGambar = namaGambar[1];
          for(i in diperbolehkan){
            if(namaGambar.substr( namaGambar.length - diperbolehkan[i].length , diperbolehkan[i].length ).toLowerCase() == diperbolehkan[i].toLowerCase() && $("#file")[0].files[0].size < 5000 )
            {
              isValid = true;
            }  
          }

        }
        else{
          isValid = true;
        }

        if(isValid == true)
        {
            var tmp = [];
            for (i in data){
              var lat = points.getAt(i).lat();
              var lng = points.getAt(i).lng()
              data[i].shape_pt_lat = lat.toString();
              data[i].shape_pt_lon = lng.toString() ;
              tmp.push( data[i].shape_id );
            }
            tmp = tmp.join(", ");
            $("#shape_id").val(tmp);
        
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
                alert(data);
                $("#form").submit();
              },
              error:function(jqXHR, textStatus, errorThrown) {
                   console.log(textStatus, errorThrown);
                   alert(textStatus, errorThrown);
                   return false;
                }
            });
        }
        else{
          alert('Gambar Harus berformat ".jpg", ".jpeg", ".bmp", ".gif", ".png" dan dibawah 5KB ')
          return false;
        }
    }
    
  })

  $("#button_delete").on('click', function(e){   
    //hapus isi textarea dulu
    e.preventDefault();
    var q = confirm('Yakin Akan Menghapus ?? ')
    if(q == true){
      var objectData = {
        '_token': $('meta[name=csrf-token]').attr('content'),
        route_id: $("#route_id").val()
      };

      console.log('deleted');
      $.ajax({
        type: "POST",
        url: "http://localhost/webserverangkot/public/delete",
        data: objectData,
        success: function(data){
          console.log(data);
          alert(data);
          location.reload();
        },
        error:function(jqXHR, textStatus, errorThrown) {
             console.log(textStatus, errorThrown);
             alert(textStatus, errorThrown);
             return false;
          }
      });
    }
    
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


var make_marker = function(a, shape_id ='', icon, id='' ){
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
            var originPoint = this.getPosition();
            var oLat = parseFloat(this.getPosition().lat().toFixed(4));
            var oLng = parseFloat(this.getPosition().lng().toFixed(4));
            var object = new google.maps.LatLng(oLat,oLng);

            var tmp = {
              id: id,
              shape_id: shape_id,
              lat: oLat,
              lng: oLng
            };

            if( jQuery.inArray( id , cekIdMarker ) == -1 ){
              cekIdMarker.push(id);
              data.push(tmp);
              points.push(object);
            }else
            {
              return;
            }


        };
  })(marker,content,infowindow));

  google.maps.event.addListener(marker,'rightclick', (function(marker,content,infowindow){ 
        return function() {
            
            addMarker.splice(this, 1);
            this.setMap(null);

        };
  })(marker,content,infowindow));

  
  

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
    google.maps.event.clearListeners(Polyline , "rightclick"); 
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
    google.maps.event.clearListeners(Polyline , "rightclick");    
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

      function index(urutan){
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

      if( typeof data[urutan] == 'undefined' ){
        data.splice(urutan, 0, item);
      }
      
      
              
    });

    google.maps.event.addListener(Polyline.getPath() , "remove_at" , function(urutan){

      function index(urutan){
        for (var i = 0; i < this.getLength(); i++)
        { 
          urutan = i;
        }
        
      }
      console.log(urutan);
      data.splice(urutan, 1);
      
              
    });
    
    google.maps.event.addListener(Polyline , "rightclick" , function(event){
        console.log('clicked');
        var minDist = Number.MAX_VALUE;
        for (var i=0; i<this.getPath().getLength(); i++){
          var distance = google.maps.geometry.spherical.computeDistanceBetween(event.latLng, this.getPath().getAt(i));
          if (distance < minDist) {
            minDist = distance;
            index = i;
          }
        }
        points.removeAt(index);  
        console.log(index);      
    });

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

var clear_marker = function(){
  if(addMarker.length !== 0){
    for (i in addMarker){
      addMarker[i].setMap(null);
    }
  }
}