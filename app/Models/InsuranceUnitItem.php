<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceUnitItem extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurance_unit_items';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $fillable = ['insurance_unit_id','spb_item_id','ppn','discount','price'];

  
}
