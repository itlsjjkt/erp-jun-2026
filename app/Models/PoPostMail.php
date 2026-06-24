<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoPostMail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'po_post_mails';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded    = ['id'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
}
