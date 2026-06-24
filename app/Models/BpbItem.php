<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpbItem extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bpb_items';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['bpb_id','spb_item_id','product_id','description','qty','pr_item_id'];

   public function SpbItem(){
      return $this->belongsTo('App\Models\BpbItem','id');
   }
  
}
