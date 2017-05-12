var map;
var new_line;
var array_Line; // old array
var array_shape_id = [];
var data = []; // tempat nampung shape_id lama, ketika user pilih select.
var newData = []; // tempat input shape_id baru, ketika user klik map.
var last_shape_id;
var last_route_id;
var last_id;
var array_marker = [];
var hasAddListener = false;
var cekIdMarker = [];

function initMap() 
{   
  new_line = new google.maps.MVCArray() ;
  array_Line = new google.maps.MVCArray() ;
  $(".file").hide();
  map = new google.maps.Map(document.getElementById('map'), {
    center: {lat: -6.914838922559386, lng: 107.60765075683594},
    zoom: 13
  });

  var Polyline = new google.maps.Polyline({
    path: array_Line ,
    strokeColor: '#4A484D',
    strokeOpacity: 1.0,
    strokeWeight: 2,
    geodesic: true,
    map: map,
    editable:true
  });

  //coding sidebar menu
  $("#menu-toggle").on('click',function(){
      if($("#navbar").is(":visible") ){
        $("#navbar").fadeOut();
      }
      else
      {
        $("#navbar").fadeIn();
      }
  })

  //coding sidebar menu
  $("#navbar").hover( function(){$("#navbar").fadeIn();}, function(){$("#navbar").fadeOut();} );

  //clear button
  $("#button_clear").on('click', function(e){
    e.preventDefault();
    var q = confirm('Yakin Akan Input Ulang Data ?');
    if( q == true)
    {
        if(hasAddListener == true)
        {
          google.maps.event.clearListeners(Polyline.getPath() , "set_at");
          google.maps.event.clearListeners(Polyline.getPath() , "insert_at");
          google.maps.event.clearListeners(Polyline.getPath() , "remove_at");
        }
        $("#namaTrayek").val('');
        $("#noTrayek").val('');
        $("#shape_id").val('');
        $("#keterangan").val('');
        $("#price").val('');
        $("#image").val('');
        array_Line.clear();
        data = [];
        cekIdMarker = [];
        clear_marker();
    
    
    
        newData = [];
        map.setCenter({lat: -6.914838922559386, lng: 107.60765075683594});
        map.setZoom(13);
    }
  })



  $("#pilih").on("change", function(){
    var select_pilih = $("#pilih").val();
    $.ajax({
      url: "http://localhost/webserverangkot/public/api/trips/get_trayek_akbar", //host + "/dbangkot3/index.php/welcome/pilih_jalur_tampil" ,
      method: "GET",
      data: "kirim=" + select_pilih,
      success:function(data) {

        window.data = data;
        clear_marker();
        for (var i = 0; i < data.length; i++) {
          var lat = data[i].shape_pt_lat;
          var lng = data[i].shape_pt_lon;
          var id = data[i].id;
          var LatLng = new google.maps.LatLng(lat,lng);
          //var icon = "http://maps.google.com/mapfiles/kml/shapes/road_shield3.png";
          var icon = {
            url: "http://maps.google.com/mapfiles/kml/shapes/road_shield3.png",
            // This marker is 20 pixels wide by 32 pixels high.
            //size: new google.maps.Size(16, 16),
            // The origin for this image is (0, 0).
            //origin: new google.maps.Point(0, 0),
            // The anchor for this image is the base of the flagpole at (0, 32).
            //anchor: new google.maps.Point(0, 32)
            scaledSize: new google.maps.Size(15, 15)
          };
          make_marker(LatLng, data[i].shape_id, icon, id );
        }
      }
    });

  });

  map.addListener("click", function(event){

    array_Line.push(event.latLng);
    /*last_shape_id = last_shape_id + 1;
    var tmp = {
      id: '',
      shape_id: last_shape_id,
      shape_pt_lat: event.latLng.lat(),
      shape_pt_lon: event.latLng.lng()
    };
    newData.push(tmp);*/

  });

  

  $("#button_save").on('click', function(e){
      e.preventDefault();
      if( $("#namaTrayek").val() == '' || $("#noTrayek").val() == '' )
      {
        alert('Mohon isi semua data terlebih dahulu.');
        return ;
      }
      //fungsinya apabila ada perubahan array line, baik itu insertAt, removeAt, atau setAt 
      // kalau tidak ada, memang wasting time
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
        //isValid = true;
        alert('Mohon Pilih Gambar terlebih dahulu')

        return false;
      }

      if(isValid == true)
      {
          for (var i = 0; i < array_Line.getArray().length; i++) {
            newData[i].shape_pt_lat = array_Line.getAt(i).lat();
            newData[i].shape_pt_lon = array_Line.getAt(i).lng();
            
          }
    
          var objectData = {
            '_token': $('meta[name=csrf-token]').attr('content'),
            data: newData,
            route_id: $("#route_id").val()
          }
    
          
          var tmp = [];
          for (i in newData)
          {
            tmp.push( newData[i].shape_id ) ;
          }
          tmp = tmp.join(', ');
          $("#shape_id").val(tmp);    
          console.log(objectData);
    
          $.ajax({
            type: "POST",
            url: "http://localhost/webserverangkot/public/insert_points",
            data: objectData,
            success: function(data){
              console.log(data);
              $("#form").submit();
            },
            error:function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
                 return false;
              }
          });
      
      }
      else{
          alert('Gambar Harus berformat ".jpg", ".jpeg", ".bmp", ".gif", ".png" dan dibawah 5KB ')
          return false;
      }
  });

  /*$("#file").on('change',function(){
     //console.log( $("#file").val() );
     var namaGambar = $("#file").val() ; 
     namaGambar = namaGambar.split('fakepath\\');
     $("#image").val('public/images/'+namaGambar[1] ) ;
     $("#image_place").empty();

     $("#image_panel").val('public/images/'+namaGambar[1] ) ;
     $("#image_place_panel").empty();     

  });*/
  $(".file").on('change',function(e){
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
         $("#image_panel").val('public/images/'+namaGambar ) ;
      }
      else{
          alert('Gambar Harus berformat ".jpg", ".jpeg", ".bmp", ".gif", ".png" dan dibawah 5KB ');
          
          return false;
      }
  });

  $("#fare_id").on('change', function(){
    var fare_id = $("#fare_id").val();
    $.ajax({
      type: "POST",
      url: "http://localhost/webserverangkot/public/api/get_fare_rule",
      data: "fare_id="+fare_id,
      success: function(data){
        data = data[0] ;
        $("#price").val(data.price);
        $("#price_panel").val(data.price);
        
      },
      error:function(jqXHR, textStatus, errorThrown) {
           alert(textStatus, errorThrown);
        }
    });
  
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
    newData[urutan].shape_pt_lat = array_Line.getAt(urutan).lat();
    newData[urutan].shape_pt_lon = array_Line.getAt(urutan).lng();
    
      
  });

  google.maps.event.addListener(Polyline.getPath() , "insert_at" , function(urutan){
    console.log('insert_at');
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
      shape_id: last_shape_id,
      shape_pt_lat: array_Line.getAt(urutan).lat(),
      shape_pt_lon: array_Line.getAt(urutan).lng(),
      shape_pt_sequence: 0
    } ;

    if( typeof newData[urutan] == 'undefined' ){
      newData.splice(urutan, 0, item);
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
    newData.splice(urutan, 1);
    
            
  });

} // tutup initMap

