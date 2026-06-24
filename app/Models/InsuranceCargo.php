<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InsuranceCargo extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurance_cargos';
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
    protected $fillable = ['period','risk_location','shipper_by','doc_no','created_by','status','spb_id','notes','publish','company_id',
            'prepared_by','approved_by','checked_by','checked_purchasing_1','checked_purchasing_2','received_by_1','received_by_2'];

    public function searchableAs()
    {
        return 'insurance_cargos';
    }

    public static function getByID($id){

        $query = DB::table('insurance_cargos')
        ->select('insurance_cargos.*','expeditions.name AS expedition','users.name AS created')
        ->leftJoin('expeditions', 'expeditions.id', '=', 'insurance_cargos.shipper_by')
         ->leftJoin('users', 'users.id', '=', 'insurance_cargos.created_by')
         ->where('insurance_cargos.id', $id)
         ->first();
        return $query;
  
    }
     
  
    public static function getProductItem($id){

        $query = DB::table('insurance_cargo_items')
            ->select('insurance_cargo_items.*',
            'po.doc_no AS noPO','purchase_requisitions.dpm_no AS noDPM',
            'spb.doc_no AS noSPB',
            'lpb.doc_no AS noLPB',
            'spb_kolis.id AS idKoli','spb_kolis.qty AS qtyKoli','spb_kolis.annotation',
            'suppliers.name AS supplier',
            'po_items.price as price','po_items.measure',
            'master_item_products.name AS product','purchase_items.notes','master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand')
            ->leftJoin('spb_kolis', 'insurance_cargo_items.spb_item_id', '=', 'spb_kolis.id')
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
            ->where('insurance_cargo_items.insurance_cargo_id', $id)
            ->get();
        return $query;

    }

}
