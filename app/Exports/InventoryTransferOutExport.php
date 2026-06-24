<?php

namespace App\Exports;

use App\Modal\Purchase_Request;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\DB;

class InventoryTransferOutExport implements FromView
{


    public function __construct($location_id = null, $type = null, $start_date = null,$end_date = null)
    {
        $this->location_id     = $location_id;
        $this->type            = $type;
        $this->start_date      = $start_date;
        $this->end_date        = $end_date;
    }

    public function view(): View
    {

        $query = DB::table('inventory_transfer_out_items')
        ->select(
            'inventory_transfer_out_items.*',
            'inventory_transfer_out.doc_no',
            'inventory_transfer_out.created_at',
            'inventory_transfer_out.operator',
            'inventory_transfer_out.status AS statusWto',
            'inventory_transfer_out.type AS typeWto',
            'inventories.code_rack',
            'inventories.id AS inv_id',
            'inventories.price AS price',
            'inventories.price_after_discount AS price_after_discount',
            'master_item_products.name AS productName',
            'master_item_products.code AS productCode',
            'master_item_products.part_number AS productPartNumber',
            'measures.name AS measure',
            'asal.name AS lokasiAsal',
            'casal.alias AS companyAsal',
            'tujuan.name AS lokasiTujuan',
            'ctujuan.alias AS companyTujuan',
        )
        ->leftJoin('inventory_transfer_out', 'inventory_transfer_out.id', '=', 'inventory_transfer_out_items.inventory_transfer_id')
        ->leftJoin('inventories', 'inventories.id', '=', 'inventory_transfer_out_items.inventory_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
        ->leftJoin('locations as asal', 'asal.id', '=', 'inventory_transfer_out.location_id')
        ->leftJoin('companies as casal','casal.id','=','asal.company_id')
        ->leftJoin('locations as tujuan', 'tujuan.id', '=', 'inventory_transfer_out.location_destination')
        ->leftJoin('companies as ctujuan','ctujuan.id','=','tujuan.company_id')
        ->when(!empty($this->location_id), function ($query) {
            return $query->where('inventory_transfer_out.location_id',$this->location_id);
        })
        ->when(isset($this->type), function ($query) {
            return $query->where('inventory_transfer_out.type',$this->type);
        })
        ->when(!empty($this->start_date), function ($query) {
            if($this->end_date){
                $start = date("Y-m-d",strtotime($this->start_date));
                $end   = date("Y-m-d",strtotime($this->end_date."+1 day"));
                return $query->whereBetween('inventory_transfer_out.created_at', [$start , $end]);
            }else{
                return $query->where('inventory_transfer_out.created_at', $this->start_date);
            }
        })
        ->orderBy('inventory_transfer_out.created_at','DESC')
        ->get();

        $result = [];
        foreach ($query as $element) {
            $result[$element->doc_no][] = $element;
        }

        return view('exports.inventory_transfer_out', [
            'transfer'  => $result ,
        ]);

    }


}
