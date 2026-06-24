<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentCompletion extends Model
{
    protected $table = 'payment_completions';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(PaymentCompletionDetail::class, 'pc_id');
    }
}
