<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestHistory extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = [ "created_at" ];
    protected $table = 'purchase_histories';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['purchase_id','user_id','jenis','created_at','message','date_approved'];
   
   public function setUpdatedAt($value)
   {
     return NULL;
   }
}
