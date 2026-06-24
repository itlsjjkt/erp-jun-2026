<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;
use Auth;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PoExport2Admin implements FromView, WithStyles, ShouldAutoSize
{


    public function __construct($project_id = null, $department_id = null, $supplier_id = null,$start_date = null,$end_date = null)
    {
        $this->project_id      = $project_id;
        $this->department_id   = $department_id;
        $this->supplier_id     = $supplier_id;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $query = DB::table('po_items')
            ->select(
                'po_items.*',
                'companies.name AS companyyy',
                'companies.alias AS company_alias',
                'purchase_requisitions.doc_no AS pr_no',
                'departments.name AS department',
                'master_item_products.code AS productCodeNumber',
                'master_item_products.name AS productName',
                'master_item_products.part_number AS productPartNumber',
                'master_item_brands.name AS productBrand',
                'measures.name AS satuan',
                'purchaser.name AS purchaser',
                'po.created_at AS tglpembuatanpo',
                'po.doc_no as nopo',
                'po.discount_type AS disc_type_po',
                'po.currency AS currency_po',
                'po.pph AS pph_po',
                'po.ppn AS ppn_po',
                'po.send_expense AS send_expense_po',
                'po.send_expense_ppn AS send_expense_ppn_po',
                'po.discount_item AS discount_item_po',
                'po.discount_amount AS discount_amount_po',
                'suppliers.name AS supplier'
            )
            ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
            ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
            ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->leftJoin('companies','companies.id','=','locations.company_id')
            ->leftJoin('departments','departments.id','=','purchase_requisitions.department_id')
            ->when(!empty($this->project_id), function ($query) {
                return $query->where('purchase_requisitions.project_id',$this->project_id);
            })
            ->when(!empty($this->department_id), function ($query) {
                return $query->where('purchase_requisitions.department_id',$this->department_id);
            })
            ->when(!empty($this->supplier_id), function ($query) {
                return $query->where('po.supplier_id',$this->supplier_id);
            })
            ->when(!empty($this->start_date), function ($query) {
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('po.created_at', [$start , $end]);
            })
            ->when(Auth::user()->data_access!=1, function ($result) {
                return $result->where('purchase_items.assigned_id', Auth::user()->id);
            })
            ->whereIn('po.status',[1,2,4,5])
	        ->orderBy('purchase_requisitions.created_at','ASC')
	        ->orderBy('po.created_at','ASC')
            ->orderBy('po.created_at','ASC')
            ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->nopo][] = $element;
        }
        return view('exports.po2admin', [
            'po'  => $result ,
        ]);

    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = 'A1:' . $highestColumn . $highestRow;

        return [
            $range => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'], // Hitam
                    ],
                ],
            ],
        ];
    }

}
