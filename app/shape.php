<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class shape extends Model
{
    //public $trayek = null;

    protected $table = 'shapes';
    protected $trayek = array('availability');

    public function getAvailabilityAttribute()
    {
        return $this->calculateAvailability();  
    }

}
