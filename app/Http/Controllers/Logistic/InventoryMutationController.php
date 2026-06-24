<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\MasterItem;
use App\Models\Workarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Traits\UploadTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Storage;
use PDF;
use Auth;

class InventoryMutationController extends Controller
{
    use UploadTrait;
    
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    
    function __construct()
    {
        $this->middleware('permission:mutation');
    }


    public function index(Request $request)
    {
      
        if(isAdministrator()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $master_item       = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")
        ->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');

        if($request->get('item_id')){
            $item_id= $request->get('item_id');
            $item_name = MasterItem::findOrFail($item_id)->name;

        }else{
            $item_id = null;
            $item_name = '';
        }

        if($request->get('location_id')){
            $loc= $request->get('location_id');
            $loc_name = Workarea::findOrFail($loc)->name;
        }else{
            $loc = 1;
            $loc_name ='';
        }


        if($request->get('start_date')){
            $start_date= $request->get('start_date');
        }else{
            $start_date = date('m/d/Y');
        }

        if($request->get('end_date')){
            $end_date= $request->get('end_date');
        }else{
            $end_date = date('m/d/Y');
        }

        $query= Inventory::getMutationDetail($start_date,$end_date,$loc,$item_id);

        $result = [];
        $result_item = [];

        foreach ($query as $element) {
            $result_item[$element->productcode] = [
                'code_rack'     => $element->code_rack,
                'productcode'   => $element->productcode,
                'productname'   => $element->productname,
                'productpartnumber'   => $element->productpartnumber,
                'productbrand'  => $element->productbrand,
                'unit'          => $element->unit,
                'qty_awal'      => $element->qty_awal,
            ];
            $result[$element->productcode][] =  $element ;
        }
   
        return view('logistic.inventory.mutation',compact('location','result','result_item','loc','end_date','start_date','master_item','item_id','item_name','loc_name'));
    }

    public function export(Request $request)
    { 

        if($request->get('item_id')){
            $item_id= $request->get('item_id');
            $item_name = MasterItem::findOrFail($item_id)->name;
        }else{
            $item_id = null;
            $item_name = 'ALL';
        }

        $loc        = $request->get('location_id');
        $loc_name   = Workarea::findOrFail($loc)->name;
        $start_date = $request->get('start_date');
        $end_date   = $request->get('end_date');
    
        $query= Inventory::getMutationDetail($start_date,$end_date,$loc,$item_id);

        $result = [];
        $result_item = [];
        
        foreach ($query as $element) {
            
            $result_item[$element->productcode] = [
                'code_rack'     => $element->code_rack,
                'productcode'   => $element->productcode,
                'productname'   => $element->productname,
                'productpartnumber'   => $element->productpartnumber,
                'productbrand'  => $element->productbrand,
                'unit'          => $element->unit,
                'qty_awal'      => $element->qty_awal,
            ];
            $result[$element->productcode][] =  $element ;
        }
      
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

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(13);
        $sheet->getColumnDimension('I')->setWidth(13);
        $sheet->getColumnDimension('J')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);

        $sheet->setCellValue('A2', 'PERGERAKAN STOCK HARIAN');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setUnderline(true);
        $sheet->setCellValue('A3', 'Dari Tanggal: '.date('d M Y', strtotime($start_date)).' s/d '.date('d M Y', strtotime($end_date)));
        $sheet->setCellValue('A4', 'Lokasi: '. $loc_name.' Kategori: '.  $item_name );
        $sheet->getStyle('A6:K6')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'KODE');
        $sheet->setCellValue('B6', 'NAMA BARANG / DESKRIPSI');
        $sheet->setCellValue('C6', 'STN');
        $sheet->setCellValue('D6', 'SALDO AWAL');
        $sheet->setCellValue('E6', 'MASUK');
        $sheet->setCellValue('F6', 'KELUAR');
        $sheet->setCellValue('G6', 'SALDO AKHIR');
        $sheet->setCellValue('H6', 'KETERANGAN');
        $sheet->setCellValue('I6', 'LOKASI RAK');
        $sheet->setCellValue('J6', 'CATATAN');
        $sheet->setCellValue('K6', 'TGL TRANSAKSI');

