<?php

namespace App\Http\Controllers\Logistic;

use App\Models\InsuranceCargo;
use App\Models\InsuranceCargoItem;
use App\Models\Spb;
use App\Models\Workarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use App\Exports\InsuranceCargoExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class InsuranceCargoController extends Controller
{

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }

        return view('logistic.insurance_cargo.index');
    }

    public function datatables()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        
        $result = DB::table('insurance_cargos')
        ->select('insurance_cargos.*','users.name AS created','expeditions.name AS expedition')
        ->leftJoin('users', 'users.id', '=', 'insurance_cargos.created_by')
        ->leftJoin('expeditions', 'expeditions.id', '=', 'insurance_cargos.shipper_by')
        ->orderBy('insurance_cargos.created_at', 'DESC');
        
       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('logistic.insurance_cargo.edit',Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_view = "<a href='".route('logistic.insurance_cargo.show',Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('logistic.insurance_cargo.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            if($result->status==0){
                return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
            }else{
                return '<div class="btn-group">'.$url_view.'</div>';
            }
        })
        ->editColumn('status', function ($result) {
            return getStatusData($result->status);
        })
        ->editColumn('period', function ($result) {
            return $result->period ? with(new Carbon($result->period))->format('d/m/Y') : '';
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->rawColumns(['action', 'status','payment_amount'])
        ->make(true);

    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $spb = DB::table('spb')
        ->select('spb.*')
        ->whereIn('spb.id', $request->spb_id)
        ->get();

        $spb_id = implode(',',$request->spb_id);
        $ekspedisi  = DB::table('expeditions')->pluck('name','id');
        return view('logistic.insurance_cargo.create', compact('spb','spb_id','ekspedisi'));
        
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        
        if ($request->get('status')==1) {
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
            $increment = DB::table('insurance_cargos')
                ->whereDate("created_at", Carbon::today()->toDateString())
                ->where('status','!=', 0)
                ->get();

            $num = sprintf("%'.03d", count($increment) + 1) ;
            $doc_no = "INS-CARGO-".date('dmy')."-".$num;
        }else{
            $data['status'] = 0;
            $doc_no = "INS-CARGO-".date('dmy')."-DRAFT";
        }


        $data['doc_no']                 = $doc_no;
        $data['shipper_by']             = $request->get('shipper_by');
        $data['period']                 = $request->get('period');
        $data['risk_location']          = $request->get('risk_location');
        $data['notes']                  = $request->get('notes');
        $data['spb_id']                 = $request->get('spbID');
        $data['prepared_by']            = $request->get('prepared_by');
        $data['approved_by']            = $request->get('approved_by');
        $data['checked_by']             = $request->get('checked_by');
        $data['checked_purchasing_1']   = $request->get('checked_purchasing_1');
        $data['checked_purchasing_2']   = $request->get('checked_purchasing_2');
        $data['received_by_1']          = $request->get('received_by_1');
        $data['received_by_2']          = $request->get('received_by_2');
        $data['company_id'] = $request->get('companyID');
        $data['created_by']             = Auth::user()->id;

        DB::beginTransaction();

        try {

            $insurance = InsuranceCargo::create($data);

            $dataInsurance = [];
            $cases  = [];
            $ids = [];

            $item = $request->get('spb_item_id');

            for($i=0;$i < count($item);$i++) {

                if (in_array($request->get('spb_item_id')[$i], $request->get('iscreateInsurance'))) {
                    $dataInsurance[] = [
                        'insurance_cargo_id'    => $insurance->id,
                        'spb_item_id'           => $request->get('spb_item_id')[$i],
                        'ppn'                 => $request->get('ppn')[$i],
                        'discount'              => $request->get('discount')[$i],
                        'price'                 => $request->get('price')[$i],
                    ];
                    $ids[]          = $request->get('spb_item_id')[$i];
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 2";
                }else{
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 1";
                }

            }

            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);

            \DB::update("UPDATE spb_kolis SET status_insurance_cargo = CASE {$cases} END WHERE id in ({$ids})");
        
            InsuranceCargoItem::insert($dataInsurance);
            
            DB::commit();
            return redirect()->route('logistic.insurance_cargo.show', Hashids::encode($insurance->id))->with(['success' => 'Input Data Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function show($id)
    { 
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $insurance         = InsuranceCargo::getByID($id['0']);
        $insurance_items   = InsuranceCargo::getProductItem($id['0']);
     
        return view('logistic.insurance_cargo.show', compact('insurance', 'insurance_items'));
    }

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $insurance_items  = InsuranceCargo::getProductItem($id['0']);
        $insurance        = InsuranceCargo::getByID($id['0']);

        $ekspedisi = DB::table('expeditions')->pluck('name','id');

        return view('logistic.insurance_cargo.edit', compact('insurance','insurance_items','ekspedisi'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }

        $insurance = InsuranceCargo::findOrFail($id);
        $data = $request->all();

        if ($request->get('status')==1) {
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
            $increment = DB::table('insurance_cargos')
                ->whereDate("created_at", Carbon::today()->toDateString())
                ->where('status','!=', 0)
                ->get();
            $num = sprintf("%'.03d", count($increment) + 1) ;
            $doc_no = "INS-CARGO-".date('dmy')."-".$num;
        }else{
            $data['status'] = 0;
            $doc_no = "INS-CARGO-".date('dmy')."-DRAFT";
        }


        $data['doc_no']                 = $doc_no;
        $data['shipper_by']             = $request->get('shipper_by');
        $data['period']                 = $request->get('period');
        $data['risk_location']          = $request->get('risk_location');
        $data['notes']                  = $request->get('notes');
        $data['prepared_by']            = $request->get('prepared_by');
        $data['approved_by']            = $request->get('approved_by');
        $data['checked_by']             = $request->get('checked_by');
        $data['checked_purchasing_1']   = $request->get('checked_purchasing_1');
        $data['checked_purchasing_2']   = $request->get('checked_purchasing_2');
        $data['received_by_1']          = $request->get('received_by_1');
        $data['received_by_2']          = $request->get('received_by_2');
        $data['updated_by'] = Auth::user()->id;

        $insurance->update($data);

        $ids = $ppn = $discount = [];

        $item = $request->get('ins_item_id');
        for($i=0;$i < count($item);$i++) {
            $ids[]          = $request->get('ins_item_id')[$i];
            $ppn[]          = "WHEN id = {$request->get('ins_item_id')[$i]} THEN ".$request->get('ppn')[$i];
            $discount[]     = "WHEN id = {$request->get('ins_item_id')[$i]} THEN ". str_replace(",", "", $request->get('discount')[$i]);
        }

        $ids            = implode(',', $ids);
        $ppn            = implode(' ', $ppn);
        $discount       = implode(' ', $discount);

        \DB::update("UPDATE insurance_cargo_items SET ppn = CASE {$ppn} END, discount = CASE {$discount} END WHERE id in ({$ids})");

        return redirect()->route('logistic.insurance_cargo.show',Hashids::encode($id))->with(['success' => 'Edit Data Berhasil!']);
        
    }


    public function delete(Request $request)
    {

        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        $insurance  = InsuranceCargo::findOrFail($request->id);

        $insurance_item = InsuranceCargoItem::where('insurance_cargo_id', $insurance->id)
        ->get();

        foreach($insurance_item as $item){
            $ids[]    = $item->spb_item_id;
            $cases[]  = "WHEN id = {$item->spb_item_id} THEN 0";
        }
        $ids        = implode(',', $ids);
        $cases      = implode(' ', $cases);

        \DB::update("UPDATE spb_kolis SET status_insurance_cargo = CASE {$cases} END WHERE id in ({$ids})");
       
        $insurance->delete();

        return redirect()->route('logistic.insurance_cargo.index')->with(['success' => 'Delete Data Berhasil!']);

    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new InsuranceCargoExport($request->get('start_date'), $request->get('end_date')), 'Report-Asuransi-Cargo-'.$date.'.xlsx');
    }


    public function print($id, $type)
    { 
        
        $id = Hashids::decode($id);

        $insurance         = InsuranceCargo::getByID($id['0']);
        $insurance_items   = InsuranceCargo::getProductItem($id['0']);
     

        $company = explode('-',$insurance->doc_no);

        $company = DB::table('companies')
        ->select('companies.*'
        )
        ->where('companies.alias', $company[2])
        ->first();

        if($type =="excel" || $type =="pdf"){

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
            $drawing = new Drawing();

            if ($type =="pdf") {
                $spreadsheet->getDefaultStyle()->getFont()->setSize(9);
            }
            $sheet      = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setScale(80);
            
            $sheet->getPageMargins()->setTop(0.24);
            $sheet->getPageMargins()->setRight(0.2);
            $sheet->getPageMargins()->setLeft(0.2);
            $sheet->getPageMargins()->setBottom(0.24);

            $sheet->getColumnDimension('A')->setWidth(4);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(25);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(25);
            $sheet->getColumnDimension('H')->setWidth(30);
            $sheet->getColumnDimension('I')->setWidth(20);
            $sheet->getColumnDimension('J')->setWidth(8);
            $sheet->getColumnDimension('K')->setWidth(8);
            $sheet->getColumnDimension('L')->setWidth(20);

            $drawing->setPath('storage'.$company->logo); 
            $drawing->setCoordinates('B2');
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $drawing->setWidthAndHeight(80, 80);

            $sheet->setCellValue('A2', strtoupper($company->name));
            $sheet->mergeCells('A2:Q2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A3', $company->address);
            $sheet->mergeCells('A3:Q3');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $sheet->setCellValue('A4', 'Telp: '. $company->telp. ' Fax: '.$company->fax);
            $sheet->mergeCells('A4:Q4');
            $sheet->getStyle('A4')->getFont()->setBold(true);
            $sheet->getStyle('A4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
   
            $sheet->setCellValue('A6', 'ASURANSI CARGO');
            $sheet->mergeCells('A6:Q6');
            $sheet->getStyle('A6')->getFont()->setBold(true)->setUnderline(true);
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('A7', $insurance->doc_no);
            $sheet->mergeCells('A7:Q7');
            $sheet->getStyle('A7')->getFont()->setBold(true);
            $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
          
            $sheet->setCellValue('B8', 'Periode');
            $sheet->setCellValue('C8', ': '. date('d/m/Y',strtotime( $insurance->period)));
            $sheet->setCellValue('B9', 'Risk Location');
            $sheet->setCellValue('C9', ': '. $insurance->risk_location);
            $sheet->setCellValue('B10', 'Keterangan');
            $sheet->setCellValue('C10', ': '. $insurance->notes);
            $sheet->setCellValue('B11', 'Ekspedisi');
            $sheet->setCellValue('C11', ': '. $insurance->expedition);

            $sheet->getStyle('A13:L13')->getFont()->setBold(true);
            $sheet->getStyle('A13:L13')->applyFromArray($styleArrayItem);

            $sheet->setCellValue('A13', 'No');
            $sheet->setCellValue('B13', 'Nama Barang');
            $sheet->setCellValue('C13', 'QTY');
            $sheet->setCellValue('D13', 'No. DPM');
            $sheet->setCellValue('E13', 'No. PO');
            $sheet->setCellValue('F13', 'No. SPB');
            $sheet->setCellValue('G13', 'Annontation');
            $sheet->setCellValue('H13', 'Supplier');
            $sheet->setCellValue('I13', 'Harga Satuan');
            $sheet->setCellValue('J13', 'Diskon');
            $sheet->setCellValue('K13', 'PPN');
            $sheet->setCellValue('L13', 'Sub Total');

            $rows = 14;
            $no = 1;
            $grandtotal = 0;
            foreach($insurance_items as $items){
                
                $total= $items->price * $items->qtyKoli - (($items->price * $items->qtyKoli) *  $items->discount /100);
                if($items->ppn == 1){
                    $subtotal = $total + ( $total * 11/100);
                }else{
                    $subtotal = $total;
                }

                $grandtotal += $subtotal;

                $sheet->setCellValue('A' . $rows, $no);
                $sheet->setCellValue('B' . $rows, $items->product."\nPartnumber: ". $items->productPartNumber." Merk: ".$items->productBrand);
                $sheet->setCellValue('C' . $rows, $items->qtyKoli.' '.$items->measure);
                $sheet->setCellValue('D' . $rows, $items->noDPM);
                $sheet->setCellValue('E' . $rows, $items->noPO);
                $sheet->setCellValue('F' . $rows, $items->noSPB);
                $sheet->setCellValue('G' . $rows, $items->annotation);
                $sheet->setCellValue('H' . $rows, $items->supplier);
                $sheet->setCellValue('I' . $rows, number_format($items->price,2,".",','));
                $sheet->setCellValue('J' . $rows, $items->discount);
                $sheet->setCellValue('K' . $rows, $items->ppn);
                $sheet->setCellValue('L' . $rows, number_format($subtotal,2,".",','));

                $sheet->getStyle('B' . $rows)->getAlignment()->setWrapText(true);
                $sheet->getStyle('C' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('L' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('A' . $rows.':L'.$rows)->applyFromArray($styleArrayTabel);
                $rows++;
                $no++;

            }

            $sheet->setCellValue('A'. $rows, 'TOTAL');
            $sheet->mergeCells('A'. $rows.':K'. $rows);
            $sheet->setCellValue('L'. $rows, number_format($grandtotal,2,".",','));
            $sheet->getStyle('A' . $rows.':L'.$rows)->applyFromArray($styleArrayTabel);
            $sheet->getStyle('A' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('L' . $rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('A'. $rows)->getFont()->setBold(true);
            $sheet->getStyle('L'. $rows)->getFont()->setBold(true);


            $rows = $rows+3;

            $sheet->setCellValue('A'. $rows, 'Prepared By');
            $sheet->mergeCells('A'. $rows.':B'. $rows);
            $sheet->setCellValue('C'. $rows, 'Checked By');
            $sheet->mergeCells('C'. $rows.':D'. $rows);
            $sheet->setCellValue('E'. $rows, 'Approved By');
            $sheet->mergeCells('E'. $rows.':F'. $rows);
            $sheet->setCellValue('G'. $rows, 'Checked By');
            $sheet->mergeCells('G'. $rows.':H'. $rows);
            $sheet->setCellValue('I'. $rows, 'Received By');
            $sheet->mergeCells('I'. $rows.':L'. $rows);
            $sheet->getStyle('A' . $rows.':L'.$rows)->applyFromArray($styleArrayTabel);
            $sheet->getStyle('A' . $rows.':L'.$rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
           
            $rows = $rows+1;
            $sheet->setCellValue('A'. $rows, '');
            $sheet->mergeCells('A'. $rows.':B'. $rows);
            $sheet->setCellValue('C'. $rows, '');
            $sheet->mergeCells('C'. $rows.':D'. $rows);
            $sheet->setCellValue('E'. $rows, '');
            $sheet->mergeCells('E'. $rows.':F'. $rows);
            $sheet->setCellValue('G'. $rows, '');
            $sheet->setCellValue('H'. $rows, '');
            $sheet->setCellValue('I'. $rows, '');
            $sheet->mergeCells('I'. $rows.':J'. $rows);
            $sheet->setCellValue('K'. $rows, '');
            $sheet->mergeCells('K'. $rows.':L'. $rows);
            $sheet->getStyle('A' . $rows.':L'.$rows)->applyFromArray($styleArrayTabel);
            $sheet->getRowDimension($rows)->setRowHeight(80);
            
            $rows = $rows+1;
            $sheet->setCellValue('A'. $rows, $insurance->prepared_by);
            $sheet->mergeCells('A'. $rows.':B'. $rows);
            $sheet->setCellValue('C'. $rows, $insurance->checked_by);
            $sheet->mergeCells('C'. $rows.':D'. $rows);
            $sheet->setCellValue('E'. $rows, $insurance->approved_by);
            $sheet->mergeCells('E'. $rows.':F'. $rows);
            $sheet->setCellValue('G'. $rows, $insurance->checked_purchasing_1);
            $sheet->setCellValue('H'. $rows, $insurance->checked_purchasing_2);
            $sheet->setCellValue('I'. $rows, $insurance->received_by_1);
            $sheet->mergeCells('I'. $rows.':J'. $rows);
            $sheet->setCellValue('K'. $rows, $insurance->received_by_2);
            $sheet->mergeCells('K'. $rows.':L'. $rows);
            $sheet->getStyle('A' . $rows.':L'.$rows)->applyFromArray($styleArrayTabel);
            $sheet->getStyle('A' . $rows.':L'.$rows)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


            $sheet->setShowGridLines(false);

            if ($type =="excel") {
                $writer = new Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'.$insurance->doc_no.'.xlsx"');
                header('Cache-Control: max-age=0');
                $writer->save("php://output");
                die();
            }else{
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="'.$insurance->doc_no.'.pdf"');
                header('Cache-Control: max-age=0');
                $writer->save("php://output");
                die();
            }
        }else{

            return view('logistic.insurance_cargo.print', compact('insurance', 'insurance_items','company'));

        }
       
    }

    public function list()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
     
        return view('logistic.insurance_cargo.list');
    }


    public function listDatatables()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        
        if (isAdministratorCompany() || isLocationAdministrator() || isEmployeeAdministrator() ) {
            $result = Spb::
            selectRaw('spb.*')
            ->leftJoin('locations', 'locations.id', '=', 'spb.location_id')
            ->whereHas('SpbKoli', function($q){
                $q->where('status_insurance_cargo', 0);
            })
            ->where('locations.company_id', Auth::user()->company_id)
            ->where('spb.status','!=', 0)
            ->where('spb.type','!=', 'SPB Vendor')
            ->orderBy('spb.created_at', 'DESC')
            ->get();
        }elseif(isAdministratorLocation()){
            $result = Spb::
            selectRaw('spb.*')
            ->whereHas('SpbKoli', function($q){
                $q->where('status_insurance_cargo', 0);
            })
            ->where('spb.location_id', Auth::user()->location_id)
            ->where('spb.status','!=', 0)
            ->where('spb.type','!=', 'SPB Vendor')
            ->orderBy('spb.created_at', 'DESC')
            ->get();
        }
        else{
            $result = Spb::
            selectRaw('spb.*')
            ->whereHas('SpbKoli', function($q){
                $q->where('status_insurance_cargo', 0);
            })
            ->where('spb.status','!=', 0)
            ->where('spb.type','!=', 'SPB Vendor')
            ->orderBy('spb.created_at', 'DESC')
            ->get();
        }

       return  DataTables::of($result)
       ->addColumn('action', function ($result) {
           $vrl = "<a href='#' value='".action('Logistic\SpbController@popup',['id'=>Hashids::encode($result->id)])."' title='Detail' data-toggle='modal' data-target='#modal' class='font-weight-bold modalDoc'><span class='ti-eye icon-lg'></span> </a>";  
           return '<div>'.$vrl.'</div>';
       })
       ->rawColumns(['action'])
        ->make(true);

    }


    public function close(Request $request)
    {

        if (! Gate::allows('spb')) {
            return abort(401);
        }

        $spb = explode(',',$spb->id);

        for($i=0;$i < count($spb);$i++) {
            $ids[]    = $spb[$i];
            $cases[]  = "WHEN id = {$spb[$i]} THEN 1";
        }
        $ids        = implode(',', $ids);
        $cases      = implode(' ', $cases);

        \DB::update("UPDATE spb SET insurance_status = CASE {$cases} END WHERE id in ({$ids})");
       
        return redirect()->route('logistic.insurance_cargo.list')->with(['success' => 'Berhasil menutup SPB!']);
    }


}
