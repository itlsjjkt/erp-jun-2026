<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class PurchaseRequest extends Model
{
   
   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchases';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $primaryKey = 'id';
   protected $guarded    = ['id'];

   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'like', '%'.$s.'%');
   }


   public static function getData($request, $user_id = null){

      $query = DB::table('purchases')
      ->select(
          'purchases.*',
          'users.name AS created',
          'projects.name AS project',
          'departments.name AS department',
          'locations.name AS locationnn'
      )
      ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
      ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
      ->leftJoin('locations','locations.id','=','purchases.location_id')
      ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
      ->leftJoin('purchase_items','purchase_items.purchase_id','=','purchases.id')
      ->whereIn('purchases.status', [0, 1, 3, 11])
      ->where('purchase_items.pr_status','=',0)
      ->where('purchase_items.status','=',1)
      ->groupBy('purchases.id','users.name','projects.name','departments.name','locations.name')
      ->when(!empty($request['company_id']), function ($query) use ($request) {
         return $query->where('locations.company_id',$request['company_id']);
      })
      ->when(!empty($request['location_id']), function ($query) use ($request) {
         return $query->where('purchases.location_id',$request['location_id']);
      })
      ->when(!empty($request['department_id']), function ($query) use ($request) {
          return $query->where('purchases.department_id',$request['department_id']);
      })
      ->when(!empty($request['amp;project_id']), function ($query) use ($request) {
          return $query->where('purchases.project_id',$request['amp;project_id']);
      })
      ->when(!empty($request['amp;location_id']), function ($query) use ($request){
          return $query->where('purchases.location_id',$request['amp;location_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('purchases.created_at', [$start , $end]);
      })
      ->when(!empty($user_id), function ($query) use ($user_id) {
         return $query->where('purchases.created_by',$user_id);
      });

      return $query;
   }

  

   public static function getByID($id){

      $query = DB::table('purchases')
        ->select('purchases.*',
         'users.name AS created',
         'departments.name AS department',
         'projects.name AS project',
         'locations.name AS location',
         'locations.alias AS locationAlias',
         'companies.name AS company', 
         'companies.address AS companyAddress',
         'companies.telp AS companyTelp',
         'companies.fax AS companyFax',
         'companies.alias AS companyAlias')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->where('purchases.id', $id)
        ->first();
      return $query;

   }



   public static function getProductItem($id){

      $query = DB::table('purchase_items')
      ->select('purchase_items.*',
      'master_item_products.id AS productId',
      'master_item_products.name AS product',
      'master_item_products.code AS productCode',
      'master_item_products.part_number AS productPartNumber',
      'users.name AS approved',
      'purchases.type AS typeDpm',
      'purchases.status AS statusDpm',
      'purchases.department_id AS departmentId',
      'purchase_requisitions.status AS statusPr',
      'master_item_brands.name AS productBrand')
      ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
      ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
      ->leftJoin('users', 'users.id', '=', 'purchase_items.last_approved')
      ->leftJoin('purchases','purchases.id','=','purchase_items.purchase_id')
      ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','purchase_items.pr_id')
      ->where('purchase_items.purchase_id', $id)
      ->orderBy('purchase_items.id','ASC')
      ->get();
      
      return $query;
    }



   
   public static function getProductItemRevisi($id){

      $query = DB::table('purchase_items')
      ->select('purchase_items.*',
      'master_item_products.name AS product', 
      'master_item_products.code AS productCode', 
      'master_item_products.part_number AS productPartNumber',
      'users.name AS approved',
      'master_item_brands.name AS productBrand')
      ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
      ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
      ->leftJoin('users', 'users.id', '=', 'purchase_items.last_approved')
      ->where('purchase_items.purchase_id', $id)
      ->where('purchase_items.po_status', 0)
      ->orderBy('id','ASC')
      ->get();

      return $query;
   }



   public function searchableAs()
   {
       return 'purchase_requisition';
   }

   public function PurchaseRequestItem(){
      return $this->hasMany('App\Models\PurchaseRequestItem','purchase_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function department(){
      return $this->belongsTo('App\Models\Department','department_id');
   }

   public function purchaseRequest(){
      return $this->hasOne(PurchaseRequisition::class,'purchase_id','id');
   }
   
   public function project(){
      return $this->belongsTo('App\Models\Project','project_id');
   }

   public function history(){
      return $this->hasMany('App\Models\PurchaseRequestHistory','purchase_id');
   }

   public function lastHistory(){
      return $this->hasMany('App\Models\PurchaseRequestHistory','purchase_id')->latest()->limit(1);
   }

   public function location(){
      return $this->belongsTo('App\Models\Workarea','location_id');
   }



}
