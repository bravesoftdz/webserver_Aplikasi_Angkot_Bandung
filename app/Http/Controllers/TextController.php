<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TextController extends Controller
{
    public function index()
    {   
      //  $trip = trip::orderBy('trip_short_name')->get();
         return view('text.index');
        
    }

    public function test()
    {   
      //  $trip = trip::orderBy('trip_short_name')->get();
         return view('text.test');
        
    }
}
