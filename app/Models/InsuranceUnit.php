<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InsuranceUnit extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurance_units';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['kepada','cc','perihal','doc_no','created_by','status','spb_id','publish','company_id',
        'prepared_by','approved_by','checked_by','checked_purchasing_1','checked_purchasing_2','received_by_1','received_by_2'];

    public function searchableAs()
    {
        return 'insurance_units';
    }

    public static function getByID($id){

      $query = DB::table('insurance_units')
      ->select('insurance_units.*','users.name AS created')
       ->leftJoin('users', 'users.id', '=', 'insurance_units.created_by')
       ->where('insurance_units.id', $id)
       ->first();

      return $query;

    }
   

  public static function getProductItem($id){

      $query = DB::table('insurance_unit_items')
          ->select('insurance_unit_items.*',
          'spb_kolis.annotation',
          'po.doc_no AS noPO','purchase_requisitions.dpm_no AS noDPM',
          'spb.doc_no AS noSPB',
          'lpb.doc_no AS noLPB',
          'spb_kolis.id AS idKoli', 
          'spb_kolis.qty AS qtyKoli',
          'suppliers.name AS supplier',
          'po_items.price as price','po_items.measure',
          'master_item_products.name AS product','purchase_items.notes', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand')
          ->leftJoin('spb_kolis', 'insurance_unit_items.spb_item_id', '=', 'spb_kolis.id')
          ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
          ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
          ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
          ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
          ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
          ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
          ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
          ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
          ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
          ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
          ->where('insurance_unit_items.insurance_unit_id', $id)
          ->get();
      return $query;

  }

}
