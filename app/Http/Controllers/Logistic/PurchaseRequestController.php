<?php

namespace App\Http\Controllers\Logistic;

use App\Exports\ApprovalHistoricalExport;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestNotes;
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
use App\Exports\DpmExportNew;
use OpenSpout\Common\Entity\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnDimension;
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
            $url_publish_approval = "<form class='publish_approval' action='".route('purchase_request.publish_approval', ['id' => Hashids::encode($result->id)])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-primary' title='Publish DPM' data-toggle='tooltip'><i class='ti-new-window icon-lg'></i></button>
                            </form>";
            $url_reject = '<button title="Reject DPM" class="btn btn-sm btn-reject-dpm text-danger" style="background-color: transparent;" data-toggle="modal" data-target="#modalAlasanReject" data-id="' .  Hashids::encode($result->id) . '"><i class="ti-power-off icon-lg"></i></button>';

            if($result->status==0){
                return '<div class="btn-group">'.$url_edit .$url_view .$url_delete.'</div>';
            }elseif($result->status==11){
                return '<div class="btn-group">'.$url_edit.$url_view.$url_publish_approval.$url_reject.'</div>';
            }elseif($result->status==3){
                return '<div class="btn-group">'.$url_revision.$url_view.$url_reject.'</div>';
            }else{
                return '<div class="btn-group">'.$url_view.'</div>';
            }
        })
        ->editColumn('doc_no', function ($result) {
            if($result->status == 11) return '<span style="font-weight:bold;">'.$result->doc_no.'</span>';
            else return $result->doc_no;
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s' ) : '';
        })
        ->editColumn('status', function ($result) {
            return getStatusDpm($result->status);
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
                ->where('status','=',1)
                ->get()
                ->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->where('status',1)
                ->where('isdpm',1)
                ->get()
                ->pluck('name', 'id')->prepend('Silahkan pilih...', '');


        }else{
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->where('isdpm',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            if(isAdministratorCompany()) $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->where('status','=',1)->where('isDPM', true)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            else $location = DB::table('locations')->where('id', Auth::user()->location_id)->where('status','=',1)->where('isDPM', true)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
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
                ->whereYear("created_at", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->where('status','!=', 0)
                ->count();

                $num = sprintf("%'.05d", $increment + 1) ;
                $no_dpm = "DPM-".$company."-".$location->alias."-".date('my')."-".$num;

                // $data['status']    = 1;
                // $data['publish']   = date('Y-m-d H:i:s');
                $data['status']    = 11;
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
                        'flag'          => $request->get('flag'),
                        'needed_on_date'=> $request->get('needed_on_date'),
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
                    // if(config('app.mail_status')=='on' && $approval->notification_email){
                    //     Mail::send('emails.notification', $msgData, function ($message) use($msgData) {
                    //         $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan DPM dengan no: '.  $msgData['no_dpm']);
                    //     });
                    // }

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
        $dataWti = DB::table('inventory_transfer_in')
            ->select(
                'inventory_transfer_in.*',
                'users.name AS created',
                'inventory_transfer_out.doc_no AS doc_no_wto',
                'inventory_transfer_out.location_id AS location_id_wto',
                'inventory_transfer_out.created_at AS created_at_wto'
            )
            ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_in.location_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_in.created_by')
            ->leftJoin('inventory_transfer_out','inventory_transfer_out.id','=','inventory_transfer_in.transfer_out_id')
            ->where('inventory_transfer_in.id','=',$pr->request_type_id)
            ->first();
        return view('logistic.dpm.show', compact('pr', 'pr_items','pr_history','dataWti'));
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
                        ->where('departments.isdpm','=',1)
                        ->where('departments.status','=',1)
                        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('status',1)->where('isdpm',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $pr = PurchaseRequest::getByID($id['0']);
        $project   = Project::findOrFail($pr->project_id);
        $category = implode(',',$project->category);

        $pr_items   = PurchaseRequest::getProductItem($id['0']);
        $pr_history = PurchaseRequestHistory::where('purchase_id',$pr->id)->where('jenis','hold')->latest()->first();
        if($pr->status == 11){
            return view('logistic.dpm.editnewdraft', compact('pr','pr_items','department','category','flag','project','pr_history','type'));
        }else{
            return view('logistic.dpm.edit', compact('pr','pr_items','department','category','flag','project','pr_history','type'));
        }
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
                ->whereYear("created_at", date('Y'))
                ->where('location_id',$request->get('location_id'))
                ->where('status','!=', 0)
                ->count();

                $location   = $request->get('location_alias');
                $company    = $request->get('company_alias');

                $num    = sprintf("%'.05d", $increment + 1) ;
                $doc_no = "DPM-".$company."-".$location."-".date('my')."-".$num;

                // $data['publish']   = date('Y-m-d H:i:s');
                $data['status']    = 11;
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
                // if (config('app.mail_status')=='on' && $approval->notification_email) {
                //     Mail::send('emails.notification', $msgData, function ($message) use ($msgData) {
                //         $message->to($msgData['email'], $msgData['name'])->subject('Pengajuan DPM dengan no: '.  $msgData['no_dpm']);
                //     });
                // }

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
        // 1. Ambil semua input dari request
        $data = $request->all();
        $dateNow = date('Y-m-d');

        // 2. Validasi input wajib
        if (empty($data['start_date']) || empty($data['end_date'])) {
            return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
        }

        try {
            // 3. Parsing tanggal dengan Carbon
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();

            // 4. Cek apakah tanggal mulai melewati tanggal selesai
            if ($start->gt($end)) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
            }

            // 5. Hitung selisih hari
            $diff = $start->diffInDays($end);
            if ($diff > 31) {
                return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . ($diff + 1) . ' hari)');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
        }

        // 6. Jalankan Export
        return Excel::download(
            new DpmExport(
                $request->company_id, 
                $request->location_id, 
                $request->department_id, 
                $request->project_id, 
                $data['start_date'], 
                $data['end_date']
            ), 
            'Report-DPM-' . $dateNow . '.xlsx'
        );
    }

    // public function export_new(Request $request){
    //     $data = $request->all();

    //     if (empty($data['start_date']) || empty($data['end_date'])) {
    //         return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
    //     }
    //     try {
    //         $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
    //         $end = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();
    //                 $diff = $start->diffInDays($end);
    //         if ($diff > 31) {
    //             return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . $diff . ' hari)');
    //         }
    //                 if ($start->gt($end)) {
    //             return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
    //         }
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
    //     }
    //     $query = DB::table('purchase_items')
    //         ->distinct('purchase_items.id')
    //         ->select('purchase_items.*',
    //             'purchases.doc_no AS no_dpm',
    //             'purchases.created_at AS tgl_dpm',
    //             'purchases.publish AS tgl_publish_dpm',
    //             'purchase_requisitions.doc_no AS no_pr',
    //             'purchase_requisitions.created_at AS tgl_pr',
    //             'purchaser.name AS purchaser',
    //             'master_item_products.code AS product_code',
    //             'master_item_products.name AS product_name',
    //             'master_item_products.part_number AS product_part_number',
    //             'master_item_brands.name AS product_brand',
    //             'purchase_items.qty AS dpm_qty',
    //             'purchase_items.measure AS dpm_satuan',
    //             'purchase_items.notes AS dpm_notes',
    //             'purchase_items.flag AS dpm_flag',
    //             'po.price_term AS po_price_term',
    //             'po.price_term_location AS po_price_term_location',
    //             'purchase_items.needed_on_date AS dpm_needed',
    //             'purchase_items.last_approved_at AS last_approval',
    //             'departments.name AS department',
    //             'projects.name AS project',
    //             'purchase_items.reason AS alasan_reject_dpm',
    //             'rejected.name AS close_pr_by',
    //             'purchase_requisitions.notes AS alasan_close_pr',
    //             'users.name AS created_by',
    //             'locations.name AS location',
    //             'po.doc_no AS no_po',
    //             'po.status AS status_po',
    //             'po.type AS type_po',
    //             'po.created_at AS tgl_po',
    //             'po.approved AS last_approved_po',
    //             'po.price_term AS price_term_po',
    //             'po.price_term_location AS price_term_location_po',
    //             'po_items.qty AS qty_po',
    //             'suppliers.name AS supplier',
    //             'lpb.doc_no AS no_lpb',
    //             'lpb.publish AS publish_lpb',
    //             'lpb.created_at AS tgl_lpb',
    //             'lpb_items.qty AS qty_lpb',
    //             'lpb_items.id AS lpb_item_id',
    //             'lpb_items.pr_item_id AS lpb_item_pritemid',
    //             'spb.doc_no AS no_spb',
    //             'spb.publish AS publish_spb',
    //             'spb.created_at AS tgl_spb',
    //             'spb.type AS type_spb',
    //             'spb.is_pickup AS is_pickup_spb',
    //             'spb.jalur_pengiriman AS jalur_pengiriman_spb',
    //             'spb_kolis.qty AS qty_spb',
    //             'spb_kolis.id AS spb_item_id',
    //             'spb_kolis.pr_item_id AS spb_item_pritemid',
    //             'bpb.doc_no AS no_bpb',
    //             'bpb.publish AS publish_bpb',
    //             'bpb.created_at AS tgl_bpb',
    //             'bpb_items.qty AS qty_bpb',
    //             'bpb_items.id AS bpb_item_id',
    //             'bpb_items.pr_item_id AS bpb_item_pritemid',
    //             'bpb.received_by AS received_bpb',
    //             'purchase_items.status AS status',
    //             'purchase_items.pr_status AS pr_status',
    //             'purchase_items.po_status AS po_status',
    //             'purchase_items.qty_parsial AS qty_parsial',
    //             'po_items.lpb_status AS po_lpb_status',
    //             'po_items.qty_parsial AS po_qty_parsial',
    //             'po_items.price AS price_item_po',
    //             'po_items.id AS po_item_id',
    //             'po_items.pr_item_id AS po_item_pritemid',
    //             'lpb.spb_status AS spb_status',
    //             'spb.status AS bpb_status',
    //             'spb.jalur_pengiriman AS jalur_spb',
    //             'purchases.type AS typeDpm',
    //             'purchases.status AS statusDpm',
    //             'purchase_requisitions.status AS statusPr',
    //             'companies.name AS companyName',
    //             'areas.name AS areaName'
    //         )
    //         ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
    //         ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
    //         ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
    //         ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
    //         ->leftJoin('purchase_requisitions','purchase_requisitions.id', '=' ,'purchase_items.pr_id')
    //         ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
    //         ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
    //         ->leftJoin('companies','companies.id','=','locations.company_id')
    //         ->leftJoin('areas','areas.id','=','locations.area_id')
    //         ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
    //         ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
    //         ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
    //         ->leftJoin('users as approved', 'users.id', '=', 'purchase_items.last_approved')
    //         ->leftJoin('users as rejected', 'users.id', '=', 'purchase_requisitions.rejected_by')
    //         ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
    //         ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
    //         ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
    //         ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
    //         ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
    //         ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
    //         ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
    //         ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
    //         ->when(!empty($data['company_id']), function ($query) use ($data){
    //             return $query->where('locations.company_id', $data['company_id']);
    //         })
    //         ->when(!empty($data['location_id']), function ($query) use ($data) {
    //             return $query->where('purchases.location_id', $data['location_id']);
    //         })
    //         ->when(!empty($data['department_id']), function ($query) use ($data){
    //             return $query->where('purchases.department_id', $data['department_id']);
    //         })
    //         ->when(!empty($data['project_id']), function ($query) use ($data){
    //             return $query->where('purchases.project_id', $data['project_id']);
    //         })
    //         ->when(!empty($data['start_date']), function ($query) use ($data){
    //             $start = date("Y-m-d",strtotime($data['start_date']));
    //             $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
    //             return $query->whereBetween('purchases.created_at', [$start , $end]);
    //         })
    //         ->orderby('purchase_items.id', 'DESC')
    //         ->get();

    //     if ($query->isEmpty()) {
    //         return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
    //     }else{
    //         $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
    //         $rows_style = (new Style())
    //         ->setFontSize(10)
    //         ->setShouldWrapText(false);
    //         return (new FastExcel($query))->headerStyle($header_style)
    //         ->rowsStyle($rows_style)
    //         ->sheet('NOMOR DPM', function ($sheet) {
    //             $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
    //         })
    //         ->download('Report-DPM-'.date('d-m-Y').'.xlsx', function ($data) {
    //             return [
    //                 'NOMOR DPM'         => $data->no_dpm,
    //                 'TGL PEMBUATAN DPM' => $data->tgl_dpm == NULL ? " " : "'".date('Y/m/d',strtotime($data->tgl_dpm)),
    //                 'TGL PUBLISH DPM'   => $data->tgl_publish_dpm == NULL ? " " : "'".date('Y/m/d',strtotime($data->tgl_publish_dpm)),
    //                 'NO PR'             => $data->no_pr,
    //                 'TGL TERBIT PR'     => $data->tgl_pr == NULL ? " " : "'".date('Y/m/d',strtotime($data->tgl_pr)),
    //                 'PURCHASER'         => $data->purchaser,
    //                 'KODE BARANG'       => $data->product_code,
    //                 'NAMA BARANG'       => $data->product_name,
    //                 'PN/SPEC'           => $data->product_part_number,
    //                 'MERK'              => $data->product_brand,
    //                 'DPM QTY'           => $data->dpm_qty ? (int) $data->dpm_qty : '',
    //                 'SATUAN'            => $data->dpm_satuan,
    //                 'CATATAN'           => strip_tags($data->dpm_notes),
    //                 'FLAG'              => $data->dpm_flag == 0 ? "Normal" : "Urgent",
    //                 'FRANCO'            => $data->po_price_term.' '.$data->po_price_term_location,
    //                 'TGL DIBUTUHKAN'    => $data->dpm_needed == NULL ? " " : "'".date('Y/m/d',strtotime($data->dpm_needed)),
    //                 'DEPARTMENT'        => $data->department,
    //                 'PROJECT'           => $data->project,
    //                 'STATUS'            => getStatusItemByQty($data->typeDpm, $data->status, $data->statusDpm, $data->pr_status, $data->po_status, $data->statusPr, getTypePoByPurchaseItem($data->id)->type ?? null, getQtyAllPoItemByPurchaseItem($data->id), getQtyAllLpbItemByPurchaseItem($data->id), getQtyAllSpbItemByPurchaseItem($data->id), getQtyAllBpbItemByPurchaseItem($data->id), $data->qty, (($data->qty - getQtyItemPoByPrItemId($data->id)) == $data->qty ? 0 : $data->qty - getQtyItemPoByPrItemId($data->id)) ?? 0,'raw'),
    //                 'CREATED BY'        => $data->created_by,
    //                 'AREA'              => $data->areaName,
    //                 'LOKASI'            => $data->location,
    //                 'PO'                => $data->no_po,
    //                 'FRANCO PO'         => $data->price_term_po.' '.$data->price_term_location_po,
    //                 'TYPE PO'           => $data->type_po,
    //                 'TANGGAL BUAT PO'   => $data->tgl_po == NULL ? " " : "'".date('Y/m/d',strtotime($data->tgl_po)),
    //                 'TANGGAL ISSUED PO' => ( $data->last_approved_po == NULL && $data->status_po != 2 && $data->status_po != 4 && $data->status_po != 5 ) ? " " : "'" . date('Y/m/d', strtotime($data->last_approved_po)),
    //                 'QTY PO'            => $data->qty_po ? (int) $data->qty_po : '',
    //                 'HARGA SATUAN'      => $data->price_item_po ? (float)$data->price_item_po : '',
    //                 'TOTAL HARGA ITEM'  => (float) ($data->price_item_po * $data->qty_po),
    //                 'SUPPLIER PO'       => $data->supplier,
    //                 'LPB'               => $data->no_lpb,
    //                 'TGL PUBLISH LPB'   => $data->publish_lpb == NULL ? " " : "'".date('Y/m/d',strtotime($data->publish_lpb)),
    //                 'QTY LPB'           => $data->qty_lpb ? (int) $data->qty_lpb : '',
    //                 'SPB'               => $data->no_spb,
    //                 'JALUR SPB'         => $data->jalur_spb,
    //                 'TGL PUBLISH SPB'   => $data->publish_spb == NULL ? " " : "'".date('Y/m/d',strtotime($data->publish_spb)),
    //                 'QTY SPB'           => $data->qty_spb ? (int) $data->qty_spb : '',
    //                 'BPB'               => $data->no_bpb,
    //                 'TGL PUBLISH BPB'   => $data->publish_bpb == NULL ? " " : "'".date('Y/m/d',strtotime($data->publish_bpb)),
    //                 'QTY BPB'           => $data->qty_bpb ? (int) $data->qty_bpb : '',
    //                 'END USER'          => $data->received_bpb,
    //                 // 'DPM_ITEM_ID'           => $data->id ,
    //                 // 'PR_ITEM_ID'            => $data->pr_id ? $data->id : null,
    //                 // 'PO_ITEM_ID'            => $data->po_item_id ,
    //                 // 'PO_ITEM_PRITEMID'      => $data->po_item_pritemid ,
    //                 // 'LPB_ITEM_ID'           => $data->lpb_item_id ,
    //                 // 'LPB_ITEM_ID_PRITEMID'  => $data->lpb_item_pritemid ,
    //                 // 'SPB_ITEM_ID'           => $data->spb_item_id ,
    //                 // 'SPB_ITEM_ID_PRITEMID'  => $data->spb_item_pritemid ,
    //                 // 'BPB_ITEM_ID'           => $data->bpb_item_id ,
    //                 // 'BPB_ITEM_ID_PRITEMID'  => $data->bpb_item_pritemid ,
    //             ];
    //         });
    //     }
    // }

    public function export_new(Request $request)
    {
        $data = $request->all();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
        }

        try {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
            $end   = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();
            $diff  = $start->diffInDays($end);

            if ($diff > 31) {
                return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . $diff . ' hari)');
            }
            if ($start->gt($end)) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
        }

        $query = DB::table('purchase_items')
            ->distinct('purchase_items.id')
            ->select(
                // --- kolom asli (tidak berubah) ---
                'purchase_items.*',
                'purchases.doc_no AS no_dpm',
                'purchases.created_at AS tgl_dpm',
                'purchases.publish AS tgl_publish_dpm',
                'purchase_requisitions.doc_no AS no_pr',
                'purchase_requisitions.created_at AS tgl_pr',
                'purchaser.name AS purchaser',
                'master_item_products.code AS product_code',
                'master_item_products.name AS product_name',
                'master_item_products.part_number AS product_part_number',
                'master_item_brands.name AS product_brand',
                'purchase_items.qty AS dpm_qty',
                'purchase_items.measure AS dpm_satuan',
                'purchase_items.notes AS dpm_notes',
                'purchase_items.flag AS dpm_flag',
                'po.price_term AS po_price_term',
                'po.price_term_location AS po_price_term_location',
                'purchase_items.needed_on_date AS dpm_needed',
                'purchase_items.last_approved_at AS last_approval',
                'departments.name AS department',
                'projects.name AS project',
                'purchase_items.reason AS alasan_reject_dpm',
                'rejected.name AS close_pr_by',
                'purchase_requisitions.notes AS alasan_close_pr',
                'users.name AS created_by',
                'locations.name AS location',
                'po.doc_no AS no_po',
                'po.status AS status_po',
                'po.type AS type_po',
                'po.created_at AS tgl_po',
                'po.approved AS last_approved_po',
                'po.price_term AS price_term_po',
                'po.price_term_location AS price_term_location_po',
                'po_items.qty AS qty_po',
                'suppliers.name AS supplier',
                'lpb.doc_no AS no_lpb',
                'lpb.publish AS publish_lpb',
                'lpb.created_at AS tgl_lpb',
                'lpb_items.qty AS qty_lpb',
                'lpb_items.id AS lpb_item_id',
                'lpb_items.pr_item_id AS lpb_item_pritemid',
                'spb.doc_no AS no_spb',
                'spb.publish AS publish_spb',
                'spb.created_at AS tgl_spb',
                'spb.type AS type_spb',
                'spb.is_pickup AS is_pickup_spb',
                'spb.jalur_pengiriman AS jalur_pengiriman_spb',
                'spb_kolis.qty AS qty_spb',
                'spb_kolis.id AS spb_item_id',
                'spb_kolis.pr_item_id AS spb_item_pritemid',
                'bpb.doc_no AS no_bpb',
                'bpb.publish AS publish_bpb',
                'bpb.created_at AS tgl_bpb',
                'bpb_items.qty AS qty_bpb',
                'bpb_items.id AS bpb_item_id',
                'bpb_items.pr_item_id AS bpb_item_pritemid',
                'bpb.received_by AS received_bpb',
                'purchase_items.status AS status',
                'purchase_items.pr_status AS pr_status',
                'purchase_items.po_status AS po_status',
                'purchase_items.qty_parsial AS qty_parsial',
                'po_items.lpb_status AS po_lpb_status',
                'po_items.qty_parsial AS po_qty_parsial',
                'po_items.price AS price_item_po',
                'po_items.id AS po_item_id',
                'po_items.pr_item_id AS po_item_pritemid',
                'lpb.spb_status AS spb_status',
                'spb.status AS bpb_status',
                'spb.jalur_pengiriman AS jalur_spb',
                'purchases.type AS typeDpm',
                'purchases.status AS statusDpm',
                'purchase_requisitions.status AS statusPr',
                'companies.name AS companyName',
                'areas.name AS areaName',

                // -------------------------------------------------------
                // FIX N+1: 6 subquery menggantikan 6 helper function
                // Pola identik dengan yang ada di datatables()
                // -------------------------------------------------------

                // getTypePoByPurchaseItem($id)->type
                DB::raw('(
                    SELECT po.type
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (1,2,3,4,5,9,10)
                    ORDER BY po.id DESC
                    LIMIT 1
                ) AS sq_type_po'),

                // getQtyAllPoItemByPurchaseItem($id)
                DB::raw('(
                    SELECT COALESCE(SUM(po_items.qty), 0)
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (1,2,3,4,5,9,10)
                ) AS sq_qty_po'),

                // getQtyAllLpbItemByPurchaseItem($id)
                DB::raw('(
                    SELECT COALESCE(SUM(lpb_items.qty), 0)
                    FROM lpb_items
                    INNER JOIN lpb ON lpb.id = lpb_items.lpb_id
                    WHERE lpb_items.pr_item_id = purchase_items.id
                    AND lpb.status IN (1,2)
                ) AS sq_qty_lpb'),

                // getQtyAllSpbItemByPurchaseItem($id)
                DB::raw('(
                    SELECT COALESCE(SUM(spb_kolis.qty), 0)
                    FROM spb_kolis
                    INNER JOIN spb ON spb.id = spb_kolis.spb_id
                    WHERE spb_kolis.pr_item_id = purchase_items.id
                    AND spb.status IN (1,2,3)
                ) AS sq_qty_spb'),

                // getQtyAllBpbItemByPurchaseItem($id)
                DB::raw('(
                    SELECT COALESCE(SUM(bpb_items.qty), 0)
                    FROM bpb_items
                    INNER JOIN bpb ON bpb.id = bpb_items.bpb_id
                    WHERE bpb_items.pr_item_id = purchase_items.id
                    AND bpb.status IN (1,2)
                ) AS sq_qty_bpb'),

                // getQtyItemPoByPrItemId($id)
                DB::raw('(
                    SELECT COALESCE(SUM(po_items.qty), 0)
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (0,1,2,3,4,5,9,10)
                ) AS sq_qty_po_approved')
            )
            ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
            ->leftJoin('areas', 'areas.id', '=', 'locations.area_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->leftJoin('users as approved', 'users.id', '=', 'purchase_items.last_approved')
            ->leftJoin('users as rejected', 'users.id', '=', 'purchase_requisitions.rejected_by')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
            ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
            ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
            ->when(!empty($data['company_id']), function ($q) use ($data) {
                return $q->where('locations.company_id', $data['company_id']);
            })
            ->when(!empty($data['location_id']), function ($q) use ($data) {
                return $q->where('purchases.location_id', $data['location_id']);
            })
            ->when(!empty($data['department_id']), function ($q) use ($data) {
                return $q->where('purchases.department_id', $data['department_id']);
            })
            ->when(!empty($data['project_id']), function ($q) use ($data) {
                return $q->where('purchases.project_id', $data['project_id']);
            })
            ->when(!empty($data['start_date']), function ($q) use ($data) {
                $start = date('Y-m-d', strtotime($data['start_date']));
                $end   = date('Y-m-d', strtotime($data['end_date'] . '+1 day'));
                return $q->whereBetween('purchases.created_at', [$start, $end]);
            })
            ->orderBy('purchase_items.id', 'DESC')
            ->get();

        if ($query->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }

        $header_style = (new Style())->setFontBold()->setBackgroundColor('F2F2F2');
        $rows_style   = (new Style())->setFontSize(10)->setShouldWrapText(false);

        return (new FastExcel($query))
            ->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('NOMOR DPM', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-DPM-' . date('d-m-Y') . '.xlsx', function ($row) {
                // Hitung qty_parsial — identik dengan pola di datatables()
                $qtyParsial = ($row->qty - $row->sq_qty_po_approved) > 0
                    ? ($row->qty - $row->sq_qty_po_approved)
                    : 0;

                return [
                    'NOMOR DPM'         => $row->no_dpm,
                    'TGL PEMBUATAN DPM' => $row->tgl_dpm         ? "'" . date('Y/m/d', strtotime($row->tgl_dpm))         : ' ',
                    'TGL PUBLISH DPM'   => $row->tgl_publish_dpm ? "'" . date('Y/m/d', strtotime($row->tgl_publish_dpm)) : ' ',
                    'NO PR'             => $row->no_pr,
                    'TGL TERBIT PR'     => $row->tgl_pr           ? "'" . date('Y/m/d', strtotime($row->tgl_pr))          : ' ',
                    'PURCHASER'         => $row->purchaser,
                    'KODE BARANG'       => $row->product_code,
                    'NAMA BARANG'       => $row->product_name,
                    'PN/SPEC'           => $row->product_part_number,
                    'MERK'              => $row->product_brand,
                    'DPM QTY'           => $row->dpm_qty   ? (int) $row->dpm_qty   : '',
                    'SATUAN'            => $row->dpm_satuan,
                    'CATATAN'           => strip_tags($row->dpm_notes),
                    'FLAG'              => $row->dpm_flag == 0 ? 'Normal' : 'Urgent',
                    'FRANCO'            => $row->po_price_term . ' ' . $row->po_price_term_location,
                    'TGL DIBUTUHKAN'    => $row->dpm_needed ? "'" . date('Y/m/d', strtotime($row->dpm_needed)) : ' ',
                    'DEPARTMENT'        => $row->department,
                    'PROJECT'           => $row->project,

                    // -------------------------------------------------------
                    // FIX N+1: semua argumen diambil dari kolom subquery,
                    // tidak ada query tambahan per baris
                    // -------------------------------------------------------
                    'STATUS'            => getStatusItemByQty(
                        $row->typeDpm,
                        $row->status,
                        $row->statusDpm,
                        $row->pr_status,
                        $row->po_status,
                        $row->statusPr,
                        $row->sq_type_po,       // getTypePoByPurchaseItem($id)->type
                        $row->sq_qty_po,        // getQtyAllPoItemByPurchaseItem($id)
                        $row->sq_qty_lpb,       // getQtyAllLpbItemByPurchaseItem($id)
                        $row->sq_qty_spb,       // getQtyAllSpbItemByPurchaseItem($id)
                        $row->sq_qty_bpb,       // getQtyAllBpbItemByPurchaseItem($id)
                        $row->qty,
                        $qtyParsial,            // getQtyItemPoByPrItemId($id) → dihitung dari sq_qty_po_approved
                        'raw'
                    ),

                    'CREATED BY'        => $row->created_by,
                    'AREA'              => $row->areaName,
                    'LOKASI'            => $row->location,
                    'PO'                => $row->no_po,
                    'FRANCO PO'         => $row->price_term_po . ' ' . $row->price_term_location_po,
                    'TYPE PO'           => $row->type_po,
                    'TANGGAL BUAT PO'   => $row->tgl_po        ? "'" . date('Y/m/d', strtotime($row->tgl_po))        : ' ',
                    'TANGGAL ISSUED PO' => ($row->last_approved_po === null && !in_array($row->status_po, [2, 4, 5]))
                                            ? ' '
                                            : "'" . date('Y/m/d', strtotime($row->last_approved_po)),
                    'QTY PO'            => $row->qty_po        ? (int)   $row->qty_po        : '',
                    'HARGA SATUAN'      => $row->price_item_po ? (float) $row->price_item_po : '',
                    'TOTAL HARGA ITEM'  => (float) ($row->price_item_po * $row->qty_po),
                    'SUPPLIER PO'       => $row->supplier,
                    'LPB'               => $row->no_lpb,
                    'TGL PUBLISH LPB'   => $row->publish_lpb   ? "'" . date('Y/m/d', strtotime($row->publish_lpb))   : ' ',
                    'QTY LPB'           => $row->qty_lpb       ? (int) $row->qty_lpb         : '',
                    'SPB'               => $row->no_spb,
                    'JALUR SPB'         => $row->jalur_spb,
                    'TGL PUBLISH SPB'   => $row->publish_spb   ? "'" . date('Y/m/d', strtotime($row->publish_spb))   : ' ',
                    'QTY SPB'           => $row->qty_spb       ? (int) $row->qty_spb         : '',
                    'BPB'               => $row->no_bpb,
                    'TGL PUBLISH BPB'   => $row->publish_bpb   ? "'" . date('Y/m/d', strtotime($row->publish_bpb))   : ' ',
                    'QTY BPB'           => $row->qty_bpb       ? (int) $row->qty_bpb         : '',
                    'END USER'          => $row->received_bpb,
                ];
            });
    }

    public function export_sla(Request $request){
        $data = $request->all();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
        }
        try {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();
                    $diff = $start->diffInDays($end);
            if ($diff > 31) {
                return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . $diff . ' hari)');
            }
                    if ($start->gt($end)) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
        }

        $query = DB::table('purchase_items')
            ->distinct('purchase_items.id')
            ->select('purchase_items.*',
                'purchase_requisitions.doc_no AS no_pr',
                'purchase_requisitions.created_at AS tgl_pr',
                'purchase_requisitions.type AS type_pr',
                'master_item_products.code AS product_code',
                'master_item_products.name AS product_name',
                'master_item_products.part_number AS product_part_number',
                'master_item_brands.name AS product_brand',
                'purchase_items.qty AS dpm_qty',
                'purchase_items.measure AS dpm_satuan',
                'purchaser.name AS purchaser',
                'departments.name AS department',
                'projects.name AS project',
                'po.doc_no AS no_po',
                'po.status AS statusPo',
                'po.created_at AS tgl_po',
                'po_items.qty AS qty_po',
                'purchase_items.status AS status',
                'purchase_items.pr_status AS pr_status',
                'purchase_items.po_status AS po_status',
                'purchase_items.qty_parsial AS qty_parsial',
                'purchases.type AS typeDpm',
                'purchase_requisitions.status AS statusPr',
                'locations.name AS locationnn',
                'companies.name AS companyyy',
                'po.approved AS last_approved_po'

            )
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id', '=' ,'purchase_items.pr_id')
            ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->leftJoin('companies','companies.id','=','locations.company_id')
            ->when(!empty($data['company_id']), function ($query) use ($data){
                return $query->where('locations.company_id', $data['company_id']);
            })
            ->when(!empty($data['location_id']), function ($query) use ($data) {
                return $query->where('purchases.location_id', $data['location_id']);
            })
            ->when(!empty($data['department_id']), function ($query) use ($data){
                return $query->where('purchases.department_id', $data['department_id']);
            })
            ->when(!empty($data['project_id']), function ($query) use ($data){
                return $query->where('purchases.project_id', $data['project_id']);
            })
            ->when(!empty($data['start_date']), function ($query) use ($data){
                $start = date("Y-m-d",strtotime($data['start_date']));
                $end   = date("Y-m-d",strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('purchase_requisitions.created_at', [$start , $end]);
            })
            ->where('purchase_requisitions.type','=','po')
            ->orderby('purchase_items.id', 'DESC')
            ->get();

        if ($query->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }else{
            return (new FastExcel($query))
            ->download('Report-SLA-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'IDMASTERITEMPR'    => $data->id,
                    'COMPANY'           => $data->companyyy,
                    'PROJECT'           => $data->project,
                    'DEPARTMENT'        => $data->department,
                    'KODE BARANG'       => $data->product_code,
                    'NAMA BARANG'       => $data->product_name,
                    'PN/SPEC'           => $data->product_part_number,
                    'MERK'              => $data->product_brand,
                    'QTY PR'            => $data->dpm_qty ? (int) $data->dpm_qty : '',
                    'QTY PO'            => $data->qty_po ? (int) $data->qty_po : '',
                    'SATUAN'            => $data->dpm_satuan,
                    'PURCHASER'         => $data->purchaser,
                    'NOMOR PR'          => $data->no_pr,
                    'NOMOR PO'          => $data->no_po,
                    'TGL PR'            => $data->tgl_pr == NULL ? " " : date('Y/m/d',strtotime($data->tgl_pr)),
                    'TGL PO'            => $data->tgl_po == NULL ? " " : date('Y/m/d',strtotime($data->tgl_po)),
                    'STATUS PR'         => getStatusPR($data->statusPr,'raw'),
                    'STATUS PO'         => $data->statusPo ? getStatusPO($data->statusPo,'raw') : "'-",
                    'STATUS ITEM PR'    => getStatusItemPR($data->pr_status, $data->po_status, $data->qty_parsial,$data->type_pr,'raw'),
                    'DURASI PR-PO CREATED [HARI]' => $data->tgl_pr && $data->tgl_po
                        ? \Carbon\Carbon::parse($data->tgl_pr)->diffInDays($data->tgl_po)
                        : '',
                    'DURASI PR-PO ISSUED [HARI]' => $data->tgl_pr && $data->last_approved_po
                        ? \Carbon\Carbon::parse($data->tgl_pr)->diffInDays($data->last_approved_po)
                        : '',
                    'DURASI PR-PO CREATED [Lengkap]' => $data->tgl_pr && $data->tgl_po
                            ? formatDurasiLengkap($data->tgl_pr,$data->tgl_po)
                            : '',
                    'DURASI PR-PO ISSUED [Lengkap]' => $data->tgl_pr && $data->last_approved_po
                            ? formatDurasiLengkap($data->tgl_pr,$data->last_approved_po)
                            : '',
                    'DURASI PENDING PR-SEKARANG [17 Juli 2025]' => $data->tgl_pr &&  !$data->tgl_po
                            ? formatDurasiLengkap($data->tgl_pr, now())
                            : ''
                ];
            });
        }
    }

    public function export_historical(Request $request)
    {
        $date = date('Y-m-d');
        
        // 1. Ambil data dari request agar variabel $data terdefinisi
        $data = $request->all();

        // 2. Cek apakah field tanggal kosong
        if (empty($data['start_date']) || empty($data['end_date'])) {
            return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
        }

        try {
            // 3. Konversi format tanggal
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();

            // 4. Validasi logika: tanggal mulai tidak boleh lebih besar dari tanggal selesai
            if ($start->gt($end)) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
            }

            // 5. Hitung selisih hari
            $diff = $start->diffInDays($end);
            if ($diff > 31) {
                return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . ($diff + 1) . ' hari)');
            }

        } catch (\Exception $e) {
            // Menangani jika format tanggal yang diinput user bukan MM/DD/YYYY
            return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
        }

        // 6. Proses Export
        return Excel::download(
            new ApprovalHistoricalExport(
                $request->get('company_id'), 
                $request->get('location_id'), 
                $request->get('department_id'), 
                $request->get('project_id'), 
                $request->get('start_date'), 
                $request->get('end_date')
            ), 
            'Report-Historical-Approval-DPM-'.$date.'.xlsx'
        );
    }

    public function getJs($id)
    {
        $id = $id;
        return view('logistic.dpm.js', compact('id'));
    }



    public function exportItem(Request $request)
    {

        $data = $request->all();

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return redirect()->back()->with('error', 'Tanggal mulai dan selesai harus diisi.');
        }
        try {
            $start = \Carbon\Carbon::createFromFormat('m/d/Y', $data['start_date'])->startOfDay();
            $end = \Carbon\Carbon::createFromFormat('m/d/Y', $data['end_date'])->endOfDay();
                    $diff = $start->diffInDays($end);
            if ($diff > 31) {
                return redirect()->back()->with('error', 'Rentang waktu tidak boleh lebih dari 31 hari. (Terpilih: ' . $diff . ' hari)');
            }
                    if ($start->gt($end)) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh melebihi tanggal selesai.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Format tanggal salah. Gunakan format MM/DD/YYYY.');
        }

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

    public function publish_approval($idr, Request $request)
    {
        $id = Hashids::decode($idr);
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $pr  = PurchaseRequest::findOrFail($id['0']);

        $approval_ = getApprovalLogistic($pr->location_id, 1);

        // API WA APPROVAL
        $user_db_ = DB::table('users')->where('id',$approval_->user_id)->first();
        $pr_items_ = DB::table('purchase_items')
        ->select('purchase_items.*',
                'master_item_brands.name AS productBrand',
                'master_items.name AS item',
                'master_item_products.name as product',
                'master_item_products.name AS product',
                'master_item_products.code AS productCode',
                'master_item_products.part_number AS productPartNumber',
                'measures.name AS measure'
            )
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
            ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->where('purchase_items.purchase_id','=',$pr->id)
            ->get();

            $body = "```Dear Pak ".getUserByID($user_db_->id).",```\n\n```Mohon Lakukan Approval DPM Berikut:```\n";
            $body .= "```".$pr->doc_no."```\n";
            $body .= "```Tipe : ".strtoupper($pr->type)."```\n\n";
            $body .= "```Daftar Item :```\n";

            foreach($pr_items_ as $a){
                $body .= "- ```".$a->product;
                $body .= " [".$a->qty;
                $body .= " ".$a->measure."]```\n";
            }
            $body .= "\n> ```Link Approval Via Web:``` _https://erp.haritashipping.com/approval/purchase_";
            $bodyS = $body;
            if($user_db_->is_whatsapp === true && $user_db_->telp != null && $pr->type != 'petty_cash'){
                sendWhatsapp($user_db_->telp, $bodyS);
            }

        $data['status']     = 1;
        $data['publish']    = date('Y-m-d H:i:s');
        $pr->update($data);

        return redirect()->route('purchase_request.index')->with(
            'success',
            'Publish Data <a href="' . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($pr->id)]) . '" target="_blank" title="Show DPM">' . $pr->doc_no . '</a> Berhasil!'
        );
    }

    public function updatenewdraft(Request $request, $id)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $pr = PurchaseRequest::findOrFail($id);
        $approval = getApprovalLogistic($request->get('location_id'), 1);

        if($approval){

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
                        'status'        => 1,
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
                $status[]   = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN 1";
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

            return redirect()->route('purchase_request.show', Hashids::encode($pr->id))->with(['success' => 'Edit Data Berhasil!']);
        }else{
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Approval Rule tidak ditemukan']);
        }
    }

    public function getDpmRejectById($id){
        $id_ = Hashids::decode($id);
        $result = DB::table('purchases')
        ->select('*')
        ->where('id','=',$id_)
        ->first();
        return response()->json($result);
    }

    public function reject(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }
        $id = $request->get('dpm_id');
        $pr  = PurchaseRequest::findOrFail($id);
        $data['status'] = 2;
        // $pr->update($data);

        if($pr->request_type == 1){
            DB::table('inventory_transfer_in')
                ->where('id', '=', $pr->request_type_id)
                ->update(['type_status' => 0]);
        }
        $pr->PurchaseRequestItem()->update(['status' => 2 , 'reason' => $request->get('alasan_reject')?? ' -']);

        $item = DB::table('purchase_items')->where('purchase_id','=',$pr->id)->get();
        $dataPRNotes = [];
        foreach($item as $a){
            $dataPRNotes[] = [
                    'user_id'       => Auth::user()->id,
                    'notes'         => $request->get('alasan_reject'),
                    'pr_item_id'    => $a->id,
                    'created_at'    => date('Y-m-d H:i:s')
                ];
        }
        $purchase_notes = PurchaseRequestNotes::insert($dataPRNotes);

        return redirect()->route('purchase_request.index')->with(['success' => 'Reject '.$pr->doc_no.' Berhasil!']);
    }

    public function list_wti(){
        $data = DB::table('inventory_transfer_in')
            ->select(
                'inventory_transfer_in.*',
                'users.name AS created',
                'inventory_transfer_out.doc_no AS doc_no_wto',
                'inventory_transfer_out.location_id AS location_id_wto',
                'inventory_transfer_out.created_at AS created_at_wto'
            )
            ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_in.location_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_in.created_by')
            ->leftJoin('inventory_transfer_out','inventory_transfer_out.id','=','inventory_transfer_in.transfer_out_id')

            // 🔥 Tambahkan join ke inventory_transfer_in_items
            ->join('inventory_transfer_in_items', 'inventory_transfer_in_items.inventory_transfer_id', '=', 'inventory_transfer_in.id')

            // 🔥 Filter hanya item dengan type_replacement = 1
            ->where('inventory_transfer_in_items.type_replacement', '=', 1)

            ->where('inventory_transfer_out.status', '=', 5)
            ->where('inventory_transfer_in.type', '=', 1)
            ->where('inventory_transfer_in.status', '=', 1)
            ->where('inventory_transfer_in.type_status', '=', 0)
            ->orderBy('inventory_transfer_in.id', 'desc')
            ->distinct() // supaya tidak duplikat jika ada banyak item
        ->get();
        return view('logistic.dpm.create_from_wti', compact('data'));
    }

    public function create_from_wti(Request $request){
        $locationAsal = $request->get('location_asal');
        $locationTujuan = $request->get('location_tujuan');
        $idWti = $request->get('wti_id');
        $dataLokasiTujuan = getLocationByID($locationTujuan);
        $dataCompanyTujuan = getCompanyByLocationId($locationTujuan);

        $dataWti = DB::table('inventory_transfer_in')
            ->select(
                'inventory_transfer_in.*',
                'users.name AS created',
                'inventory_transfer_out.doc_no AS doc_no_wto',
                'inventory_transfer_out.location_id AS location_id_wto',
                'inventory_transfer_out.created_at AS created_at_wto'
            )
            ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_in.location_id')
            ->leftJoin('users', 'users.id', '=', 'inventory_transfer_in.created_by')
            ->leftJoin('inventory_transfer_out','inventory_transfer_out.id','=','inventory_transfer_in.transfer_out_id')
            ->where('inventory_transfer_in.id','=',$idWti)
            ->first();

        $dataWtiItem = DB::table('inventory_transfer_in_items')
            ->select(
                'inventory_transfer_in_items.*',
                'inventories.stock_onhand AS qty_soh',
                'measures.name AS satuan',
                'master_item_products.name AS produkName',
                'master_item_products.part_number AS ProductPn',
                'master_item_products.id AS productId',
                'master_item_products.code AS productCode',
                'master_item_products.conversion AS productConversion',
                'master_item_brands.name AS productBrand',
                'satuanPembelian.name AS satuanPembelianName'
            )
            ->leftJoin('inventory_transfer_out_items','inventory_transfer_out_items.id','=','inventory_transfer_in_items.inventory_transfer_out_item_id')
            ->leftJoin('inventories','inventories.id','=','inventory_transfer_out_items.inventory_id')
            ->leftJoin('measures','measures.id','=','inventories.measure_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventories.product_id')
            ->leftJoin('measures AS satuanPembelian','satuanPembelian.id','=','master_item_products.measure_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->where('inventory_transfer_in_items.inventory_transfer_id','=',$idWti)
            ->where('inventory_transfer_in_items.type_replacement','=',1)
            ->get();

        $department = DB::table('departments')
            ->leftJoin('companies','companies.id','=','departments.company_id')
            ->select(DB::raw("CONCAT(companies.code, ' - ', departments.name) as department_name"), 'departments.id AS id')
            ->where('departments.company_id', $dataCompanyTujuan->id)
            ->where('departments.status',1)
            ->where('departments.isdpm',1)
            ->orderBy('departments.name','ASC')
            ->get()
            ->pluck('department_name', 'id')
            ->prepend('Silahkan pilih...', '');
        $selectedProject = Project::whereNull('deleted_at')
            ->where('code', '=', 'RPL')
            ->orderBy('name', 'ASC')
            ->first(); // ambil 1 data

        $project = Project::whereNull('deleted_at')
            ->orderBy('name', 'ASC')
            ->get()
            ->pluck('name', 'id')
            ->prepend('Silahkan pilih...', '');
        return view('logistic.dpm.create_from_wti2', compact('dataLokasiTujuan','dataCompanyTujuan','dataWti','dataWtiItem','selectedProject','department','project','locationAsal','locationTujuan'));
    }

    public function store_from_wti(Request $request){

        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $location  = Workarea::where("id", $request->get('location_tujuan'))->first();

        if(isAdministrator() || isAdmin() ){
            $company   = Company::where('id', $location->company_id)->first()->alias;
        }else{
            $company   = Company::where('id', Auth::user()->company_id)->first()->alias;
        }
        $approval = getApprovalLogistic($location->id, 1);

        if($approval){

            $increment = DB::table('purchases')
            ->whereYear("created_at", date('Y'))
            ->where('location_id','=',$location->id)
            ->where('status','!=', 0)
            ->count();

            $num = sprintf("%'.05d", $increment + 1) ;
            $no_dpm = "DPM-".$company."-".$location->alias."-".date('my')."-".$num;

            $data['status']    = 11;
            $dataHistory['jenis']   = 'insert';

            if ($request->hasFile('mr_file')) {
                $file = $request->file('mr_file');
                $name = 'DPM-'.time();
                $folder = '/uploads/dpm/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $data['mr_file'] = $filePath;
            }

            $data['location_id'] = $request->get('location_tujuan');
            $data['department_id'] = $request->get('department_id');
            $data['project_id'] = $request->get('project_id');
            $data['description'] = $request->get('description');
            $data['uuid']  = Str::uuid();
            $data['doc_no'] = $no_dpm;
            $data['created_by'] = Auth::user()->id;
            $data['type'] = $request->get('type');
            $data['request_type'] = 1;
            $data['request_type_id'] = $request->get('wti_id');

            DB::beginTransaction();

            try {

                $purchase = PurchaseRequest::create($data);

                DB::table('inventory_transfer_in')
                    ->where('id', '=', $request->get('wti_id'))
                    ->update(['type_status' => 1]);

                $dataPR = [];
                $itemList = $request->get('product_id');
                for($i=0;$i < count($itemList);$i++) {
                    $dataPR[] = [
                        'purchase_id'           => $purchase->id,
                        'product_id'            => $request->get('product_id')[$i],
                        'flag'                  => $request->get('flag'),
                        'needed_on_date'        => $request->get('needed_on_date'),
                        'notes'                 => $request->get('notes')[$i],
                        'qty'                   => $request->get('qty')[$i],
                        'measure'               => $request->get('measure')[$i],
                        'position'              => $approval->user_id,
                        'status'                => $request->get('status'),
                        'step'                  => 1,
                        'request_type_item'     => 1,
                        'request_type_item_id'  => $request->get('wti_item_id')[$i],
                        'return_location'       => $request->get('lokasi_wto')
                    ];
                }

                $purchase_items = PurchaseRequestItem::insert($dataPR);


                $dataHistory['purchase_id'] = $purchase->id;
                $dataHistory['user_id']     =  Auth::user()->id;
                PurchaseRequestHistory::create($dataHistory);

                $dataNotification['title']      = "Approval DPM";
                $dataNotification['link']       = "/approval/purchase_set/".Hashids::encode($purchase->id);
                $dataNotification['data_id']    = $purchase->id;
                $dataNotification['content']    = "Terdapat pengajuan DPM Penggantian dengan nomor: ". $no_dpm;
                $dataNotification['user_id']    = $approval->user_id;
                $notifications = Notification::create($dataNotification);

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
}
