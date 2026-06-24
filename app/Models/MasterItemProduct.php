<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class MasterItemProduct extends Model
{

    // 

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_item_products';
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
        return 'products';
    }


    public static function cekProductName($name, $part_number, $measure_id, $item_id, $brand, $id = null){

        $query = DB::table('master_item_products')
        ->select('id','name')
        ->where('item_id', $item_id)
        ->where('measure_id',  $measure_id)
        ->when(($part_number), function ($query) use ($part_number) {
           return $query->where('part_number', $part_number);
        })
        ->when(($brand), function ($query) use ($brand) {
            return $query->where('brand_id', $brand);
        })
        ->when(!is_null($id), function ($query) use ($id) {
            return $query->where('id','!=',$id);
        })
        ->where('name', $name)
        ->whereNull('deleted_at')
        ->get();
  
        return $query;
     }
     
     public function item(){
        return $this->belongsTo('App\Models\MasterItem','item_id');
     }

     public function brand(){
        return $this->belongsTo('App\Models\MasterBrand','brand_id');
     }

     public function measure(){
        return $this->belongsTo('App\Models\MasterMeasure','measure_id');
     }

     public function measureInventory(){
        return $this->belongsTo('App\Models\MasterMeasure','measure_inventory');
     }

}
