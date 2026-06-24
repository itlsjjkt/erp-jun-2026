<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItemCategory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_item_categories';
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
    protected $fillable = ['name', 'status','pid','created_by','updated_by'];


}
