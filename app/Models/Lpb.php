<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use Auth;

class Lpb extends Model
{


   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lpb';
    protected $guarded  = ['id'];

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'like', '%'.$s.'%');
   }


   public static function getData($request, $user_id = null){

      $query = DB::table('lpb')
      ->select(
         'lpb.*',
         'po.doc_no AS po_no',
         'purchase_requisitions.doc_no AS pr_no',
         'purchase_requisitions.dpm_no AS dpm_no',
         'purchases.created_at AS created_dpm'
      )
      ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
      ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
      ->leftJoin('purchases','purchases.id','=','purchase_requisitions.purchase_id')
      ->leftJoin('locations','locations.id','=','lpb.location_id')
      ->leftJoin('users', 'users.id', '=', 'lpb.created_by')
      ->when(!empty($request['location_id']), function ($query) use ($request) {
         return $query->where('lpb.location_id',$request['location_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('lpb.created_at', [$start , $end]);
      })
      ->when(!empty($user_id), function ($query) use ($user_id) {
         return $query->where('lpb.created_by',$user_id);
      });
      if(isAdministratorCompany()){
         $query->where('locations.company_id','=',Auth::user()->company_id);
      }

      return $query;

  }


   public static function getByID($id){

      $query = DB::table('lpb')
      ->select('lpb.*', 'po.doc_no AS po_no', 'purchase_requisitions.doc_no as pr_no',
       'purchase_requisitions.dpm_no as dpm_no',
         'purchase_requisitions.location_id as locationID',
         'suppliers.name AS supplier',
         'supplier_contacts.id AS supplierPICID',
         'supplier_contacts.name AS supplierPIC',
         'supplier_contacts.email AS supplierEmail',
         'supplier_contacts.telp AS supplierTelp',
         'locations.name AS location',
         'locations.address AS locationAddress',
         'locations.telp AS locationTelp',
         'companies.name AS company',
         'companies.logo AS companyLogo',
         'companies.fax AS companyFax',
         'companies.address AS companyAddress',
         'companies.telp AS companyTelp',
         'users.name AS created',
         'purchase_requisitions.purchase_id AS dpm_id',
         'departments.name AS department')
      ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('purchases','purchases.id','=','purchase_requisitions.purchase_id')
      ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
      ->leftJoin('supplier_contacts', 'supplier_contacts.id', '=', 'po.supplier_contact_id')
      ->leftJoin('locations', 'locations.id', '=', 'lpb.location_id')
      ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
      ->leftJoin('users', 'users.id', '=', 'lpb.created_by')
      ->where('lpb.id', $id)
      ->first();

      return $query;

   }


   public static function getByDPM($id){

      $query = DB::table('lpb')
      ->select('lpb.*', 'po.doc_no AS po_no', 'purchase_requisitions.doc_no as pr_no', 'purchase_requisitions.dpm_no as dpm_no','purchase_requisitions.location_id as locationID',
          'users.name AS created')
      ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
      ->leftJoin('users', 'users.id', '=', 'lpb.created_by')
      ->where('purchase_requisitions.purchase_id', $id)
      ->get();

      return $query;

   }

   public static function getProductItem($id){

      $query = DB::table('lpb_items')
        ->select('lpb_items.*','po_items.qty as qtyPO','po_items.qty_parsial as qty_parsial', 'po_items.lpb_status',
        'po_items.price as price',
        'master_item_products.name AS product','po_items.specification',
         'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
         'po_items.measure','master_item_brands.name AS productBrand',
         'master_item_products.conversion AS productConversion', 'minv.name AS measure_inventory')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('measures as minv', 'master_item_products.measure_inventory', '=', 'minv.id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->where('lpb_items.lpb_id', $id)
      //   ->where('lpb_items.status', 1)
        ->get();
      return $query;

   }

   public static function gethistory($id){

      $query =  DB::table('lpb_histories')
      ->select('lpb_histories.*','users.name AS employee')
      ->leftJoin('users', 'users.id', '=', 'lpb_histories.user_id')
      ->where('lpb_histories.lpb_id', $id)
      ->orderBy('lpb_histories.created_at', 'DESC')
      ->get();

      return $query;

   }


   public function searchableAs()
   {
       return 'lpb';
   }

   public function LpbItem(){
      return $this->hasMany('App\Models\LpbItem','lpb_id');
   }

}
