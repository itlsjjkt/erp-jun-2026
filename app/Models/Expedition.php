<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Expedition extends Model
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'expeditions';
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
    protected $fillable = ['name','telp','pic', 'address', 'email','status','fax','created_by','updated_by','is_handcarry'];

    public function searchableAs()
    {
        return 'expeditions';
    }
}
