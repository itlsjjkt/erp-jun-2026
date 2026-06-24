<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpbHistory extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = [ "created_at" ];
    protected $table = 'lpb_histories';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['lpb_id','user_id','jenis','created_at','message'];
   
   public function setUpdatedAt($value)
   {
     return NULL;
   }
}
