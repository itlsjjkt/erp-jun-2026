<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use Auth;

class Spb extends Model
{


   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spb';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded    = ['id'];

   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'ilike', '%'.$s.'%');
   }


   public static function getData($request, $user = null){

      $query = DB::table('spb')
      ->select(
         'spb.id',
         'spb.doc_no',
         'spb.created_by',
         'spb.created_at',
         'spb.modified_by',
         'spb.updated_at',
         'spb.notes',
         'spb.status',
         'spb.delivered_by',
         'spb.delivered_pic',
         'spb.delivered_pic_telp',
         'spb.received_pic',
         'spb.received_pic_telp',
         'spb.lpb_id',
         'spb.type',
         'spb.inventory_unit',
         'spb.checker',
         'spb.checker_sign',
         'spb.operator',
         'spb.operator_sign',
         'spb.publish',
         'spb.date_transaction',
         'spb.address',
         'spb.company_id',
         'spb.pickup_from',
         'spb.pickup_address',
         'spb.pickup_pic_name',
         'spb.pickup_pic_telp',
         'spb.jalur_pengiriman',
         'spb.is_pickup',
         'spb.estimate_receives',
         'spb.attachment_file',
         'spb.receipt_type',
         'spb.notes_receipt_non_bpb',
         'users.name AS created'
      )
      ->leftJoin('users', 'users.id', '=', 'spb.created_by')
      ->when(!empty($user), function ($query) use ($user) {
          return $query->where('spb.created_by', $user);
      })
      ->when(!empty($request['type']), function ($query) use ($request) {
         return $query->where('spb.type',$request['type']);
      })
      ->when(!empty($request['amp;company_id']), function ($query) use ($request) {
         return $query->where('spb.company_id',$request['amp;company_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('spb.created_at', [$start , $end]);
      });

      if(isAdministratorCompany()){
         $query->leftJoin('spb_kolis','spb_kolis.spb_id','=','spb.id')
         ->leftJoin('purchase_items','purchase_items.id','=','spb_kolis.pr_item_id')
         ->leftJoin('purchases','purchases.id','=','purchase_items.purchase_id')
         ->leftJoin('locations','locations.id','=','purchases.location_id')
         ->where('locations.company_id','=',Auth::user()->company_id)
         ->groupBy(
            'spb.id',
            'spb.doc_no',
            'spb.created_by',
            'spb.created_at',
            'spb.modified_by',
            'spb.updated_at',
            'spb.notes',
            'spb.status',
            'spb.delivered_by',
            'spb.delivered_pic',
            'spb.delivered_pic_telp',
            'spb.received_pic',
            'spb.received_pic_telp',
            'spb.lpb_id',
            'spb.type',
            'spb.inventory_unit',
            'spb.checker',
            'spb.checker_sign',
            'spb.operator',
            'spb.operator_sign',
            'spb.publish',
            'spb.date_transaction',
            'spb.address',
            'spb.company_id',
            'spb.pickup_from',
            'spb.pickup_address',
            'spb.pickup_pic_name',
            'spb.pickup_pic_telp',
            'spb.jalur_pengiriman',
            'spb.is_pickup',
            'spb.estimate_receives',
            'spb.attachment_file',
            'spb.receipt_type',
            'spb.notes_receipt_non_bpb',
            'users.name'
         );
     }
      return $query;

  }



   public static function getByID($id){

      $query = DB::table('spb')
      ->select('spb.*',
         'expeditions.name AS expedition',
         'expeditions.address AS expeditionAddress',
         'expeditions.pic AS expeditionPIC',
         'expeditions.email AS expeditionEmail',
         'users.name AS created',
        )
      ->leftJoin('expeditions', 'expeditions.id', '=', 'spb.delivered_by')
      ->leftJoin('users', 'users.id', '=', 'spb.created_by')
      ->where('spb.id', $id)
      ->first();

      return $query;

   }


   public static function getByLPB($id){
      $query = DB::table('spb')
      ->select(
         'spb.*',
         'users.name AS created'
      )
      ->leftJoin('users', 'users.id', '=', 'spb.created_by')
      ->where('spb.lpb_id', $id)
      ->get();

      return $query;

   }


   public static function getByLPBItem($id){
      $query = DB::table('spb_kolis')
      ->select(
         'spb.doc_no',
         'spb.created_at',
         'spb.type',
         'spb_kolis.*',
         'users.name AS created',
         'spb.is_pickup AS is_pickup',
         'spb.status AS status_spb'
      )
      ->leftJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
      ->leftJoin('users', 'users.id', '=', 'spb.created_by')
      ->whereIn('spb_kolis.lpb_id', $id)
      ->get();

      return $query;

   }


   public static function getByDPMItem($id){
      $query = DB::table('spb_kolis')
      ->select(
         'spb.doc_no',
         'spb.created_at',
         'spb.type',
         'spb_kolis.*',
         'users.name AS created',
         'spb.is_pickup AS is_pickup',
         'spb.status AS status_spb'
      )
      ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
      ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
      ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
      ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
      ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
      ->leftJoin('users', 'users.id', '=', 'spb.created_by')
      ->where('purchase_requisitions.purchase_id', $id)
      ->get();
      return $query;
   }

   public static function getProductItem($id){

      $query = DB::table('spb_kolis')
        ->select(
            DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
            'spb_kolis.id AS idKoli',
            'spb_kolis.qty AS qtyKoli',
            'spb_kolis.uuid AS uuid',
            'spb_kolis.annotation',
            'spb_kolis.qty AS qty_kolis',
            'spb_kolis.status_insurance AS status_insurance',
            'suppliers.name AS supplier',
            'po.doc_no AS noPO',
            'po.ppn AS ppn',
            'po.currency AS currency',
            'po.discount_type AS discount_type',
            'po.discount_amount AS discount_amount',
            'purchase_requisitions.dpm_no AS noDPM',
            'lpb.doc_no AS noLPB',
            'lpb_items.qty AS qty',
            'po_items.price as price',
            'po_items.discount AS price_discount',
            'po_items.price_discount AS price_after_discount',
            'master_item_products.name AS product',
            'master_item_products.id AS productID',
            'departments.name AS department',
            'po_items.specification',
            'suppliers.id AS supplierID',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand',
            'po_items.measure',
            'spb.type',
            'currencies.name as currencies_name',
			   'currencies.conversion_idr as conversion_idr',
            'locations.name AS locationnn'
         )
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	     ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
        ->where('spb_kolis.spb_id', $id)
        ->orderBy('spb_kolis.id', 'ASC');
         if(isAdministratorCompany()){
            $query->where('po.company_id','=',Auth::user()->company_id);
         }
         $data = $query->get();
      return $data;

   }

   // public static function getBPBItem($id){

   //    $query = DB::table('spb_kolis')
   //      ->select(
   //       DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
   //       'spb_kolis.id AS idKoli',
   //       'spb_kolis.qty AS qtyKoli',
   //       'spb_kolis.annotation',
   //       'spb_kolis.qty_parsial',
   //       'spb_kolis.uuid AS uuid',
   //       'spb_kolis.bpb_status',
   //       'spb_kolis.pr_item_id',
   //       'spb_kolis.location_id',
   //       'suppliers.name AS supplier',
   //       'po.doc_no AS noPO',
   //       'purchase_requisitions.dpm_no AS noDPM',
   //       'locations.name AS locationDPM',
   //       'po_items.price as price',
   //       'po_items.price_discount AS price_after_discount',
   //       'po_items.discount AS price_discount',
   //       'master_item_products.name AS product',
   //       'master_item_products.id AS productID',
   //       'po_items.specification',
   //       'po_items.measure',
   //       'measures.name AS measureInventory',
   //       'master_item_products.code AS productCode',
   //       'master_item_products.conversion AS productConversion',
   //       'master_item_products.measure_inventory AS productMeasure',
   //       'master_item_products.part_number AS productPartNumber',
   //       'master_item_brands.name AS productBrand',
   //       'purchase_items.request_type_item AS request_type_item',
   //       'purchase_items.return_location AS return_location',
   //       'purchases.mr_file AS file_dpm'
   //      )
   //      ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'spb_kolis.pr_item_id')
   //      ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
   //      ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
   //      ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
   //      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
   //      ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
   //      ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
   //      ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
   //      ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_inventory')
	//      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
   //      ->where('spb_kolis.spb_id', $id)
   //      ->whereIn('po.status', array('4','5'))
   //      ->whereIn('spb_kolis.bpb_status', [0,2])
   //      ->orderBy('no_koli', 'ASC')
   //      ->get();
   //    return $query;

   // }

   public static function getBpbItem($id){

      $query = DB::table('spb_kolis')
        ->select(
            DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
            'spb_kolis.id AS idKoli',
            'spb_kolis.qty AS qtyKoli',
            'spb_kolis.annotation',
            'spb_kolis.qty_parsial',
            'spb_kolis.uuid AS uuid',
            'spb_kolis.bpb_status',
            'spb_kolis.pr_item_id',
            'spb_kolis.location_id',
            'suppliers.name AS supplier',
            'po.doc_no AS noPO',
            'purchase_requisitions.dpm_no AS noDPM',
            'locations.name AS locationDPM',
            'po_items.price as price',
            'po_items.price_discount AS price_after_discount',
            'po_items.discount AS price_discount',
            'master_item_products.name AS product',
            'master_item_products.id AS productID',
            'po_items.specification',
            'po_items.measure',
            'measures.name AS measureInventory',
            'master_item_products.code AS productCode',
            'master_item_products.conversion AS productConversion',
            'master_item_products.measure_inventory AS productMeasure',
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand',
            'purchase_items.request_type_item AS request_type_item',
            'purchase_items.return_location AS return_location',
            'purchases.mr_file AS file_dpm'
         )
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_inventory')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	     ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
        ->where('spb_kolis.spb_id', $id)
        ->whereIn('po.status', array('4','5'))
        ->whereIn('spb_kolis.bpb_status', [0,2])
        ->orderBy('spb_kolis.id', 'ASC')
        ->get();
      return $query;

   }


   public static function getInsuranceItem($id,$type){

      $query = DB::table('spb_kolis')
        ->select(
        'spb_kolis.uuid AS uuid',
        'spb_kolis.id AS idKoli',
        'spb_kolis.qty AS qtyKoli',
        'spb_kolis.annotation',
        'suppliers.name AS supplier',
        'po.doc_no AS noPO',
        'purchase_requisitions.dpm_no AS noDPM',
        'lpb_items.qty AS qty',
        'po_items.price as price',
        'master_item_products.name AS product', 'master_item_products.id AS productID',
        'departments.name AS department','purchase_items.notes',
        'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand',  'po_items.measure')
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	     ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_ids')
        ->where('spb_kolis.spb_id', $id)
        ->whereIn('po.status', array('4','5'))
        ->where('spb_kolis.status_insurance_'.$type, 0)
        ->orderBy('spb_kolis.no', 'ASC')
        ->get();
      return $query;

   }

   public static function getInsuranceUnitItem($id){

      $query = DB::table('spb_kolis')
        ->select('spb_kolis.*',
        'suppliers.name AS supplier',
        'po.doc_no AS noPO',
        'purchase_requisitions.dpm_no AS noDPM',
        'lpb_items.qty AS qty',
        'po_items.price as price',
        'master_item_products.name AS product', 'master_item_products.id AS productID',
        'departments.name AS department', 'purchase_items.notes',
        'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand',  'po_items.measure')
        ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	     ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
        ->where('spb_kolis.spb_id', $id)
        ->get();
      return $query;

   }

   public static function gethistory($id){
      $query =  DB::table('spb_histories')
      ->select('spb_histories.*','users.name AS employee')
      ->leftJoin('users', 'users.id', '=', 'spb_histories.user_id')
      ->where('spb_histories.spb_id', $id)
      ->orderBy('spb_histories.created_at', 'DESC')
      ->get();
      return $query;
   }


   public function searchableAs()
   {
       return 'spb';
   }

   public function SpbKoli(){
      return $this->hasMany('App\Models\SpbKoli','spb_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function company(){
      return $this->belongsTo('App\Models\Company','company_id');
   }

   public function expedition(){
      return $this->belongsTo('App\Models\Expedition','delivered_by');
   }

}
