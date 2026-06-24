<?php

namespace App\Http\Controllers\Logistic;

use App\Exports\ApprovalHistoricalExport;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\MasterItem;
use App\Models\Workarea;
use App\Models\Company;
use App\Models\Project;
use App\Models\Department;
use App\Models\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Mail\SendMailable;
use App\Exports\DpmExport;
use Illuminate\Support\Facades\Redirect;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Auth;
use Storage;
use PDF;

class PurchaseRequestController extends Controller
{
    use UploadTrait;

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        if(isAdministrator() || isAdmin() ){
            $company  = DB::table('companies')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $location = '';
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            if(isAdministratorCompany()){
                $location = DB::table('locations')->where('isDPM', true)->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            }else{
                $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            }
            $company  = DB::table('companies')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $project = Project::whereNull('deleted_at')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        return view('logistic.dpm.index',compact('location','department','company','project'));
    }

    public function datatables(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = PurchaseRequest::getData($data);
        else $result = PurchaseRequest::getData($data, Auth::user()->id);

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {

            $url_revision = "<a href='".route('purchase_request.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'> </a>";
            $url_edit = "<a href='".route('purchase_request.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_view = "<a href='".route('purchase_request.show', Hashids::encode($result->id))."' title='".trans('app.show_title')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            $url_delete = "<form class='delete' action='".route('purchase_request.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            if($result->status==0){
                return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
            }elseif($result->status==3){
                return '<div class="btn-group">'.$url_revision.$url_view.'</div>';
            }else{
                return '<div class="btn-group">'.$url_view.'</div>';
            }

        })
        ->editColumn('doc_no', function ($result) {
            if($result->status==3) return $result->doc_no." <span class='badge badge-danger'> Hold </span>";
            else return $result->doc_no;
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s' ) : '';
        })
        ->rawColumns(['action', 'status', 'doc_no'])
        ->make(true);

    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        if(isAdministrator() || isAdmin() ){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->where('isDPM', true)
                ->get()
                ->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->where('status',1)
                ->get()
                ->pluck('name', 'id')->prepend('Silahkan pilih...', '');
           
                    
        }else{
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            if(isAdministratorCompany()) $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            else $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
       
        $project  = DB::table('projects')->whereNull('deleted_at')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('logistic.dpm.create', compact('department','location','project'));
    }

    public function create_item(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $flag = array(
            'normal' => 'Normal',
            'urgent'  => 'Urgent'
        );
        $location_id    = $request->get('location_id');
        $department_id  = $request->get('department_id');
        $project_id     = $request->get('project_id');
        $type    = $request->get('type');

        $location = Workarea::findOrFail($location_id);
        $department = Department::findOrFail($department_id);
        $project   = Project::findOrFail($project_id);

        if( $project->category == NULL) return redirect()->back()->withInput()->withErrors(['error' => 'Project belum disetting Kategori Produk']);
        $arr = array($location->company_id, $department->company_id);
       
        if (count(array_unique($arr)) === 1) {
            $category = implode(',',$project->category);
            $item = MasterItem::whereIn('id',$project->category)->get();
            $category_name = [];

            foreach ($item as $val){
                $category_name []= $val->name;
            }
            return view('logistic.dpm.item', compact('flag','location_id','department_id','project_id','type','category','category_name'));
        }else{
            return redirect()->back()->withInput()->withErrors(['error' => 'Perusahaan yang dipilih tidak sama']);
        }

    }


    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $location  = Workarea::where("id", $request->get('location_id'))->first();

        if(isAdministrator() || isAdmin() ){
            $company   = Company::where('id', $location->company_id)->first()->alias;
        }else{
            $company   = Company::where('id', Auth::user()->company_id)->first()->alias;
        }
        $approval = getApprovalLogistic($location->id, 1);

        if($approval){
            if ($request->get('status')==1) {

                $increment = DB::table('purchases')
                ->whereYear("publish", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->where('status','!=', 0)
                ->count();

                $num = sprintf("%'.05d", $increment + 1) ;
                $no_dpm = "DPM-".$company."-".$location->alias."-".date('my')."-".$num;

                $data['status']    = 1;
                $data['publish']   = date('Y-m-d H:i:s');
                $dataHistory['jenis']   = 'insert';

            }else{
                $no_dpm = "DPM-".$company."-".$location->alias."-".date('my')."-DRAFT";
                $data['status']    = 0;
                $dataHistory['jenis']  = 'DRAFT';
            }

            if ($request->hasFile('mr_file')) {
                $file = $request->file('mr_file');
                $name = 'DPM-'.time();
                $folder = '/uploads/dpm/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $data['mr_file'] = $filePath;
            }

            $data['location_id'] = $request->get('location_id');
            $data['department_id'] = $request->get('department_id');
            $data['project_id'] = $request->get('project_id');
            $data['description'] = $request->get('description');
            $data['uuid']  = Str::uuid();
            $data['doc_no'] = $no_dpm;
            $data['created_by'] = Auth::user()->id;
            $data['type'] = $request->get('type');

            DB::beginTransaction();

            try {

                $purchase = PurchaseRequest::create($data);

                $dataPR = [];
                $itemList = $request->get('product_id');
                for($i=0;$i < count($itemList);$i++) {
                    $dataPR[] = [
                        'purchase_id'   => $purchase->id,
                        'product_id'    => $request->get('product_id')[$i],
                        'flag'          => $request->get('flag')[$i],
                        'needed_on_date'=> $request->get('needed_on_date')[$i],
                        'notes'         => $request->get('notes')[$i],
                        'qty'           => $request->get('qty')[$i],
                        'measure'       => $request->get('measure')[$i],
                        'position'      => $approval->user_id,
                        'status'        => $request->get('status'),
                        'step'          => 1
                    ];
                }

                $purchase_items = PurchaseRequestItem::insert($dataPR);

                $dataHistory['purchase_id'] = $purchase->id;
                $dataHistory['user_id']     =  Auth::user()->id;
                PurchaseRequestHistory::create($dataHistory);

                if ($request->get('status')==1) {

                    $content = "Terdapat pengajuan DPM dengan Nomor: ". $no_dpm. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
                    $msgData = array(
                        'title'         => 'Konfirmasi Approval DPM',
                        'content'       => $content,
                        'name'          => $approval->name,
                        'email'         => $approval->email,
                        'no_dpm'        => $no_dpm
                    );
                    if(config('app.mail_status')=='on' && $approval->notification_email){
                        Mail::send('emails.notification', $msgData, function ($message) use($msgData) {
                            $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan DPM dengan no: '.  $msgData['no_dpm']);
                        });
                    }

                    $dataNotification['title']      = "Approval DPM";
                    $dataNotification['link']       = "/approval/purchase_set/".Hashids::encode($purchase->id);
                    $dataNotification['data_id']    = $purchase->id;
                    $dataNotification['content']    = "Terdapat pengajuan DPM dengan nomor: ". $no_dpm;
                    $dataNotification['user_id']    = $approval->user_id;
                    $notifications = Notification::create($dataNotification);
                }

                DB::commit();
                return redirect()->route('purchase_request.show',Hashids::encode($purchase->id))->with(['success' => 'Input Data Berhasil!']);

            } catch (\Exception $e) {

                DB::rollback();
                return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
            }

        }else{
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Approval Rule tidak ditemukan']);
        }

    }


    public function show($id)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $id = Hashids::decode($id);
        $pr         = PurchaseRequest::getByID($id['0']);
        $pr_history = PurchaseRequestHistory::where('purchase_id',$pr->id)->where('jenis','hold')->latest()->first();
        $pr_items   = PurchaseRequest::getProductItem($id['0']);
        return view('logistic.dpm.show', compact('pr', 'pr_items','pr_history'));
    }

    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $id = Hashids::decode($id);

        $type = array(
            "im" => "IM",
            "petty_cash" => "Petty Cash",
            "po" => "PO",
        );
       
   
        $flag = array(
            "normal" => "Normal",
            "urgent" => "Urgent",
        );
       
        if(isAdministrator() || isAdmin() ){
            $department  = DB::table('departments')
                        ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                        ->leftjoin('companies','companies.id','=','departments.company_id')
                        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $pr = PurchaseRequest::getByID($id['0']);
        $project   = Project::findOrFail($pr->project_id);
        $category = implode(',',$project->category);

        $pr_items   = PurchaseRequest::getProductItem($id['0']);
        $pr_history = PurchaseRequestHistory::where('purchase_id',$pr->id)->where('jenis','hold')->latest()->first();
        return view('logistic.dpm.edit', compact('pr','pr_items','department','category','flag','project','pr_history','type'));
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
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $pr = PurchaseRequest::findOrFail($id);
        $approval = getApprovalLogistic($request->get('location_id'), 1);

        if($approval){
            if ($request->get('status')==1 && $request->get('revision')== 0) {
                $increment = DB::table('purchases')
                ->whereYear("publish", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->where('status','!=', 0)
                ->count();

                $location   = $request->get('location_alias');
                $company    = $request->get('company_alias');

                $num    = sprintf("%'.05d", $increment + 1) ;
                $doc_no = "DPM-".$company."-".$location."-".date('my')."-".$num;

                $data['publish']   = date('Y-m-d H:i:s');
                $data['status']    = 1;
                $data['doc_no']    = $doc_no;
            }

            if ($request->hasFile('mr_file')) {
                $file = $request->file('mr_file');
                $name = 'DPM_'.time();
                $folder = '/uploads/dpm/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $data['mr_file'] = $filePath;
            }

            $data['updated_by'] = Auth::user()->id;
            $data['type'] = $request->get('type');
            $data['department_id'] = $request->get('department_id');
            $data['description'] = $request->get('description');
            $data['status'] = $request->get('status');
           
            $pr->update($data);

            if($request->get('product_id_new')){
                $dataPR = [];
                $itemList = $request->get('product_id_new');

                for($i=0;$i < count($itemList);$i++) {
                    $dataPR[] = [
                        'purchase_id'   => $pr->id,
                        'product_id'    => $request->get('product_id_new')[$i],
                        'flag'          => $request->get('flag_new')[$i],
                        'needed_on_date'=> $request->get('needed_on_date_new')[$i],
                        'notes'         => $request->get('notes_new')[$i],
                        'qty'           => $request->get('qty_new')[$i],
                        'measure'       => $request->get('measure_new')[$i],
                        'position'      => $approval->user_id,
                        'status'        => $request->get('status'),
                        'step'          => 1
                    ];
                }

                $purchase_items = PurchaseRequestItem::insert($dataPR);
            }

            $dpm_ids = $product_id = $flag = $needed_on_date = $notes  = $measure  = $qty  = $position = $status = $step = [];

            $itemListOld = $request->get('product_id');

            for($i=0;$i < count($itemListOld);$i++) {
                $dpm_ids[]    = $request->get('dpm_item_id')[$i];
                $product_id[] = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $request->get('product_id')[$i];
                $flag[]       = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '". $request->get('flag')[$i]."'";
                $needed_on_date[] = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN DATE('". $request->get('needed_on_date')[$i]."')";
                $notes[]    = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '".$request->get('notes')[$i]."'";
                $qty[]      = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $request->get('qty')[$i];
                $measure[]  = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '". $request->get('measure')[$i]."'";
                $position[] = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $approval->user_id;
                $status[]   = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $request->get('status');
                $step[]     = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN 1";

            }

            $dpm_ids        = implode(',', $dpm_ids);
            $product_id     = implode(' ', $product_id);
            $flag           = implode(' ', $flag);
            $needed_on_date = implode(' ', $needed_on_date);
            $notes    = implode(' ', $notes);
            $measure  = implode(' ', $measure);
            $qty      = implode(' ', $qty);
            $position = implode(' ', $position);
            $status   = implode(' ', $status);
            $step     = implode(' ', $step);

            \DB::update("UPDATE purchase_items SET 
                product_id  = CASE {$product_id} END, 
                flag  = CASE {$flag} END, 
                needed_on_date = CASE {$needed_on_date} END, 
                measure  = CASE {$measure} END, 
                qty  = CASE {$qty} END, 
                notes = CASE {$notes} END,  
                position = CASE {$position} END,  
                status = CASE {$status} END, 
                step = CASE {$step} END 
            WHERE id in ({$dpm_ids})");


            $dataHistory['purchase_id'] = $id;
            $dataHistory['user_id'] = Auth::user()->id;
            $dataHistory['jenis'] = 'update';

            $purchase = PurchaseRequestHistory::create($dataHistory);

            if ($request->get('status')==1 || $request->get('revision')==1 ) {
                $content = "Terdapat pengajuan DPM dengan Nomor: ". $pr->doc_no. " yang menunggu approval anda. Mohon segara untuk melakukan approval dengan login kedalam aplikasi ERP Shipping.";
                $msgData = array(
                    'title'         => 'Konfirmasi Approval DPM',
                    'content'       => $content,
                    'name'          => $approval->name,
                    'email'         => $approval->email,
                    'no_dpm'        => $pr->doc_no
                );
                if (config('app.mail_status')=='on' && $approval->notification_email) {
                    Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                        $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan DPM dengan no: '.  $msgData['no_dpm']);
                    });
                }

                $dataNotification['title']      = "Approval DPM";
                $dataNotification['link']       = "/approval/purchase_set/".Hashids::encode($pr->id);
                $dataNotification['data_id']    = $pr->id;
                $dataNotification['content']    =  "Terdapat pengajuan DPM dengan nomor: ". $pr->doc_no;
                $dataNotification['user_id']    = $approval->user_id;
                Notification::create($dataNotification);
            }

            if ($request->get('revision')==1) {
                $notification = Notification::where(['user_id' => Auth::user()->id, 'data_id' => $pr->id, 'status' => 0])->first();
                if($notification){
                    $data['status'] = 1;
                    $notification->update($data);
                }
                return redirect()->route('purchase_revision.index', Hashids::encode($pr->id))->with(['success' => 'Revisi DPM Berhasil!']);
            }
            else  return redirect()->route('purchase_request.show', Hashids::encode($pr->id))->with(['success' => 'Edit Data Berhasil!']);

        }else{
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Approval Rule tidak ditemukan']);
        }


    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {

        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $pr  = PurchaseRequest::findOrFail($request->id);
        $pr->delete();

        return redirect()->route('purchase_request.index')->with(['success' => 'Delete Data Berhasil!']);

    }

    public function getPR($pid)
    {
        return DB::table('purchases')
        ->where('location_id', $pid)
        ->where('status', 4)
        ->pluck('pr_no', 'id');
    }

    public function getPrItemNotes($pid)
    {
        $notes = DB::table('purchase_notes')
        ->select('purchase_notes.*','users.name AS user')
        ->leftJoin('users', 'users.id', '=', 'purchase_notes.user_id')
        ->where('purchase_notes.pr_item_id', $pid)
        ->orderBy('purchase_notes.created_at','DESC')
        ->get();
        return view('logistic.dpm.notes',compact('notes'))->renderSections()['content'];
    }


    public function getPRItem($pid)
    {
        return DB::table('purchases')
        ->where('location_id', $pid)
        ->where('status', 4)
        ->pluck('pr_no', 'id');
    }

    public function search(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $data = $request->all();
        $query = "company_id=".$request->get('company_id')."&location_id=".$request->get('location_id')."&department_id=".$request->get('department_id')."&project_id=". $request->get('project_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
     
        $search = "Cari Berdasarkan: ";
        if($request->input('company_id')) $search .= "<strong> Company: </strong>".getDataByID('companies',$request->input('company_id'))->name;
        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        if($request->input('project_id'))  $search .= "<strong> Project: </strong>".getDataByID('projects',$request->input('project_id'))->name;
        if($request->input('department_id')) $search .= "<strong> Departemen/Kapal: </strong>".getDataByID('departments',$request->input('department_id'))->name;
        $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

        if(isAdministrator()){
            $company  = DB::table('companies')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $location = '';
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            if(isAdministratorCompany()){
                $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            }elseif(isAdministratorLocation()){
                $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            }else{
                $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            }
            $company  = DB::table('companies')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $project = Project::whereNull('deleted_at')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        return view('logistic.dpm.search', compact('data', 'search','department','company','project','query'));
    }

    public function print($id, $type)
    {

        $id = Hashids::decode($id);

        $pr         = PurchaseRequest::getByID($id['0']);
        $pr_items   = PurchaseRequest::getProductItem($id['0']);
        return view('logistic.dpm.print', compact('pr', 'pr_items'));
    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new DpmExport($request->get('company_id'), $request->get('location_id'), $request->get('department_id'), $request->get('project_id'), $request->get('start_date'), $request->get('end_date')), 'Report-DPM-'.$date.'.xlsx');
    }

    public function export_historical(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new ApprovalHistoricalExport($request->get('company_id'), $request->get('location_id'), $request->get('department_id'), $request->get('project_id'), $request->get('start_date'), $request->get('end_date')), 'Report-Historical-Approval-DPM-'.$date.'.xlsx');
    }

    public function getJs($id)
    {
        $id = $id;
        return view('logistic.dpm.js', compact('id'));
    }



    public function exportItem(Request $request)
    {

        $data = $request->all();

        $query = DB::table('purchases')
        ->select('purchases.*','users.name AS created','departments.name AS department')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->when(!empty($data['doc_no']), function ($query) use ($data) {
            return $query->where('purchases.doc_no',$data['doc_no']);
        })->when(!empty($data['location_id']), function ($query) use ($data) {
            return $query->where('purchases.location_id',$data['location_id']);
        })->when(!empty($data['department_id']), function ($query) use ($data) {
            return $query->where('purchases.department_id',$data['department_id']);
        })->when(!empty($data['start_date']), function ($query) use ($data) {
            if($data['end_date']){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('purchases.created_at', [$start , $end]);
            }else{
                return $query->where('purchases.created_at', $data['start_date']);
            }
        })

        ->get();
        if( $query->isEmpty() ){
            return redirect()->route('purchase_request.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('REPORT-DPM-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'Nomor DPM'         => $data->doc_no,
                    'Department'        => $data->department,
                    'Dibuat'            => $data->created,
                    'Tanggal Input'     => dateTextMySQL2ID($data->created_at),
                    'Status'            => $data->location_id,
                ];
            });
        }


    }


