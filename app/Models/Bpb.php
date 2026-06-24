<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use Auth;

class Bpb extends Model
{


   /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bpb';



    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

   protected $guarded  = ['id'];

   public function scopeSearch($query, $s)
   {
      return $query->where('doc_no', 'like', '%'.$s.'%');
   }



   public static function getData($request, $user = null){

        $query = DB::table('bpb')
        ->select(
            'bpb.id',
            'bpb.doc_no',
            'bpb.received_by',
            'bpb.status',
            'bpb.created_at',
            'bpb.updated_at',
            'bpb.created_by',
            'bpb.updated_by',
            'bpb.notes',
            'bpb.spb_id',
            'bpb.publish',
            'bpb.po_id',
            'bpb.attachment_file',
            'users.name AS created',
            'spb.doc_no AS noSPB'
        )
        ->leftJoin('spb', 'spb.id', '=', 'bpb.spb_id')
        ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
        ->whereNull('bpb.po_id')
        ->when(!empty($user), function ($query) use ($user) {
            return $query->where('bpb.created_by', $user);
        })
        ->when(!empty($request['start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('bpb.created_at', [$start , $end]);
        });

        if(isAdministratorCompany()){
            $query->leftJoin('bpb_items','bpb_items.bpb_id','=','bpb.id')
            ->leftJoin('purchase_items','purchase_items.id','=','bpb_items.pr_item_id')
            ->leftJoin('purchases','purchases.id','=','purchase_items.purchase_id')
            ->leftJoin('locations','locations.id','=','purchases.location_id')
            ->where('locations.company_id','=',Auth::user()->company_id)
            ->groupBy(
                'bpb.id',
                'bpb.doc_no',
                'bpb.received_by',
                'bpb.status',
                'bpb.created_at',
                'bpb.updated_at',
                'bpb.created_by',
                'bpb.updated_by',
                'bpb.notes',
                'bpb.spb_id',
                'bpb.publish',
                'bpb.po_id',
                'bpb.attachment_file',
                'users.name',
                'spb.doc_no'
            );
        }
      return $query;

   }



   public static function getDataFranco($request, $user = null){

      $query = DB::table('bpb')
      ->select(
         'bpb.*',
         'users.name AS created',
         'po.doc_no AS noPO'
      )
      ->leftJoin('po', 'po.id', '=', 'bpb.po_id')
      ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
      ->whereNull('bpb.spb_id')
      ->when(!empty($user), function ($query) use ($user) {
          return $query->where('bpb.created_by', $user);
      })
      ->when(!empty($request['start_date']), function ($query) use ($request) {
          $start = date("Y-m-d",strtotime($request['start_date']));
          $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
          return $query->whereBetween('bpb.created_at', [$start , $end]);
      });

      if(isAdministratorCompany()){
        $query->where('po.company_id','=',Auth::user()->company_id);
      }

      return $query;

   }



   public static function getByID($id){

      $query = DB::table('bpb')
      ->select('bpb.*',
         'users.name AS created',
         'spb.doc_no AS noSPB',
      )
      ->leftJoin('spb', 'spb.id', '=', 'bpb.spb_id')
      ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
      ->where('bpb.id', $id)
      ->first();

      return $query;

   }


    public static function getProductItem($id){

        $query = DB::table('bpb_items')
            ->select(
            DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
            'bpb_items.*',
            'spb_kolis.id AS idKoli',
            'spb_kolis.qty AS qtyKoli',
            'spb_kolis.bpb_status',
            'spb_kolis.qty_parsial',
            'spb_kolis.location_id',
            'po.doc_no AS noPO',
            'po_items.price',
            'po_items.discount AS price_discount',
            'po_items.measure',
            'suppliers.name AS supplier',
            'purchase_requisitions.doc_no AS noPR',
            'purchase_requisitions.dpm_no AS noDPM',
            'spb.doc_no AS noSPB',
            'lpb.doc_no AS noLPB',
            'master_item_products.id AS product_id',
            'po_items.specification',
            'master_item_products.name AS product',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'master_item_products.measure_inventory AS productMeasure',
            'master_item_products.conversion AS productConversion',
            'master_item_brands.name AS productBrand'
            )
            ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
            ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
            ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
            ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->where('bpb_items.bpb_id', $id)
            ->orderBy('no_koli', 'ASC');

            if(isAdministratorCompany()){
                $query->where('po.company_id','=',Auth::user()->company_id);
            }

            $data = $query->get();

        return $data;

    }


   public static function getProductFrancoItem($id){

      $query = DB::table('bpb_items')
        ->select('bpb_items.*',
         'po_items.id AS idPO',
         'po_items.qty AS qtyPO',
         'po_items.lpb_status',
         'po_items.qty_parsial',
         'po_items.price',
         'po_items.discount AS price_discount',
         'po_items.measure',
         'master_item_products.id AS product_id',
         'po_items.specification',
         'master_item_products.name AS product',
         'master_item_products.code AS productCode',
         'master_item_products.part_number AS productPartNumber',
         'master_item_products.measure_inventory AS productMeasure',
         'master_item_products.conversion AS productConversion',
         'master_item_brands.name AS productBrand',
         'purchase_requisitions.doc_no AS noPR',
         'purchase_requisitions.dpm_no AS noDPM',
         'purchase_requisitions.location_id',
        )
        ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->where('bpb_items.bpb_id', $id)
        ->orderBy('idPO', 'ASC')
        ->get();
       return $query;


   }

   public static function getByLPBItem($id){
      $query = DB::table('bpb_items')
      ->select('bpb.doc_no',
         'bpb.created_at',
         'bpb.received_by',
         'spb.doc_no AS spb_no',
         'spb_kolis.id AS idKoli',
         'spb_kolis.qty AS qtyKoli',
         'spb_kolis.bpb_status',
         'spb_kolis.qty_parsial',
         'spb_kolis.spb_item_id AS spb_item_id',
         'bpb_items.*',
         'users.name AS created'
      )
      ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
      ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
      ->leftJoin('spb', 'bpb.spb_id', '=', 'spb.id')
      ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
      ->whereIn('spb_kolis.spb_item_id', $id)
      ->whereNotNull('bpb.spb_id')
      ->get();

      return $query;

   }


   public static function getBySPB($id){

      $query = DB::table('bpb')
      ->select('bpb.*','spb.doc_no AS noSPB','users.name AS created')
      ->leftJoin('spb', 'spb.id', '=', 'bpb.spb_id')
      ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
      ->whereIn('bpb.spb_id', $id)
      ->get();

      return $query;

   }


   public static function getBpbFranco($id){

      $query = DB::table('bpb')
      ->select('bpb.*',
         'users.name AS created',
         'po.doc_no AS noPO',
      )
      ->leftJoin('po', 'po.id', '=', 'bpb.po_id')
      ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
      ->whereIn('po.id', $id)
      ->get();

      return $query;

   }



   public function BpbItem(){
      return $this->hasMany('App\Models\BpbItem','bpb_id');
   }

   public function purchaseOrder(){
      return $this->belongsTo(PurchaseOrder::class,'po_id');
   }


   public function spb(){
      return $this->belongsTo(Spb::class,'spb_id');
   }

   public function creator(){
      return $this->belongsTo('App\User','created_by');
   }

   public function searchableAs()
   {
       return 'bpb';
   }


    public static function getAllBpbData(){
        try{
            $query = DB::table('bpb')->where('status','=',1)->select('*')->orderBy('id','DESC')->get();
            return response()->json($query);
        } catch (\Exception $e) {
            return response()->json(['error' => 'AN ERROR OCCURRED WHILE FETCHING THE DATA.'], 500);
        }
    }
    public static function getBpbDatabyIdBpb($id)
    {
        try {
            // Fetch BPB data based on ID
            $query = DB::table('bpb')
                ->where('status', '=', 1)
                ->where('bpb.id', '=', $id)
                ->select('*')
                ->orderBy('id', 'DESC')
                ->get();

            if ($query->isNotEmpty()) {
                $queryItem = self::getBpbItemByIdBpb($query[0]->id, $query[0]->po_id)->getData();
            } else {
                $queryItem = [];
            }
            return response()->json([
                'bpb' => $query,
                'bpb_items' => $queryItem
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the data.'], 500);
        }
    }

    public static function getBpbItemByIdBpb($id, $po_id){
        try{
            if(!$po_id){
                $data = DB::table('bpb_items')
                    ->select(
                        'bpb.id AS idBpb',
                        'bpb.doc_no AS doc_noBpb',
                        'bpb_items.id AS idBpbItem',
                        'bpb_items.qty AS qtyBpbItem',
                        'po.id AS idPo',
                        'po.doc_no AS doc_noPo',
                        'po_items.id AS idPoItem',
                        'po_items.qty AS qtyPoItem',
                        'po_items.price AS pricePoItem',
                        'po_items.discount AS discountPoItem',
                        'po_items.measure AS measurePoItem',
                        'po_items.specification AS specificationPoItem',
                        'master_item_products.id AS idProduct',
                        'master_item_products.name AS nameProduct',
                        'master_item_products.code AS codeProduct',
                        'master_item_products.part_number AS part_numberProduct',
                        'master_item_brands.id AS idBrand',
                        'master_item_brands.name AS nameBrand',
                        'purchase_requisitions.id AS idPr',
                        'purchase_requisitions.doc_no AS doc_noPr'
                    )
                    ->leftJoin('bpb','bpb.id','=','bpb_items.bpb_id')
                    ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
                    ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
                    ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
                    ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
                    ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
                    ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
                    ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
                    ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
                    ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
                    ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
                    ->where('bpb.doc_no','NOT LIKE','%FRANCO%')
                    ->where('bpb.status','=',1)
                    ->where('bpb.id','=',$id)
                    ->get();
            }else{
                $data = DB::table('bpb_items')
                    ->select(
                        'bpb.id AS idBpb',
                        'bpb.doc_no AS doc_noBpb',
                        'bpb_items.id AS idBpbItem',
                        'bpb_items.qty AS qtyBpbItem',
                        'po.id AS idPo',
                        'po.doc_no AS doc_noPo',
                        'po_items.id AS idPoItem',
                        'po_items.qty AS qtyPoItem',
                        'po_items.price AS pricePoItem',
                        'po_items.discount AS discountPoItem',
                        'po_items.measure AS measurePoItem',
                        'po_items.specification AS specificationPoItem',
                        'master_item_products.id AS idProduct',
                        'master_item_products.name AS nameProduct',
                        'master_item_products.code AS codeProduct',
                        'master_item_products.part_number AS part_numberProduct',
                        'master_item_brands.id AS idBrand',
                        'master_item_brands.name AS nameBrand',
                        'purchase_requisitions.id AS idPr',
                        'purchase_requisitions.doc_no AS doc_noPr'
                    )
                    ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
                    ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
                    ->leftJoin('po','po.id','=','po_items.po_id')
                    ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
                    ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                    ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
                    ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
                    ->where('bpb.doc_no', 'LIKE', '%FRANCO%')
                    ->where('bpb.status','=',1)
                    ->where('bpb.id','=',$id)
                    ->get();
            }
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'AN ERROR OCCURRED WHILE FETCHING THE DATA.'], 500);
        }
    }

    public static function getAllDataItemBpb(){
        try{
        $itemBpb = DB::table('bpb_items')
                ->select(
                    'bpb.id AS idBpb',
                    'bpb.doc_no AS doc_noBpb',
                    'bpb_items.id AS idBpbItem',
                    'bpb_items.qty AS qtyBpbItem',
                    'po.id AS idPo',
                    'po.doc_no AS doc_noPo',
                    'po_items.id AS idPoItem',
                    'po_items.qty AS qtyPoItem',
                    'po_items.price AS pricePoItem',
                    'po_items.discount AS discountPoItem',
                    'po_items.measure AS measurePoItem',
                    'po_items.specification AS specificationPoItem',
                    'master_item_products.id AS idProduct',
                    'master_item_products.name AS nameProduct',
                    'master_item_products.code AS codeProduct',
                    'master_item_products.part_number AS part_numberProduct',
                    'master_item_brands.id AS idBrand',
                    'master_item_brands.name AS nameBrand',
                    'purchase_requisitions.id AS idPr',
                    'purchase_requisitions.doc_no AS doc_noPr'
                )
                ->leftJoin('bpb','bpb.id','=','bpb_items.bpb_id')
                ->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
                ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
                ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
                ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
                ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
                ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
                ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
                ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
                ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
                ->where('bpb.doc_no','NOT LIKE','%FRANCO%')
                ->where('bpb.status','=',1);

        $itemBpbFranco = DB::table('bpb_items')
                ->select(
                    'bpb.id AS idBpb',
                    'bpb.doc_no AS doc_noBpb',
                    'bpb_items.id AS idBpbItem',
                    'bpb_items.qty AS qtyBpbItem',
                    'po.id AS idPo',
                    'po.doc_no AS doc_noPo',
                    'po_items.id AS idPoItem',
                    'po_items.qty AS qtyPoItem',
                    'po_items.price AS pricePoItem',
                    'po_items.discount AS discountPoItem',
                    'po_items.measure AS measurePoItem',
                    'po_items.specification AS specificationPoItem',
                    'master_item_products.id AS idProduct',
                    'master_item_products.name AS nameProduct',
                    'master_item_products.code AS codeProduct',
                    'master_item_products.part_number AS part_numberProduct',
                    'master_item_brands.id AS idBrand',
                    'master_item_brands.name AS nameBrand',
                    'purchase_requisitions.id AS idPr',
                    'purchase_requisitions.doc_no AS doc_noPr'
                )
                ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
                ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
                ->leftJoin('po','po.id','=','po_items.po_id')
                ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
                ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
                ->where('bpb.doc_no', 'LIKE', '%FRANCO%')
                ->where('bpb.status','=',1);
            $combinedData = $itemBpb->unionAll($itemBpbFranco)->get();
        return response()->json($combinedData);
        } catch (\Exception $e) {
        return response()->json(['error' => 'AN ERROR OCCURRED WHILE FETCHING THE DATA.'], 500);
        }
    }

}
