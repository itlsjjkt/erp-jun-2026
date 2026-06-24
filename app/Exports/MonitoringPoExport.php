<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonitoringPoExport implements WithMultipleSheets
{
    protected $assigned_id;
    protected $supplier_id;
    protected $start_date;
    protected $end_date;

    public function __construct($assigned_id = null, $supplier_id = null, $start_date = null, $end_date = null)
    {
        $this->assigned_id = $assigned_id;
        $this->supplier_id = $supplier_id;
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
    }

    public function sheets(): array
    {
        return [
            new MonitoringPoDetailSheet($this->assigned_id, $this->supplier_id, $this->start_date, $this->end_date),
            new MonitoringPoSummarySheet($this->assigned_id, $this->supplier_id, $this->start_date, $this->end_date),
        ];
    }
}

// ============================================================
// SHEET 1: Detail Item PO
// ============================================================
class MonitoringPoDetailSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $assigned_id;
    protected $supplier_id;
    protected $start_date;
    protected $end_date;

    public function __construct($assigned_id, $supplier_id, $start_date, $end_date)
    {
        $this->assigned_id = $assigned_id;
        $this->supplier_id = $supplier_id;
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
    }

    public function title(): string
    {
        return 'Detail Item PO';
    }

    public function query()
    {
        return DB::table('po_items')
            ->select(
                'po_items.id',
                'po_items.qty',
                'po_items.price',
                'po_items.measure',
                'po.doc_no AS doc_noPo',
                'purchase_requisitions.doc_no AS doc_noPr',
                'purchase_requisitions.created_at AS pr_created_at',
                'purchases.doc_no AS no_dpm',
                'purchases.created_at AS dpm_created_at',
                'po.created_at AS po_created_at',
                'po.status AS statusss',
                'po.type AS typePo',
                'master_item_products.name AS nameProduct',
                'master_item_products.part_number AS partNumberProduct',
                'master_item_brands.name AS brandProduct',
                'users.name AS userName',
                'suppliers.name AS supplierName',
                'currencies.symbol AS symb',

                // qty LPB — dipakai jika po.type = 'lpb'
                DB::raw('(
                    SELECT COALESCE(SUM(lpb_items.qty), 0)
                    FROM lpb_items
                    LEFT JOIN lpb ON lpb.id = lpb_items.lpb_id
                    WHERE lpb_items.po_item_id = po_items.id
                    AND lpb.status IN (1, 2)
                ) AS qty_lpb'),

                // qty BPB Lokal — dipakai jika po.type != 'lpb'
                // bpb_items.spb_item_id → po_items.id (untuk tipe lokal)
                DB::raw('(
                    SELECT COALESCE(SUM(bi.qty), 0)
                    FROM bpb_items bi
                    LEFT JOIN bpb b ON b.id = bi.bpb_id
                    WHERE bi.spb_item_id = po_items.id
                    AND b.status IN (1, 2)
                ) AS qty_bpb_lkl')
            )
            ->leftJoin('master_item_products', 'po_items.product_id', '=', 'master_item_products.id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->join('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->leftJoin('users', 'po.created_by', '=', 'users.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('po.created_by', Auth::user()->id);
            })
            ->when(!empty($this->assigned_id), function ($query) {
                return $query->where('po.created_by', $this->assigned_id);
            })
            ->when(!empty($this->supplier_id), function ($query) {
                return $query->where('suppliers.id', $this->supplier_id);
            })
            ->when(!empty($this->start_date), function ($query) {
                $start = date('Y-m-d', strtotime($this->start_date));
                $end   = date('Y-m-d', strtotime($this->end_date . '+1 day'));
                return $query->whereBetween('po.created_at', [$start, $end]);
            })
            ->orderBy('po.created_at', 'DESC');
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Part Number',
            'Brand',
            'No DPM',
            'Pembuatan DPM',
            'No PR',
            'Pembuatan PR',
            'No PO',
            'Pembuatan PO',
            'Supplier',
            'Harga Satuan',
            'Qty PO',
            'Satuan',
            'Qty Diterima',
            'Purchaser',
            'Tgl PO',
            'Status PO',
        ];
    }

    protected $rowNumber = 0;

    public function map($row): array
    {
        $this->rowNumber++;

        // Jika type = 'lpb' pakai qty_lpb, selain itu pakai qty_bpb_lkl
        $qtyDiterima = $row->typePo === 'lpb' ? $row->qty_lpb : $row->qty_bpb_lkl;

        return [
            $this->rowNumber,
            $row->nameProduct,
            $row->partNumberProduct ?? '-',
            $row->brandProduct ?? '-',
            $row->no_dpm,
            $row->dpm_created_at,
            $row->doc_noPr,
            $row->pr_created_at,
            $row->doc_noPo,
            $row->po_created_at,
            $row->supplierName,
            $row->symb . '. ' . number_format($row->price, 2, ',', '.'),
            $row->qty,
            $row->measure,
            $qtyDiterima,
            $row->userName,
            $row->po_created_at ? date('d/m/Y H:i', strtotime($row->po_created_at)) : '-',
            $this->resolveStatus($row),
        ];
    }

    protected function resolveStatus($row): string
    {
        return getStatusPO($row->statusss, true);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'N'; // 14 kolom: A–N

        // Header
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E4057']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Zebra striping
            for ($i = 2; $i <= $lastRow; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle("A{$i}:{$lastCol}{$i}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
                    ]);
                }
            }

            // No (A) & Qty (I, K) center
            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I2:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('K2:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getRowDimension(1)->setRowHeight(20);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 30,  // Nama Barang
            'C' => 20,  // Part Number
            'D' => 18,  // Brand
            'E' => 18,  // No PO
            'F' => 18,  // No PR
            'G' => 25,  // Supplier
            'H' => 18,  // Harga Satuan
            'I' => 10,  // Qty PO
            'J' => 10,  // Satuan
            'K' => 14,  // Qty Diterima
            'L' => 18,  // Purchaser
            'M' => 18,  // Tgl PO
            'N' => 20,  // Status
        ];
    }
}

