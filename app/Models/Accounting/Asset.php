<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;


class Asset extends Model
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assets';
    protected $guarded  = ['id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    

    public static function getData($user_id =  null){

        return Asset::select(
            'assets.*',
            'asset_categories.name AS category',
            'master_item_products.name AS product',
            'master_item_products.code AS productCode',
            'users.name AS created'
        )
        ->leftJoin('asset_categories', 'asset_categories.id', '=', 'assets.asset_category_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'assets.product_id')
        ->leftJoin('users', 'users.id', '=', 'assets.created_by')
        ->when(!empty($user_id), function ($query) use ($user_id ) {
            return $query->where('assets.created_by',$user_id);
        })
        ->whereNull('assets.deleted_at');
    }


    public function product(){
        return $this->belongsTo('App\Models\MasterItemProduct','product_id');
    }


    public function category(){
        return $this->belongsTo(AssetCategory::class,'asset_category_id');
    }

    public function depreciation(){
        return $this->hasMany(AssetDepreciationBoard::class,'asset_id');
    }

    public function searchableAs()
    {
        return 'assets';
    }
}
