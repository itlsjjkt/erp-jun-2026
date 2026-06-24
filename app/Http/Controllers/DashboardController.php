<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Dashboard;
use App\Models\Announcement;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequisition;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\Gate;

use Auth;
class DashboardController extends Controller
{
    public function index($param =  null)
    {
        $announcement = DB::table('announcements')->latest()->first();
        if(Auth::user()->dashboard == 1){
            $grade_value= [];
            $month =  array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul','Aug', 'Sep', 'Oct', 'Nov', 'Dec');
            $statistics = Dashboard::getStatisticLogistik6Mount();
            $grade_value = [];
            foreach ($statistics as $item) {
                switch ($item->category) {
                    case 'DPM':
                        $grade_value['gradeValueDPM'][] = $item->two_months_ago;
                        $grade_value['gradeValueDPM'][] = $item->last_month;
                        $grade_value['gradeValueDPM'][] = $item->current_month;
                        break;
                    case 'PR':
                        $grade_value['gradeValuePR'][] = $item->two_months_ago;
                        $grade_value['gradeValuePR'][] = $item->last_month;
                        $grade_value['gradeValuePR'][] = $item->current_month;
                        break;
                    case 'PO Jakarta':
                        $grade_value['gradeValuePOJ'][] = $item->two_months_ago;
                        $grade_value['gradeValuePOJ'][] = $item->last_month;
                        $grade_value['gradeValuePOJ'][] = $item->current_month;
                        break;
                    case 'PO Lokal':
                        $grade_value['gradeValuePOL'][] = $item->two_months_ago;
                        $grade_value['gradeValuePOL'][] = $item->last_month;
                        $grade_value['gradeValuePOL'][] = $item->current_month;
                        break;
                    case 'Done PO Jakarta':
                        $grade_value['gradeValuePOJDone'][] = $item->two_months_ago;
                        $grade_value['gradeValuePOJDone'][] = $item->last_month;
                        $grade_value['gradeValuePOJDone'][] = $item->current_month;
                        break;
                    case 'Done PO Lokal':
                        $grade_value['gradeValuePOLDone'][] = $item->two_months_ago;
                        $grade_value['gradeValuePOLDone'][] = $item->last_month;
                        $grade_value['gradeValuePOLDone'][] = $item->current_month;
                        break;
                    default:
                        break;
                }
            }
            $gradeValueDPM      = implode(',', $grade_value['gradeValueDPM']) ?? '';
            $gradeValuePR       = implode(',', $grade_value['gradeValuePR']) ?? '';
            $gradeValuePOJ      = implode(',', $grade_value['gradeValuePOJ']) ?? '';
            $gradeValuePOL      = implode(',', $grade_value['gradeValuePOL']) ?? '';
            $gradeValuePOJDone  = implode(',', $grade_value['gradeValuePOJDone']) ?? '';
            $gradeValuePOLDone  = implode(',', $grade_value['gradeValuePOLDone']) ?? '';

            $countDPMP = DB::table('purchases')
            ->whereIn('status', [1,3,11])
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('purchase_items')
                      ->whereColumn('purchase_items.purchase_id', 'purchases.id')
                      ->where('purchase_items.pr_status', 0)
                      ->where('purchase_items.status', 1);
            })
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->where('purchases.type','=','po')
            ->count();

            $countPRP = DB::table('purchase_requisitions')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->whereNotIn('purchase_requisitions.status', [4,5,6]) //Done, Close, Close Parsial
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->where('purchases.type','=','po')
            ->count();

            $countPOJP = DB::table('po')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('po.status',[0,1,2,3,4,9,10])
            ->where('po.type','=','lpb')
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->count();

            $countPOLP = DB::table('po')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('po.status',[0,1,2,3,4,9,10])
            ->where('po.type','=','non_lpb')
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->count();

            $countLPBP = DB::table('lpb')
            ->select('lpb.*')
            ->leftJoin('po','po.id','=','lpb.po_id')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('lpb.status', [0,1,2])
            ->whereIn('lpb.spb_status', [0,2])
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->count();

            $subquerySPBP = DB::table('purchase_items')
            ->select('spb.doc_no')
            ->rightJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('po', 'po.id', '=', 'po_items.po_id')
            ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->rightJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->rightJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
            ->rightJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('spb.status', [0, 1, 2])
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->groupBy('spb.doc_no');
            $countSPBP = DB::table(DB::raw("({$subquerySPBP->toSql()}) as subquery"))
                ->mergeBindings($subquerySPBP)
                ->count();

