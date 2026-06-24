<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLogistic extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'approval_logistics';
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
    protected $fillable = ['employee_id','location_id','step'];

      /*
    |------------------------------------------------------------------------------------
    | Attributes
    |------------------------------------------------------------------------------------
    */

    
}
