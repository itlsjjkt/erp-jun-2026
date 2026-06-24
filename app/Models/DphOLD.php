<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dph extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dph';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


   public function supplier(){
    return $this->belongsTo('App\Models\Supplier','supplier_id');
 }

  
}
