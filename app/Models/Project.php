<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded    = ['id'];
    
    protected $casts = [
        'category' => 'json',
      ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

 
}
