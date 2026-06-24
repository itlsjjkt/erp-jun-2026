<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PurchaseRequestItem extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_items';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];


   public static function getItem($id, $purchaser_id = null){

      $query = DB::table('purchase_items')
      ->select('purchase_items.*',
      'users.name AS purchaser',
      'master_item_products.name AS product', 
      'master_item_products.code AS productCode', 
      'master_item_products.part_number AS productPartNumber',
      'master_item_brands.name AS productBrand')
      ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
      ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
      ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
      ->when(isset($purchaser_id), function ($result) use ($purchaser_id) {
         return $result->where('purchase_items.assigned_id',$purchaser_id);
      })
      ->where('purchase_items.pr_id', $id)
      ->where('purchase_items.pr_status',1)
      ->orderBy('purchase_items.id', 'ASC')
      ->get();


      return $query;
   }


   public function PurchaseRequest(){
      return $this->belongsTo('App\Models\PurchaseRequest','id');
   }

   public function PurchaseRequestion(){
      return $this->belongsTo('App\Models\PurchaseRequestion','id');
   }

   public function product(){
      return $this->belongsTo('App\Models\MasterItemProduct','product_id');
   }

   public function purchaser(){
      return $this->belongsTo('App\User','assigned_id');
   }


}
