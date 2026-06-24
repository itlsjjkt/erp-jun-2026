<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbHistory extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = [ "created_at" ];
    protected $table = 'spb_histories';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];
   
   public function setUpdatedAt($value)
   {
     return NULL;
   }
}
