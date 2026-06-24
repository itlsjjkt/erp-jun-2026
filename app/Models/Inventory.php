<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class Inventory extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventories';
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
    protected $guarded  = ['id'];


    public function searchableAs()
    {
        return 'inventories';
    }


    public function product(){
        return $this->belongsTo(MasterItemProduct::class,'product_id');
    }

    public function measure(){
        return $this->belongsTo(MasterMeasure::class,'measure_id');
    }


    public function location(){
        return $this->belongsTo(Workarea::class,'location_id');
    }

    public function department(){
        return $this->belongsTo(Department::class,'department_id');
    }

    public static function getByID($id){

        $query = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID',
        'locations.name AS location',
        'locations.email AS locationEmail',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->where('inventories.id', $id)
        ->first();

        return $query;
  
      }

    public static function getHistoryStock($id,$start_date= null, $end_date =null){

        $date_ttb = $date_adj = $date_return = $date_history ='';
        if($start_date != null){
            $start_date =  date("Y-m-d", strtotime($start_date) );
            $end_date =  date("Y-m-d", strtotime($end_date) );

            $date_return = " AND inventory_returns.created_at::date >= to_date('".$start_date."','YYYY-MM-DD') AND inventory_returns.created_at::date <= to_date('".$end_date."','YYYY-MM-DD') ";
            $date_history= " AND inventory_histories.created_at::date >= to_date('".$start_date."','YYYY-MM-DD') AND inventory_histories.created_at::date <= to_date('".$end_date."','YYYY-MM-DD') ";
        }

        $sql = "
        SELECT 
            inventory_histories.id,
            inventory_histories.message AS doc_no,
            inventory_histories.qty_in AS qty_in,
            inventory_histories.qty_out AS qty_out, 
            inventory_histories.description AS description, 
            inventory_histories.created_at AS created,
            inventory_histories.qty_awal AS stock_awal
        FROM inventory_histories
        WHERE inventory_histories.inventory_id = $id
        $date_history

        ORDER BY created DESC, id DESC

        ";

        return DB::select( DB::raw($sql));
        
    }

    public static function getMutation($date,$location = null, $is_local = null){
        
        if($is_local == 'local' ){
            $where = " WHERE t1.is_local = TRUE";
        }else{
            $where = " WHERE t1.is_local = FALSE";
        }
        if($location != 0 ){
            $where .= " AND t1.location_id = ". $location;
        }

        $start_date =  date("Y-m-d", strtotime($date) );

        $sql = "
        SELECT 
        master_item_products.name AS productname, 
        master_item_products.code AS productcode,
        master_item_products.part_number AS productpartnumber,
        locations.name AS location,measures.name AS unit, 
        t2.in, 
        t2.out, 
        t2.initial, 
        t1.code_rack,
        t1.id, 
        master_items.name AS item_name, 
        master_items.code AS item_code
        FROM inventories t1 
        INNER JOIN (

        WITH summary AS (
            SELECT p.inventory_id, 
                p.qty_in, 
                p.qty_out, 
                p.created_at,
                LAST_VALUE(p.qty_awal) OVER (
                    PARTITION BY p.inventory_id ORDER BY p.id DESC
                    RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING
                ) AS qty_awal
            FROM inventory_histories p
            WHERE CAST(p.created_at AS DATE) = '".$start_date."' 
            )

            SELECT inventory_id, COALESCE(SUM(qty_in),0) AS in, COALESCE(SUM(qty_out),0) AS out, max(qty_awal) AS initial
            FROM summary
            GROUP BY inventory_id

        ) t2 ON t1.id = t2.inventory_id 
        LEFT JOIN locations ON locations.id = t1.location_id 
        LEFT JOIN master_item_products ON master_item_products.id = t1.product_id 
        LEFT JOIN master_items ON master_items.id = master_item_products.item_id 
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory 
        $where
        ORDER BY master_item_products.name ASC
        ";

        return DB::select( $sql);
        
    }


    public static function getMutationDetail($start_date= null, $end_date =null,$location = null , $item  = null, $is_local = null){
       
        $start_date =  date("Y-m-d", strtotime($start_date) );

        if($end_date != null){
            $end_date =  date("Y-m-d", strtotime($end_date) );
            $where = " WHERE inventory_histories.created_at::date >= to_date('".$start_date."','YYYY-MM-DD') AND inventory_histories.created_at::date <= to_date('".$end_date."','YYYY-MM-DD') ";
        }else{
            $where = " WHERE CAST(inventory_histories.created_at AS DATE) = '".$start_date."' ";
        }
        if($location != null ){
            $where .= " AND inventories.location_id = ". $location;
        }
        if($item != null ){
            $where .= " AND master_item_products.item_id = ". $item;
        }
        if($is_local == 'local' ){
            $where .= " AND inventories.is_local = TRUE";
        }

        $sql = "
        SELECT
            inventories.code_rack,
            master_item_products.name AS productname, 
            master_item_products.code AS productcode, 
            master_item_products.part_number AS productpartnumber,
            master_item_brands.name AS productbrand,
            measures.name AS unit, 
            inventory_histories.id,
            inventory_histories.message,
            inventory_histories.notes,
            inventory_histories.qty_in AS qty_in,
            inventory_histories.qty_out AS qty_out, 
            inventory_histories.description AS description, 
            inventory_histories.created_at AS created,
            inventory_histories.qty_awal AS qty_awal
        FROM inventory_histories
        LEFT JOIN inventories ON inventories.id = inventory_histories.inventory_id
        LEFT JOIN master_item_products ON master_item_products.id = inventories.product_id
        LEFT JOIN master_item_brands ON master_item_brands.id = master_item_products.brand_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory 
        $where
        ORDER BY master_item_products.code, inventory_histories.created_at DESC
        ";

        return DB::select( $sql);
        
    }


    public static function getMutationMonth($month, $year, $location = null, $is_local = null){
       
        if($is_local == 'local' ){
            $where = " WHERE t1.is_local = TRUE";
        }else{
            $where = " WHERE t1.is_local = FALSE";
        }
        if($location != 0 ){
            $where .= " AND t1.location_id = ". $location;
        }

        $month =  date("m", strtotime($month) );

        $sql = "

        SELECT 
        master_item_products.name AS productname, 
        master_item_products.code AS productcode, 
        master_item_products.part_number AS productpartnumber,
        locations.name AS location,
        measures.name AS unit,
        t2.in, t2.out, t2.initial, t1.code_rack,t1.id,
        master_items.name AS item_name, master_items.code AS item_code
        FROM inventories t1 
        INNER JOIN (

        WITH summary AS (
            SELECT p.inventory_id, 
                p.qty_in, 
                p.qty_out, 
                p.created_at,
                LAST_VALUE(p.qty_awal) OVER (
                    PARTITION BY p.inventory_id ORDER BY p.id DESC
                    RANGE BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING
                ) AS qty_awal
            FROM inventory_histories p
            WHERE EXTRACT(YEAR from p.created_at) = '$year' 
            AND EXTRACT(MONTH from p.created_at) = '".$month."' 
            )

            SELECT inventory_id, COALESCE(SUM(qty_in),0) AS in, COALESCE(SUM(qty_out),0) AS out, max(qty_awal) AS initial
            FROM summary
            GROUP BY inventory_id

        ) t2 ON t1.id = t2.inventory_id 
        LEFT JOIN locations ON locations.id = t1.location_id 
        LEFT JOIN master_item_products ON master_item_products.id = t1.product_id 
        LEFT JOIN master_items ON master_items.id = master_item_products.item_id 
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory 
        $where
        ORDER BY master_item_products.name ASC

        ";

        return DB::select( $sql);
        
    }


    public static function getStokOpname($start_date= null, $end_date =null,$location = null , $item  = null, $is_local = null){
       
        $start_date =  date("Y-m-d", strtotime($start_date) );

        if($end_date != null){
            $end_date =  date("Y-m-d", strtotime($end_date) );
            $date= " WHERE inventory_stock_opname.created_at::date >= to_date('".$start_date."','YYYY-MM-DD') AND inventory_stock_opname.created_at::date <= to_date('".$end_date."','YYYY-MM-DD') ";
        }else{
            $date= " WHERE CAST(inventory_stock_opname.created_at AS DATE) = '".$start_date."' ";
        }
        $loc='';
        if($location != null ){
            $loc = " AND inventories.location_id = ". $location;
        }

        $item_id ='';
        if($item != null ){
            $item_id  = " AND master_item_products.item_id = ". $item;
        }


        $sql = "
        SELECT
            inventories.code_rack,
            users.name AS creator, 
            master_item_products.name AS productname, 
            master_item_products.code AS productcode, 
            master_item_products.part_number AS productpartnumber,
            measures.name AS unit, 
            master_item_brands.name AS productbrand,
            inventory_stock_opname.*
        FROM inventory_stock_opname
        LEFT JOIN inventories ON inventories.id = inventory_stock_opname.inventory_id
        LEFT JOIN master_item_products ON master_item_products.id = inventories.product_id
        LEFT JOIN master_item_brands ON master_item_brands.id = master_item_products.brand_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory 
        LEFT JOIN users ON users.id = inventory_stock_opname.created_by
        $date
        $loc
        $item_id
        ORDER BY inventory_stock_opname.created_at DESC
        ";

        return DB::select( $sql);
        
    }


    public static function getMutationLocal($start_date= null, $end_date =null,$location = null , $department = null, $is_local = null){
       
        $start_date =  date("Y-m-d", strtotime($start_date) );

        if($end_date != null){
            $end_date =  date("Y-m-d", strtotime($end_date) );
            $where = " WHERE inventory_histories.created_at::date >= to_date('".$start_date."','YYYY-MM-DD') AND inventory_histories.created_at::date <= to_date('".$end_date."','YYYY-MM-DD') ";
        }else{
            $where = " WHERE CAST(inventory_histories.created_at AS DATE) = '".$start_date."' ";
        }
        if($location != null ){
            $where .= " AND inventories.location_id = ". $location;
        }
        if($department != null ){
            $where .= " AND inventories.department_id = ". $department;
        }
        if($is_local == 'local' ){
            $where .= " AND inventories.is_local = TRUE";
        }

        $sql = "
        SELECT
            inventories.code_rack,
            master_item_products.name AS productname, 
            master_item_products.code AS productcode,
            measures.name AS unit,
            master_item_brands.name AS productbrand,
            inventory_histories.id,
            inventory_histories.message,
            inventory_histories.notes,
            inventory_histories.qty_in AS qty_in,
            inventory_histories.qty_out AS qty_out, 
            inventory_histories.description AS description, 
            inventory_histories.created_at AS created,
            inventory_histories.qty_awal AS qty_awal
        FROM inventory_histories
        LEFT JOIN inventories ON inventories.id = inventory_histories.inventory_id
        LEFT JOIN master_item_products ON master_item_products.id = inventories.product_id
        LEFT JOIN master_item_brands ON master_item_brands.id = master_item_products.brand_id
        LEFT JOIN measures ON measures.id = master_item_products.measure_inventory 
        $where
        ORDER BY master_item_products.code, inventory_histories.created_at DESC
        ";

        return DB::select( $sql);
        
    }

}
