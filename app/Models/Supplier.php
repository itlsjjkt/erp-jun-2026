<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table      = 'suppliers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'is_ppn',
        'payment_term',
        'is_block',
        'block_reason',
        'address',
        'status',
        'created_by',
        'updated_by',
        'npwp',
        'nib',
        'pkp',
        'surat_agent',
        'currency',
        'payment_method_id',
        'step',
        'position',
        'approval_status',
    ];

    public function searchableAs()
    {
        return 'suppliers';
    }
}