$(document).ready(function(){
    //$('[data-toggle="popover"]').popover();
    $("#fare_id_panel").on('change', function(){
      var fare_id = $("#fare_id_panel").val();
      $("#fare_id").val(fare_id);
      $("#fare_id").trigger('change');
    });
    // $('#file').hide();
    /*$("#file_panel").hide();

    $("#file_panel").on('change',function(){
       //console.log( $("#file").val() );
       var namaGambar = $("#file_panel").val() ; 
       namaGambar = namaGambar.split('fakepath\\');
       $("#image_panel").val('public/images/'+namaGambar[1] ) ;
       $("#image_place_panel").empty();
       //file di isi langsung ketika pilih gambar pertama
       $("#file").val( namaGambar );
    });*/

    $("#button_clear_panel").on('click' , function(e){
        e.preventDefault();
        var q = confirm('Yakin Akan Input Ulang Data ?')
        if(q == true){
                $("#namaTrayek_panel").val('');
                $("#noTrayek_panel").val('');
                
                $("#keterangan_panel").val('');
                $("#price_panel").val('');
                //$("#image").val('');
                $("#image_panel").val('');
                
                $('#colorSelector_panel div').css('backgroundColor', '');
                $('#colorText_panel').text('Changes Color (Click Me !)');
                $('#route_color_panel').val('');
                cekIdMarker = [];
        }
    });

    
    $("#button_cancel_panel").on('click' , function(e){
        e.preventDefault();
        $(".overlay").hide();
    });

    $('#colorSelector_panel').ColorPicker({
      color: '#0000ff',
      onShow: function (tt) {
        $(tt).css("z-index", "9999");
        $(tt).fadeIn(500);
        return false;
      },
      onHide: function (tt) {
        $(tt).css("z-index", "9999");
        $(tt).fadeOut(500);
        return false;
      },
      onChange: function (hsb, hex, rgb) {
        $('#colorSelector_panel div').css('backgroundColor', '#' + hex);
        $('#colorText_panel').text( '#' + hex);
        $('#route_color_panel').val('#' + hex );
        //langsung ngaruh ke layer berikutnya
        $('#colorSelector div').css('backgroundColor', '#' + hex);
        $('#colorText').text( '#' + hex);
        $('#route_color').val('#' + hex );

      }
    });

    $('#button_save_panel').on('click', function(e){
        e.preventDefault();
        if($("#noTrayek_panel").val() == '' || $("#namaTrayek_panel").val() == '' || $('#route_color_panel').val() =='' || $("#file").val() == '' )
        {
          alert("mohon isi semua data terlebih dahulu");
          return ;
        }
        $("#noTrayek").val($("#noTrayek_panel").val());
        $("#namaTrayek").val($("#namaTrayek_panel").val());
        $("#keterangan").val($("#keterangan_panel").val());
        $("#price").val( $("#price_panel").val() );
        alert("silahkan buat jalur dengan klik di peta");
        $(".overlay").hide();
    });

});

