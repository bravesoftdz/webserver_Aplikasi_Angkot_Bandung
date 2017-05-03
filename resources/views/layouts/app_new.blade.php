<!DOCTYPE html> 
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
                <li class="active"><a href="http://localhost/webserverangkot/public/">Cari Jalur Angkot<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-home"></span></a></li>

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
              </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="z-index: 0">
            <nav class="navbar navbar-default navbar-static-top" role="navigation" style="background-image: url('http://localhost/webserverangkot/public/images/header.png'); background-size: 100% 100%; ">
                <div class="navbar-header">
                     

                    <button type="button" class=" navbar-toggle button"  data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" id="menu-toggle" style="display: block; float: left; margin-left: 10px  ">
                        <span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    

                    <!--<ul class="nav navbar-nav navbar-right">
                    <li><a id="menu-toggle" href="#"><span class="glyphicon glyphicon-align-justify"></span> Menu </a></li>
                    </ul>-->
                </div>
                
                
                <!--<img id="image" style="width: 100%" src="http://localhost/webserverangkot/public/images/header.png" alt="Dispute Bills">-->
                
            </nav>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4" style="" >
          
            <!--<img alt="Bootstrap Image Preview" src="http://localhost/webserverangkot/public/images/powered_by.png" class="img-circle" style="border: solid;"> -->
                <div id="select_routingresult">
                  <label for="exampleInputEmail1">
                     Pilihan angkot
                  </label>
                
                  <select class="form-control" name="select_routingresult" id="select_routingresult_select"  >
                    <!--  <option value="" disabled selected >---pilih---</option> -->
                  </select>
                </div>

                <div class="form-group">
                     
                    <!--<label for="exampleInputEmail1">
                        start location
                    </label>-->
                    <input type="input" class="form-control" id="start_location" placeholder="Pilih titik awal, atau klik Peta" >
                     <input type="input" class="form-control" id="start_location_name" placeholder="Pilih titik awal, atau klik Peta" >
                </div>
                    
                    <div id="select_start" class="ui-widget" >
                        <select class="form-control" name="select_start" id="select_start_select" >
                     <!--   <option disabled selected>---pilih---</option> -->
                       </select>
                    </div>    
                <br>

                <div class="ui-widget">
                     
                    <!--<label for="exampleInputPassword1">
                        finish location
                    </label> -->
                    <input type="input" class="form-control" id="finish_location" placeholder="pilih tujuan">
                    <input type="input" class="form-control" id="finish_location_name" placeholder="pilih tujuan">
                    
                </div>

                <!--<div id="loading" ><img class="img-responsive img-circle" alt="Cinque Terre" src="http://localhost/webserverangkot/public/images/loading.gif"> </div>-->
                    
                <div id="select_finish">
                    <select class="form-control" name="select_finish" id="select_finish_select">
                    <!--    <option disabled selected>---pilih---</option>  -->
                       
                    </select>
                </div>

                <br>
                    
       
                <button type="submit" class="btn btn-primary" id="submit" ">
                    Submit
                </button>
                <button type="submit" class="btn btn-primary" id="button_clear" >
                    Clear
                </button>
                <!--<a href="#" id="reverse" class="btn btn-primary">reverse</a>-->
                 <button type="submit" class="btn btn-primary" id="reverse" > Reverse </button>
                <input type="checkbox" name="walk_route_check" id="walk_route_check" checked="checked"> use walk route ? <br>
                <br>
                <ul class="nav nav-tabs" id="tabs">
                  <!--<li role="presentation" onchange=""  ><a href="#">profile </a></li>
                  <li role="presentation" class="active"><a href="#"></a></li>
                  <li role="presentation"><a href="#"></a></li> --> 
                </ul>
                <div class="tab-content clearfix" id="isiTabs">
                    <div class="tab-pane active" id="1a">
                      <!--<h3>Content's background color is the same for the tab</h3> -->
                      <!--<div> Cek cek cek</div>-->
                      <table  class="table" id="tableKet0">
                        <!--<tr>
                          <td align="center" width=20% > Gambar</td>
                          <td align="center" width="100%"> Petunjuk </td>
                        </tr>-->
                      </table>
                    </div>
                    <!--<div class="tab-pane" id="2a">

                        <h3>We use the class nav-pills instead of nav-tabs which automatically creates a background color for the tab</h3>
                    </div>
                    <div class="tab-pane" id="3a">

                      <h3>We applied clearfix to the tab-content to rid of the gap between the tab and the content</h3>
                    </div>
                      <div class="tab-pane" id="4a">
                      <h3>We use css to change the background color of the content to be equal to the tab</h3>
                    </div>-->
                </div>
                
                <div class="se-pre-con"></div>
                <!--<div id="progressBar"></div>-->
                <div class="myProgressBar" style="width: 64px;
                  height: 64px;
                  background: url(images/loader-64x/Preloader_3.gif) center no-repeat #fff;">
                    
                </div>
                <div class="label label-info myProgressBar"> Hang on ... </div>

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