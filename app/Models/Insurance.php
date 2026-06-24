<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class Insurance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurances';
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
    protected $fillable = ['spb_id','company','project','doc_no','manifest_no','expedition_forwarder','risk_location','etd_eta','shipped_by','prepared_by','checked_by_1','checked_by_2','known_by_1','known_by_2','known_by_3','received_by','approved_by','created_by','created_at','updated_at','status','mr_file','notes'];


    public function searchableAs()
    {
        return 'insurances';
    }

    public static function getByID($id){

        $query = DB::table('insurances')
            ->select('insurances.*','users.name AS created')
            ->leftJoin('users', 'users.id', '=', 'insurances.created_by')
            ->where('insurances.id', $id)
            ->first();
        return $query;
    }
     
  
    public static function getProductItem($id){

        $query = DB::table('insurance_items')
            ->select('insurance_items.*',
            'po.doc_no AS noPO','purchase_requisitions.dpm_no AS noDPM',
            'spb.doc_no AS noSPB',
            'lpb.doc_no AS noLPB',
            'spb_kolis.id AS idKoli',
            'spb_kolis.qty AS qtyKoli',
            'spb_kolis.annotation',
            'suppliers.name AS supplier',
            'po_items.price as price_po',
            'po_items.measure',
            'master_item_products.name AS product',
            'purchase_items.notes',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
		    'currencies.symbol AS symbol',
            'master_item_brands.name AS productBrand')
            ->leftJoin('spb_kolis', 'insurance_items.spb_item_id', '=', 'spb_kolis.id')
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
    	    ->leftJoin('currencies','currencies.name','=','po.currency')
            ->where('insurance_items.insurance_id', $id)
            ->get();
        return $query;
    }

}
