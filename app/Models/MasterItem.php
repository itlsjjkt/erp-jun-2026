<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_items';
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

     
    protected $fillable = ['name','code','type', 'status','created_by','updated_by'];


}
