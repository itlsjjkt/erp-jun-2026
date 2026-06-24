<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityHistories extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = 'activity_id';
    protected $table = 'user_activity_histories';
    protected $dateFormat = 'Y-m-d H:i:sO';
    public $timestamps = false;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['library_id','library_id_history','action','created_by','created_at', 'company_id', 'department_id', 'notes'];

    public function searchableAs()
    {
        return 'userActivityHistories';
    }
}
