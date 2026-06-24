<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supplier_categories';

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
    protected $fillable = [
        'category_id',
        'supplier_id'
    ];
    public function searchableAs()
    {
        return 'supplier_categories';
    }
    public function masterItems(){
        return $this->belongsTo('App\Models\MasterItemProduct','category_id');
    }

    public function suppliers(){
        return $this->belongsTo('App\Models\Supplier','supplier_id');
    }

}
