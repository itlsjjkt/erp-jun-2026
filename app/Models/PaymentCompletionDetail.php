<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Component;

class PaymentCompletionDetail extends Model
{
    protected $table = 'payment_completion_details';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'component'     => Component::class, 
        'value_date'    => 'date',
        'value_number'  => 'decimal:2',
        'value_integer' => 'integer',
        'verify_status' => 'integer',
        'verify_date'   => 'datetime',
        'verify_by'     => 'integer',
        'pc_id'         => 'integer',
        'created_by'    => 'integer',
    ];

    public function upsertDetail(
        int $pcId,
        Component $component,
        ?string $valueText = null,
        ?string $valueDate = null,
        ?float $valueNumber = null,
        ?int $valueInteger = null
    ) {
        return PaymentCompletionDetail::updateOrCreate(
            [
                'pc_id'     => $pcId,
                'component' => $component,   
            ],
            [
                'value_text'    => $valueText,
                'value_date'    => $valueDate,
                'value_number'  => $valueNumber,
                'value_integer' => $valueInteger,
            ]
        );
    }

    // Relasi PaymentCompletion
    public function payment()
    {
        return $this->belongsTo(PaymentCompletion::class, 'pc_id'); 
    }

    public function verifiedBy()
    {
        return $this->belongsTo(\App\User::class, 'verify_by');
    }

    // Scope filter by payment ID
    public function scopeForPayment(Builder $query, int $paymentId): Builder
    {
        return $query->where('pc_id', $paymentId);
    }

    // scope by component
    public function scopeComponent(Builder $query, Component|string $component): Builder
    {
        $val = $component instanceof Component ? $component->value : $component;
        return $query->where('component', $val);
    }

    // helper nilai display
    public function getDisplayValueAttribute(): mixed
    {
        return match ($this->component) {
            Component::TGL_INVOICE,
            Component::TGL_PR,
            Component::TGL_PO,
            Component::TGL_SURAT_JALAN,
            Component::TGL_JATUH_TEMPO => $this->value_date,
            Component::JUMLAH => $this->value_number,
            Component::NILAI_INVOICE => $this->value_number,
            Component::NILAI_FAKTUR_PAJAK => $this->value_number,

            default => $this->value_text ?? $this->value_integer,
        };
    }
}
