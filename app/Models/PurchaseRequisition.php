<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class PurchaseRequisition extends Model
{

   /**
     * The table associated with the model.
     *
     * @var string
     */
   protected $table = 'purchase_requisitions';
   public $timestamps = false;

   protected $primaryKey = 'id';
   protected $guarded = ['id'];


   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'like', '%'.$s.'%');
   }

   public static function getByID($id){

        $query = DB::table('purchase_requisitions')
        ->select('purchase_requisitions.*',
        'purchases.mr_file',
        'purchases.doc_no AS no_dpm',
        'purchases.description AS description',
        'users.name AS created',
        'departments.name AS department',
        'projects.name AS project',
        'locations.name AS location',
        'companies.name AS company',
        'companies.alias AS company_code',
        'companies.id AS company_id',
        'companies.address AS companyAddress',
        'companies.telp AS companyTelp',
        'companies.fax AS companyFax',
        'rejected.name AS rejected')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->leftJoin('users as rejected', 'rejected.id', '=', 'purchase_requisitions.rejected_by')
        ->where('purchase_requisitions.id', $id)
        ->first();
      return $query;

   }


   public static function getProductItem($id, $purchaser_id = null){

      $query = DB::table('purchase_items')
      ->select('purchase_items.*',
      'master_item_products.name AS product',
      'master_item_products.code AS productCode',
      'master_item_products.part_number AS productPartNumber',
      'master_item_brands.name AS productBrand')
      ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
      ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
      ->when(isset($purchaser_id), function ($result) use ($purchaser_id) {
         return $result->where('purchase_items.assigned_id',$purchaser_id);
      })
      ->where('purchase_items.pr_id', $id)
      ->where('purchase_items.pr_status', 1)
      ->whereIn('purchase_items.po_status',[0,2])
      ->orderBy('purchase_items.id','ASC')
      ->get();

      return $query;
   }

   public static function getByPOID($id){

      $query = DB::table('purchase_requisitions')
      ->select('purchase_requisitions.*','po.doc_no AS no_po','purchases.doc_no AS no_dpm','users.name AS created','departments.name AS department','locations.name AS location','companies.name AS company')
      ->leftJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
      ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
      ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
      ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
      ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
      ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
      ->where('purchase_requisitions.id', $id)
      ->first();
      return $query;
   }

   public static function getPOItem($id){

      $query = DB::table('po_items')
        ->select('po_items.*','master_item_products.name AS product','purchase_items.notes', 'purchase_items.flag', 'purchase_items.usage', 'purchase_items.needed_on_date', 'purchase_items.notes',
        'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','master_item_brands.name AS productBrand')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->where('po_items.po_id', $id)
        ->orderBy('po_items.pr_item_id','ASC')
        ->get();
      return $query;

   }


   public static function getByDPM($id){

      $query = DB::table('purchase_requisitions')
         ->select('purchase_requisitions.*',
         'purchases.mr_file',
         'purchases.doc_no AS no_dpm',
         'users.name AS created',
         'departments.name AS department',
         'locations.name AS location',
         'companies.name AS company')
         ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
         ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
         ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
         ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
         ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
         ->where('purchase_requisitions.purchase_id', $id)
         ->get();
      return $query;

   }



   public static function getStats($assign_id = null){

      $where = '';
      $whereCompany = '';
      if($assign_id){
          $where =  "
          RIGHT JOIN
            (SELECT pr_id
               FROM purchase_items
               WHERE purchase_items.po_status IN (0,2)
               AND purchase_items.status = 4
               AND purchase_items.assigned_id = ". $assign_id."
               GROUP BY pr_id
            ) t2 ON t2.pr_id = purchase_requisitions.id";
      }

      if(isAdministratorCompany()){
         $whereCompany = "
             LEFT JOIN locations ON locations.id = purchase_requisitions.location_id
            WHERE locations.company_id = ".Auth::user()->company_id;
      }

      $sql = "

          SELECT
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 0 OR purchase_requisitions.status IS NULL THEN 1 ELSE 0 END),0) AS elevated_po,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 1 THEN 1 ELSE 0 END),0) AS on_progress,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 2 THEN 1 ELSE 0 END),0) AS parsial,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 3 THEN 1 ELSE 0 END),0) AS revision,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 4 THEN 1 ELSE 0 END),0) AS done,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 5 THEN 1 ELSE 0 END),0) AS close,
          COALESCE(SUM(CASE WHEN purchase_requisitions.status = 6 THEN 1 ELSE 0 END),0) AS close_parsial
          FROM purchase_requisitions
          $where
          $whereCompany
      ";
      return DB::select( DB::raw($sql));

  }



   public function searchableAs()
   {
       return 'purchase_requisition';
   }

   public function PurchaseRequest(){
      return $this->belongsTo('App\Models\PurchaseRequest','purchase_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function department(){
      return $this->belongsTo('App\Models\Department','department_id');
   }

   public function project(){
      return $this->belongsTo('App\Models\Project','project_id');
   }

   public function PurchaseRequestItem(){
      if(Auth::user()->data_access==2) return $this->hasMany('App\Models\PurchaseRequestItem','pr_id')->where('assigned_id',Auth::user()->id)->orderBy('id','ASC');
      else return $this->hasMany('App\Models\PurchaseRequestItem','pr_id')->orderBy('id','ASC');
   }

   public function location(){
      return $this->belongsTo('App\Models\Workarea','location_id');
   }


   public function assignItem(){
      return $this->hasMany('App\Models\PurchaseRequestItem','pr_id')->whereIn('po_status',array('0','2'))->whereNull('assigned_id')->orderBy('id','ASC');
   }


   public function reassignItem(){
      return $this->hasMany('App\Models\PurchaseRequestItem','pr_id')->whereIn('po_status',array('0','2'))->whereNotNull('assigned_id')->orderBy('id','ASC');
   }

}
