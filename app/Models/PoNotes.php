<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoNotes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'po_notes';
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
    protected $fillable = ['name','status','description','created_by','updated_by'];

  
}
