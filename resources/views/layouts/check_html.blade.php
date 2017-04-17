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
                <li class="active"><a href="http://localhost/webserverangkot/public/">Home<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-home"></span></a></li>

                <li><a href="http://localhost/webserverangkot/public/trayek">Info Angkutan Umum<span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-th-list"></span></a></li>
                
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
          

                  
                  
                 
              <div class="se-pre-con"></div>
                  


              <label for="exampleInputEmail1">
                  Trayek 1
              </label>
              <select class="form-control" id="pilih" name="pilih">
              <?php foreach ($trip as $a): ?>
              <option value= <?php echo $a['route_id']; ?> > <?php echo $a['trip_short_name']; ?> </option>  
              <?php endforeach ?> 
              </select>
              <br>
              <label for="exampleInputEmail1">
                  Trayek 2
              </label>
              <select class="form-control" id="pilih2" name="pilih">
              <?php foreach ($trip as $a): ?>
              <option value= <?php echo $a['route_id']; ?> > <?php echo $a['trip_short_name']; ?> </option>  
              <?php endforeach ?> 
              </select>
              <br>
              <table  class="table" id="tableInfo" >
   
              </table>
                  
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