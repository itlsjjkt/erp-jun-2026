<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Auth;

class DphHistory extends Model
{
   /**
     * The table associated with the model.
     *
     * @var string
     */
   protected $table = 'dph_histories';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];

}
