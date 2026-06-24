<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;


class AssetCategory extends Model
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'asset_categories';
    protected $guarded  = ['id'];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    

    public function searchableAs()
    {
        return 'asset_categories';
    }
}
