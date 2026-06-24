<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentCompletionExport implements WithMultipleSheets
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new PaymentCompletionTempoSheet($this->filters),
            new PaymentCompletionCbdSheet($this->filters),
        ];
    }
}

// ── SHEET TEMPO ──
class PaymentCompletionTempoSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'TEMPO';
    }

    public function headings(): array
    {
        return [
            'No', 'No PC', 'No PO', 'No PR', 'Company', 'Supplier',
            'Type PC', 'Status', 'No SI', 'No Invoice', 'Nilai Invoice',
            'Tgl Invoice', 'Tgl Terima Invoice', 'Periode Tempo',
            'Tgl Jatuh Tempo', 'No Faktur Pajak', 'Tgl Surat Jalan',
            'Dibuat Oleh', 'Tgl Pembuatan',
        ];
    }

    public function collection()
    {
        $q = DB::table('payment_completions as pc')
            ->leftJoin('po', 'po.id', '=', 'pc.po_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('companies as c', 'c.id', '=', 'po.company_id')
            ->leftJoin('users as u', 'u.id', '=', 'pc.created_by')
            ->where('pc.type_payment', 1)
            ->select(
                'pc.id', 'pc.doc_no', 'po.doc_no as no_po', 'pr.doc_no as no_pr',
                'c.name as company', 'suppliers.name as supplier',
                'pc.type_payment', 'pc.status', 'pc.created_at', 'u.name as created_by'
            );

        $this->applyFilters($q);

        $pcs = $q->orderByDesc('pc.id')->get();

        $rows = collect();
        $no   = 1;

        foreach ($pcs as $pc) {
            $details = DB::table('payment_completion_details')
                ->where('pc_id', $pc->id)
                ->get()
                ->groupBy('index');

            foreach ($details as $index => $comps) {
                $get = fn($comp) => $comps->firstWhere('component', $comp);

                $rows->push([
                    $no++,
                    $pc->doc_no,
                    $pc->no_po,
                    $pc->no_pr,
                    $pc->company,
                    $pc->supplier,
                    'TEMPO',
                    getStatusPC($pc->status, 'raw'),
                    $get('no_si')?->value_text,
                    $get('invoice')?->value_text,
                    $get('nilai_invoice')?->value_number,
                    $get('tgl_invoice')?->value_date,
                    $get('tgl_terima_invoice')?->value_date,
                    $get('periode_tempo')?->value_integer,
                    $get('tgl_jatuh_tempo')?->value_date,
                    $get('faktur_pajak')?->value_text,
                    $get('tgl_surat_jalan')?->value_date,
                    $pc->created_by,
                    $pc->created_at ? \Carbon\Carbon::parse($pc->created_at)->format('d/m/Y H:i') : '-',
                ]);
            }
        }

        return $rows;
    }

    protected function applyFilters(&$q)
    {
        if (!empty($this->filters['company_id'])) {
            $q->where('po.company_id', $this->filters['company_id']);
        }
        if (!empty($this->filters['tgl_dari'])) {
            $q->whereDate('pc.created_at', '>=', $this->filters['tgl_dari']);
        }
        if (!empty($this->filters['tgl_sampai'])) {
            $q->whereDate('pc.created_at', '<=', $this->filters['tgl_sampai']);
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}

// ── SHEET CBD/COD/DP ──
class PaymentCompletionCbdSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'CBD-COD-DP';
    }

    public function headings(): array
    {
        return [
            'No', 'No PC', 'No PO', 'No PR', 'Company', 'Supplier',
            'Type PC', 'Status', 'No Invoice', 'Nilai Invoice',
            'Tgl Akhir Bayar', 'No Faktur Pajak', 'No Proforma Invoice',
            'Nilai Proforma', 'Dibuat Oleh', 'Tgl Pembuatan',
        ];
    }

    public function collection()
    {
        $q = DB::table('payment_completions as pc')
            ->leftJoin('po', 'po.id', '=', 'pc.po_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchase_requisitions as pr', 'pr.id', '=', 'po.purchase_id')
            ->leftJoin('companies as c', 'c.id', '=', 'po.company_id')
            ->leftJoin('users as u', 'u.id', '=', 'pc.created_by')
            ->where('pc.type_payment', 2)
            ->select(
                'pc.id', 'pc.doc_no', 'po.doc_no as no_po', 'pr.doc_no as no_pr',
                'c.name as company', 'suppliers.name as supplier',
                'pc.type_payment', 'pc.status', 'pc.created_at', 'u.name as created_by'
            );

        $this->applyFilters($q);

        $pcs = $q->orderByDesc('pc.id')->get();

        $rows = collect();
        $no   = 1;

        foreach ($pcs as $pc) {
            $details = DB::table('payment_completion_details')
                ->where('pc_id', $pc->id)
                ->get()
                ->groupBy('index');

            foreach ($details as $index => $comps) {
                $get = fn($comp) => $comps->firstWhere('component', $comp);

                $rows->push([
                    $no++,
                    $pc->doc_no,
                    $pc->no_po,
                    $pc->no_pr,
                    $pc->company,
                    $pc->supplier,
                    'CBD/COD/DP',
                    getStatusPC($pc->status, 'raw'),
                    $get('invoice')?->value_text,
                    $get('nilai_invoice')?->value_number,
                    $get('tgl_jatuh_tempo')?->value_date,
                    $get('faktur_pajak')?->value_text,
                    $get('proforma_invoice')?->value_text,
                    $get('nilai_proforma_invoice')?->value_number,
                    $pc->created_by,
                    $pc->created_at ? \Carbon\Carbon::parse($pc->created_at)->format('d/m/Y H:i') : '-',
                ]);
            }
        }

        return $rows;
    }

    protected function applyFilters(&$q)
    {
        if (!empty($this->filters['company_id'])) {
            $q->where('po.company_id', $this->filters['company_id']);
        }
        if (!empty($this->filters['tgl_dari'])) {
            $q->whereDate('pc.created_at', '>=', $this->filters['tgl_dari']);
        }
        if (!empty($this->filters['tgl_sampai'])) {
            $q->whereDate('pc.created_at', '<=', $this->filters['tgl_sampai']);
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
            ], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
