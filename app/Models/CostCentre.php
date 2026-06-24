<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCentre extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cost_centre';
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
    protected $fillable = ['code','name', 'status','company_id','created_by'];


    
}
