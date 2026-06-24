<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProductType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_item_types';
    public $timestamps = false;

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
    protected $fillable = ['code','product_id', 'status'];


}
