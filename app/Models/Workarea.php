<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workarea extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locations';
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
    protected $fillable = ['name', 'alias','telp','email','city_id','company_id','address','isDPM','area_id','status'];


    public function company(){
        return $this->belongsTo(Company::class,'company_id');
     }

}
