<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbKoli extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spb_kolis';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];

   public function Spb(){
      return $this->belongsTo('App\Models\Spb','id');
   }
  
}
