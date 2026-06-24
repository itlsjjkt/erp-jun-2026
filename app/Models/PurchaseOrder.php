<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Auth;

class PurchaseOrder extends Model
{


   /**
     * The table associated with the model.
     *
     * @var string
     */
   protected $table = 'po';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];


   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'like', '%'.$s.'%');
   }


   public static function getData($request, $user = null){

      $query = DB::table('po')
      ->select(
          'po.*',
          'purchase_requisitions.doc_no AS no_pr',
          'suppliers.name AS supplier',
          'users.name AS created',
          'dph.doc_no AS no_dph',
          'dph.id AS dph_id'
      )
      ->leftJoin('users', 'users.id', '=', 'po.created_by')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('dph','dph.id','=','po.dph_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->when(!empty($user), function ($query) use ($user) {
         return $query->where('po.created_by', $user);
      })
      ->when(!empty($request['purchaser_id']), function ($query) use ($request) {
          return $query->where('po.created_by',$request['purchaser_id']);
      })
      ->when(!empty($request['amp;project_id']), function ($query) use ($request) {
          return $query->where('purchase_requisitions.project_id',$request['amp;project_id']);
      })
      ->when(!empty($request['amp;supplier_id']), function ($query) use ($request) {
          return $query->where('po.supplier_id',$request['amp;supplier_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('po.created_at', [$start , $end]);
      });


      if(isAdminEmail()) {
         $query->whereIn('po.status',[2,4,5,8])->orderBy('po.id','DESC');
      }else if(Auth::user()->type == 4 && Auth::user()->data_access == 1){
         $query->orderByRaw("CASE WHEN po.approved IS NOT NULL AND po.last_print IS NULL THEN 0 ELSE 1 END");
      }else{
         $query->orderBy('po.id','DESC');
      }

      if(isAdministratorCompany()){
         $query->where('po.company_id','=',Auth::user()->company_id);
      }

      return $query;
  }



   public static function getByID($id){

      $query = DB::table('po')
      ->select('po.*',
         'payment_terms.name AS payment_term',
         'payment_terms.type_body_email AS typeBodyEmail',
         'payment_terms.dp_percentage AS payment_term_dp_percentage',
         'po_terms.name AS po_term',
         'po_terms.description AS po_termDescription',
         'purchases.mr_file',
         'purchase_requisitions.doc_no AS pr_no',
         'purchase_requisitions.purchase_id AS dpm_id',
         'purchase_requisitions.dpm_no AS dpm_no',
         'purchase_requisitions.location_id as locationID',
         'suppliers.name AS supplier',
         'supplier_contacts.name AS picName',
         'supplier_contacts.telp AS picTelp',
         'supplier_contacts.title AS picTitle',
         'supplier_contacts.email AS picEmail',
         'locations.name AS location',
         'locations.created_at AS location_created_at',
         'companies.name AS company',
         'companies.alias AS company_code',
         'companies.id AS company_id',
         'companies.address AS companyAddress',
         'companies.telp AS companyTelp',
         'companies.fax AS companyFax',
         'created_users.name AS created',  // Aliased for clarity
         'departments.name AS department',
         'currencies.name AS currencysymbol',
         'po_notes.description AS notesDescription',
         'ttd_users.ttd AS ttd',  // Aliased for clarity,
         'created_users.email AS emailPurchaser'

      )
      ->leftJoin('users AS ttd_users', 'ttd_users.id', '=', 'po.approved_by')  // Aliased join for approved_by
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('supplier_contacts', function($join)
      {
          $join->on('suppliers.id', '=', 'supplier_contacts.supplier_id')
          ->on('po.supplier_contact_id', '=', 'supplier_contacts.id');
      })
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
      ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
      ->leftJoin('po_terms', 'po_terms.id', '=', 'po.po_term_id')
      ->leftJoin('users AS created_users', 'created_users.id', '=', 'po.created_by')  // Aliased join for created_by
      ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
      ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
      ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
      ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
	  ->leftJoin('po_notes','po_notes.id','=','po.po_note')
      ->where('po.id', $id)
      ->first();

      return $query;

   }

   public static function getByDPM($id){

      $query = DB::table('po')
      ->select('po.*',
          'purchase_requisitions.doc_no AS pr_no',
          'purchase_requisitions.dpm_no AS dpm_no',
          'suppliers.name AS supplier',
          'supplier_contacts.name AS picName',
          'supplier_contacts.telp AS picTelp',
          'supplier_contacts.title AS picTitle',
          'supplier_contacts.email AS picEmail',
          'users.name AS created')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('supplier_contacts', 'supplier_contacts.id', '=', 'po.supplier_contact_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
      ->leftJoin('users', 'users.id', '=', 'po.created_by')
      ->where('purchase_requisitions.purchase_id', $id)
      ->where('po.status','!=', 10)
      ->get();

      return $query;

   }

   public static function getByPRID($id){

      $query = DB::table('po')
      ->select('po.*', 'payment_terms.name AS payment_term',  'po_terms.name AS po_term', 'purchases.mr_file',
          'purchase_requisitions.doc_no AS pr_no','purchase_requisitions.dpm_no AS dpm_no','purchase_requisitions.location_id as locationID',
          'suppliers.name AS supplier',
          'supplier_contacts.name AS picName',
          'supplier_contacts.telp AS picTelp',
          'supplier_contacts.title AS picTitle',
          'supplier_contacts.email AS picEmail',
          'users.name AS created')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('supplier_contacts', 'supplier_contacts.id', '=', 'po.supplier_contact_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
      ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
      ->leftJoin('po_terms', 'po_terms.id', '=', 'po.po_term_id')
      ->leftJoin('users', 'users.id', '=', 'po.created_by')
      ->where('purchase_requisitions.id', $id)
      ->where('po.status','!=', 10)
      ->get();

      return $query;

   }


   public static function getByPR($id){

      $query = DB::table('po')
      ->select('po.*', 'payment_terms.name AS payment_term',  'po_terms.name AS po_term',
          'purchase_requisitions.doc_no AS pr_no','purchase_requisitions.location_id as locationID',
          'suppliers.name AS supplier',
          'supplier_contacts.name AS picName',
          'supplier_contacts.telp AS picTelp',
          'supplier_contacts.title AS picTitle',
          'supplier_contacts.email AS picEmail',
          'users.name AS created')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('supplier_contacts', 'supplier_contacts.id', '=', 'po.supplier_contact_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('payment_terms', 'payment_terms.id', '=', 'po.payment_term_id')
      ->leftJoin('po_terms', 'po_terms.id', '=', 'po.po_term_id')
      ->leftJoin('users', 'users.id', '=', 'po.created_by')
      ->where('purchase_requisitions.id', $id)
      ->first();

      return $query;

   }

   public static function getProductItem($id){

      $query = DB::table('po_items')
        ->select('po_items.*','purchase_items.qty_parsial', 'purchase_items.flag AS flag', 'purchase_items.needed_on_date', 'purchase_items.qty AS qty_pr', 'purchase_items.po_status',
        'master_item_products.name AS product',
        'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','master_item_brands.name AS productBrand')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->where('po_items.po_id', $id)
        ->orderBy('pr_item_id','ASC')
        ->get();
      return $query;

   }

   public static function getItemPo()
   {
      return DB::table('po_items AS pi')
         ->select('pi.*', 'po.doc_no AS doc_noPo', 'mip.name AS nameProduct')
         ->leftJoin('master_item_products AS mip', 'pi.product_id', '=', 'mip.id')
         ->leftJoin('po AS po', 'pi.po_id', '=', 'po.id')
         ->orderBy('pi.id', 'DESC')
         ->get();
   }


   public static function getProductItemLPB($id){

      $query = DB::table('po_items')
        ->select(
         'po_items.*',
         'po_items.price as price',
         'po_items.price_discount AS price_after_discount',
         'po_items.discount AS price_discount',
         'master_item_products.name AS product',
         'master_item_products.id AS productID',
         'master_item_products.code AS productCode',
         'master_item_products.conversion AS productConversion',
         'master_item_products.part_number AS productPartNumber',
         'master_item_products.measure_inventory AS productMeasure',
         'master_item_brands.name AS productBrand',
         'minv.name AS measure_inventory'
         )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('measures as minv', 'master_item_products.measure_inventory', '=', 'minv.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->where('po_items.po_id', $id)
        ->whereIn('po.status', array('2','4'))
        ->where('po_items.lpb_status', '!=' , 1)
        ->get();
      return $query;

   }



   public static function getProductItemFranco($id){

      $query = DB::table('po_items')
        ->select(
         'po_items.*',
         'po_items.price as price',
         'po_items.price_discount AS price_after_discount',
         'po_items.discount AS price_discount',
         'master_item_products.name AS product',
         'master_item_products.id AS productID',
         'master_item_products.code AS productCode',
         'master_item_products.conversion AS productConversion',
         'master_item_products.part_number AS productPartNumber',
         'master_item_products.measure_inventory AS productMeasure',
         'master_item_brands.name AS productBrand',
         'purchase_items.request_type_item AS request_type_item',
         'purchase_items.return_location AS return_location',
         'purchases.mr_file AS file_dpm',
         'purchases.location_id AS location_dpm'
         )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->where('po_items.po_id', $id)
        ->whereIn('po.status', array('2','4'))
        ->where('po_items.lpb_status', '!=' , 1)
        ->get();
      return $query;

   }


   public static function gethistory($id){

      $query =  DB::table('po_histories')
      ->select('po_histories.*','users.name AS employee')
      ->leftJoin('users', 'users.id', '=', 'po_histories.user_id')
      ->where('po_histories.po_id', $id)
      ->orderBy('po_histories.created_at', 'DESC')
      ->get();

      return $query;

   }


   public static function getProductItemHistory($id){
      $query = DB::table('po_items')
        ->select('po_items.*','po.created_at','po.doc_no','suppliers.name AS supplier','po.id as poId')
        ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
        ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
        ->where('po_items.product_id', $id)
        ->get();
      return $query;
   }



      public static function getStats($assign_id = null){

         $where = '';
         if($assign_id)  $where =  " WHERE created_by = ". $assign_id;

         $where2 = '';
         if(isAdministratorCompany()) $where2 = " WHERE company_id = ". Auth::user()->company_id;

         $sql = "

            SELECT
            COALESCE(SUM(CASE WHEN po.status = 1 THEN 1 ELSE 0 END),0) AS on_progress,
            COALESCE(SUM(CASE WHEN po.status = 2 THEN 1 ELSE 0 END),0) AS issued,
            COALESCE(SUM(CASE WHEN po.status = 3 THEN 1 ELSE 0 END),0) AS revision,
            COALESCE(SUM(CASE WHEN po.status = 4 THEN 1 ELSE 0 END),0) AS lpb_parsial,
            COALESCE(SUM(CASE WHEN po.status = 5 THEN 1 ELSE 0 END),0) AS done,
            COALESCE(SUM(CASE WHEN po.status = 6 THEN 1 ELSE 0 END),0) AS close,
            COALESCE(SUM(CASE WHEN po.status = 8 THEN 1 ELSE 0 END),0) AS revised_closed,
            COALESCE(SUM(CASE WHEN po.status = 9 OR po.status = 10 OR po.status = 0 THEN 1 ELSE 0 END),0) AS draft
            FROM po
            $where
            $where2
         ";
         return DB::select( DB::raw($sql));

      }


   public function searchableAs()
   {
       return 'purchase_order';
   }

   public function purchaseRequisition(){
      return $this->belongsTo('App\Models\PurchaseRequisition','purchase_id');
   }

   public function supplier(){
      return $this->belongsTo('App\Models\Supplier','supplier_id');
   }

   public function supplierContact(){
      return $this->belongsTo('App\Models\SupplierContact','supplier_contact_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function paymentTerm(){
      return $this->belongsTo('App\Models\PaymentTerm','payment_term_id');
   }

   public function money(){
      return $this->belongsTo(Currency::class,'currency','name');
   }

   public function poTerm(){
      return $this->belongsTo('App\Models\PoTerm','po_term_id');
   }

   public function poNote(){
      return $this->belongsTo('App\Models\PoNotes','po_note');
   }

   public function purchaseOrderItem(){
      return $this->hasMany('App\Models\PurchaseOrderItem','po_id')->orderBy('pr_item_id','ASC');
   }

}
