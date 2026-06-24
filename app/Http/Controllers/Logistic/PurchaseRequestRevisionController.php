<?php

namespace App\Http\Controllers\Logistic;

use App\Exports\ApprovalHistoricalExport;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\MasterItem;
use App\Models\Notification;
use App\Models\Project;

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
use Auth;
use Storage;
use PDF;

class PurchaseRequestRevisionController extends Controller
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
        return view('logistic.dpm_revision.index');
    }

    public function datatables()
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $user = Auth::user()->id;

        $result = DB::table('purchases')
        ->select(
            'purchases.*',
            'users.name AS created',
            'departments.name AS department',
            'projects.name AS project'
        )
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->whereIn('purchases.status',array(3,6))
        ->when(!isAdministrator(), function ($result) use ($user) {
            return $result->where('purchases.created_by', $user);
        })
        ->orderBy('purchases.updated_at', 'DESC');
       
        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            if($result->status == 6) $action = "<a href='".route('purchase_revision.edit', Hashids::encode($result->id))."' title='Perbaiki' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'> </a>";
            else $action = "<a href='".route('purchase_request.edit', Hashids::encode($result->id))."' title='Perbaiki' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'> </a>";

            return '<div class="btn-group">'.$action.'</div>';

        })
        ->editColumn('doc_no', function ($result) {
            return $result->doc_no;
        })
        ->addColumn('status', function ($result) {
            if($result->status==3) return "<span class='badge badge-danger'> Hold </span>";
            else return "<span class='badge badge-warning'> Reject PR </span>";
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s' ) : '';
        })
        ->rawColumns(['action', 'status', 'doc_no'])
        ->make(true);
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
        $pr = PurchaseRequest::findOrFail($id['0']);    
        $project   = Project::findOrFail($pr->project_id);
        $category = implode(',',$project->category);
      
        $pr_items   = PurchaseRequest::getProductItemRevisi($id['0']);
        $pr_history = PurchaseRequestHistory::where('purchase_id',$pr->id)->where('jenis','hold')->latest()->first();
        $statusPR = 'full';
        if(count($pr->PurchaseRequestItem) != count($pr_items)) $statusPR = 'parsial';

        return view('logistic.dpm_revision.edit', compact('pr','pr_items','flag','pr_history','type','category','statusPR'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function publish(Request $request)
    {
        if (! Gate::allows('dpm')) {
            return abort(401);
        }

        $pr = PurchaseRequest::findOrFail($request->get('pr_id'));
        $purchase_requisitions = PurchaseRequisition::where('purchase_id',$pr->id)->first();

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
        $data['status'] = 4;
        $data['description'] = $request->get('description');
        
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
                    'status' => 4,
                    'pr_status' => 1,
                    'pr_id' => $purchase_requisitions->id,
                    'step'          => 1
                ];
            }

            PurchaseRequestItem::insert($dataPR);
        }

        $dpm_ids = $product_id = $flag = $needed_on_date = $notes  = $measure  = $qty  = $position = [];

        $itemListOld = $request->get('product_id');

        for($i=0;$i < count($itemListOld);$i++) {
            $dpm_ids[]    = $request->get('dpm_item_id')[$i];
            $product_id[] = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $request->get('product_id')[$i];
            $flag[]       = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '". $request->get('flag')[$i]."'";
            $needed_on_date[] = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN DATE('". $request->get('needed_on_date')[$i]."')";
            $notes[]    = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '".$request->get('notes')[$i]."'";
            $qty[]      = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN ". $request->get('qty')[$i];
            $measure[]  = "WHEN id = {$request->get('dpm_item_id')[$i]} THEN '". $request->get('measure')[$i]."'";
        }

        $dpm_ids        = implode(',', $dpm_ids);
        $product_id     = implode(' ', $product_id);
        $flag           = implode(' ', $flag);
        $needed_on_date = implode(' ', $needed_on_date);
        $notes    = implode(' ', $notes);
        $measure  = implode(' ', $measure);
        $qty      = implode(' ', $qty);

        \DB::update("UPDATE purchase_items SET 
            product_id  = CASE {$product_id} END, 
            flag  = CASE {$flag} END, 
            needed_on_date = CASE {$needed_on_date} END, 
            measure  = CASE {$measure} END, 
            qty  = CASE {$qty} END, 
            notes = CASE {$notes} END
        WHERE id in ({$dpm_ids})");


        $dataHistory['purchase_id'] = $pr->id;
        $dataHistory['user_id'] = Auth::user()->id;
        $dataHistory['jenis'] = 'update';
        $dataHistory['message'] = 'Perbaikan DPM-PR';

        PurchaseRequestHistory::create($dataHistory);

        $getItem_ = DB::table('purchase_items')->where('pr_id','=',$purchase_requisitions->id)->whereNotNull('assigned_id')->first();

        if($request->get('statusPR') == 'parsial') {
            $purchase_requisitions->update(
            array(
                'status' => 2,
                'type' => $request->get('type')
            ));
        }
        else {
            $status_ = null;
            if($getItem_){
                $status_ = 1;
            }
            $purchase_requisitions->update(
            array(
                'status' => $status_,
                'type' => $request->get('type')
            ));
        }

        $purchasing = getAdminPurchasing();

        $email = $dataNotification = [];
        foreach($purchasing as $val){
            $dataNotification[] = array(
                'title'     => "Perbaikan DPM-PR",
                'link'      => "/purchasing/pr_show/".Hashids::encode($purchase_requisitions->id),
                'data_id'   => $purchase_requisitions->id,
                'content'   => "Terdapat Perbaikan DPM-PR dengan nomor: ". $purchase_requisitions->doc_no,
                'user_id'   => $val->id
            );
        }
        
        Notification::insert($dataNotification);

        return redirect()->route('purchase_revision.index')->with(['success' => 'Revisi DPM-PR Berhasil!']);


    }

   
}