            $subqueryBPBJP = DB::table('purchase_items')
            ->select('bpb.doc_no')
            ->rightJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('po', 'po.id', '=', 'po_items.po_id')
            ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->rightJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->rightJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
            ->rightJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
            ->rightJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
            ->rightJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->where('bpb.status','0')
            ->whereNotNull('bpb.spb_id')
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->groupBy('bpb.doc_no');
            $countBPBJP = DB::table(DB::raw("({$subqueryBPBJP->toSql()}) as subquery"))
                ->mergeBindings($subqueryBPBJP)
                ->count();

            $countBPBLP = DB::table('bpb')
            ->leftJoin('po','po.id','=','bpb.po_id')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->where('bpb.status','0')
            ->whereNull('bpb.spb_id')
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', '2024-01-01')
            ->count();
            return view('admin.dashboard.logistic',
                compact('countBPBJP',
                'countBPBLP',
                'countSPBP',
                'countLPBP',
                'countPOJP',
                'countPOLP',
                'countDPMP',
                'countPRP',
                'gradeValueDPM',
                'gradeValuePR',
                'gradeValuePOJ',
                'gradeValuePOL',
                'gradeValuePOJDone',
                'gradeValuePOLDone',
                'month'));
        }

        else if(Auth::user()->dashboard == 2){
            $statistics = Dashboard::getCountQtyItemLpb30Days();
            $grade_value = [
                'gradeValueIN' => [],
                'gradeValueOut' => []
            ];

            foreach ($statistics as $item) {
                // Check if the date exists in the item
                $totalIn = $item->total_in_qty ?? 0;  // Use the total_in_qty from the query
                $totalOut = $item->total_out_qty ?? 0; // Use the total_out_qty from the query

                $grade_value['gradeValueIN'][] = $totalIn;   // Sum for "in"
                $grade_value['gradeValueOut'][] = $totalOut; // Sum for "out"
            }

            // Convert arrays to comma-separated strings
            $gradeValueIN = implode(',', $grade_value['gradeValueIN']) ?? '';
            $gradeValueOUT = implode(',', $grade_value['gradeValueOut']) ?? '';

            $countPOJP = DB::table('po')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('po.status',[0,1,2,3,4,9,10])
            ->where('po.type','=','lpb')
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', Auth::user()->id == 304 ? '2024-01-01' : '2024-01-01')
            ->count();

            $countLPBP = DB::table('lpb')
            ->select('lpb.*')
            ->leftJoin('po','po.id','=','lpb.po_id')
            ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
            ->when(GATE::allows('admin_dpm'), function ($query) {
                return $query->where('purchases.created_by', Auth::user()->id);
            })
            ->whereIn('lpb.status', [0,1,2])
            ->where('lpb.spb_status', 0)
            ->where('purchases.type', 'po')
            ->where('purchases.created_at', '>=', Auth::user()->id == 304 ? '2024-01-01' : '2024-01-01')
            ->count();

            return view('admin.dashboard.lpb_dashboard',
                compact(
                'countLPBP',
                'countPOJP',
                'gradeValueIN',
                'gradeValueOUT'
            ));
        }
        elseif(Auth::user()->dashboard == 3){
            $query = DB::table('purchase_items')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->join('locations', 'locations.id', '=', 'purchases.location_id')
                ->join('companies', 'companies.id', '=', 'locations.company_id')
                ->where('purchases.status', '!=', 0)
                ->select(
                    'companies.name AS company',
                    'companies.id AS company_id',
                    DB::raw('count(companies.name) as total')
                )
                ->groupBy('companies.name', 'companies.id')
                ->orderBy('companies.name', 'asc');

            if (isAdministratorCompany()) {
                $query->where('locations.company_id', '=', Auth::user()->company_id);
            }

            $dataCompany = $query->get();

            // --- Variabel untuk Panel Filter & Export ---
            if (isAdministrator() || isAdmin()) {
                $company    = DB::table('companies')
                                ->get()
                                ->pluck('name', 'id')
                                ->prepend('Silahkan pilih...', '');
                $location   = ''; // di-load dinamis via AJAX saat company dipilih
                $department = DB::table('departments')
                                ->where('company_id', Auth::user()->company_id)
                                ->where('status', 1)
                                ->orderBy('name', 'ASC')
                                ->get()
                                ->pluck('name', 'id')
                                ->prepend('Silahkan pilih...', '');
            } else {
                if (isAdministratorCompany()) {
                    $location   = DB::table('locations')
                                    ->where('isDPM', true)
                                    ->where('company_id', Auth::user()->company_id)
                                    ->orderBy('name', 'ASC')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->prepend('Silahkan pilih...', '');
                    $department = DB::table('departments')
                                    ->where('company_id', Auth::user()->company_id)
                                    ->where('status', 1)
                                    ->orderBy('name', 'ASC')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->prepend('Silahkan pilih...', '');
                } else {
                    $location   = DB::table('locations')
                                    ->where('id', Auth::user()->location_id)
                                    ->orderBy('name', 'ASC')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->prepend('Silahkan pilih...', '');
                    $department = DB::table('departments')
                                    ->where('company_id', Auth::user()->company_id)
                                    ->where('status', 1)
                                    ->orderBy('name', 'ASC')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->prepend('Silahkan pilih...', '');
                }
                $company = DB::table('companies')
                            ->where('id', Auth::user()->company_id)
                            ->orderBy('name', 'ASC')
                            ->get()
                            ->pluck('name', 'id')
                            ->prepend('Silahkan pilih...', '');
            }

            $project = \App\Models\Project::whereNull('deleted_at')
                        ->orderBy('name', 'ASC')
                        ->get()
                        ->pluck('name', 'id')
                        ->prepend('Silahkan pilih...', '');

            return view('admin.dashboard.user_monitoring', compact(
                'announcement',
                'dataCompany',
                'company',
                'location',
                'department',
                'project'
            ));
        }
        elseif(isAdministrator()){
            $count      = Dashboard::getCount(date('Y'));
            $statistics = Dashboard::getStatistic(date('Y'));
            return view('admin.dashboard.index',compact('announcement','count','statistics'));
        }elseif(isPurchasing()){

            $countSupplier = DB::table('suppliers')->count();
            $countApproval = DB::table('po')->where('status',1)->where('po.position', Auth::user()->id)->count();

            $location      = Dashboard::getCountPOByLocation();

            $loc = [];

            foreach ($location as $key => $item) {
                $loc[$item->company][] = $item;
            }


            if(Auth::user()->data_access==1){
                $countPR  = DB::table('purchase_requisitions')->count();
                $countPO  = DB::table('po')->whereIn('status',[1,2,3,4,5])->count();

                $query = DB::table('po')
                    ->select('po.*','users.name AS created','suppliers.name AS supplier')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
                    ->leftJoin('users', 'users.id', '=', 'po.created_by')
                    ->where('po.status', 1)
                    ->where('po.position', Auth::user()->id)
                    ->orderBy('po.created_at', 'DESC');
                $po = $query->paginate(10);

                $supplier_approval = DB::table('suppliers')
                    ->select('suppliers.*', 'users.name AS created_by_name')
                    ->leftJoin('users', 'users.id', '=', 'suppliers.created_by')
                    ->where('suppliers.position', Auth::user()->id)
                    ->where('suppliers.approval_status', 1)
                    ->orderBy('suppliers.created_at', 'DESC')
                    ->paginate(10);

                return view('admin.dashboard.purchasing_approval', compact(
                    'loc',
                    'countPR',
                    'countPO',
                    'countSupplier',
                    'countApproval',
                    'po',
                    'supplier_approval'
                ));
        }else{
                $countPR  = PurchaseRequisition::whereHas('PurchaseRequestItem', function($q){
                    $q->where('assigned_id', Auth::user()->id);
                })->count();

                $countPO  = DB::table('po')->where('created_by', Auth::user()->id)->whereIn('status',[1,2,3,4,5])->count();
                $pr = PurchaseRequisition::
                selectRaw('purchase_requisitions.*,
                departments.name AS department,
                purchases.type,
                projects.name AS project'
                )
                ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
                ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
                ->leftJoin('projects', 'projects.id', '=', 'purchase_requisitions.project_id')
                ->whereHas('PurchaseRequestItem', function($q){
                    $q->where('pr_status', 1)
                    ->where('status', 4)
                    ->where('assigned_id', Auth::user()->id)
                    ->whereIn('po_status', [0,2]);
                })
                ->distinct('purchase_requisitions.id')
                ->orderBy('purchase_requisitions.id','DESC')
                ->paginate(10);

                return view('admin.dashboard.purchasing',compact('loc','countPR','countPO','countSupplier','countApproval','pr'));
            }
        }else{
            $dpm = PurchaseRequest::
            selectRaw('purchases.*,departments.name AS department,users.name AS created')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->where('purchases.status', 1)
            ->whereHas('PurchaseRequestItem', function($q){
                $q->where('position', Auth::user()->id)
                ->where('pr_status', 0)
                ->where('status', 1);
            })
            ->orderBy('updated_at','DESC')
            ->get();
            return view('admin.dashboard.user',compact('announcement','dpm'));
        }
    }
    public function export_new(Request $request){
        $data = $request->all();
        $query = DB::table('purchases')
            ->select(
                'purchases.*',
                'purchase_requisitions.doc_no AS no_pr',
                'purchase_requisitions.created_at AS tgl_pr',
                'po.doc_no AS no_po',
                'po.created_at AS tgl_po',
                'lpb.doc_no AS no_lpb',
                'lpb.created_at AS tgl_lpb',
                DB::raw('spb_unnest.doc_no AS no_spb'),
                DB::raw('spb_unnest.created_at AS tgl_spb'),
                'bpb.doc_no AS no_bpb',
                'bpb.created_at AS tgl_bpb',
                'projects.name AS project',
                'departments.name AS department',
                'locations.name AS location'
            )
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id') // LEFT JOIN dengan projects
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('locations','locations.id','=','purchases.location_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->leftJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('lpb', 'lpb.po_id', '=', 'po.id')
            ->leftJoin(DB::raw('(SELECT UNNEST(regexp_split_to_array(spb.lpb_id, \',\'))::INTEGER AS lpb_id, spb.doc_no, spb.created_at, spb.status FROM spb) AS spb_unnest'), 'spb_unnest.lpb_id', '=', 'lpb.id')
            ->leftJoin('bpb', 'bpb.spb_id', '=', 'spb_unnest.lpb_id')
            ->when(!empty($data['start_date']) && !empty($data['end_date']), function ($query) use ($data){
                $start = date("Y-m-d", strtotime($data['start_date']));
                $end   = date("Y-m-d", strtotime($data['end_date']."+1 day"));
                return $query->whereBetween('purchases.created_at', [$start , $end]);
            })
            ->when(!empty($data['purchases']['status']), function ($query) use ($data){
                return $query->where('purchases.status','!=', 0);
            })
            ->when(!empty($data['purchase_requisitions']['status']), function ($query) use ($data){
                return $query->where('purchase_requisitions.status','!=', 0);
            })
            ->when(!empty($data['po']['status']), function ($query) use ($data){
                return $query->where('po.status','!=', 0);
            })
            ->when(!empty($data['lpb']['status']), function ($query) use ($data){
                return $query->where('lpb.status','!=', 0);
            })
            ->when(!empty($data['spb']['status']), function ($query) use ($data){
                return $query->where('spb.status','!=', 0);
            })
            ->when(!empty($data['bpb']['status']), function ($query) use ($data){
                return $query->where('bpb.status','!=', 0);
            })
            ->orderBy('purchases.id', 'DESC')
            ->get();
            if ($query->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
            }else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($query))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('NOMOR DPM', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500); // Set width for column A
            })
            ->download('Report-DPM-'.date('d-m-Y').'.xlsx', function ($data) {
                return [
                    'NOMOR DPM'         => $data->doc_no,
                    'LOKASI'            => $data->location,
                    'DEPARTMENT'        => $data->department,
                    'PROJECT'           => $data->project,
                    'TGL PUBLISH DPM'   => $data->created_at == NULL ? " " : date('Y/m/d',strtotime($data->created_at)),
                    'NO PR'             => $data->no_pr,
                    'TGL PUBLISH PR'    => $data->tgl_pr == NULL ? " " : date('Y/m/d',strtotime($data->tgl_pr)),
                    'PO'                => $data->no_po,
                    'TGL PUBLISH PO'    => $data->tgl_po == NULL ? " " : date('Y/m/d',strtotime($data->tgl_po)),
                    'LPB'               => $data->no_lpb,
                    'TGL PUBLISH LPB'   => $data->tgl_lpb == NULL ? " " : date('Y/m/d',strtotime($data->tgl_lpb)),
                    'SPB'               => $data->no_spb,
                    'TGL PUBLISH SPB'   => $data->tgl_spb == NULL ? " " : date('Y/m/d',strtotime($data->tgl_spb)),
                    'BPB'               => $data->no_bpb,
                    'TGL PUBLISH BPB'   => $data->tgl_bpb == NULL ? " " : date('Y/m/d',strtotime($data->tgl_bpb)),
                ];
            });
        }
    }

    public function export_instan($bulan, $tahun)
    {
        // Validasi input bulan dan tahun
        if (!is_numeric($bulan) || !is_numeric($tahun) || $bulan < 1 || $bulan > 12) {
            return redirect()->back()->with('error', 'Bulan atau tahun tidak valid.');
        }

        // Query database
        $query = DB::table('purchase_items')
            ->select(
                'purchase_items.*',
                'purchases.doc_no AS no_dpm',
                'purchases.created_at AS tgl_dpm',
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
                'po_items.qty AS qty_po',
                'suppliers.name AS supplier',
                'lpb.doc_no AS no_lpb',
                'lpb.publish AS publish_lpb',
                'lpb_items.qty AS qty_lpb',
                'spb.doc_no AS no_spb',
                'spb.publish AS publish_spb',
                'spb_kolis.qty AS qty_spb',
                'bpb.doc_no AS no_bpb',
                'bpb.publish AS publish_bpb',
                'bpb_items.qty AS qty_bpb',
                'bpb.received_by AS received_bpb',
                'purchase_items.status AS status',
                'purchase_items.pr_status AS pr_status',
                'purchase_items.po_status AS po_status',
                'purchase_items.qty_parsial AS qty_parsial',
                'po_items.lpb_status AS po_lpb_status',
                'po_items.qty_parsial AS po_qty_parsial',
                'lpb.spb_status AS spb_status',
                'spb.status AS bpb_status'
            )
            ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
            ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
            ->leftJoin('users as purchaser', 'purchaser.id', '=', 'purchase_items.assigned_id')
            ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
            ->leftJoin('users as approved', 'approved.id', '=', 'purchase_items.last_approved')
            ->leftJoin('users as rejected', 'rejected.id', '=', 'purchase_requisitions.rejected_by')
            ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
            ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
            ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
            ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
            ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
            ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->orderBy('purchases.id', 'DESC')
            ->get();

        if ($query->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        } else {
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())->setFontSize(10)->setShouldWrapText(false);

            return (new FastExcel($query))->headerStyle($header_style)
                ->rowsStyle($rows_style)
                ->sheet('NOMOR DPM', function ($sheet) {
                    $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
                })
                ->download('Report-DPM-'.date('d-m-Y').'.xlsx', function ($data) {
                    return [
                        'NOMOR DPM'         => $data->no_dpm,
                        'TGL PUBLISH DPM'   => $data->tgl_dpm == NULL ? " " : date('d/m/Y', strtotime($data->tgl_dpm)),
                        'NO PR'             => $data->no_pr,
                        'TGL PR'            => $data->tgl_pr == NULL ? " " : date('d/m/Y', strtotime($data->tgl_pr)),
                        'PURCHASER'         => $data->purchaser,
                        'KODE BARANG'       => $data->product_code,
                        'NAMA BARANG'       => $data->product_name,
                        'PN/SPEC'           => $data->product_part_number,
                        'MERK'              => $data->product_brand,
                        'DPM QTY'           => $data->dpm_qty ? (int) $data->dpm_qty : '',
                        'SATUAN'            => $data->dpm_satuan,
                        'CATATAN'           => strip_tags($data->dpm_notes),
                        'FLAG'              => $data->dpm_flag == 0 ? "Normal" : "Urgent",
                        'FRANCO'            => $data->po_price_term . ' ' . $data->po_price_term_location,
                        'TGL DIBUTUHKAN'    => $data->dpm_needed == NULL ? " " : date('d/m/Y', strtotime($data->dpm_needed)),
                        'TGL APPROVED'      => $data->last_approval == NULL ? " " : date('d/m/Y', strtotime($data->last_approval)),
                        'DEPARTMENT'        => $data->department,
                        'PROJECT'           => $data->project,
                        'STATUS'            => getStatusItemExportDPM($data->status, $data->pr_status, $data->po_status, $data->qty_parsial, $data->po_lpb_status, $data->po_qty_parsial, $data->spb_status, $data->bpb_status),
                        'CREATED BY'        => $data->created_by,
                        'LOKASI'            => $data->location,
                        'PO'                => $data->no_po,
                        'QTY PO'            => $data->qty_po ? (int) $data->qty_po : '',
                        'SUPPLIER PO'       => $data->supplier,
                        'LPB'               => $data->no_lpb,
                        'TGL PUBLISH LPB'   => $data->publish_lpb == NULL ? " " : date('d/m/Y', strtotime($data->publish_lpb)),
                        'QTY LPB'           => $data->qty_lpb ? (int) $data->qty_lpb : '',
                        'SPB'               => $data->no_spb,
                        'TGL PUBLISH SPB'   => $data->publish_spb == NULL ? " " : date('d/m/Y', strtotime($data->publish_spb)),
                        'QTY SPB'           => $data->qty_spb ? (int) $data->qty_spb : '',
                        'BPB'               => $data->no_bpb,
                        'TGL PUBLISH BPB'   => $data->publish_bpb == NULL ? " " : date('d/m/Y', strtotime($data->publish_bpb)),
                        'QTY BPB'           => $data->qty_bpb ? (int) $data->qty_bpb : '',
                        'END USER'          => $data->received_bpb
                    ];
                }
            );
        }
    }
    public function pdf($bulan, $tahun)
    {
        // DPM
        //capprovaldpm
        $capdpm = PurchaseRequest::selectRaw('purchases.*')
            ->where('purchases.status','=',1)
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->whereHas('PurchaseRequestItem', function($q) {
                $q->where('pr_status', 0)
                ->where('status', 1);
            })
            ->count();
        $capdpmdone = PurchaseRequest::selectRaw('purchases.*')
            ->where('purchases.status','=',1)
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->whereHas('PurchaseRequestItem', function($q) {
                $q->where('pr_status','!=', 0)
                ->where('status', '!=',1);
            })
            ->count();
        $purchases = DB::table('purchases')
            ->select('id', 'status', 'created_at')
            ->whereNotNull('status')
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('created_by', Auth::user()->id);
            })
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->get();
        $cdpm = 0;
        $cdpmCounts = array_fill(0, 7, 0);
        foreach ($purchases as $purchase) {
            $cdpm++;
            if ($purchase->status >= 0 && $purchase->status <= 6) {
                $cdpmCounts[$purchase->status]++;
            }
        }
        $cdpm0 = $cdpmCounts[0];
        $cdpm1 = $cdpmCounts[1];
        $cdpm2 = $cdpmCounts[2];
        $cdpm3 = $cdpmCounts[3];
        $cdpm4 = $cdpmCounts[4];
        $cdpm5 = $cdpmCounts[5];
        $cdpm6 = $cdpmCounts[6];

        // PR
        $purchaseRequisitions = DB::table('purchases')
            ->rightJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->select('purchase_requisitions.doc_no','purchase_requisitions.status')
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->groupBy('purchase_requisitions.doc_no','purchase_requisitions.status')
            ->get();
        $cpr = 0;
        $cprCounts = array_fill(0, 8, 0);
        foreach ($purchaseRequisitions as $requisition) {
            $cpr++;
            if ($requisition->status === null) {
                $cprCounts[7]++;
            } elseif ($requisition->status >= 0 && $requisition->status <= 6) {
                $cprCounts[$requisition->status]++;
            }
        }

        $cpr0 = $cprCounts[0];
        $cpr1 = $cprCounts[1];
        $cpr2 = $cprCounts[2];
        $cpr3 = $cprCounts[3];
        $cpr4 = $cprCounts[4];
        $cpr5 = $cprCounts[5];
        $cpr6 = $cprCounts[6];
        $cprNull = $cprCounts[7];

        // PO Jakarta
        $poJData = DB::table('purchases')
            ->rightJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->rightJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->select('po.status')
            ->where('po.type','=','lpb')
            ->whereNotNull('po.status')
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->get();
        $cpoj = 0;
        $cpojCounts = array_fill(0, 7, 0);
        foreach ($poJData as $poj) {
            $cpoj++; // Total jumlah po
            if ($poj->status >= 0 && $poj->status <= 6) {
                $cpojCounts[$poj->status]++;
            }
        }
        $cpoj0 = $cpojCounts[0];
        $cpoj1 = $cpojCounts[1];
        $cpoj2 = $cpojCounts[2];
        $cpoj3 = $cpojCounts[3];
        $cpoj4 = $cpojCounts[4];
        $cpoj5 = $cpojCounts[5];
        $cpoj6 = $cpojCounts[6];

        // PO Lokal
        $polData = DB::table('purchases')
            ->rightJoin('purchase_requisitions', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
            ->rightJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->select('po.status')
            ->where('po.type','=','non_lpb')
            ->whereNotNull('po.status')
            ->when(GATE::allows('admin_dpm'), function ($query2) {
                return $query2->where('purchases.created_by', Auth::user()->id);
            })
            ->whereYear('purchases.created_at', $tahun)
            ->whereMonth('purchases.created_at', $bulan)
            ->get();
        $cpol = 0;
        $cpolCounts = array_fill(0, 7, 0);
        foreach ($polData as $pol) {
            $cpol++; // Total jumlah po
            if ($pol->status >= 0 && $pol->status <= 6) {
                $cpolCounts[$pol->status]++;
            }
        }
        $cpol0 = $cpolCounts[0];
        $cpol1 = $cpolCounts[1];
        $cpol2 = $cpolCounts[2];
        $cpol3 = $cpolCounts[3];
        $cpol4 = $cpolCounts[4];
        $cpol5 = $cpolCounts[5];
        $cpol6 = $cpolCounts[6];

        // PO DONE JAKARTA
        $whereee = '';
        if (Gate::allows('admin_dpm')) {
            $whereee = " AND purchases.created_by = " . Auth::user()->id;
        }
        $bpbData = DB::select('
            SELECT
                grouped_po.created_at, grouped_po.status
            FROM (
                    SELECT DISTINCT
                        po.id,
                        purchases.created_at,
                        bpb.status
                    FROM
                        purchases
                    LEFT JOIN purchase_requisitions ON purchases.id = purchase_requisitions.purchase_id
                    LEFT JOIN po ON po.purchase_id = purchase_requisitions.id
                    LEFT JOIN po_items ON po.id = po_items.po_id
                    LEFT JOIN bpb_items ON po_items.pr_item_id = bpb_items.pr_item_id
                    LEFT JOIN bpb ON bpb.id = bpb_items.bpb_id
                    WHERE
                        EXTRACT(YEAR FROM purchases.created_at) = ?
                        AND EXTRACT(MONTH FROM purchases.created_at) = ?
                         ' . $whereee . '
                        AND po.status IS NOT NULL
                        AND bpb.id IS NOT NULL
                        AND po.type = \'lpb\'
                ) AS grouped_po
            ', [$tahun, $bulan]);

            $cbpb = 0;
            $cbpbCounts = array_fill(0, 3, 0);
            foreach ($bpbData as $bpb) {
                $cbpb++;
                if ($bpb->status >= 0 && $bpb->status <= 2) {
                    $cbpbCounts[$bpb->status]++;
                }
            }
            $cbpb0 = $cbpbCounts[0];$cbpb1 = $cbpbCounts[1];$cbpb2 = $cbpbCounts[2];

        // BPBF
        $bpbfData = DB::select('
            SELECT
                grouped_po.created_at, grouped_po.status
            FROM (
                SELECT DISTINCT
                    po.id,
                    purchases.created_at,
                    bpb.status
                FROM
                    po
                LEFT JOIN
                    bpb ON bpb.po_id = po.id
                LEFT JOIN
                    purchase_requisitions ON purchase_requisitions.id = po.purchase_id
                LEFT JOIN
                    purchases ON purchases.id = purchase_requisitions.purchase_id
                WHERE
                    EXTRACT(YEAR FROM purchases.created_at) = ?
                    AND EXTRACT(MONTH FROM purchases.created_at) = ?
                    AND po.status IS NOT NULL
                     ' . $whereee . '
                    AND po.status = 5
                    AND bpb.id IS NOT NULL
                    AND po.type = \'non_lpb\'
            ) AS grouped_po
        ', [$tahun, $bulan]);

            $cbpbf = 0;
            $cbpbfCounts = array_fill(0, 3, 0);
            foreach ($bpbfData as $bpbf) {
                $cbpbf++;
                if ($bpbf->status >= 0 && $bpbf->status <= 2) {
                    $cbpbfCounts[$bpbf->status]++;
                }
            }
            $cbpbf0 = $cbpbfCounts[0];$cbpbf1 = $cbpbfCounts[1];$cbpbf2 = $cbpbfCounts[2];

        $namaBulan = [
            1 => 'Januari',2 => 'Februari',3 => 'Maret',4 => 'April',5 => 'Mei',6 => 'Juni',7 => 'Juli',8 => 'Agustus',9 => 'September',10 => 'Oktober',11 => 'November',12 => 'Desember'
        ];
        $bulanIndex = $bulan;
        $bulanNama = $namaBulan[$bulanIndex];
        $pdf    = PDF::loadView('admin.dashboard.pdf', compact('capdpmdone','capdpm','bulanNama','tahun','bulan','cdpm','cdpm0','cdpm1','cdpm2','cdpm3','cdpm4','cdpm5','cdpm6','cpr','cprNull','cpr0','cpr1','cpr2','cpr3','cpr4','cpr5','cpr6','cpoj','cpoj0','cpoj1','cpoj2','cpoj3','cpoj4','cpoj5','cpoj6','cpol','cpol0','cpol1','cpol2','cpol3','cpol4','cpol5','cpol6','cbpb','cbpb0','cbpb1','cbpb2','cbpbf','cbpbf0','cbpbf1','cbpbf2'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('Data DPM '.$bulanNama.'['.$tahun.']'.'.pdf');
    }

    public function export_instan_pending_table()
    {
        $result = DB::table('purchase_items')
        ->select(
            'purchases.id AS id',
            'purchases.doc_no AS doc_no',
            'departments.name AS kd',
            'purchases.created_at AS created_at',
            'purchases.created_by AS created_by',
            'users.name AS created',
            'projects.name AS project',
            'locations.name AS lokasi'
        )
        ->leftJoin('po_items', 'po_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
        ->rightJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
        ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('users', 'users.id', '=', 'purchases.created_by')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
        ->leftJoin('lpb_items', 'lpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
        ->leftJoin('spb_kolis', 'spb_kolis.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
        ->leftJoin('bpb_items', 'bpb_items.pr_item_id', '=', 'purchase_items.id')
        ->leftJoin('bpb', 'bpb_items.bpb_id', '=', 'bpb.id')
        ->whereNotIn('purchases.status', [2]) // Reject, Cancel
        ->where('purchases.type', 'po') //type dpm
        ->whereNotIn('purchase_items.status',[2,3])
        ->whereNotIn('purchase_items.po_status',[3,4])
        ->where('purchases.created_at', '>=', '2024-01-01')
        ->where(function ($query) {
            $query->whereNull('purchase_requisitions.status')
                  ->orWhereNotIn('purchase_requisitions.status', [5,6]);
        })
        ->where(function ($query) {
            $query->whereNull('po.status')
                  ->orWhereNotIn('po.status', [6,8]);
        })
        ->where(function ($query) {
            $query->whereNull('lpb.status')
                  ->orWhereNotIn('lpb.status', [3,4]); // LPB CLOSE
        })
        ->where(function ($query) {
            $query->whereNull('spb.status')
                  ->orWhere('spb.status', '!=', 4);
        })
        ->whereNull('bpb.id')
        ->when(Gate::allows('admin_dpm'), function ($query) {
            return $query->where('purchases.created_by', Auth::user()->id);
        })
        ->orderBy('purchases.created_at','ASC')
        ->groupBy('purchases.id',
            'purchases.doc_no',
            'departments.name',
            'purchases.created_at',
            'purchases.created_by',
            'users.name',
            'locations.name',
            'projects.name')
        ->get();

        if ($result->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        } else {
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())->setFontSize(10)->setShouldWrapText(false);
            $dataaa = 'ALL';
            if(Gate::allows('admin_dpm')){
                $dataaa = getUserByID(Auth::user()->id);
            }
            return (new FastExcel($result))->headerStyle($header_style)
                ->rowsStyle($rows_style)
                ->sheet('NOMOR DPM', function ($sheet) {
                    $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
                })
                ->download('REPORT-DPM-PENDING-[Data : '.date('Y-m-d H;i').']['.$dataaa.'].xlsx', function ($data) {
                    $created_at = \Carbon\Carbon::parse($data->created_at);
                    $now = \Carbon\Carbon::now();
                    $difference = $created_at->diff($now);
                    return [
                        'NO DPM'            => $data->doc_no,
                        'LOKASI/KAPAL'       => $data->lokasi,
                        'DEPARTEMENT'       => $data->kd,
                        'PROJECT'           => $data->project,
                        'DIBUAT OLEH'       => $data->created,
                        'TANGGAL INPUT'     => $data->created_at,
                        'USIA DATA'         => $difference->days . ' Hari ' . $difference->h . ' Jam ' . $difference->i . ' Menit'
                    ];
                }
            );
        }
    }

    public function export_instan_item_lpb_30Days()
    {
        $result = DB::table('lpb')
        ->select(
            'lpb.spb_status AS spb_status',
            'lpb.doc_no AS no_lpb',
            'lpb.created_at AS created',
            'lpb.received_by AS received',
            'purchases.doc_no AS no_dpm',
            'master_item_products.name AS product',
            'master_item_products.part_number AS pn',
            'lpb_items.qty AS qty',
            'departments.name AS dept',
            'locations.name AS loc',
            'areas.name AS area',
            'po.doc_no AS no_po',
            'master_item_products.code AS code',
            'po_items.measure AS measure'
        )
        ->leftJoin('po','po.id','=','lpb.po_id')
        ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','po.purchase_id')
        ->leftJoin('purchases', 'purchases.id', '=', 'purchase_requisitions.purchase_id')
        ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
        ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
        ->leftJoin('areas','areas.id','=','locations.area_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->join('lpb_items','lpb.id','=','lpb_items.lpb_id')
        ->leftJoin('po_items','po_items.id','=','lpb_items.po_item_id')
        ->leftJoin('master_item_products','master_item_products.id','=','lpb_items.product_id')
        ->whereIn('lpb.status', [1, 2])
        ->whereBetween('lpb.created_at', [Carbon::now()->subDays(30), Carbon::now()])
        ->groupBy(
            'lpb.doc_no',
            'lpb.created_at',
            'lpb.received_by',
            'purchases.doc_no',
            'master_item_products.name',
            'master_item_products.part_number',
            'lpb_items.qty',
            'departments.name',
            'locations.name',
            'areas.name',
            'po.doc_no',
            'master_item_products.code',
            'po_items.measure',
            'po.created_at',
            'lpb.spb_status'
        )
        ->orderBy('lpb.created_at', 'DESC')
        ->get();
        if ($result->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak terdapat data untuk di Export');
        }
        else{
            $header_style = (new Style())->setFontBold()->setBackgroundColor("F2F2F2");
            $rows_style = (new Style())
            ->setFontSize(10)
            ->setShouldWrapText(false);
            return (new FastExcel($result))->headerStyle($header_style)
            ->rowsStyle($rows_style)
            ->sheet('DATA LPB', function ($sheet) {
                $sheet->getDelegate()->getColumnDimension('A')->setWidth(500);
            })
            ->download('Report-Item-LPB-'.date('d-m-Y H-i').'.xlsx', function ($data) {
                return [
                    'No LPB'            => $data->no_lpb,
                    'No PO'             => $data->no_po,
                    'No DPM'            => $data->no_dpm,
                    'Area'              => $data->area,
                    'Location'          => $data->loc,
                    'Department'        => $data->dept,
                    'Code'              => $data->code,
                    'Product'           => $data->product,
                    'Part Number'       => $data->pn,
                    'QTY'               => (float)$data->qty,
                    'Satuan'            => $data->measure,
                    'Tanggal Input LPB' => $data->created,
                    'Penerima'          => $data->received,
                    'Status'            => $data->spb_status == 1 ? 'Sudah SPB' : 'Belum SPB'
                ];
            });
        }
    }

}
