<!DOCTYPE html> 
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ Session::token() }}"> 
    
    <title>Aplikasi Angkot Bandung</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/jquery-ui-1.10.0.custom.css" rel="stylesheet">
    
    <style type="text/css">
      #map {
        color: black;
        height: 100%;
        width: 100%;
        position: absolute;
      }

      #floating-panel {
        position: absolute;
        bottom: 1%;
        left: 3%;

        opacity: 50%;
        z-index: 5;
        background-color: #ccg;
        padding: 5px;
        border: 1px solid #999;
        text-align: center;
        font-family: 'Roboto','sans-serif';
        line-height: 30px;
        padding-left: 10px;
      }

      
      .no-js #loader { display: none;  }
      .js #loader { display: block; position: absolute; left: 100px; top: 0; }

        .se-pre-con {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%; 
        z-index: 9999; 
        background: url(images/loader-64x/Preloader_2.gif) center no-repeat #fff;
        }

      html,body,.container {
          height:100%;
      }
      .container {
          display:table;
          width: 100%;
          margin-top: -50px;
          padding: 50px 0 0 0; /*set left/right padding according to needs*/
          box-sizing: border-box;
      }
      .body {
          display: table;
          background-color: green;
      }

      .left-side {
          background-color: blue;
          float: none;
          display: table-cell;
          border: 1px solid;
      }

      .right-side {
          background-color: red;
          float: none;
          display: table-cell;
          border: 1px solid;
      }

        /* EXAMPLE 8 - Center on mobile*/
        @media only screen and (max-width : 768px){
        .example-8 .navbar-brand {
        padding: 0px;
        transform: translateX(-50%);
        left: 50%;
        position: absolute;
      }
      .example-8 .navbar-brand>img {
        height: 100%;
        width: auto;
        padding: 7px 14px; 
      }
      }


      /* EXAMPLE 8 - Center Background */
      .example-8 .navbar-brand {
        background: url(http://res.cloudinary.com/candidbusiness/image/upload/v1455406304/dispute-bills-chicago.png) center / contain no-repeat;
        width: 200px;
        transform: translateX(-50%);
        left: 50%;
        position: absolute;
      }

        nav.sidebar, .main{
          -webkit-transition: margin 200ms ease-out;
            -moz-transition: margin 200ms ease-out;
            -o-transition: margin 200ms ease-out;
            transition: margin 200ms ease-out;
        }

        .main{
          padding: 10px 10px 0 10px;
        }

       @media (min-width: 765px) {

          .main{
            position: absolute;
            width: calc(100% - 40px); 
            margin-left: 40px;
            float: right;
          }

          nav.sidebar:hover + .main{
            margin-left: 200px;
          }

          nav.sidebar.navbar.sidebar>.container .navbar-brand, .navbar>.container-fluid .navbar-brand {
            margin-left: 0px;
          }

          nav.sidebar .navbar-brand, nav.sidebar .navbar-header{
            text-align: center;
            width: 100%;
            margin-left: 0px;
          }
          
          nav.sidebar a{
            padding-right: 13px;
          }

          nav.sidebar .navbar-nav > li:first-child{
            border-top: 1px #e5e5e5 solid;
          }

          nav.sidebar .navbar-nav > li{
            border-bottom: 1px #e5e5e5 solid;
          }

          nav.sidebar .navbar-nav .open .dropdown-menu {
            position: static;
            float: none;
            width: auto;
            margin-top: 0;
            background-color: transparent;
            border: 0;
            -webkit-box-shadow: none;
            box-shadow: none;
          }

          nav.sidebar .navbar-collapse, nav.sidebar .container-fluid{
            padding: 0 0px 0 0px;
          }

          .navbar-inverse .navbar-nav .open .dropdown-menu>li>a {
            color: #777;
          }

          nav.sidebar{
            width: 200px;
            height: 100%;
            margin-left: -160px;
            float: left;
            margin-bottom: 0px;
          }

          nav.sidebar li {
            width: 100%;
          }

          nav.sidebar:hover{
            margin-left: 0px;
          }

          .forAnimate{
            opacity: 0;
          }
        }
         
        @media (min-width: 1330px) {

          .main{
            width: calc(100% - 200px);
            margin-left: 200px;
          }

          nav.sidebar{
            margin-left: 0px;
            float: left;
          }

          nav.sidebar .forAnimate{
            opacity: 1;
          }
        }

        nav.sidebar .navbar-nav .open .dropdown-menu>li>a:hover, nav.sidebar .navbar-nav .open .dropdown-menu>li>a:focus {
          color: #CCC;
          background-color: transparent;
        }

        nav:hover .forAnimate{
          opacity: 1;
        }
        section{
          padding-left: 15px;
        }   
    </style>
  </head>
  <body>

    <nav id="navbar" hidden="true" class="navbar navbar-default sidebar" role="navigation" style="z-index: 999; position: absolute;  height: 100%;  ">
        <div class="container-fluid" >
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>      
            </div>
            <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1">
              <ul class="nav navbar-nav">
                <li class="active"><a href="http://localhost/webserverangkot/public/">Home<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-home"></span></a></li>

                <li><a href="http://localhost/webserverangkot/public/trayek">Info Angkutan Umum<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-th-list"></span></a></li>
                @if (!Auth::guest())
                  <li>
                    <a href="http://localhost/webserverangkot/public/edit">Edit Angkutan Umum<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-pencil"></span>
                    </a>
                  </li>
                  
                  <li>
                    <a href="http://localhost/webserverangkot/public/input">Input Angkutan Umum<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-plus"></span>
                    </a>
                  </li>
                  
                  <li>
                      <a href="{{ url('/logout') }}"
                          onclick="event.preventDefault();
                                   document.getElementById('logout-form').submit();">
                          Logout<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-log-out"></span>
                      </a>

                      <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                          {{ csrf_field() }}
                      </form>
                  </li>
                  
                @endif
            </div>
        </div>
    </nav>

    <div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="z-index: 998">
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="background-image: url('http://localhost/webserverangkot/public/images/header.png'); background-size: 100% 100%; ">
                <div class="navbar-header">
                    <button type="button" class=" navbar-toggle button navbar-left"  data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" id="menu-toggle" style="float: left; margin-left: 10px; "  >
                        <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                
                <div class="collapse navbar-collapse">
                  <ul class="nav navbar-nav navbar-left ">
                        
                        @if (!Auth::guest())
                          <li class="dropdown" >
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Admin<span class="caret"></span></a>
                            <ul class="dropdown-menu" >
                                <li>
                                  <a href="http://localhost/webserverangkot/public/" > <span class="glyphicon glyphicon-home" ></span>  Cari Rute Angkot </a> 
                                </li>
                                
                                <li> 
                                  <a href="http://localhost/webserverangkot/public/trayek"> <span class="glyphicon glyphicon-th-list" ></span> Info Angkutan Umum  </a>
                                </li>
                                
                                <li>
                                  <a href="http://localhost/webserverangkot/public/edit"> <span class="glyphicon glyphicon-pencil"></span> Edit Angkutan Umum
                                  </a>
                                </li>

                                <li>
                                  <a href="http://localhost/webserverangkot/public/input"><span class="glyphicon glyphicon-plus"></span> Input Angkutan Umum
                                  </a>
                                </li>
                                
                                <li>
                                    <a href="{{ url('/logout') }}"
                                        onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                        <span style="font-size:16px;" class="hidden-xs showopacity glyphicon glyphicon-log-out"></span>
                                        Logout
                                    </a>

                                    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                            
                          </li>
                        @else
                          <li>
                            <a href="http://localhost/webserverangkot/public/" > <span class="glyphicon glyphicon-home" ></span>  Cari Rute Angkot </a> 
                          </li>
                          
                          <li> 
                            <a href="http://localhost/webserverangkot/public/trayek"> <span class="glyphicon glyphicon-th-list" ></span> Info Angkutan Umum  </a>
                          </li>
                        @endif
                  </ul>

                </div>
                
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 pre-scrollable" style="max-height: 550px" >
            

            <form id="form"  enctype="multipart/form-data" action="http://localhost/webserverangkot/public/update" method="POST" enctype="multipart/form-data">
              {{csrf_field()}}

              <label for="exampleInputEmail1">
                  Trayek Angkot
              </label>
              <select class="form-control" id="pilih" name="pilih">
              <option value=99> All </option>
              <?php foreach ($trip as $a): ?>
              <option value= <?php echo $a['route_id']; ?> > <?php echo $a['trip_short_name'].". ".$a['trip_headsign']; ?> </option>  
              <?php endforeach ?> 
              </select>
              <br>

              @include('layouts.flash')               
              
                <table class="table" >
                  <tr hidden="hidden">
                    <td ><label for="exampleInputPassword1">
                          Route Id
                      </label></td>
                    <td><input type="input" name="route_id" id="route_id" class="form-control" ></td>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1" > Import Data Angkot </label> </td>
                    <td>
                      <select class="form-control" id="pilihImport" name="pilihImport">
                      <option value=99> All </option>
                      <?php foreach ($trip as $a): ?>
                      <option value= <?php echo $a['route_id']; ?> > <?php echo $a['trip_short_name'].". ".$a['trip_headsign']; ?> </option>  
                      <?php endforeach ?> 
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1">
                          No Trayek
                      </label></td>
                    <td><input type="input" name="namaTrayek" id="namaTrayek" class="form-control" placeholder="Silahkan isikan no trayek. Contoh: 01"></td>
                  </tr>     
                  <tr >
                    <td ><label for="exampleInputPassword1">
                          Nama Trayek
                      </label></td>
                    <td><input type="input" name="trip_headsign" id="trip_headsign" class="form-control" placeholder="Silahkan isikan nama Trayek. Contoh: Ciroyom - Antapani"></td>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1">
                          Warna Trayek
                        </label>
                    </td>
                    <td>
                      <p>
                      <div id="colorSelector">
                        <!-- <button type="submit" class="btn btn-primary"  style="" >
                          Change Color
                        </button> -->
                        <div id="colorText" style="height: 100%; width: 100%"> Ganti Warna </div>
                        <input type="input" name="route_color" hidden="hidden" id="route_color">
                      </div>
                        <!-- <div id="colorSelector"><div style="background-color: #0000ff; z-index: 9999 "></div> </div> -->
                      </p>
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <label for="exampleInputPassword1">
                        Harga
                      </label>
                    </td>
                    <td>
                    <div class="form-inline">
                    <!-- <select id="fare_id" name="fare_id" class="form-control">
                      @foreach ($fare_attributes as $data)
                      <option value={{$data->fare_id}}> {{$data->fare_id}} </option>
                      @endforeach
                    </select> --> 
                    <input type="input" id="price" name="price" class="form-control" data-toggle="modal" data-target="#myModal">
                    </td>
                    </div>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1">
                          Gambar
                      </label></td>
                    <td>
                      <div id="image_place"></div>
                      <input type="input" id="image" name="image" class="form-control"  placeholder="Silahkan Klik Browse" >
                      <label class="btn btn-default btn-file">
                          Browse <input type="file" id="file" name="file" enctype="multipart/form-data" >
                      </label>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1">
                          Shape_id
                      </label></td>
                    <td>
                      <!-- <input type="input" id="shape_id" class="form-control" > -->
                      <textarea id="shape_id" class="form-control" name="shape_id" ></textarea>
                    </td>
                  </tr>
                  <tr>
                    <td><label for="exampleInputPassword1">
                          Keterangan
                      </label></td>
                    <td>
                      <!-- <input type="input" id="keterangan" class="form-control" > -->
                      <textarea id="keterangan" class="form-control" name="keterangan" placeholder="Silahkan isikan keterangan jalur. Contoh: "></textarea>
                    </td>
                  </tr>
                  
                  <tr>
                    <td></td>
                    <td>
                      <button type="button" class="btn btn-primary" id="button_clear" >
                        Clear
                      </button>

                      <button type="submit" class="btn btn-primary" id="button_save" >
                        Save
                      </button>

                      <button type="button" class="btn btn-primary" id="button_delete" >
                        Delete
                      </button>

                    </td>
                  </tr>
                  
                </table>
              </form>
          @yield('content') 
            
        </div>
           
        
        <div class="col-md-8" style="height: 550px">
             <div id="map" style="height: 100%; width: 95%; ">                    
             </div>
               
               
        </div>

        </div>    
          
        </div>
    </div>
    </div>

    
  </body>
</html>