// ============================================================
// SHEET 2: Summary Per Purchaser
// ============================================================
class MonitoringPoSummarySheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $assigned_id;
    protected $supplier_id;
    protected $start_date;
    protected $end_date;

    public function __construct($assigned_id, $supplier_id, $start_date, $end_date)
    {
        $this->assigned_id = $assigned_id;
        $this->supplier_id = $supplier_id;
        $this->start_date  = $start_date;
        $this->end_date    = $end_date;
    }

    public function title(): string
    {
        return 'Summary Per Purchaser';
    }

    public function query()
    {
        return DB::table('po_items')
            ->select(
                'users.name AS purchaser_name',
                DB::raw('COUNT(po_items.id) AS total_item'),
                DB::raw('COUNT(DISTINCT po.id) AS total_po'),
                DB::raw('COUNT(DISTINCT po.supplier_id) AS total_supplier'),
                DB::raw('SUM(po_items.qty * po_items.price) AS total_nilai')
            )
            ->join('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('users', 'po.created_by', '=', 'users.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->when(Auth::user()->data_access == 2, function ($query) {
                return $query->where('po.created_by', Auth::user()->id);
            })
            ->when(!empty($this->assigned_id), function ($query) {
                return $query->where('po.created_by', $this->assigned_id);
            })
            ->when(!empty($this->supplier_id), function ($query) {
                return $query->where('suppliers.id', $this->supplier_id);
            })
            ->when(!empty($this->start_date), function ($query) {
                $start = date('Y-m-d', strtotime($this->start_date));
                $end   = date('Y-m-d', strtotime($this->end_date . '+1 day'));
                return $query->whereBetween('po.created_at', [$start, $end]);
            })
            ->groupBy('po.created_by', 'users.name')
            ->orderBy('users.name');
    }

    public function headings(): array
    {
        return [
            'No',
            'Purchaser',
            'Total Item',
            'Total PO',
            'Total Supplier',
            'Total Nilai (IDR)',
        ];
    }

    protected $rowNumber = 0;

    public function map($row): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->purchaser_name,
            $row->total_item,
            $row->total_po,
            $row->total_supplier,
            number_format($row->total_nilai, 2, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2E4057']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        if ($lastRow > 1) {
            $sheet->getStyle('A2:F' . $lastRow)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            for ($i = 2; $i <= $lastRow; $i++) {
                if ($i % 2 === 0) {
                    $sheet->getStyle('A' . $i . ':F' . $i)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F5F5']],
                    ]);
                }
            }

            $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getRowDimension(1)->setRowHeight(20);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 14,
            'D' => 12,
            'E' => 16,
            'F' => 24,
        ];
    }
}