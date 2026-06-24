<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbChecker extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = false;
    protected $table = 'spb_checkers';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['id','name','sign'];
   
  
}