var make_marker = function(a, shape_id ='', icon, id='' ){
  var marker = new google.maps.Marker({
          position: a,
          map: map,
          icon: icon
          //draggable:true
      });
  array_marker.push(marker);
  

  var infowindow = new google.maps.InfoWindow();
  if(shape_id == ''){
    var shape_id = Number(last_shape_id) + addMarker.length ;
  }
  else{
    shape_id = shape_id;
  }


  var content = "shape_id : "+shape_id+" <br> latLng :" +marker.position;



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
              shape_pt_lat: oLat,
              shape_pt_lon: oLng
            };

            if( jQuery.inArray( id , cekIdMarker ) == -1 ){
              cekIdMarker.push(id);
              newData.push(tmp);
              array_Line.push(object);
            }else
            {
              return;
            }


        };
  })(marker,content,infowindow));

  google.maps.event.addListener(marker,'rightclick', (function(marker,content,infowindow){ 
        return function() {
            
            data.splice(this, 1);
            this.setMap(null);

        };
  })(marker,content,infowindow));

  
   
  /*google.maps.event.addListener(marker,'click', function(){ 
    
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
          draggable:true
        });
    
  });*/
        
  
  

  return marker;    
}

$(window).load(function() {
  
    last_shape_id = $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_last_shapes_id", 
        method: "GET",
    });

    last_route_id = $.ajax({
        url: "http://localhost/webserverangkot/public/api/get_last_route_id", 
        method: "GET",
    });

    $.when(last_shape_id, last_route_id).done( function(){
      last_shape_id = last_shape_id.responseJSON ;
      last_route_id = last_route_id.responseJSON ;

      last_route_id = last_route_id.route_id;
      last_route_id = last_route_id + 1;
      $("#route_id").val(last_route_id);
      $("#route_id_panel").val(last_route_id);
      last_id = last_shape_id.id;
      last_shape_id = last_shape_id.shape_id;
      
      $(".se-pre-con").fadeOut("slow");
    });
  
});

var clear_marker = function(){
  if(array_marker.length !== 0){
    for (i in array_marker){
      array_marker[i].setMap(null);
    }
  }
}