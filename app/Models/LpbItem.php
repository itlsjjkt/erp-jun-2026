<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LpbItem extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lpb_items';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['lpb_id','po_item_id','product_id','qty','qty_retur','notes','pr_item_id', 'status', 'qty_parsial'];

   public function LpbItem(){
      return $this->belongsTo('App\Models\LpbItem','id');
   }
  
}
