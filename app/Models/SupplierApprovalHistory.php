<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierApprovalHistory extends Model
{
    protected $table      = 'supplier_approval_histories';
    protected $primaryKey = 'id';

    protected $fillable = [
        'supplier_id',
        'user_id',
        'jenis',
        'message',
        'date_approved',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