    public function form(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        if($request->get('inv_id')){

            $invID = explode(',',$request->get('inv_id'));

            $inventory = DB::table('inventories')
            ->select('inventories.*','locations.name AS locationName',
            'master_item_products.id AS productID','master_item_products.item_id AS itemID', 'master_items.name AS itemName',
            'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand',
            'measures.name AS unit')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
            ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
            ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->whereIn('inventories.id',$invID)
            ->get();



            $location = [];
            $items = [];
            foreach($inventory as $item){
               $status[]    = $item->status;
               $location[]  = $item->location_id;
               $items[]     = $item->itemID;
               $itemID     = $item->itemID;
               $itemName   = $item->itemName;
               $locationID  = $item->location_id;
               $locationName= $item->locationName;
            }

            if(count(array_unique($location)) === 1 ){
                if(count(array_unique($items)) === 1 ){
                    if (in_array(3, $status)){
                        return redirect()->back()
                        ->withErrors(['Terdapat Item yang sudah dilakukan Write Off (WO)']);
                    }else{

                        $flag = array(
                            "normal" => "Normal",
                            "urgent" => "Urgent",
                        );

                        $locationID = $locationID;
                        $locationName = $locationName;

                        $workarea = Workarea::findOrFail($locationID );
                        
                        $project   = DB::table('projects')->where('company_id', $workarea->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                        $department = DB::table('departments')->where('company_id', $workarea->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                        $item   = DB::table('master_items')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

                        return view('logistic.dpm.form', compact('department','item','flag','locationID','locationName', 'inventory','project','itemID','itemName'));

                    }

                }else{
                    return redirect()->back()
                    ->withErrors(['Terdapat Item Kategori yang berbeda pada Produk yang dipilih. DPM hanya bisa diajukan per Kategori']);
                }
            }else{
                return redirect()->back()
                ->withErrors(['Lokasi Stock Gudang berbeda pada Item yang dipilih']);
            }


        }else{
                return redirect()->back()
                ->withErrors(['Belum melakukan Checklist Item Inventory']);
        }
    }


    public function detail($id)
    {

        $pr = DB::table('purchases')
        ->select('purchases.*','users.name AS created','departments.name AS department','locations.name AS location',
            'companies.name AS company','companies.address AS companyAddress','companies.telp AS companyTelp','companies.fax AS companyFax'
        )
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->where('purchases.uuid', $id)
        ->first();

        $pr_items   = PurchaseRequest::getProductItem($pr->id);

        return view('logistic.dpm.print', compact('pr', 'pr_items'));


    }


    public function remove_item(Request $request)
    {
        $pr = PurchaseRequestItem::findOrFail($request->get('dpm_id'));
        $pr->delete();
        return redirect()->back()->with(['success' => 'Delete Data Berhasil!']);
    }

   
}
