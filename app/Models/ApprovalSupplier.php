<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalSupplier extends Model
{
    protected $table      = 'approval_suppliers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'step',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
}
