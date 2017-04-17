<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\trip;
use App\trip_new;
class MapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        $trip = trip::orderBy('trip_short_name')->get();
         //$trip = trip::all();
         return view('map.index',compact('trip'));
         //return view('map.plain_map');
    }

    public function graph()
    {   
        return view('map.graphlat');
        
    }

    public function map()
    {
        $trip = trip::orderBy('trip_short_name')->get();
         return view('map.map',compact('trip'));
         
    }

    
    public function trayek()
    {    
        $trip = trip::where("shape_id", "!=", "" )-> orderBy('trip_short_name')->get();   
        return view('map.trayek',compact('trip'));
        //return view("map.trayek");
    }


    public function input()
    {    
        $trip = trip::where("shape_id", "!=", "" )-> orderBy('trip_short_name')->get();   
        return view('map.input',compact('trip'));
        //return view("map.trayek");
    }

    public function edit()
    {    
        $trip = trip::where("shape_id", "!=", "" )-> orderBy('trip_short_name')->get();   
        return view('map.edit',compact('trip'));
        
    }

    public function cekdb()
    {    
        $trip = trip::where("shape_id", "!=", "" )-> orderBy('trip_short_name')->get();   
        return view('map.cek_db',compact('trip'));
        //return view("map.trayek");
    }

    public function check()
    {    
        $trip = trip::where("shape_id", "!=", "" )-> orderBy('trip_short_name')->get();   
        return view('map.check',compact('trip'));
        //return view("map.trayek");
    }

    
}
