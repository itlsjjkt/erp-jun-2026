<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'po_items';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['po_id', 'product_id','price','price_discount','discount','notes','isReady','qty','last_updated','specification'];
   
   public function product(){
      return $this->belongsTo('App\Models\MasterItemProduct','product_id');
   }

}
