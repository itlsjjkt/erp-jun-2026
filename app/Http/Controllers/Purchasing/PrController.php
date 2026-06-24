<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestHistory;
use App\Models\PurchaseRequisition;
use App\Models\Notification;
use App\User;
use App\Models\Dph;
use App\Models\Workarea;
use App\Models\Project;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Exports\PrExport;
use App\Exports\PrExportMerge;
use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Rap2hpoutre\FastExcel\FastExcel;

use Auth;
use PDF;
use Imagick;
use File;

class PrController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:purchase_requisition', ['only' => ['index','create','destroy','edit','show','datatables','close']]);

        $this->type = array(
            '' => 'Silahkan Pilih',
            'po' => 'PO',
            'im'  => 'IM',
            'petty_cash' => 'Petty Cash'
        );

        $this->status = array(
            '' => 'Silahkan Pilih',
            'null' => 'Elevated PO',
            '1'  => 'On Progress',
            '2' => 'PR Parsial',
            '3' => 'Revisi',
            '4' => 'Done',
            '5' => 'Close',
        );
    }

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
        $location    = DB::table('locations')
        ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
        ->leftjoin('companies','companies.id','=','locations.company_id')
        ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $type = $this->type;
        $status = $this->status;
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        
        if(Auth::user()->data_access==1) $statistic = PurchaseRequisition::getStats();
        else $statistic = PurchaseRequisition::getStats(Auth::user()->id);

        return view('purchase.pr.index',compact('location','project','status','type','statistic'));
    }

    public function datatables(Request $request)
    {
        if(Auth::user()->data_access==1){
            $result = PurchaseRequisition::
            select(
                'purchase_requisitions.*',
                'departments.name AS department', 
                'projects.name AS project',
                'locations.company_id as companyId',
                'locations.name as locationName'
            )
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
            ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->when(!empty($request->get('amp;type')), function ($result) use ($request) {
                return $result->where('purchase_requisitions.type',$request->get('amp;type'));
            })
            ->when(!empty($request->get('amp;type')), function ($result) use ($request) {
                return $result->where('purchase_requisitions.type',$request->get('amp;type'));
            })
            ->when(!empty($request->get('amp;status')), function ($result) use ($request) {
                $status = ($request->get('amp;status') == 'null') ? 0 : $request->get('amp;status');
                if ($status == 0) {
                    return $result->where(function($q) {
                        $q->where('purchase_requisitions.status', 0)
                        ->orWhereNull('purchase_requisitions.status');
                    });
                } else {
                    return $result->where('purchase_requisitions.status', $status);
                }
            })
            ->when(!empty($request->get('amp;project_id')), function ($result) use ($request) {
                return $result->where('purchase_requisitions.project_id',$request->get('amp;project_id'));
            })
            ->when(!empty($request->get('amp;location_id')), function ($result) use ($request){
                return $result->where('purchase_requisitions.location_id',$request->get('amp;location_id'));
            })
            ->when(!empty($request->get('amp;start_date')), function ($result) use ($request) {
                $start = date("Y-m-d",strtotime($request->get('amp;start_date')));
                $end   = date("Y-m-d",strtotime($request->get('amp;end_date')."+1 day"));
                return $result->whereBetween('purchase_requisitions.created_at', [$start , $end]);
            })
            ->distinct('purchase_requisitions.id')
            ->orderBy('purchase_requisitions.id','DESC');
        }else{
            $result = PurchaseRequisition::
            select(
                'purchase_requisitions.*',
                'departments.name AS department', 
                'projects.name AS project',
                'locations.company_id as companyId',
                'locations.name as locationName'
            )
            ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
            ->leftJoin('locations','locations.id','=','purchase_requisitions.location_id')
            ->when($request->get('mode')!='search', function ($result) use ($request) {
                 return $result->whereHas('PurchaseRequestItem', function($q){
                    $q->where('pr_status', 1)
                    ->where('status', 4)
                    ->where('assigned_id', Auth::user()->id)
                    ->whereIn('po_status', [0,2]);
                });
            })
            ->when(!empty($request->get('amp;type')), function ($result) use ($request) {
                return $result->where('purchase_requisitions.type',$request->get('amp;type'));
            })
            ->when(!empty($request->get('amp;status')), function ($result) use ($request) {
                $status = ($request->get('amp;status')=='null') ? 0 : $request->get('amp;status');
                return $result->where('purchase_requisitions.status',$status);
            })
            ->when(!empty($request->get('amp;project_id')), function ($result) use ($request) {
                return $result->where('purchase_requisitions.project_id',$request->get('amp;project_id'));
            })
            ->when(!empty($request->get('amp;location_id')), function ($result) use ($request){
                return $result->where('purchase_requisitions.location_id',$request->get('amp;location_id'));
            })
            ->when(!empty($request->get('amp;start_date')), function ($result) use ($request) {
                $start = date("Y-m-d",strtotime($request->get('amp;start_date')));
                $end   = date("Y-m-d",strtotime($request->get('amp;end_date')."+1 day"));
                return $result->whereBetween('purchase_requisitions.created_at', [$start , $end]);
            })
            ->distinct('purchase_requisitions.id')
            ->orderBy('purchase_requisitions.id','DESC');
        }
        if(isAdministratorCompany()){
            $result->where('locations.company_id','=',Auth::user()->company_id);
        }

       return  DataTables::of($result)
        ->editColumn('doc_no', function ($result) {
            if ( $result->is_seen != true) {
				$doc_no = "<a href='".route('purchasing.pr.show', Hashids::encode($result->id))."' title='Detail PR' data-toggle='tooltip' ><span class='font-weight-bold'>".$result->doc_no."</span></a>";
			}else{
				$doc_no = "<a target='_blank' href='".route('purchasing.pr.show',Hashids::encode($result->id))."' title='Detail PR' data-toggle='tooltip' >".$result->doc_no."</a>";
            }
            return $doc_no;
        })
		
        ->addColumn('action', function ($result) {

            $action = "<a href='".route('purchasing.pr.show', Hashids::encode($result->id))."' data-toggle='tooltip' title='detail' class='btn btn-outline'><span class='ti-eye icon-lg'></span></a>";
           
            if(auth()->user()->can('purchase_admin') ) {

                if(Auth::user()->can('purchaser_assign') && in_array($result->status, array(null,'0'))) $action .= "<a href='".route('purchasing.pr.assign', ['id' => Hashids::encode($result->id)])."' title='Assign Purchaser' class='btn btn-outline'><span class='ti-user icon-lg'></span> </a>";
                                
                if($result->type=='po') {
                    if(in_array($result->status, array(null,'1','2','3'))) $action .= "<a href='".route('purchasing.pr.cancel', Hashids::encode($result->id))."' data-toggle='tooltip' title='Tutup PR' class='btn btn-outline'><span class='ti-power-off text-danger icon-lg'></span></a>";
                    
                    if(in_array($result->status, array('1','2'))) $action .= " <a href='".route('purchasing.po.create', ['id' => Hashids::encode($result->id)])."' data-toggle='tooltip' title='Buat PO' class='btn btn-outline'><span class='ti-file icon-lg'></span></a>";
                    
                }
                
                //FIXUPDATE ACCESS 2
                // if(in_array($result->status, array('1','2')) && $result->type=='po'){
                //     $action .= " <a href='".route('purchasing.dph.create_list_item', ['id' => Hashids::encode($result->id)])."' data-toggle='tooltip' title='Buat DPH' class='btn btn-outline'><span class='ti-files icon-lg'></span></a>";
                // }
            }

            return $action;
        })
		->editColumn('dpm_no', function ($result) {
			$dpm_no = "<a target='_blank' href='".route('logistic.monitoring.dpm.detail',Hashids::encode($result->purchase_id))."' title='Detail DPM' data-toggle='tooltip' >".$result->dpm_no."</a>";
			return $dpm_no;
        })
        ->editColumn('status', function ($result) {
            return getStatusPR($result->status);
        })
        ->editColumn('type', function ($result) {
            return ($result->type) ? strtoupper($result->type) : '-';
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->rawColumns(['action', 'status','check','doc_no','dpm_no'])
        ->make(true);

    }

    

    public function show($id)
    {
        $id = Hashids::decode($id);
        $pr = PurchaseRequisition::findOrFail($id['0']);

        $notification = Notification::where(['user_id' => Auth::user()->id, 'data_id' => $pr->id, 'status' => 0])->first();
        if($notification){
            $data['status'] = 1;
            $notification->update($data);
        }
        if(Auth::user()->data_access==2){
            $pr_items   = PurchaseRequestItem::getItem($id['0'], Auth::user()->id);
        }else{
            $pr_items   = PurchaseRequestItem::getItem($id['0']);
        }
        if($pr->is_seen == NULL && Auth::user()->data_access==2){
            $prUpdate= PurchaseRequisition::findOrFail($id['0']);
            $data['is_seen'] = true;
            $prUpdate->update($data);
        }
        $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');

        return view('purchase.pr.show', compact('pr', 'pr_items','purchaser'));
    }

    public function history($id)
    {
      
        $id = Hashids::decode($id);

        $pr         = PurchaseRequisition::getByID($id['0']);
        $pr_items   = PurchaseRequestItem::getItem($id['0']);
        $po         = PurchaseOrder::getByPRID($id['0']);

        return view('purchase.pr.history', compact('pr', 'po','pr_items'));
    }


    public function close(Request $request)
    {
       
        $pr   = PurchaseRequisition::findOrFail($request->get('pr_id'));
        $update['po_status'] = 3;
        DB::table('purchase_items')->where('pr_id', $pr->id)->whereIn('po_status', [0,2])->update($update);
        $data['status'] = 5;
        $data['notes']  = $request->get('reason');
        $data['rejected_by'] = Auth::user()->id;
        $pr->update($data);
        return redirect()->route('purchasing.pr')->with(['success' => 'Close PR Berhasil!']);

    }


    public function search(Request $request)
    {
        $query = 'mode='.$request->get('mode').'&type='.$request->get('type').'&status='.$request->get('status').'&project_id='. $request->get('project_id')."&location_id=".$request->get('location_id')."&start_date=".$request->get('start_date')."&end_date=". $request->get('end_date');
        $data = $request->all();
        $search = "Cari Berdasarkan: ";

        if($request->input('type')) $search .= "<strong> Tipe : </strong> ".strtoupper($request->input('type'));
        if($request->input('status')) $search .= "<strong> Status : </strong> ".getStatusPR($request->input('status'),'raw');
        if($request->input('project_id')) $search .= "<strong> Project: </strong>".getDataByID('projects',$request->input('project_id'))->name;
        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".date('d/m/Y',strtotime( $request->input('start_date'))). " - ". date('d/m/Y',strtotime( $request->input('end_date')));

        if(isAdministrator()){
            $location    = DB::table('locations')
            ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
            ->leftjoin('companies','companies.id','=','locations.company_id')
            ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('company_id', Auth::user()->company_id)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $type = $this->type;
        $status = $this->status;
        $project = Project::where('status', 1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        if(Auth::user()->data_access==1) $statistic = PurchaseRequisition::getStats();
        else $statistic = PurchaseRequisition::getStats(Auth::user()->id);


        return view('purchase.pr.search', compact('location', 'search','project','type','data','query','status','statistic'));
  
    }


    public function print($id, $type)
    {

        $id = Hashids::decode($id);
        $pr = PurchaseRequisition::getByID($id['0']);

        if(Auth::user()->data_access==2) $pr_items = PurchaseRequestItem::getItem($id['0'], Auth::user()->id);
        else $pr_items = PurchaseRequestItem::getItem($id['0']);
        
        if($pr->is_seen == NULL && Auth::user()->data_access==2){
            $prUpdate= PurchaseRequisition::findOrFail($id['0']);
            $data['is_seen'] = true;
            $prUpdate->update($data);
        }  

        $num_page = 0;
        if(File::exists(public_path('storage'.$pr->mr_file)) && $pr->mr_file !=null){
            $imagick = new Imagick();
            $imagick->readImage(asset('storage'.$pr->mr_file));
            $num_page = $imagick->getnumberimages();
            $saveImagePath = storage_path('app/public/mr_file/'.$pr->doc_no.'.jpg');
            $imagick->writeImages($saveImagePath, true);
        }
        return view('purchase.pr.print', compact('pr', 'pr_items','num_page'));
    }

    public function print_merge(Request $request)
    {
        $id = explode(',',$request->get('pr_id'));

        $pr_data  =  PurchaseRequisition::whereIn('id',$id)
        ->orderBy('created_at','DESC')
        ->get();
 
        if(Auth::user()->data_access==2){
            $prUpdate= PurchaseRequisition::whereIn('id',$id)->update([
                'is_seen' => true,
            ]);
        }

        $pr = [];
        for($i = 0; $i < count($pr_data); $i++){
            $pr[$i] = PurchaseRequisition::getByID($pr_data[$i]->id);

            $num_page[$i] = 0;
            if(File::exists(public_path('storage'.$pr[$i]->mr_file)) && $pr[$i]->mr_file !=null){
                $imagick[$i] = new Imagick();
                $imagick[$i]->readImage(asset('storage'.$pr[$i]->mr_file));
                $num_page[$i] = $imagick[$i]->getnumberimages();
                $saveImagePath[$i] = storage_path('app/public/mr_file/'.$pr[$i]->doc_no.'.jpg');
                $imagick[$i]->writeImages($saveImagePath[$i], true);
            }
        }

        return view('purchase.pr.print_merge', compact('pr_data', 'pr', 'num_page'));
    }


    public function export(Request $request)
    {
        $filter = $request->all();
        $date = date('d-m-Y', strtotime($request->get('start_date')));
        return Excel::download(new PrExport($filter), 'Report-PR-'.$date.'.xlsx');
    }

    public function dph($id)
    {
        $id = Hashids::decode($id);
        $pr = PurchaseRequisition::findOrFail($id['0']);
        $pr_items   = PurchaseRequisition::getProductItem($id['0']);

        $dph = Dph::where('pr_id',$id['0'])->whereNull('deleted_at')->get();
        return view('purchase.pr.dph', compact('pr','pr_items','dph'));
    }

    public function dph_store(Request $request)
    {

        $data = $request->all();
        $data['created_by'] = Auth::user()->id;
        Dph::create($data);
        return redirect()->route('purchasing.pr')->with(['success' => 'Add was successful!']);
    }


    public function list()
    {
        
        if(isAdministrator()){
            $company = User::where('status','1')->where('type','company')->whereNull('deleted_at')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $data = Inventory::getDistribution();
        }else{
            $company = User::where('id', Auth::user()->id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $data = Inventory::getDistribution(Auth::user()->id);

        }
        return view('module.inventory.list', compact('company','data'))->renderSections()['content'];
    }

    
    public function assign(Request $request)
    {
       
        if($request->isMethod('get')){

            $id = Hashids::decode($request->get('id'));
            $pr = PurchaseRequisition::findOrFail($id['0']);
            if(count($pr->assignItem) == 0) return redirect()->route('purchasing.pr')->with(['success' =>'Seluruh Item PR telah berhasil di assign']);

            $type = array(
                'po' => 'PO',
                'im'  => 'IM',
                'petty_cash' => 'Petty Cash'
            );
            $purchaser = User::where('type',4)->where('is_pr_sign','=',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
            return view('purchase.pr.assign', compact('purchaser','pr','type'));

        }else{
            //$pr = PurchaseRequisition::findOrFail($request->get('id'));
			$pr = PurchaseRequisition::with('PurchaseRequest')->findOrFail($request->get('id'));
			
            $items = $request->get('pr_item_id');

            for($i=0;$i < count($items);$i++) {
                if (in_array($request->get('pr_item_id')[$i], $request->get('isPR'))) {
                    $data['assigned_id'] = $request->get('assigned_id');
                    $pr_items = PurchaseRequestItem::findOrFail($request->get('pr_item_id')[$i]);
                    $pr_items->update($data);
                }
            }
            
            $dataNotification['title']      = "Assigned PR";
            $dataNotification['link']       = "/purchasing/pr_show/".Hashids::encode($pr->id);
            $dataNotification['data_id']    = $pr->id;
            $dataNotification['content']    =  "Terdapat PR dengan nomor: ". $pr->doc_no;
            $dataNotification['user_id']    = $request->get('assigned_id');
            $notifications = Notification::create($dataNotification);
            
            $pr->update([
                'type' => $request->get('type'),
                'status' => 1
            ]);
			$purchaseRequest = $pr->PurchaseRequest;
            $purchaseRequest->update([
                'type' => $request->get('type')
			]);
            return redirect()->route('purchasing.pr.assign',['id' => Hashids::encode($request->get('id'))])->with(['success' =>'Assigned PR Berhasil!']);
        }
    }


    public function reassign(Request $request)
    {
       
        if($request->isMethod('get')){

            $id = Hashids::decode($request->get('id'));
            $pr = PurchaseRequisition::findOrFail($id['0']);
            if(count($pr->reassignItem) == 0) return redirect()->route('purchasing.pr')->with(['success' =>'Seluruh Item PR telah berhasil di assign']);

            $type = array(
                'po' => 'PO',
                'im'  => 'IM',
                'petty_cash' => 'Petty Cash'
            );
            $purchaser = User::where('type',4)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Pilih Purchaser…', '');
            return view('purchase.pr.reassign', compact('purchaser','pr','type'));

        }else{
            //$pr = PurchaseRequisition::findOrFail($request->get('id'));
			$pr = PurchaseRequisition::with('PurchaseRequest')->findOrFail($request->get('id'));
			
            $items = $request->get('pr_item_id');

            for($i=0;$i < count($items);$i++) {
                if (in_array($request->get('pr_item_id')[$i], $request->get('isPR'))) {
                    $data['assigned_id'] = $request->get('assigned_id');
                    $pr_items = PurchaseRequestItem::findOrFail($request->get('pr_item_id')[$i]);
                    $pr_items->update($data);
                }
            }
            
            $dataNotification['title']      = "Re-assigned PR";
            $dataNotification['link']       = "/purchasing/pr_show/".Hashids::encode($pr->id);
            $dataNotification['data_id']    = $pr->id;
            $dataNotification['content']    =  "Terdapat PR dengan nomor: ". $pr->doc_no;
            $dataNotification['user_id']    = $request->get('assigned_id');
            $notifications = Notification::create($dataNotification);
            
            $pr->update([
                'type' => $request->get('type')
            ]);
            
			$purchaseRequest = $pr->PurchaseRequest;
            $purchaseRequest->update([
                'type' => $request->get('type')
			]);
            return redirect()->route('purchasing.pr.reassign',['id' => Hashids::encode($request->get('id'))])->with(['success' =>'Assigned PR Berhasil!']);
        }
    }

    public function done(Request $request)
    {
        $pr   = PurchaseRequisition::findOrFail($request->get('pr_id'));
        $data['status'] = 4;
        $pr->update($data);
        PurchaseRequestItem::where('purchase_id', $pr->purchase_id)->update(['po_status' => 1]);
        return redirect()->route('purchasing.pr')->with(['success' =>'PR Berhasil di Set Selesai!']);

    }


    public function revision(Request $request)
    {

        return redirect()->route('purchasing.pr')->with(['error' =>'Revisi PR tidak bisa dilakukan']);

        $data['status'] = 3;

        $pr = PurchaseRequisition::findOrFail($request->get('pr_id'));
        $pr->update($data);

        $dataDPM['status'] = 6;

        $dpm = PurchaseRequest::findOrFail($pr->purchase_id);
        $dpm->update($dataDPM);

        $dataHistory['purchase_id'] = $pr->purchase_id;
        $dataHistory['user_id'] = Auth::user()->id;
        $dataHistory['jenis'] = 'revisi-pr';
        $dataHistory['message'] = $request->get('reason');

        PurchaseRequestHistory::create($dataHistory);

        $dataNotification = array(
            'title'     => "Perbaikan DPM-PR",
            'link'      => "/purchase_revision_edit/".Hashids::encode($pr->purchase_id),
            'data_id'   => $pr->purchase_id,
            'content'   => "Terdapat Perbaikan DPM-PR dengan nomor: ". $pr->doc_no,
            'user_id'   => $dpm->created_by
        );
        
        Notification::insert($dataNotification);

        // NOTIF WHATSAPP REJECT PR
        $user_db_ = DB::table('users')->where('id',$dpm->created_by)->first();
        if($user_db_){
            $body = "```Dear User ".getUserByID($user_db_->id).",```\n\n```Mohon Lakukan Perbaikan Reject PR / Revisi DPM Berikut:```\n";
            $body .= "```".$dpm->doc_no."```\n";
            $body .= "```Tipe : ".strtoupper($dpm->type)."```\n";
            $body .= "```Alasan Reeject PR : ".$request->get('reason')."```\n";
            $bodyS = $body;
            if($user_db_->is_whatsapp === true && $user_db_->telp != null){
                sendWhatsapp($user_db_->telp, $bodyS);
            }
        }

        return redirect()->route('purchasing.pr')->with(['success' =>'Berhasil melakukan permintaan Revisi PR']);

    }

    public function cancel($id)
    {
        $id = Hashids::decode($id);
        $pr = PurchaseRequisition::findOrFail($id['0']);
        if(Auth::user()->data_access==1){
            $pr_items = DB::table('purchase_items')
            ->select('purchase_items.*',
            'users.name AS purchaser',
            'master_item_products.name AS product', 
            'master_item_products.code AS productCode', 
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
            ->where('purchase_items.pr_id', $id)
            ->whereIn('purchase_items.po_status',[0, 2, 4])
            ->orderBy('purchase_items.id', 'ASC')
            ->get();
        } else {
            $pr_items = DB::table('purchase_items')
            ->select('purchase_items.*',
            'users.name AS purchaser',
            'master_item_products.name AS product', 
            'master_item_products.code AS productCode', 
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
            ->where('purchase_items.assigned_id', '=', Auth::user()->id)
            ->where('purchase_items.pr_id', $id)
            ->whereIn('purchase_items.po_status',[0, 2, 4])
            ->orderBy('purchase_items.id', 'ASC')
            ->get();
        }
        return view('purchase.pr.cancel', compact('pr', 'pr_items',));
    }

    public function closePR($id, Request $request){
        try{
            $items = $request->prItemId;
            foreach($items as $item){
                $prItem = PurchaseRequestItem::where('id', $item)->first();
                
                $data['reason'] = $request->prItemTextarea[$item];
                if($prItem->po_status == 2){
                    $data['po_status'] = 4;
                } else{
                    $data['po_status'] = 3;
                }
                $prItem->update($data);
            }
            $pr = PurchaseRequisition::where('id', $id)->first();
            $pr_items_status = PurchaseRequestItem::where('pr_id', $id)->pluck('po_status')->toArray();   
            
            if (!in_array(0, $pr_items_status) && !in_array(1, $pr_items_status) && !in_array(2, $pr_items_status) && !in_array(4, $pr_items_status)) {
                $prData['status'] = 5;
                $pr->update($prData);
            } elseif(!in_array(0, $pr_items_status) && !in_array(2, $pr_items_status)) {
                if(in_array(1, $pr_items_status) || in_array(4, $pr_items_status)){
                    $prData['status'] = 6;
                    $pr->update($prData);
                }
            }

            return redirect()->route('purchasing.pr')->with(['success' => 'Close PR Berhasil!']);
        }
        catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    public function close_document(Request $request)
    {
        try {

            DB::beginTransaction();

            $data['status'] = 5;
            $data['notes'] = $request->get('reason');
            $data['rejected_by'] = Auth::user()->id;

            $pr = PurchaseRequisition::findOrFail($request->get('pr_id'));
            $pr->update($data);

            // Update item PR (collection → gunakan where update)
            PurchaseRequestItem::where('pr_id', $pr->id)->update([
                'po_status' => 3,
                'reason'    => $request->get('reason')
            ]);

            // NOTIF WA
            $dpm = PurchaseRequest::findOrFail($pr->purchase_id);
            $user_db_ = DB::table('users')->where('id', $dpm->created_by)->first();

            if ($user_db_) {
                $body  = "```Dear User " . getUserByID($user_db_->id) . ",```\n\n";
                $body .= "```Mohon Lakukan pengecekan Reject PR Berikut:```\n";
                $body .= "```" . $dpm->doc_no . "```\n";
                $body .= "```Tipe : " . strtoupper($dpm->type) . "```\n";
                $body .= "```Alasan Reject PR : " . $request->get('reason') . "```\n";

                if ($user_db_->is_whatsapp === true && $user_db_->telp != null) {
                    sendWhatsapp($user_db_->telp, $body);
                }
            }

            DB::commit();

            return redirect()
                ->route('purchasing.pr')
                ->with(['success' => 'Berhasil melakukan close document PR']);

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with(['error' => 'Gagal melakukan close document PR']);
        }
    }
}
