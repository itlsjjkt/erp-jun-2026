<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Auth;

class DphSupplier extends Model
{
   /**
     * The table associated with the model.
     *
     * @var string
     */
   protected $table = 'dph_suppliers';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
   protected $guarded    = ['id'];
   
   public static function getData($request){
      $query = DB::table('dph_suppliers')
      ->select(
          'dph_suppliers.*',
          'suppliers.name AS supplier'
      )
      ->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'dph_suppliers.supplier_id')
      ->when(!empty($request['amp;supplier_id']), function ($query) use ($request) {
          return $query->where('dph_suppliers.supplier_id',$request['amp;supplier_id']);
      })
      ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['amp;start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('dph.created_at', [$start , $end]);
      });
      return $query;
   }  

   public static function getByID($id){
      $query = DB::table('dph_suppliers')
      ->select(
         'dph_suppliers.*',
         'payment_terms.name AS payment_term',  
         'po_terms.name AS po_term',
         'po_terms.description AS po_termDescription',
         'purchases.mr_file',
         'purchase_requisitions.doc_no AS pr_no',
         'purchase_requisitions.purchase_id AS dpm_id',
         'purchase_requisitions.dpm_no AS dpm_no',
         'purchase_requisitions.location_id as location_id',
         'suppliers.name AS supplier',
         'supplier_contacts.name AS picName',
         'supplier_contacts.telp AS picTelp',
         'supplier_contacts.title AS picTitle',
         'supplier_contacts.email AS picEmail',
         'locations.name AS location',
         'companies.name AS company', 
         'companies.alias AS company_code',
         'companies.id AS company_id',
         'companies.address AS companyAddress',
         'companies.telp AS companyTelp',
         'companies.fax AS companyFax',
         'created_users.name AS created',  
         'departments.name AS department'
      )
      ->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'dph_suppliers.supplier_id')
      ->leftJoin('supplier_contacts', function($join) {
         $join->on('suppliers.id', '=', 'supplier_contacts.supplier_id')
               ->on('dph_suppliers.supplier_contact_id', '=', 'supplier_contacts.id');
      })
      ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'dph.purchase_id')
      ->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
      ->leftJoin('payment_terms', 'payment_terms.id', '=', 'dph_suppliers.payment_term_id')
      ->leftJoin('po_terms', 'po_terms.id', '=', 'dph_suppliers.po_term_id')
      ->leftJoin('users AS created_users', 'created_users.id', '=', 'dph.created_by') 
      ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
      ->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
      ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
      ->where('dph_suppliers.id', $id)
      ->first();
      return $query;
   }

   public static function getProductItem($id){
      $query = DB::table('dph_items')
        ->select(
         'dph_items.*',
         'dph_items.qty_parsial AS qty_parsialPO' ,
         'purchase_items.qty_parsial',
         'purchase_items.qty AS qtyPR',
         'purchase_items.flag AS flag',
         'purchase_items.needed_on_date',
         'purchase_items.qty AS qty_pr', 
         'purchase_items.po_status',
         'master_item_products.name AS product', 
         'master_item_products.code AS productCode', 
         'master_item_products.part_number AS productPartNumber',
         'master_item_brands.name AS productBrand'
         )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'dph_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'dph_items.pr_item_id')
        ->where('dph_items.dph_id', $id)
        ->orderBy('pr_item_id','ASC')
        ->get();
      return $query;
   }

   public static function getByDphID($id , $user = null){
      $query = DB::table('dph_suppliers')
      ->select(
         'dph_suppliers.*',
         'users.name AS created',
         'payment_terms.name AS payment_term',
         'suppliers.name AS supplier',
         'supplier_contacts.name AS picName',
         'supplier_contacts.telp AS picTelp',
         'supplier_contacts.title AS picTitle',
         'supplier_contacts.email AS picEmail',
      )
      ->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'dph_suppliers.supplier_id')
      ->leftJoin('supplier_contacts', function($join) {
         $join->on('suppliers.id', '=', 'supplier_contacts.supplier_id')
               ->on('dph_suppliers.supplier_contact_id', '=', 'supplier_contacts.id');
      })
      ->leftJoin('payment_terms', 'payment_terms.id', '=', 'dph_suppliers.payment_term_id')
      ->leftJoin('users', 'users.id', '=', 'dph.created_by')
      ->when(!empty($user), function ($query) use ($user) {
         return $query->where('dph.created_by', $user);
      })
      ->where('dph.id',$id)
      ->get();
      return $query;
   }
   
   public function searchableAs()
   {
         return 'dph_suppliers';
   }

   public function Dph(){
      return $this->belongsTo('App\Models\Dph','dph_id');
   }

   public function DphItem(){
      return $this->hasMany('App\Models\DphItem','dph_suppliers_id');
   }

   public function supplier(){
      return $this->belongsTo('App\Models\Supplier','supplier_id');
   }

   public function supplierContact(){
      return $this->belongsTo('App\Models\SupplierContact','supplier_contact_id');
   }
   
}