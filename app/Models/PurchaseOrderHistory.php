<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderHistory extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    public $timestamps = [ "created_at" ];
    protected $table = 'po_histories';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['po_id','user_id','jenis','created_at','message','date_approved'];
   
   public function setUpdatedAt($value)
   {
     return NULL;
   }
}
