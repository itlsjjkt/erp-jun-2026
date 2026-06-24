<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Auth;

class Dph extends Model
{
   /**
     * The table associated with the model.
     *
     * @var string
     */
   protected $table = 'dph';

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
      $query = DB::table('dph')
      ->select(
          'dph.*',
          'purchase_requisitions.doc_no AS no_pr',
          'purchase_requisitions.id AS id_pr',
          'users.name AS created'
      )
      ->leftJoin('users', 'users.id', '=', 'dph.created_by')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'dph.purchase_id')
      ->when(!empty($user), function ($query) use ($user) {
         return $query->where('dph.created_by', $user);
      })
      ->when(!empty($request['purchaser_id']), function ($query) use ($request) {
          return $query->where('dph.created_by',$request['purchaser_id']);
      })
      ->when(!empty($request['amp;project_id']), function ($query) use ($request) {
          return $query->where('purchase_requisitions.project_id',$request['amp;project_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('dph.created_at', [$start , $end]);
      })
      ->where('dph.status','!=', 10);

      return $query;
   }  

   public static function getByID($id) {  
      $query = DB::table('dph')
          ->select(
              'dph.*',
              'purchases.mr_file',
              'purchases.type AS type_dpm',
              'purchase_requisitions.doc_no AS pr_no',
              'purchase_requisitions.purchase_id AS dpm_id',
              'purchase_requisitions.dpm_no AS dpm_no',
              'purchase_requisitions.location_id AS location_id',
              'locations.name AS location',
              'companies.name AS company',
              'companies.alias AS company_code',
              'companies.id AS company_id',
              'companies.address AS companyAddress',
              'companies.telp AS companyTelp',
              'companies.fax AS companyFax',
              'created_users.name AS created',
              'departments.name AS department',
              'projects.name AS project',
              'approval.name AS position'
         )
         ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'dph.purchase_id')
         ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
         ->leftJoin('users AS created_users', 'created_users.id', '=', 'dph.created_by')
         ->leftJoin('users AS approval', 'approval.id', '=', 'dph.position')
         ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
         ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
         ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
         ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
         ->leftJoin('dph_suppliers','dph_suppliers.dph_id','=','dph.id')
         ->where('dph.id', $id)
         ->first();
  
      return $query;
  }

  public static function getItemCreate($pr_id, $id_items,$purchaser_id = null){
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
      ->where('purchase_items.pr_id', $pr_id)
      ->whereIn('purchase_items.id',$id_items)
      ->where('purchase_items.pr_status', 1)
      ->where('purchase_items.po_status','!=', 1)
      ->orderBy('purchase_items.id','ASC')
      ->get();

      return $query;
   }

   public function searchableAs()
   {
      return 'dph';
   }

   public function purchaseRequisition(){
      return $this->belongsTo('App\Models\PurchaseRequisition','purchase_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function DphVendor(){
      return $this->hasMany('App\Models\DphVendor','dph_id');
   }
}