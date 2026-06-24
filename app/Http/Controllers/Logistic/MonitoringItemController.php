<?php

namespace App\Http\Controllers\Logistic;

use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequisition;
use App\Models\Lpb;
use App\Models\Spb;
use App\Models\Bpb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use App\Exports\MonitoringExport;
use Auth;
use Storage;
use Rap2hpoutre\FastExcel\FastExcel;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringItemController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:dpm_monitoring_item');
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $company   = DB::table('companies')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        if(isAdministrator() || isAdmin() || isPurchasing() ){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->where('departments.status','=',1)
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status','=',1)->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorLocation()){
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status','=',1)->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status','=',1)->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $type = array(
            "po" => "PO",
            "im" => "IM",
            "petty_cash" => "Petty Cash",
        );
        return view('logistic.monitoring.item.index',compact('location','department','type','company'));
    }

    // public function datatables(Request $request)
    // {

    //     if(isAdministrator() || isAdmin()  || isPurchasing() ){
    //         $result = DB::table('purchase_items')
    //         ->select(
    //             'purchase_items.*',
    //             'purchases.doc_no AS no_dpm',
    //             'purchases.type',
    //             'master_item_products.part_number',
    //             'purchases.created_at AS created',
    //             'purchases.status AS statusDPM',
    //             'master_item_products.name AS product',
    //             'users.name AS approval',
    //             'departments.name AS department',
    //             'master_item_brands.name AS brand',
    //             'purchase_requisitions.status AS statusPr',
    //             'locations.name AS location'
    //         )
    //         ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
    //         ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
    //         ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
    //         ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','purchase_items.pr_id')
    //         ->leftJoin('users', 'users.id', '=', 'purchase_items.position')
    //         ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
    //         ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
    //         ->where('purchases.status', '!=' ,0);

    //     }elseif(isAdministratorCompany()){
    //         $result = DB::table('purchase_items')
    //         ->select(
    //             'purchase_items.*',
    //             'purchases.doc_no AS no_dpm',
    //             'purchases.type',
    //             'master_item_products.part_number',
    //             'purchases.created_at AS created',
    //             'purchases.status AS statusDPM',
    //             'master_item_products.name AS product',
    //             'users.name AS approval',
    //             'departments.name AS department',
    //             'master_item_brands.name AS brand',
    //             'purchase_requisitions.status AS statusPr',
    //             'locations.name AS location'
    //         )
    //         ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
    //         ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
    //         ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
    //         ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','purchase_items.pr_id')
    //         ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
    //         ->leftJoin('users', 'users.id', '=', 'purchase_items.position')
    //         ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
    //         ->where('departments.company_id', Auth::user()->company_id)
    //         ->where('purchases.status', '!=' ,0);

    //     } else{
    //         $result = DB::table('purchase_items')
    //         ->select(
    //             'purchase_items.*',
    //             'purchases.doc_no AS no_dpm',
    //             'purchases.type',
    //             'master_item_products.part_number',
    //             'purchases.created_at AS created',
    //             'purchases.status AS statusDPM',
    //             'master_item_products.name AS product',
    //             'departments.name AS department',
    //             'users.name AS approval',
    //             'master_item_brands.name AS brand',
    //             'purchase_requisitions.status AS statusPr',
    //             'locations.name AS location'
    //         )
    //         ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
    //         ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
    //         ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
    //         ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','purchase_items.pr_id')
    //         ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
    //         ->leftJoin('users', 'users.id', '=', 'purchase_items.position')
    //         ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id')
    //         ->where('purchases.location_id', Auth::user()->location_id)
    //         ->where('purchases.status', '!=' ,0);
    //     }
    //     if ($request->has('company_id') && $request->company_id != '') {
    //         $result->where('departments.company_id', $request->company_id);
    //     }
    //     return  DataTables::of($result)
    //     ->addColumn('action', function ($result) {
    //         $url_view = "<a href='" . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->purchase_id)]) . "'
    //                         title='" . trans('app.show_title') . "'
    //                         data-toggle='tooltip'
    //                         target='_blank'
    //                         class='btn btn-outline'>
    //                         <span class='ti-eye icon-lg'></span>
    //                     </a>";
    //         $url_view .= "<a value='" . route('logistic.monitoring.item.log', ['id' => $result->id]) . "' class='icon-lg modalMd'
    //                         style='padding-top: 5px;padding-left: 5px;'
    //                         title='Show Timeline ".$result->product."'
    //                         data-toggle='modal'
    //                         data-target='#modalMd'>
    //                         <span class='ti-signal text-primary'></span>
    //                     </a>";



    //         return '<div class="btn-group">' . $url_view . '</div>';
    //     })
    //     ->editColumn('created_at', function ($result) {
    //         return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y H:i:s') : '';
    //     })
    //     ->editColumn('notes', function ($result) {
    //         return $result->notes ?? '-';
    //     })
    //     ->addColumn('approval', function ($result) {
    //         return ( $result->status == 1 && $result->statusDPM == 1) ? $result->approval : '-';
    //     })
    //     ->filterColumn('approval', function($query, $keyword) {
    //         $keyword = strtolower($keyword);
    //         $query->where('purchase_items.status', 1)
    //             ->where('purchases.status', 1)
    //             ->whereRaw('LOWER(users.name) LIKE ?', ["%{$keyword}%"]);
    //     })
    //     ->addColumn('status', function ($result) {
    //         return getStatusItemByQty($result->type, $result->status, $result->statusDPM, $result->pr_status, $result->po_status, $result->statusPr, getTypePoByPurchaseItem($result->id)->type ?? null , getQtyAllPoItemByPurchaseItem($result->id), getQtyAllLpbItemByPurchaseItem($result->id), getQtyAllSpbItemByPurchaseItem($result->id), getQtyAllBpbItemByPurchaseItem($result->id), $result->qty, (($result->qty - getQtyItemPoByPrItemId($result->id) == $result->qty ? 0 : ($result->qty - getQtyItemPoByPrItemId($result->id)))) ?? 0 );
    //     })
    //     ->addColumn('product', function ($result) {
    //         return $result->product.'<br><small>'. ($result->part_number? 'PN : '.$result->part_number : 'PN : -') .' <br> '.($result->brand? 'Brand : '.$result->brand : 'Brand : -').' </small>';
    //     })
    //     ->rawColumns(['action', 'status','product','notes'])
    //     ->make(true);

    // }

    public function datatables(Request $request)
    {
        $commonSelect = [
            'purchase_items.*',
            'purchases.doc_no AS no_dpm',
            'purchases.type',
            'master_item_products.part_number',
            'purchases.created_at AS created',
            'purchases.status AS statusDPM',
            'master_item_products.name AS product',
            'users.name AS approval',
            'departments.name AS department',
            'master_item_brands.name AS brand',
            'purchase_requisitions.status AS statusPr',
            'locations.name AS location',
    
            // -------------------------------------------------------
            // FIX N+1: Subquery menggantikan 6 function helper
    
            // getTypePoByPurchaseItem($id)
            // Tabel: po_items, kolom: pr_item_id | join: po, status IN [1,2,3,4,5,9,10]
            DB::raw('(
                SELECT po.type
                FROM po_items
                INNER JOIN po ON po.id = po_items.po_id
                WHERE po_items.pr_item_id = purchase_items.id
                AND po.status IN (1,2,3,4,5,9,10)
                ORDER BY po.id DESC
                LIMIT 1
            ) AS type_po'),
    
            // getQtyAllPoItemByPurchaseItem($id)
            // Tabel: po_items, kolom: pr_item_id | join: po, status IN [1,2,3,4,5,9,10]
            DB::raw('(
                SELECT COALESCE(SUM(po_items.qty), 0)
                FROM po_items
                INNER JOIN po ON po.id = po_items.po_id
                WHERE po_items.pr_item_id = purchase_items.id
                AND po.status IN (1,2,3,4,5,9,10)
            ) AS qty_po'),
    
            // getQtyAllLpbItemByPurchaseItem($id)
            // Tabel: lpb_items, kolom: pr_item_id | join: lpb, status IN [1,2]
            DB::raw('(
                SELECT COALESCE(SUM(lpb_items.qty), 0)
                FROM lpb_items
                INNER JOIN lpb ON lpb.id = lpb_items.lpb_id
                WHERE lpb_items.pr_item_id = purchase_items.id
                AND lpb.status IN (1,2)
            ) AS qty_lpb'),
    
            // getQtyAllSpbItemByPurchaseItem($id)
            // Tabel: spb_kolis, kolom: pr_item_id | join: spb, status IN [1,2,3]
            DB::raw('(
                SELECT COALESCE(SUM(spb_kolis.qty), 0)
                FROM spb_kolis
                INNER JOIN spb ON spb.id = spb_kolis.spb_id
                WHERE spb_kolis.pr_item_id = purchase_items.id
                AND spb.status IN (1,2,3)
            ) AS qty_spb'),
    
            // getQtyAllBpbItemByPurchaseItem($id)
            // Tabel: bpb_items, kolom: pr_item_id | join: bpb, status IN [1,2]
            DB::raw('(
                SELECT COALESCE(SUM(bpb_items.qty), 0)
                FROM bpb_items
                INNER JOIN bpb ON bpb.id = bpb_items.bpb_id
                WHERE bpb_items.pr_item_id = purchase_items.id
                AND bpb.status IN (1,2)
            ) AS qty_bpb'),
    
            // getQtyItemPoByPrItemId($id)
            // Tabel: po_items, kolom: pr_item_id | join: po, status IN [0,1,2,3,4,5,9,10]
            DB::raw('(
                SELECT COALESCE(SUM(po_items.qty), 0)
                FROM po_items
                INNER JOIN po ON po.id = po_items.po_id
                WHERE po_items.pr_item_id = purchase_items.id
                AND po.status IN (0,1,2,3,4,5,9,10)
            ) AS qty_po_approved'),
        ];
    
        $commonJoins = function ($query) {
            return $query
                ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
                ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
                ->leftJoin('users', 'users.id', '=', 'purchase_items.position')
                ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
                ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id');
        };
    
        $result = DB::table('purchase_items')->select($commonSelect);
        $result = $commonJoins($result);
    
        // Role-based filter
        if (isAdministrator() || isAdmin() || isPurchasing()) {
            $result->where('purchases.status', '!=', 0);
    
        } elseif (isAdministratorCompany()) {
            $result->where('departments.company_id', Auth::user()->company_id)
                ->where('purchases.status', '!=', 0);
    
        } else {
            $result->where('purchases.location_id', Auth::user()->location_id)
                ->where('purchases.status', '!=', 0);
        }
    
        // Request filters
        if ($request->filled('company_id')) {
            $result->where('departments.company_id', $request->company_id);
        }
        if ($request->filled('location_id')) {
            $result->where('purchases.location_id', $request->location_id);
        }
        if ($request->filled('department_id')) {
            $result->where('purchases.department_id', $request->department_id);
        }
    
        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_view = "
                    <a href='" . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->purchase_id)]) . "'
                        title='" . trans('app.show_title') . "'
                        data-toggle='tooltip'
                        target='_blank'
                        class='btn btn-outline'>
                        <span class='ti-eye icon-lg'></span>
                    </a>
                    <a value='" . route('logistic.monitoring.item.log', ['id' => $result->id]) . "'
                        class='icon-lg modalMd'
                        style='padding-top:5px;padding-left:5px;'
                        title='Show Timeline " . $result->product . "'
                        data-toggle='modal'
                        data-target='#modalMd'>
                        <span class='ti-signal text-primary'></span>
                    </a>";
    
                return '<div class="btn-group">' . $url_view . '</div>';
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at
                    ? (new Carbon($result->created_at))->format('d/m/Y H:i:s')
                    : '';
            })
            ->editColumn('notes', function ($result) {
                return $result->notes ?? '-';
            })
            ->addColumn('approval', function ($result) {
                return ($result->status == 1 && $result->statusDPM == 1) ? $result->approval : '-';
            })
            ->filterColumn('approval', function ($query, $keyword) {
                $query->where('purchase_items.status', 1)
                    ->where('purchases.status', 1)
                    ->whereRaw('LOWER(users.name) LIKE ?', ['%' . strtolower($keyword) . '%']);
            })
            ->addColumn('status', function ($result) {
                // FIX N+1: semua nilai sudah di-load via subquery, tidak ada query tambahan
                $qtyParsial = ($result->qty - $result->qty_po_approved > 0)
                    ? ($result->qty - $result->qty_po_approved)
                    : 0;
    
                return getStatusItemByQty(
                    $result->type,
                    $result->status,
                    $result->statusDPM,
                    $result->pr_status,
                    $result->po_status,
                    $result->statusPr,
                    $result->type_po,
                    $result->qty_po,
                    $result->qty_lpb,
                    $result->qty_spb,
                    $result->qty_bpb,
                    $result->qty,
                    $qtyParsial
                );
            })
            ->addColumn('product', function ($result) {
                $pn    = $result->part_number ? 'PN : ' . $result->part_number : 'PN : -';
                $brand = $result->brand       ? 'Brand : ' . $result->brand    : 'Brand : -';
                return $result->product . '<br><small>' . $pn . ' <br> ' . $brand . '</small>';
            })
            ->rawColumns(['action', 'status', 'product', 'notes'])
            ->make(true);
    }



    public function search(Request $request)
    {
        $search = "Cari Berdasarkan: ";

        if ($request->input('company_id')) {
            $search .= "<strong> Company: </strong> " . getDataByID('companies', $request->input('company_id'))->name;
        }
        if ($request->input('location_id')) {
            $search .= "<strong> Lokasi: </strong>" . getDataByID('locations', $request->input('location_id'))->name;
        }
        if ($request->input('department_id')) {
            $search .= "<strong> Department: </strong>" . getDataByID('departments', $request->input('department_id'))->name;
        }
        if ($request->input('type_dpm')) {
            $search .= "<strong> Tipe DPM: </strong> " . strtoupper($request->input('type_dpm'));
        }
        if ($request->input('start_date') || $request->input('end_date')) {
            $search .= "<strong> Periode: </strong>" . $request->input('start_date') . " - " . $request->input('end_date');
        }

        // Jika request dari DataTables (ajax), kembalikan data JSON
        if ($request->ajax()) {
            $commonSelect = [
                'purchase_items.*',
                'purchases.doc_no AS no_dpm',
                'purchases.type',
                'master_item_products.part_number',
                'purchases.created_at AS created',
                'purchases.status AS statusDPM',
                'master_item_products.name AS product',
                'users.name AS approval',
                'departments.name AS department',
                'master_item_brands.name AS brand',
                'purchase_requisitions.status AS statusPr',
                'locations.name AS location',

                DB::raw('(
                    SELECT po.type
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (1,2,3,4,5,9,10)
                    ORDER BY po.id DESC
                    LIMIT 1
                ) AS type_po'),

                DB::raw('(
                    SELECT COALESCE(SUM(po_items.qty), 0)
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (1,2,3,4,5,9,10)
                ) AS qty_po'),

                DB::raw('(
                    SELECT COALESCE(SUM(lpb_items.qty), 0)
                    FROM lpb_items
                    INNER JOIN lpb ON lpb.id = lpb_items.lpb_id
                    WHERE lpb_items.pr_item_id = purchase_items.id
                    AND lpb.status IN (1,2)
                ) AS qty_lpb'),

                DB::raw('(
                    SELECT COALESCE(SUM(spb_kolis.qty), 0)
                    FROM spb_kolis
                    INNER JOIN spb ON spb.id = spb_kolis.spb_id
                    WHERE spb_kolis.pr_item_id = purchase_items.id
                    AND spb.status IN (1,2,3)
                ) AS qty_spb'),

                DB::raw('(
                    SELECT COALESCE(SUM(bpb_items.qty), 0)
                    FROM bpb_items
                    INNER JOIN bpb ON bpb.id = bpb_items.bpb_id
                    WHERE bpb_items.pr_item_id = purchase_items.id
                    AND bpb.status IN (1,2)
                ) AS qty_bpb'),

                DB::raw('(
                    SELECT COALESCE(SUM(po_items.qty), 0)
                    FROM po_items
                    INNER JOIN po ON po.id = po_items.po_id
                    WHERE po_items.pr_item_id = purchase_items.id
                    AND po.status IN (0,1,2,3,4,5,9,10)
                ) AS qty_po_approved'),
            ];

            $result = DB::table('purchase_items')->select($commonSelect)
                ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
                ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
                ->leftJoin('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
                ->leftJoin('users', 'users.id', '=', 'purchase_items.position')
                ->leftJoin('departments', 'departments.id', '=', 'purchases.department_id')
                ->leftJoin('locations', 'locations.id', '=', 'purchases.location_id');

            // Role-based filter
            if (isAdministrator() || isAdmin() || isPurchasing()) {
                $result->where('purchases.status', '!=', 0);
            } elseif (isAdministratorCompany()) {
                $result->where('departments.company_id', Auth::user()->company_id)
                    ->where('purchases.status', '!=', 0);
            } else {
                $result->where('purchases.location_id', Auth::user()->location_id)
                    ->where('purchases.status', '!=', 0);
            }

            // Request filters
            if ($request->filled('company_id')) {
                $result->where('departments.company_id', $request->company_id);
            }
            if ($request->filled('location_id')) {
                $result->where('purchases.location_id', $request->location_id);
            }
            if ($request->filled('department_id')) {
                $result->where('purchases.department_id', $request->department_id);
            }
            if ($request->filled('type_dpm')) {
                $result->where('purchases.type', $request->type_dpm);
            }
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start = date("Y-m-d", strtotime($request->start_date));
                $end   = date("Y-m-d", strtotime($request->end_date . "+1 day"));
                $result->whereBetween('purchases.created_at', [$start, $end]);
            }

            return DataTables::of($result)
                ->addColumn('action', function ($result) {
                    $url_view = "
                        <a href='" . route('logistic.monitoring.dpm.detail', ['id' => Hashids::encode($result->purchase_id)]) . "'
                            title='" . trans('app.show_title') . "'
                            data-toggle='tooltip'
                            target='_blank'
                            class='btn btn-outline'>
                            <span class='ti-eye icon-lg'></span>
                        </a>
                        <a value='" . route('logistic.monitoring.item.log', ['id' => $result->id]) . "'
                            class='icon-lg modalMd'
                            style='padding-top:5px;padding-left:5px;'
                            title='Show Timeline " . $result->product . "'
                            data-toggle='modal'
                            data-target='#modalMd'>
                            <span class='ti-signal text-primary'></span>
                        </a>";

                    return '<div class="btn-group">' . $url_view . '</div>';
                })
                ->editColumn('created', function ($result) {
                    return $result->created
                        ? (new Carbon($result->created))->format('d/m/Y H:i:s')
                        : '';
                })
                ->editColumn('notes', function ($result) {
                    return $result->notes ?? '-';
                })
                ->addColumn('approval', function ($result) {
                    return ($result->status == 1 && $result->statusDPM == 1) ? $result->approval : '-';
                })
                ->filterColumn('approval', function ($query, $keyword) {
                    $query->where('purchase_items.status', 1)
                        ->where('purchases.status', 1)
                        ->whereRaw('LOWER(users.name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                })
                ->addColumn('status', function ($result) {
                    $qtyParsial = ($result->qty - $result->qty_po_approved > 0)
                        ? ($result->qty - $result->qty_po_approved)
                        : 0;

                    return getStatusItemByQty(
                        $result->type,
                        $result->status,
                        $result->statusDPM,
                        $result->pr_status,
                        $result->po_status,
                        $result->statusPr,
                        $result->type_po,
                        $result->qty_po,
                        $result->qty_lpb,
                        $result->qty_spb,
                        $result->qty_bpb,
                        $result->qty,
                        $qtyParsial
                    );
                })
                ->addColumn('product', function ($result) {
                    $pn    = $result->part_number ? 'PN : ' . $result->part_number : 'PN : -';
                    $brand = $result->brand       ? 'Brand : ' . $result->brand    : 'Brand : -';
                    return $result->product . '<br><small>' . $pn . ' <br> ' . $brand . '</small>';
                })
                ->rawColumns(['action', 'status', 'product', 'notes'])
                ->make(true);
        }

        // Jika bukan ajax, kembalikan view biasa
        if (isAdministrator() || isAdmin() || isPurchasing()) {
            $location = DB::table('locations')
                ->selectRaw("CONCAT(locations.name,' - ', companies.alias) as name, locations.id")
                ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')
                ->selectRaw("CONCAT(departments.name,' - ', companies.alias) as name, departments.id")
                ->leftJoin('companies', 'companies.id', '=', 'departments.company_id')
                ->where('departments.status', '=', 1)
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        } elseif (isAdministratorCompany()) {
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status', '=', 1)->where('company_id', Auth::user()->company_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        } elseif (isAdministratorLocation()) {
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status', '=', 1)->where('company_id', Auth::user()->company_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        } else {
            $location   = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('status', '=', 1)->where('company_id', Auth::user()->company_id)->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }

        $type     = ["po" => "PO", "im" => "IM", "petty_cash" => "Petty Cash"];
        $company  = DB::table('companies')->orderBy('name', 'ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $type_dpm = $request->input('type_dpm');

        return view('logistic.monitoring.item.search', compact('location', 'search', 'type', 'type_dpm', 'company', 'department'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new MonitoringExport($request->get('location_id'), $request->get('department_id'), $request->get('start_date'), $request->get('end_date')), 'Report-Monitoring-DPM-'.$date.'.xlsx');
    }

    public function getLogItems($pid)
    {
        return view('logistic.monitoring.item.log',compact('pid'))->renderSections()['content'];
    }

}