        $rows = 7;

        foreach ($result_item  as $item){

            $sum_in  = 0;
            $sum_out = 0;
            asort($result[$item['productcode']]);

            $partnumber = '';
            if($item['productpartnumber'] !=''){
                $partnumber =' PN: '. $item['productpartnumber'];
            }
           
           
            $sheet->setCellValue('A' . $rows, $item['productcode']);
            $sheet->setCellValue('B' . $rows, $item['productname'].$partnumber);
            $sheet->setCellValue('C' . $rows, $item['unit']);
            $sheet->setCellValue('D' . $rows, $item['qty_awal']);
            $sheet->mergeCells('E'. $rows.':G'. $rows);
            $sheet->setCellValue('I' . $rows,  $item['code_rack']);

            $sheet->getStyle('A'. $rows.':K'. $rows)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A'. $rows.':K'. $rows)->getFill()->getStartColor()->setARGB('E6EDF5');

            $rows_item = $rows + 1;

            foreach ($result[$item['productcode']] as $val){
                $sum_in  += $val->qty_in;
                $sum_out += $val->qty_out;

                $notes = json_decode($val->notes, true);

                if ($notes) {
                    if (!is_array($notes)) {
                        $po = '';
                    } else {
                        $po  = ($notes['po'] ?? '');
                        $po .= "\n Harga: " . ($notes['price'] ?? '');
                    }
                } else {
                    $po = '';
                }


                $sheet->setCellValue('A' . $rows_item, '');
                $sheet->setCellValue('B' . $rows_item, $val->message);
                $sheet->setCellValue('C' . $rows_item, '');
                $sheet->setCellValue('D' . $rows_item, '');
                $sheet->setCellValue('E' . $rows_item, $val->qty_in);
                $sheet->setCellValue('F' . $rows_item, $val->qty_out);
                $sheet->setCellValue('G' . $rows_item, '');
                $sheet->setCellValue('H' . $rows_item, $val->description);
                $sheet->setCellValue('I' . $rows_item, '');
                $sheet->setCellValue('J' . $rows_item, $po);
                $sheet->setCellValue('K' . $rows_item, date('d/m/Y',strtotime( $val->created)));
                $rows_item++;
            }

            $sum_last = $item['qty_awal'] - $sum_out + $sum_in;

            $rows_end = $rows_item;
          
            $sheet->mergeCells('A'. $rows_end.':D'. $rows_end);
            $sheet->setCellValue('A' . $rows_end, 'Sub Total');
            $sheet->getStyle('A' . $rows_end)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('D' . $rows_end, '');
            $sheet->setCellValue('E' . $rows_end, $sum_in);
            $sheet->setCellValue('F' . $rows_end, $sum_out);
            $sheet->setCellValue('G' . $rows_end, $sum_last);
            $sheet->mergeCells('I'. $rows_end.':J'. $rows_end);
            
            $rows_spasi = $rows_end+1;

            $sheet->mergeCells('A'. $rows_spasi.':K'. $rows_spasi);

            $rows = $rows_spasi+1;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Mutasi Barang -'.date('d M Y', strtotime($start_date)).' s/d '.date('d M Y', strtotime($end_date)).'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save("php://output");
        die();
       
    }

