<?php

namespace App\Http\Controllers\Logistic;

use App\Models\Inventory;
use App\Models\InventoryHistory;
use App\Models\InventoryTtb;
use App\Models\InventoryTtbItem;
use App\Models\Company;
use App\Models\CostCentre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Exports\TtbExport;
use App\Exports\TtbExportCostCenter;
use App\Traits\UploadTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Mail\SendMailable;
use Illuminate\Support\Facades\Mail;
use Auth;
use Storage;
use Illuminate\Support\Facades\Redirect;


class TtbController extends Controller
{

    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:ttb');
    }

    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(isAdministrator() ){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $project = DB::table('projects')->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('logistic.ttb.index',compact('location','department','project'));
    }


    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator() || isAdmin() ) $result  = InventoryTtb::getData($data);
        elseif(isAdministratorCompany() ) $result  = InventoryTtb::getData($data, Auth::user()->company_id);
        else $result = InventoryTtb::getData($data,null,Auth::user()->location_id);

        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $url = 'printExternal("/logistic/ttb_print/'.Hashids::encode($result->id).'")';
            $url_print = "<a onclick='".$url."' data-toggle='Print' class='btn btn-outline'><span class='ti-printer icon-lg'></span> </a>";
            $url_show  = "<a href='".route('logistic.ttb.show',Hashids::encode($result->id))."' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            $url_edit = "<a href='".route('logistic.ttb.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            $url_revision = "<a href='".route('logistic.ttb.edit', Hashids::encode($result->id))."?param=revision' title='Revision' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'></span> </a>";
            $url_rollback = "<form class='delete' action='".route('logistic.ttb.delete', ['id' => $result->id])."' method='POST'>
                            ".csrf_field()."
                            <button class='btn btn-outline text-danger' title='Reversal' data-toggle='tooltip'><i class='ti-back-left icon-lg'></i></button>
                        </form>";
            if($result->status==0){
                return '<div class="btn-group">'.$url_edit.$url_show.'</div>';
            }else{
                if(isAdministrator()){
                    if($result->status==2) return '<div class="btn-group">'.$url_show.$url_print.'</div>';
                    else return '<div class="btn-group">'.$url_show.$url_print.$url_revision.$url_rollback.'</div>';
                    // else return '<div class="btn-group">'.$url_show.$url_print.$url_rollback.'</div>';
                }
                else return '<div class="btn-group">'.$url_show.$url_print.'</div>';
            }
        })
        ->addColumn('status', function ($result) {
            return getStatusDataTTB($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);
    }


    public function create(Request $request)
    {
        if($request->get('inv_id')){

            $invID = explode(',',$request->get('inv_id'));

            $inventory = DB::table('inventories')
            ->select('inventories.*','locations.name AS locationName', 'locations.company_id AS companyId',
            'master_item_products.name AS productName', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand',
            'measures.name AS unit')
            ->leftJoin('master_item_products', 'master_item_products.id', '=', 'inventories.product_id')
            ->leftJoin('locations', 'locations.id', '=', 'inventories.location_id')
            ->leftJoin('measures', 'measures.id', '=', 'inventories.measure_id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->whereIn('inventories.id',$invID)
			->orderByRaw(DB::raw("array_position(ARRAY[" . implode(',', $invID) . "], inventories.id)"))
            ->get();

            $location = $checkStock = [];
            foreach($inventory as $item){
                $location [] = $item->location_id;
                $checkStock[] = $item->stock_onhand;
                $locationID   = $item->location_id;
                $companyID   = $item->companyId;
                $locationName = $item->locationName;
            }

            if(count(array_unique($location)) === 1 ){

                if (in_array(0, $checkStock)) return redirect()->back()->withErrors(['Terdapat item dengan Stock On Hand kosong']);

                $locationID = $locationID;
                $locationName = $locationName;

                if(isAdministrator()){
                    $department  = DB::table('departments')
                                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                                ->leftjoin('companies','companies.id','=','departments.company_id')
                                ->where('departments.company_id','=',$companyID)
                                ->where('departments.status','=',1)
                                ->orderBy('name','ASC')
                                ->get()
                                ->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                }else{
                    $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('departments.status','=',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                }
                $project   = DB::table('projects')->whereNull('deleted_at')->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
                return view('logistic.ttb.create', compact('department','item','locationID','locationName', 'inventory','project'));
            }else{
                return redirect()->back()->withErrors(['Lokasi Stock Gudang berbeda pada Item yang dipilih']);
            }
        }else{
                return redirect()->back()
                ->withErrors(['Belum melakukan Checklist Item Inventory']);
        }
    }


    public function store(Request $request)
    {
        $location  = DB::table('locations')
        ->select('locations.name AS name',
        'locations.alias AS code',
        'companies.alias AS companyCode',
        'locations.email AS email'
        )
        ->leftJoin('companies','companies.id','=','locations.company_id')
        ->where("locations.id", $request->get('location_id'))->first();

        if ($request->get('status')==1) {
            $increment = DB::table('inventory_ttbs')
            ->whereYear("created_at", date('Y'))
            ->where('is_local', false)
            ->where('status', '!=', 0)
            ->where('location_id',$request->get('location_id'))
            ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no = "TTB-".$location->companyCode."-".$location->code."-".date('my')."-".$num;
            $data['status']  = 1;
            $data['publish'] = date('Y-m-d');

        }else{
            $no = "TTB-".$location->companyCode."-".$location->code."-".date('my')."-DRAFT";
            $data['status'] = 0;
        }

        $data['operator']       = $request->get('operator');
        $data['project_id']            = $request->get('project_id');
        $data['received']       = $request->get('received');
        $data['department_id']  = $request->get('department_id');
        $data['location_id']    = $request->get('location_id');
        $data['doc_no']         = $no;
        $data['created_by']     = Auth::user()->id;
        $data['date_transaction']  = $request->get('date_transaction');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'ttb_'.time();
            $folder = '/uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }


        DB::beginTransaction();

        try {

            $ttb = InventoryTtb::create($data);

            $dataTTB = $invHistory = $productUrgentStock = $ids  = $update = $out = $status = [];

            $product = $request->get('inv_id');
            for($i=0;$i < count($product);$i++) {

                $inv = Inventory::find($request->get('inv_id')[$i]);

                $dataTTB[] = [
                    'inventory_ttb_id'  => $ttb->id,
                    'inventory_id'      => $request->get('inv_id')[$i],
                    'description'       => $request->get('notes')[$i],
                    'qty'               => $request->get('qty')[$i],
                    'qty_awal'          => $inv->stock_onhand,
                    'price'             => $inv->price,
                ];

                $invHistory[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_out'       => $request->get('qty')[$i],
                    'qty_awal'      => $inv->stock_onhand,
                    'message'       => $ttb->doc_no,
                    'description'   => $request->get('notes')[$i]
                ];

                $stock_onhand   = $inv->stock_onhand - $request->get('qty')[$i];
                $stock_out      = $inv->out +  $request->get('qty')[$i];
                $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                $ids[]          = $request->get('inv_id')[$i];
                $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                $update[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                if($stock_status == 'Urgent Order'){
                    $productUrgentStock [] = [
                        'productCode' => $inv->product->code,
                        'productName' => $inv->product->name,
                        'stock'       => $stock_onhand,
                        'min'         => $inv->stock_min,
                        'max'         => $inv->stock_max
                    ];

                    $email_inventory = config('app.mail_inventory');
                    $emailCC         = explode(",",$location->email);
                    $msgData = array(
                        'title'         => 'Informasi Stock Urgent Order',
                        'emailCC'       => $emailCC,
                        'no_ttb'        => $ttb->doc_no,
                        'email'         => $location->email,
                        'name'          => $location->name,
                        'product'       => $productUrgentStock
                    );
                }

            }
            InventoryTtbItem::insert($dataTTB);

            if ($request->get('status')==1) {
                $ids        = implode(',', $ids);
                $update     = implode(' ', $update);
                $out        = implode(' ', $out);
                $status     = implode(' ', $status);

                \DB::update("UPDATE inventories SET stock_onhand = CASE {$update} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");

                InventoryHistory::insert($invHistory);
            }

            DB::commit();
            return redirect()->route('logistic.ttb.show',Hashids::encode($ttb->id))->with(['success' => 'Berhasil melakukan TTB!']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $id = Hashids::decode($id);
        $ttb      = InventoryTtb::getById($id['0']);
        $ttb_items= InventoryTtbItem::getByTtbId($ttb->id);
        return view('logistic.ttb.show', compact('ttb','ttb_items'));
    }


    public function edit($id, Request $request)
    {

        $id = Hashids::decode($id);
        $ttb      = InventoryTtb::findOrFail($id['0']);
        $companyID = getDataByID('locations',$ttb->location_id)->company_id;
        $ttb_items= InventoryTtbItem::getByTtbId($ttb->id);

        if(isAdministrator()){
            $department  = DB::table('departments')
                    ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                    ->leftjoin('companies','companies.id','=','departments.company_id')
                    ->where('departments.status','=',1)
                    ->where('departments.company_id','=',$companyID)
                    ->orderBy('name','ASC')
                    ->get()
                    ->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->where('departments.status','=',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $project = DB::table('projects')->whereNull('deleted_at')->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $param = $request->get('param');
        if(isset($param) && $param !='revision') abort(404);
        if($param =='revision') return view('logistic.ttb.revision', compact('ttb','ttb_items','department','project','param'));
        else return view('logistic.ttb.edit', compact('ttb','ttb_items','department','project','param'));
    }


    public function update(Request $request, $id)
    {
        $ttb = InventoryTtb::findOrFail($id);

        DB::beginTransaction();

        try {

            if ($request->get('status')==1) {
                $increment = DB::table('inventory_ttbs')
                ->whereYear("created_at", date('Y'))
                ->where('is_local', false)
                ->where('status', '!=', 0)
                ->where('location_id', $ttb->location_id)
                ->count();

                $num = sprintf("%'.05d", $increment + 1) ;
                $doc_no     = explode('-',$ttb->doc_no);
                $location   = $doc_no['2'];
                $company    = $doc_no['1'];
                $data['doc_no'] = "TTB-".$company."-".$location."-".date('my')."-".$num;
                $data['status']  = 1;
                $data['publish'] = date('Y-m-d');
            }else{
                $data['status'] = 0;
            }

            $data['operator']       = $request->get('operator');
            $data['received']       = $request->get('received');
            $data['date_transaction']  = $request->get('date_transaction');
            $data['project_id']     = $request->get('project_id');
            $data['department_id']  = $request->get('department_id');

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $name = 'ttb_'.time();
                $folder = '/uploads/inventory/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $data['file'] = $filePath;
            }
            $ttb->update($data);

            $location  = DB::table('locations')
            ->select('locations.name AS name','locations.alias AS code','companies.alias AS companyCode','locations.email AS email')
            ->leftJoin('companies','companies.id','=','locations.company_id')
            ->where("locations.id", $request->get('location_id'))->first();

            InventoryTtbItem::where('inventory_ttb_id', $ttb->id)->delete();

            $dataTTB = $ids = $update = $out = $productUrgentStock = [];
            $product = $request->get('inv_id');

            for($i=0;$i < count($product);$i++) {
                $inv = Inventory::find($request->get('inv_id')[$i]);
                $dataTTB[] = [
                    'inventory_ttb_id'  => $ttb->id,
                    'inventory_id'      => $request->get('inv_id')[$i],
                    'description'       => $request->get('notes')[$i],
                    'qty'               => $request->get('qty')[$i],
                    'qty_awal'          => $inv->stock_onhand,
                    'price'             => $inv->price,
                ];

                $stock_onhand   = $inv->stock_onhand - $request->get('qty')[$i];
                $stock_out      = $inv->out +  $request->get('qty')[$i];
                $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                $ids[]          = $request->get('inv_id')[$i];
                $out[]          = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_out";
                $update[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id')[$i]} THEN '".$stock_status."'";

                if($stock_status == 'Urgent Order'){
                    $productUrgentStock [] = [
                        'productCode' => $inv->product->code,
                        'productName' => $inv->product->name,
                        'stock'       => $stock_onhand,
                        'min'         => $inv->stock_min,
                        'max'         => $inv->stock_max
                    ];
                }

                $invHistory[] = [
                    'inventory_id'  => $request->get('inv_id')[$i],
                    'qty_out'       => $request->get('qty')[$i],
                    'qty_awal'      => $request->get('qty_stock')[$i],
                    'message'       => $ttb->doc_no,
                    'description'   => $request->get('notes')[$i],
                ];
            }

            InventoryTtbItem::insert($dataTTB);

            if ($request->get('status')==1) {

                $ids        = implode(',', $ids);
                $update     = implode(' ', $update);
                $out        = implode(' ', $out);
                $status     = implode(' ', $status);

                \DB::update("UPDATE inventories SET stock_onhand = CASE {$update} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");

                InventoryHistory::insert($invHistory);

                $email_inventory = config('app.mail_inventory');
                $emailCC  = explode(",",$email_inventory);
                $msgData = array(
                    'title'         => 'Informasi Stock Urgent Order',
                    'emailCC'       => $emailCC,
                    'no_ttb'        => $ttb->doc_no,
                    'email'         => $location->email,
                    'name'          => $location->name,
                    'product'       => $productUrgentStock
                );

                // if (config('app.mail_status')=='on' && count($productUrgentStock)) {
                //     Mail::send('emails.inv_stock', $msgData, function ($message) use ($msgData) {
                //         $message->to($msgData['email'], $msgData['name'])
                //         ->subject('Informasi Stock Urgent Order');
                //         $message->cc($msgData['emailCC'], $name = null)
                //         ->subject('Informasi Stock Urgent Order');
                //     });
                // }

            }

            DB::commit();
            return redirect()->route('logistic.ttb.show',Hashids::encode($ttb->id))->with(['success' => 'Berhasil melakukan TTB!']);

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function revision(Request $request, $id)
    {
        $ttb = InventoryTtb::findOrFail($id);

        $data['operator']       = $request->get('operator');
        $data['received']       = $request->get('received');
        $data['date_transaction']  = $request->get('date_transaction');
        $data['project_id']     = $request->get('project_id');
        $data['department_id']  = $request->get('department_id');

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = 'ttb_'.time();
            $folder = '/uploads/inventory/'.date('Y').'/'.date('M').'/';
            $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
            $this->uploadOne($file, $folder, 'public', $name);
            $data['file'] = $filePath;
        }
        $ttb->update($data);

        $location  = DB::table('locations')
        ->select('locations.name AS name','locations.alias AS code','companies.alias AS companyCode','locations.email AS email')
        ->leftJoin('companies','companies.id','=','locations.company_id')
        ->where("locations.id", $request->get('location_id'))->first();

        if($request->get('ttb_itemID')){
            $ttb_itemID = $request->get('ttb_itemID');
            for($i=0;$i < count($ttb_itemID);$i++) {
                $ids[]      = $request->get('ttb_itemID')[$i];
                $notes[]    = "WHEN id = {$request->get('ttb_itemID')[$i]} THEN '".$request->get('notes')[$i]."'";
            }
            $ids    = implode(',', $ids);
            $notes  = implode(' ', $notes);
            \DB::update("UPDATE inventory_ttb_items SET description = CASE {$notes} END WHERE id in ({$ids})");
        }

        if($request->get('inv_id_new')){

            $dataTTB = $ids = $update = $out =  $productUrgentStock = [];

            $product = $request->get('inv_id_new');
            for($i=0;$i < count($product);$i++) {
                $inv = Inventory::find($request->get('inv_id_new')[$i]);

                $dataTTB[] = [
                    'inventory_ttb_id'  => $ttb->id,
                    'inventory_id'      => $request->get('inv_id_new')[$i],
                    'description'       => $request->get('notes_new')[$i],
                    'qty'               => $request->get('qty_new')[$i],
                    'qty_awal'          => $inv->stock_onhand,
                    'price'             => $inv->price,
                ];

                $invHistoryNew[] = [
                    'inventory_id'  => $request->get('inv_id_new')[$i],
                    'qty_out'       => $request->get('qty_new')[$i],
                    'qty_awal'      => $inv->stock_onhand,
                    'message'       => $ttb->doc_no,
                    'description'   => $request->get('notes_new')[$i]
                ];

                $stock_onhand   = $inv->stock_onhand - $request->get('qty_new')[$i];
                $stock_out      = $inv->out +  $request->get('qty_new')[$i];
                $stock_status   = getStatusInventory($inv->stock_max, $inv->stock_min, $stock_onhand,'raw');

                $ids[]          = $request->get('inv_id_new')[$i];
                $out[]          = "WHEN id = {$request->get('inv_id_new')[$i]} THEN $stock_out";
                $update[]       = "WHEN id = {$request->get('inv_id_new')[$i]} THEN $stock_onhand";
                $status[]       = "WHEN id = {$request->get('inv_id_new')[$i]} THEN '".$stock_status."'";

                if($stock_status  == 'Urgent Order'){
                    $productUrgentStock [] = [
                        'productCode' => $inv->product->code,
                        'productName' => $inv->product->name,
                        'stock'       => $stock_onhand,
                        'min'         => $inv->stock_min,
                        'max'         => $inv->stock_max
                    ];
                }
            }
            InventoryTtbItem::insert($dataTTB);

            $ids        = implode(',', $ids);
            $update     = implode(' ', $update);
            $out        = implode(' ', $out);
            $status     = implode(' ', $status);

            \DB::update("UPDATE inventories SET stock_onhand = CASE {$update} END, out = CASE {$out} END, stock_status = CASE {$status} END WHERE id in ({$ids})");

            InventoryHistory::insert($invHistoryNew);

            // if (config('app.mail_status')=='on' && count($productUrgentStock)) {

            //     $email_inventory = config('app.mail_inventory');
            //     $emailCC         = explode(",",$location->email);
            //     $msgData = array(
            //         'title'         => 'Informasi Stock Urgent Order',
            //         'emailCC'       => $emailCC,
            //         'no_ttb'        => $ttb->doc_no,
            //         'email'         => $location->email,
            //         'name'          => $location->name,
            //         'product'       => $productUrgentStock
            //     );

            //     Mail::send('emails.inv_stock', $msgData, function ($message) use ($msgData) {
            //         $message->to($msgData['email'], $msgData['name'])
            //         ->subject('Informasi Stock Urgent Order');
            //         $message->cc($msgData['emailCC'], $name = null)
            //         ->subject('Informasi Stock Urgent Order');
            //     });
            // }
        }

        return redirect()->route('logistic.ttb.index')->with(['success' => 'Berhasil melakukan TTB!']);
    }


    public function delete(Request $request)
    {

        $ttb  = InventoryTtb::findOrFail($request->id);
        $data = InventoryTtbItem::getByTtbId($request->id);

        DB::beginTransaction();

        try {

            if($ttb->status == 0){
                $ttb->delete();
            }else{
                if(count($data)){

                    $ids = $stock = $in = $status = $invHistory = [];
                    foreach($data as $val){
                        $stock_onhand = $val->stock_onhand + $val->qty;
                        $stock_in = $val->in + $val->qty;
                        $stock_status = getStatusInventory($val->stock_max, $val->stock_min, $stock_onhand,'raw');

                        $ids[] = $val->inventory_id;
                        $stock[] = "WHEN id = {$val->inventory_id} THEN $stock_onhand";
                        $in[] = "WHEN id = {$val->inventory_id} THEN $stock_in";
                        $status[] = "WHEN id = {$val->inventory_id} THEN '".$stock_status."'";

                        $invHistory[] = [
                            'inventory_id'=> $val->inventory_id,
                            'qty_in' => $val->qty,
                            'qty_awal' => $val->stock_onhand,
                            'message' => 'REV-'.$ttb->doc_no,
                            'description'   => 'Reversal from '. $ttb->doc_no
                        ];
                    }

                    $ids  = implode(',', $ids);
                    $stock = implode(' ', $stock);
                    $in = implode(' ', $in);
                    $status = implode(' ', $status);

                    \DB::update('UPDATE inventories SET stock_onhand = CASE '.$stock.' END, "in" = CASE '.$in.' END, stock_status = CASE '.$status.' END WHERE id in ('.$ids.')');
                    InventoryHistory::insert($invHistory);
                }

                $ttb->update([
                    'status' => 2
                ]);
            }




            DB::commit();
            return redirect()->route('logistic.ttb.index')->with(['success' => 'Reversal Data Berhasil!']);

        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }


    }


    public function print($id, $type = null)
    {
        $id = Hashids::decode($id);
        $ttb      = InventoryTtb::getById($id['0']);
        $ttb_items= InventoryTtbItem::getByTtbId($ttb->id);

        if($type =="pdf"){
            $data['ttb'] = $ttb;
            $data['ttb_items'] = $ttb_items;

            $pdf = PDF::loadView('logistic.ttb.pdf', $data);
            $pdf->setPaper('A4', 'landscape');
            return $pdf->download($ttb->doc_no.'.pdf');
        }else{
            return view('logistic.ttb.print', compact('ttb','ttb_items'));
        }


    }


    public function search(Request $request)
    {
        $query = 'department_id='.$request->get('department_id').'&project_id='. $request->get('project_id').'&location_id='.$request->get('location_id').'&start_date='.$request->get('start_date').'&end_date='. $request->get('end_date');
        $data = $request->all();
        $search = "Cari Berdasarkan: ";

        if($request->input('department_id'))$search .= "<strong> Kapal: </strong>".getDataByID('departments',$request->input('department_id'))->name;
        if($request->input('project_id'))  $search .= "<strong> Project: </strong>".getDataByID('projects',$request->input('project_id'))->name;
        if($request->input('location_id')) $search .= "<strong> Lokasi: </strong>".getDataByID('locations',$request->input('location_id'))->name;
        if($request->input('start_date') || $request->input('end_date')) $search .= "<strong> Periode: </strong>".$request->input('start_date'). " - ". $request->input('end_date');

        if(isAdministrator()){
            $location    = DB::table('locations')
                ->selectRaw("CONCAT (locations.name,' - ', companies.alias) as name, locations.id")
                ->leftjoin('companies','companies.id','=','locations.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department  = DB::table('departments')
                ->selectRaw("CONCAT (departments.name,' - ', companies.alias) as name, departments.id")
                ->leftjoin('companies','companies.id','=','departments.company_id')
                ->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }elseif(isAdministratorCompany()){
            $location   = DB::table('locations')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }else{
            $location = DB::table('locations')->where('id', Auth::user()->location_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $department = DB::table('departments')->where('company_id', Auth::user()->company_id)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        }
        $project = DB::table('projects')->where('status',1)->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('logistic.ttb.search', compact('data','location','department','search','project','query'));
    }


    public function getJs($id)
    {
        $id = $id;
        return view('logistic.ttb.js', compact('id'));
    }


    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new TtbExport($request->get('department_id'), $request->get('location_id'),  $request->get('project_id'), $request->get('start_date'), $request->get('end_date')), 'Report-TTB-'.$date.'.xlsx');
    }


    public function remove_item($id, $param = null)
    {
        $data = InventoryTtbItem::findOrFail($id);
        if($param == 'revision'){
            $inv = Inventory::findOrFail($data->inventory_id);

            $invHistory[] = [
                'inventory_id'=> $data->inventory_id,
                'qty_in' => $data->qty,
                'qty_awal' => $inv->stock_onhand,
                'message' => 'REV-'.$data->ttb->doc_no,
                'description'   => 'Reversal from '. $data->ttb->doc_no
            ];

            $inv->update([
                'stock_onhand'  => $inv->stock_onhand + $data->qty
            ]);
            InventoryHistory::insert($invHistory);
        }
        $data->delete();
        return redirect()->back()->with(['success' => 'Delete Data Berhasil!']);
    }


}
