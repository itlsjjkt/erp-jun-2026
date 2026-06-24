<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class InventoryHistory extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_histories';
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
    protected $fillable = ['inventory_id', 'qty_in','message','qty_awal','qty_out','description','notes'];

    protected $casts = [
        'notes' => 'array',
    ];

}