    public function summary(Request $request)
    {
       
        if(isAdministrator()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        if($request->get('date')){
            $date= $request->get('date');
        }else{
            $date = date('m/d/Y');
        }
        return view('logistic.inventory.mutation_summary',compact('location','date'));
    }

    public function datatables(Request $request)
    {
        
        if($request->get('date')){
            $date= $request->get('date');
        }else{
            $date = date('m/d/Y');
        }

        if (isAdministratorCompany()) {
            $location = 0;
        }elseif(isAdministratorLocation()){
            $location = Auth::user()->location_id;
        }elseif(isEmployee()){
            $location = Auth::user()->location_id;
        } else{
            $location = 0;
        }

        $result = Inventory::getMutation($date,$location);


       return  DataTables::of($result)
        ->addColumn('soh', function ($result){
            return $result->initial + $result->in - $result->out;
        })
        ->editColumn('productname', function ($result) {
            return $result->productname."<br><small>". $result->productpartnumber."</small>";
        })
        ->rawColumns(['productname'])
        ->make(true);

    }

    public function summary_export(Request $request)
    {   

        $query  = Inventory::getMutation($request->input('date'),$request->input('location_id'));

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


        $styleArrayBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ],
            ],
        ];


        $result = [];
        $result_item = [];

        foreach ($query as $element) {
            $result_item[$element->item_code] = [
                'item_name'     => $element->item_name,
                'item_code'     => $element->item_code,
            ];
            $result[$element->item_code][] =  $element ;
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setScale(80);
        
        $sheet->getPageMargins()->setTop(0.24);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setBottom(0.24);

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'MUTASI BARANG');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('A3:I3');
        $sheet->setCellValue('A3', 'Tanggal: '.date('d M Y', strtotime($request->input('date'))));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A6:I6')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'KODE');
        $sheet->setCellValue('B6', 'NAMA BARANG');
        $sheet->setCellValue('C6', 'PN/SPEC');
        $sheet->setCellValue('D6', 'STN');
        $sheet->setCellValue('E6', 'S.AWAL');
        $sheet->setCellValue('F6', 'MASUK');
        $sheet->setCellValue('G6', 'KELUAR');
        $sheet->setCellValue('H6', 'S.AKHIR');
        $sheet->setCellValue('I6', 'LOKASI');

        $rows = 7;
        $i = 1;
        foreach($result_item as $val){

            $sum_in  = $sum_out = $sum_soh = $sum_initial = 0;

            $sheet->setCellValue('A' . $rows, $val['item_code']." - ".$val['item_name']);
           
            $sheet->getStyle('A'. $rows.':H'. $rows)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A'. $rows.':H'. $rows)->getFill()->getStartColor()->setARGB('E6EDF5');
            $sheet->getStyle('A'. $rows)->getFont()->setBold(true);

            $rows_item = $rows + 1;

            foreach($result[$val['item_code']] as $item){
                
                $soh = $item->initial + $item->in - $item->out;

                $sum_in  += $item->in;
                $sum_out += $item->out;
                $sum_initial += $item->initial;
                $sum_soh += $soh;

                $partnumber = '';
                if($item->productpartnumber !=''){
                    $partnumber =' PN: '. $item->productpartnumber;
                }
            

                $sheet->setCellValue('A' . $rows_item, $item->productcode);
                $sheet->setCellValue('B' . $rows_item, $item->productname);
                $sheet->setCellValue('C' . $rows_item, $item->$partnumber);
                $sheet->setCellValue('D' . $rows_item, $item->unit);
                $sheet->setCellValue('E' . $rows_item, $item->initial);
                $sheet->setCellValue('F' . $rows_item, $item->in);
                $sheet->setCellValue('G' . $rows_item, $item->out);
                $sheet->setCellValue('H' . $rows_item, $soh);
                $sheet->setCellValue('I' . $rows_item, $item->code_rack);
                $rows_item++;
                $i++;
            }

            $sum_last = $sum_initial - $sum_out + $sum_in;
            $rows_end = $rows_item;
          
            $sheet->mergeCells('A'. $rows_end.':D'. $rows_end);
            $sheet->setCellValue('A' . $rows_end, 'Sub Total');
            $sheet->getStyle('A' . $rows_end)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $sheet->setCellValue('E' . $rows_end, $sum_initial);
            $sheet->setCellValue('F' . $rows_end, $sum_in);
            $sheet->setCellValue('G' . $rows_end, $sum_out);
            $sheet->setCellValue('H' . $rows_end, $sum_last);
            
            $rows_spasi = $rows_end+1;

            $sheet->mergeCells('A'. $rows_spasi.':H'. $rows_spasi);

            $rows = $rows_spasi+1;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Mutasi Barang Tanggal -'.$request->input('date').'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save("php://output");
        die();
       
    }



    public function summary_month(Request $request)
    {
       
        if(isAdministrator()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        if($request->get('month')){
            $month= $request->get('month');
        }else{
            $month = date('F');
        }

        if($request->get('year')){
            $year= $request->get('year');
        }else{
            $year = date('Y');
        } 


        return view('logistic.inventory.mutation_summary_month',compact('location','month','year'));
    }

    public function datatables_month($month, $year)
    {
       
        $month = $month;
        $year = $year;

        if (isAdministratorCompany()) {
            $location = 0;
        }elseif(isAdministratorLocation()){
            $location = Auth::user()->location_id;
        }elseif(isEmployee()){
            $location = Auth::user()->location_id;
        } else{
            $location = 0;
        }

        $result = Inventory::getMutationMonth($month,$year,$location);

       return  DataTables::of($result)
        ->addColumn('soh', function ($result){
            return $result->initial + $result->in - $result->out;
        })
        ->editColumn('productname', function ($result) {
            return $result->productname."<br><small>". $result->productpartnumber."</small>";
        })
        ->rawColumns(['productname'])
        ->make(true);

    }

    public function summary_month_export(Request $request)
    { 
        
        $query = Inventory::getMutationMonth($request->input('month'),$request->input('year'),  $request->input('location_id'));

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


        $styleArrayBorder = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                ],
            ],
        ];
        
        $result = [];
        $result_item = [];

        foreach ($query as $element) {
            $result_item[$element->item_code] = [
                'item_name'     => $element->item_name,
                'item_code'     => $element->item_code,
            ];
            $result[$element->item_code][] =  $element ;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setScale(80);
        
        $sheet->getPageMargins()->setTop(0.24);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setBottom(0.24);

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(13);
        $sheet->getColumnDimension('E')->setWidth(13);
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $sheet->setCellValue('A2', 'MUTASI PERSEDIAAN BARANG');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setUnderline(true);
        $sheet->setCellValue('A3', 'Periode: '.date('M', strtotime($request->input('month'))).'-'.$request->input('year'));
        $sheet->getStyle('A6:H6')->getFont()->setBold(true);
        $sheet->setCellValue('A6', 'KODE');
        $sheet->setCellValue('B6', 'NAMA BARANG');
        $sheet->setCellValue('C6', 'STN');
        $sheet->setCellValue('D6', 'S.AWAL');
        $sheet->setCellValue('E6', 'MASUK');
        $sheet->setCellValue('F6', 'KELUAR');
        $sheet->setCellValue('G6', 'S.AKHIR');
        $sheet->setCellValue('H6', 'LOKASI');

        $rows = 7;
        $i = 1;
        foreach($result_item as $val){

            $sum_in  = $sum_out = $sum_soh = $sum_initial = 0;

            $sheet->setCellValue('A' . $rows, $val['item_code']." - ".$val['item_name']);
            $sheet->getStyle('A'. $rows.':H'. $rows)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle('A'. $rows.':H'. $rows)->getFill()->getStartColor()->setARGB('E6EDF5');
            $sheet->getStyle('A'. $rows)->getFont()->setBold(true);
            $rows_item = $rows + 1;

            foreach($result[$val['item_code']] as $item){
                
                $soh = $item->initial + $item->in - $item->out;
                $partnumber ='';
                if($item->productpartnumber !=''){
                    $partnumber =' PN: '. $item->productpartnumber;
                }
            
                $sheet->setCellValue('A' . $rows_item, $item->productcode);
                $sheet->setCellValue('B' . $rows_item, $item->productname.$partnumber);
                $sheet->setCellValue('C' . $rows_item, $item->unit);
                $sheet->setCellValue('D' . $rows_item, $item->initial);
                $sheet->setCellValue('E' . $rows_item, $item->in);
                $sheet->setCellValue('F' . $rows_item, $item->out);
                $sheet->setCellValue('G' . $rows_item, $soh);
                $sheet->setCellValue('H' . $rows_item, $item->code_rack);
                $rows_item++;
                $i++;
            }
            $rows_spasi = $rows_item+1;
            $sheet->mergeCells('A'. $rows_spasi.':H'. $rows_spasi);
            $rows = $rows_spasi+1;
        }


        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Mutasi Barang Periode '.$request->input('month').'-'.$request->input('year').'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save("php://output");
        die();
       
    }

    
}
