<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryAdjustment;
use App\Models\InventoryWriteOff;
use App\Models\InventoryReturn;
use App\Models\InventoryHistory;
use App\Models\InventoryProcess;
use App\Models\Workarea;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use App\Traits\UploadTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;
use Auth;

class MonitoringInvController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:inventory_monitoring');
    }
    
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $month = date('m');
        $year = date('Y');
        $item = DB::table('master_items')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        if(isAdministrator() || isAdministratorCompany() || isAdmin()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            return view('logistic.monitoring.inv.index',compact('location','item'));

        } elseif(isAdministratorLocation()){
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();

            if($cek){
                $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                return view('logistic.monitoring.inv.index',compact('location','item'));
            } else{
                $location = Auth::user()->location_id;
                return view('logistic.inventory.process',compact('location'));
            }
        
        }else{
            $cek = InventoryProcess::where('location_id',Auth::user()->location_id)
            ->where('month',$month)
            ->where('year',$year)->first();

            if($cek){
                return view('logistic.monitoring.inv.index',compact('location','item'));
            } else{
                return view('logistic.inventory.process');
            }
         }
      
    }

    public function datatables()
    {
        $result  = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 
        'master_item_brands.name AS productBrand',
        'master_item_products.code AS productCode', 
        'master_item_products.part_number AS productPartNumber',
        'locations.name AS location',
        'companies.alias AS companyCode',
        'measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id');

        if (isAdministratorCompany()) {
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('locations.company_id', Auth::user()->company_id)
            ->where('inventories.is_local', false)
            ->where('inventories.deleted',false);
        }elseif(isAdministratorLocation() || isEmployee() ){
            $location = Auth::user()->location_id;
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('inventories.location_id',$location )
            ->where('inventories.is_local', false)
            ->where('inventories.deleted',false);
        } else{
            $result->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->where('inventories.is_local', false)
            ->where('inventories.deleted',false);
        }


       return  DataTables::of($result)
        ->addColumn('action', function ($result) {

            $action = "<a href='".route('logistic.inventory.history', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='ti-server mr-2'></span> Kartu Stock</a>";  
           
            if(Gate::allows('adjustment')){
                $action .= "<a href='".route('logistic.inventory.adjustment', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='ti-write mr-2'></span> Adjustment</a>";  
            }
            if(Gate::allows('writeoff')){
                $action .= "<a href='".route('logistic.inventory.writeoff', ['id' => Hashids::encode($result->id)])."' class='dropdown-item'><span class='fa fa-eraser text-danger mr-2'></span> Write Off</a>";  
            }
           
            return
            '<div class="dropdown">
                <a class="btn btn-outline bd dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Aksi
                </a>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">'
                    .$action.
                '</div>
            </div>';
        }) 
        ->editColumn('price', function ($result) {
            return "<span class='currency'>".number_format($result->price,2,".",',')."</span>";
        })
        ->editColumn('status', function ($result) {
            return statusInventory($result->stock_status);
        })
        ->editColumn('location', function ($result) {
            if (isAdministrator()) return $result->location.'-'.$result->companyCode;
            else return $result->location;
        })
        ->editColumn('productName', function ($result) {
            if($result->kind == 'Fast Moving'){
                $kind = "<label class='badge badge-success mr-2' style='padding: .1em .4em;'>&nbsp;</label>";
            }else{
                $kind = "<label class='badge badge-danger mr-2' style='padding: .1em .4em;'>&nbsp;</label>";
            }
            $brand  =  $result->productBrand != NULL ? ' Brand: '.$result->productBrand : '';
            return $kind.$result->productName."<br><small>". $brand."</small>";
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status','productName','kind','price'])
        ->make(true);

    }


    public function export(Request $request)
    {
        $data = $request->all();

        $query = DB::table('inventories')
        ->select('inventories.*','master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','master_item_brands.name AS productBrand','measures.name AS unit',
        'locations.name AS location',
        DB::raw('ROW_NUMBER () OVER (ORDER BY inventories.id) as number'))
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('inventories.location_id',$data['location_id']);
        })
        ->when(!empty($data['item_id']), function ($query) use ($data) {
            return $query->where('master_items.id',$data['item_id']);
        })
        ->when(!empty($data['status']), function ($query) use ($data) {
            return $query->where('inventories.stock_status',$data['status']);
        })
        ->get();


        if( $query->isEmpty() ){
            return redirect()->route('logistic.inventory.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('inventory-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'No'            => $inv->number,
                    'Nomor Rak'     => $inv->code_rack,
                    'Kode Produk'   => $inv->productCode,
                    'Nama Produk'   => $inv->productName,
                    'PN/SPEC'   => $inv->productPartNumber,
                    'Brand'         => $inv->productBrand,
                    'Satuan'        => $inv->unit,
                    'Saldo Awal'    => $inv->initial,
                    'In'            => $inv->in,
                    'Out'           => $inv->out,
                    'SOH'           => $inv->stock_onhand,
                    'Min'           => $inv->stock_min,
                    'Max'           => $inv->stock_max,
                    'Status'        => statusInventory($inv->stock_status,'raw'),
                    'Tipe Barang'   => $inv->kind,
                    'Lokasi'        => $inv->location,
                ];
            });
        }
      

    }


    public function history($id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID','locations.name AS location','measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->where('inventories.id', $id['0'])
        ->first();  

        return view('logistic.inventory.history',compact('inventory'));
    }

    public function historyDatatables($id)
    {
        if (! Gate::allows('inventory')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $result = Inventory::getHistoryStock($id['0']);

        return  DataTables::of($result)
        ->editColumn('stock_awal', function ($result) {
            return format_number($result->stock_awal);
        })
        ->editColumn('qty_in', function ($result) {
            return format_number($result->qty_in);
        })
        ->editColumn('qty_out', function ($result) {
            return format_number($result->qty_out);
        })
        ->editColumn('stock', function ($result) {
            return format_number($result->stock_awal+$result->qty_in-$result->qty_out);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created ? with(new Carbon($result->created))->format('d M Y  H:i') : '';
        })
        ->make(true);

    }

    public function history_export(Request $request,$id)
    { 
        

        $inventory = DB::table('inventories')
        ->select('inventories.*',
        'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber',
        'locations.id AS locationID','locations.name AS location','measures.name AS unit')
        ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->where('inventories.id', $id)
        ->first();  

        $inventory_history = Inventory::getHistoryStock($id, $request->input('start_date'), $request->input('end_date'));

        $styleArrayTabel = array(
        'alignment' => array(
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'rotation'   => 0,
                    'wrap'       => true
        ),
        'borders' => array(
            'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, //BORDER_THIN BORDER_MEDIUM BORDER_HAIR
                    'color' => array('rgb' => '000000')
            )
            )
        );


        $styleArrayItem = array(
            'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'rotation'   => 0,
                    'wrap'       => true
            ),
            'borders' => array(
                'allBorders' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, //BORDER_THIN BORDER_MEDIUM BORDER_HAIR
                        'color' => array('rgb' => '000000')
                )
                )
        );

        $styleArrayBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ],
            ],
        ];
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setScale(80);
        
        $sheet->getPageMargins()->setTop(0.24);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setBottom(0.24);

        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'KARTU STOCK - '. $inventory->location);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Periode: '.date('d M Y', strtotime($request->input('start_date'))).' - '. date('d M Y', strtotime($request->input('end_date'))));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('B6', 'Nomor Rak');
        $sheet->setCellValue('C6', ': '. $inventory->code_rack);
        
        $sheet->setCellValue('B7', 'Nama Produk');
        $sheet->setCellValue('C7', ': ['. $inventory->productCode.']'.$inventory->productName);

        $sheet->setCellValue('B8', 'PN/SPEC');
        $sheet->setCellValue('C8', ': '. $inventory->productPartNumber);

        $sheet->setCellValue('B9', 'Stock On Hand');
        $sheet->setCellValue('C9', ': '. $inventory->stock_onhand);

        $sheet->getStyle('A11:H12')->getFont()->setBold(true);
        $sheet->getStyle('A11:H12')->applyFromArray($styleArrayItem);

        $sheet->setCellValue('A11', 'NO');
        $sheet->mergeCells('A11:A12');
        $sheet->setCellValue('B11', 'TGL INPUT');
        $sheet->mergeCells('B11:B12');
        $sheet->setCellValue('C11', 'NO. DOKUMEN');
        $sheet->mergeCells('C11:C12');
        $sheet->setCellValue('D11', 'DESKRIPSI');
        $sheet->mergeCells('D11:D12');
        $sheet->setCellValue('E11', 'QTY');
        $sheet->mergeCells('E11:H11');
        $sheet->getStyle('E11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


        $sheet->setCellValue('E12', 'STOCK AWAL');
        $sheet->getStyle('E12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('F12', 'IN');
        $sheet->getStyle('F12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G12', 'OUT');
        $sheet->getStyle('G12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H12', 'STOCK ON HAND');
        $sheet->getStyle('H12')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $rows = 13;
        $i = 1;
        foreach($inventory_history as $items){
            $stock = $items->stock_awal+$items->qty_in-$items->qty_out;

            $sheet->setCellValue('A' . $rows, $i);
            $sheet->setCellValue('B' . $rows, date('d M Y H:i', strtotime($items->created)));
            $sheet->setCellValue('C' . $rows, $items->doc_no);
            $sheet->setCellValue('D' . $rows, $items->description);
            $sheet->setCellValue('E' . $rows, $items->stock_awal);
            $sheet->setCellValue('F' . $rows, $items->qty_in);
            $sheet->setCellValue('G' . $rows, $items->qty_out);
            $sheet->setCellValue('H' . $rows, $stock);

            $sheet->getStyle('C' . $rows)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $rows.':H'.$rows)->applyFromArray($styleArrayTabel);
            $rows++;
            $i++;
        }

        $sheet->setShowGridLines(false);
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Kartu-Stock Produk ID-'.$inventory->product_id.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save("php://output");
        die();
       
    }

}
