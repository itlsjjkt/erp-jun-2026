<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequestNotes extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_notes';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['pr_item_id', 'notes','message','user_id'];
  
}
