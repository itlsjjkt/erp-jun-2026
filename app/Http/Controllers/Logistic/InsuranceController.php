<?php

namespace App\Http\Controllers\Logistic;

use App\Models\InsuranceCargo;
use App\Models\Insurance;
use App\Models\InsuranceItem;
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
use App\Exports\InsuranceExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; 
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Traits\UploadTrait;
use PDF;

class InsuranceController extends Controller
{
    use UploadTrait;
    public function __construct (){
        $this->ppn = array(
            '0' => 'Tidak',
            '10%' => '10',
            '11%' => '11'
        );
    }

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

        return view('logistic.insurance.index');
    }

    public function datatables()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        
        $result = DB::table('insurances')
        ->select('insurances.*','users.name AS created')
        ->leftJoin('users', 'users.id', '=', 'insurances.created_by')
        ->orderBy('insurances.created_at', 'DESC');
        
       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('logistic.insurance.edit',Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_approved = "<a href='".route('logistic.insurance.approved',Hashids::encode($result->id))."' title='".trans('Next Action')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-lock icon-lg'></span> </a>";  
            $url_view = "<a href='".route('logistic.insurance.show',Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            if($result->status==0){
                return '<div class="btn-group">'.$url_view.$url_edit.'</div>';
            } else if($result->status==1){
                return '<div class="btn-group">'.$url_view.$url_approved.'</div>';
            } else{
                return '<div class="btn-group">'.$url_view.'</div>';
            }
        })
        ->editColumn('doc_no', function ($result) {
        $doc_no = "<a target='_blank' href='".route('logistic.insurance.show', Hashids::encode($result->id))."' title='Detail' data-toggle='tooltip' >".$result->doc_no."</a>";
        return $doc_no;
        })
        ->editColumn('status', function ($result) {
            return getStatusInsurance($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->rawColumns(['action', 'status','doc_no'])
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
        ->select('spb.*','companies.name AS company')
        ->leftJoin('companies','companies.id','=','spb.company_id')
        ->whereIn('spb.id', $request->spb_id)
        ->get();
        $spb_id = implode(',',$request->spb_id);
        $ppn = $this->ppn;
        return view('logistic.insurance.create', compact('spb','spb_id','ppn'));
        
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // var_dump($request->all());
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        
        if ($request->get('status')==1) {
            $data['status']    = 1;
        }else{
            $data['status'] = 0;
        }

        $data['publish']   = date('Y-m-d');
        $increment = DB::table('insurances')
            ->whereDate("created_at", Carbon::today()->toDateString())
            ->where('status','!=', 0)
            ->get();
        $num = sprintf("%'.03d", count($increment) + 1) ;
        $doc_no = "INS-LOG-".date('dmy')."-".$num;

        $data['spb_id']                 = $request->get('spbID');
        $data['company']                = $request->get('company');
        $data['project']                = $request->get('project');
        $data['doc_no']                 = $doc_no;
        $data['expedition_forwarder']   = $request->get('expedition_forwarder');
        $data['risk_location']         = $request->get('risk_location');
        $data['etd_eta']                = $request->get('etd_eta');
        $data['shipped_by']             = $request->get('shipped_by');
        $data['prepared_by']            = $request->get('prepared_by');
        $data['checked_by_1']           = $request->get('checked_by_1');
        $data['checked_by_2']           = $request->get('checked_by_2');
        $data['known_by_1']             = $request->get('known_by_1');
        $data['known_by_2']             = $request->get('known_by_2');
        $data['known_by_3']             = $request->get('known_by_3');
        $data['received_by']            = $request->get('received_by');
        $data['approved_by']            = $request->get('approved_by');
        $data['created_by']             = Auth::user()->id;

        DB::beginTransaction();
        try {
            $insurance = Insurance::create($data);

            $dataInsurance = [];
            $cases  = [];
            $ids = [];

            $item = $request->get('spb_item_id');

            for($i=0;$i < count($item);$i++) {

                if (in_array($request->get('spb_item_id')[$i], $request->get('iscreateInsurance'))) {
                    $dataInsurance[] = [
                        'insurance_id'          => $insurance->id,
                        'spb_item_id'           => $request->get('spb_item_id')[$i],
                        'ppn'                   => $request->get('ppn')[$i],
                        'discount'              => $request->get('discount')[$i],
                        'price'                 => $request->get('price')[$i],
                    ];
                    $ids[]          = $request->get('spb_item_id')[$i];
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 1";
                }else{
                    $cases[]        = "WHEN id = {$request->get('spb_item_id')[$i]} THEN 0";
                }

            }
            $ids        = implode(',', $ids);
            $cases      = implode(' ', $cases);

            \DB::update("UPDATE spb_kolis SET status_insurance = CASE {$cases} END WHERE id in ({$ids})");
        
            InsuranceItem::insert($dataInsurance);
            
            DB::commit();
            return redirect()->route('logistic.insurance.show', Hashids::encode($insurance->id))->with(['success' => 'Input Data Berhasil!']);
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
        $id                = Hashids::decode($id);
        $insurance         = Insurance::getByID($id['0']);
        $insurance_items   = Insurance::getProductItem($id['0']);
        return view('logistic.insurance.show', compact('insurance', 'insurance_items'));
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
        $insurance_items  = Insurance::getProductItem($id['0']);
        $insurance        = Insurance::getByID($id['0']);

        return view('logistic.insurance.edit', compact('insurance','insurance_items'));
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
        $insurance = Insurance::findOrFail($id);
        $data = $request->all();

        if ($request->get('status')==1) {
            $data['status']    = 1;
            $data['publish']   = date('Y-m-d');
        }else{
            $data['status'] = 0;
        }
        $data['spb_id']                 = $request->get('spbID');
        $data['company']                = $request->get('company');
        $data['project']                = $request->get('project');
        $data['expedition_forwarder']   = $request->get('expedition_forwarder');
        $data['risk_location']          = $request->get('risk_location');
        $data['etd_eta']                = $request->get('etd_eta');
        $data['shipped_by']             = $request->get('shipped_by');
        $data['prepared_by']            = $request->get('prepared_by');
        $data['checked_by_1']           = $request->get('checked_by_1');
        $data['checked_by_2']           = $request->get('checked_by_2');
        $data['known_by_1']             = $request->get('known_by_1');
        $data['known_by_2']             = $request->get('known_by_2');
        $data['known_by_3']             = $request->get('known_by_3');
        $data['received_by']            = $request->get('received_by');
        $data['approved_by']            = $request->get('approved_by');
        $data['updated_by']             = Auth::user()->id;

        $insurance->update($data);

        $ids = $ppn = $discount = $price = [];

        $item = $request->get('ins_item_id');
        for($i=0;$i < count($item);$i++) {
            $ids[]          = $request->get('ins_item_id')[$i];
            $ppn[]          = "WHEN id = {$request->get('ins_item_id')[$i]} THEN ".$request->get('ppn')[$i];
            $discount[]     = "WHEN id = {$request->get('ins_item_id')[$i]} THEN ". str_replace(",", "", $request->get('discount')[$i]);
            $price[]        = "WHEN id = {$request->get('ins_item_id')[$i]} THEN ".$request->get('price')[$i];
        }
        $ids            = implode(',', $ids);
        $ppn            = implode(' ', $ppn);
        $discount       = implode(' ', $discount);
        $price          = implode(' ', $price);

        \DB::update("UPDATE insurance_items SET ppn = CASE {$ppn} END, discount = CASE {$discount} END,price = CASE {$price} END WHERE id in ({$ids})");

        return redirect()->route('logistic.insurance.show',Hashids::encode($id))->with(['success' => 'Edit Data Berhasil!']);
        
    }


    public function approved($id)
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
        $id = Hashids::decode($id);
        $insurance_items  = Insurance::getProductItem($id['0']);
        $insurance        = Insurance::getByID($id['0']);

        return view('logistic.insurance.approved', compact('insurance','insurance_items'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_approved(Request $request){
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }

        $id = $request->get('id');
        $insurance = Insurance::findOrFail($id);
        $data = $request->all();
        $data['status']      = $request->get('status_'); 
        $data['notes']      = $request->get('notes');

        if ($request->hasFile('mr_file')) {
            $file = $request->file('mr_file');
            $name = 'INS-'.time();
            $folder = '/uploads/insurance/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['mr_file'] = $filePath;
        }
        
        $insurance->update($data);
        
        return redirect()->route('logistic.insurance.show',Hashids::encode($id))->with(['success' => 'Update Asuransi Berhasil!']);
    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new InsuranceExport($request->get('start_date'), $request->get('end_date')), 'Report-Asuransi-'.$date.'.xlsx');
    }

    public function print($id, $type)
    {
        $id = Hashids::decode($id);

        $insurance = Insurance::getByID($id['0']);
        $insurance_items = Insurance::getProductItem($id['0']);

        $pdf = PDF::loadView('logistic.insurance.print', compact('insurance', 'insurance_items'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($insurance->doc_no . '.pdf');
    }

    public function list()
    {
        if (! Gate::allows('asuransi')) {
            return abort(401);
        }
     
        return view('logistic.insurance.list');
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
                $q->where('status_insurance', 0);
            })
            ->where('locations.company_id', Auth::user()->company_id)
            ->where('spb.status','=', 1)
            ->orderBy('spb.created_at', 'DESC')
            ->get();
        }elseif(isAdministratorLocation()){
            $result = Spb::
            selectRaw('spb.*')
            ->whereHas('SpbKoli', function($q){
                $q->where('status_insurance','=', 0);
            })
            ->where('spb.location_id', Auth::user()->location_id)
            ->where('spb.status','=', 1)
            ->orderBy('spb.created_at', 'DESC')
            ->get();
        }
        else{
            $result = Spb::
            selectRaw('spb.*')
            ->whereHas('SpbKoli', function($q){
                $q->where('status_insurance','=', 0);
            })
            ->where('spb.status','=', 1)
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
       
        return redirect()->route('logistic.insurance.list')->with(['success' => 'Berhasil menutup SPB!']);
    }


}
