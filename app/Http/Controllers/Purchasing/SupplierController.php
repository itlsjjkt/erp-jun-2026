<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Models\SupplierApprovalHistory;
use App\Models\PaymentTerm;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\SupplierExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SupplierImport;
use App\Models\SupplierCategory;
use App\Models\MasterItem;

use Auth;
use File;

class SupplierController extends Controller
{

    function __construct()
    {
        $this->ppn = array(
            '0'  => 'Tidak PPN',
            '11' => 'PPN 11%',
            '12' => 'PPN 12%',
        );
    }

    public function index()
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }
        $type_user   = Auth::user()->type;
        $datAcc_user = Auth::user()->data_access;
        $idSuper     = Auth::user()->id;
        return view('purchase.supplier.index', compact('type_user', 'datAcc_user', 'idSuper'));
    }

    public function datatables()
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $result = DB::table('suppliers as s')
            ->select(
                's.*',
                DB::raw("STRING_AGG(mi.name::TEXT, ', ') AS all_category_ids"),
                'pt.name AS payment_term',
                'pm.name AS payment_method'
            )
            ->leftJoin('supplier_categories as sc', 'sc.supplier_id', '=', 's.id')
            ->leftJoin('master_items as mi', 'mi.id', '=', 'sc.category_id')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 's.payment_term')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 's.payment_method_id')
            ->whereIn('s.status', [0, 1])
            ->when(request('filter') == 'approved',  fn($q) => $q->where('s.approval_status', 2))
            ->when(request('filter') == 'pending',   fn($q) => $q->whereIn('s.approval_status', [1, 3]))
            ->when(request('filter') == 'cancelled', fn($q) => $q->where('s.approval_status', 4))
            ->when(request('filter') == 'blacklist', fn($q) => $q->where('s.is_block', 1))
            ->when(request('filter') == 'revision', fn($q) => $q->where('s.approval_status', 3))
            ->when(request('filter') == 'draft',    fn($q) => $q->where('s.approval_status', 0))
            ->groupBy(
                's.id', 's.name', 's.created_by', 's.created_at', 's.updated_by', 's.updated_at',
                's.status', 's.address', 's.block_reason', 's.is_block', 's.is_ppn',
                's.step', 's.position', 's.approval_status',
                'pt.name', 'pm.name'
            )
            ->orderBy('s.name', 'ASC')
            ->get();

        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url_show    = "<a href='".route('purchasing.suppliers.show', Hashids::encode($result->id))."' title='Show' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
                $type_user   = Auth::user()->type;
                $datAcc_user = Auth::user()->data_access;
                $idSuper     = Auth::user()->id;

                $url_cancel  = '';
                $isOwner     = (Auth::user()->id == $result->created_by);
                $isAdmin     = ($idSuper == 1);
                $canCancel   = in_array($result->approval_status, [1, 3]) && ($isOwner || $isAdmin);

                if ($canCancel) {
                    $url_cancel = "<a data-id='{$result->id}' data-name='{$result->name}' data-toggle='modal' data-target='#modalCancel' title='Batalkan Pengajuan' class='btn btn-outline text-dark'><span class='ti-na icon-lg'></span></a>";
                }

                if ($type_user == 4 && $datAcc_user == 1 || $idSuper == 1) {
                    $url_edit   = "<a href='".route('purchasing.suppliers.edit', Hashids::encode($result->id))."' title='Edit' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
                    $url_delete = "<form class='delete' action='".route('purchasing.suppliers.delete', ['id' => $result->id])."' method='POST'>".csrf_field()."<button class='btn btn-outline text-danger' title='Delete' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button></form>";
                    $url_block  = "<a data-id='$result->id' data-toggle='modal' data-target='#modalBlock' title='Blacklist Supplier' class='btn btn-outline'><span class='ti-power-off icon-lg'></span> </a>";
                    if ($idSuper == 1) {
                        return '<div class="btn-group">'.$url_show.$url_edit.$url_block.$url_cancel.$url_delete.'</div>';
                    } else {
                        return '<div class="btn-group">'.$url_show.$url_edit.$url_block.$url_cancel.'</div>';
                    }
                } else {
                    return '<div class="btn-group">'.$url_show.$url_cancel.'</div>';
                }
            })
            ->editColumn('status', function ($result) {
                if ($result->status == 1) {
                    return "<span class='badge badge-success'>Aktif</span>";
                }
                return "<span class='badge badge-danger'>Non Aktif</span>";
            })
            ->addColumn('approval_badge', function ($result) {
                switch ($result->approval_status) {
                    case 0: return "<span class='badge badge-secondary'>Draft</span>";
                    case 1: return "<span class='badge badge-warning'>Pending Approval</span>";
                    case 2: return "<span class='badge badge-success'>Approved</span>";
                    case 3: return "<span class='badge badge-danger'>Perlu Revisi</span>";
                    case 4: return "<span class='badge badge-dark'>Dibatalkan</span>";
                    default: return "-";
                }
            })
            ->editColumn('is_ppn', function ($result) {
                if ($result->is_ppn != 0) {
                    return "<div class='text-center'><span class='badge badge-primary'>PPN ".$result->is_ppn."%</span></div>";
                }
                return "<div class='text-center'><span class='badge badge-warning'>Tidak</span></div>";
            })
            ->addColumn('block', function ($result) {
                if ($result->is_block == 1) {
                    return "<span class='badge badge-danger'>Blacklist</span> <br><small>".$result->block_reason."</small>";
                }
                return "-";
            })
            ->editColumn('payment_term', function ($result) {
                return $result->payment_term ? $result->payment_term : '-';
            })
            ->editColumn('payment_method', function ($result) {
                return $result->payment_method ? $result->payment_method : '-';
            })
            ->editColumn('updated_at', function ($result) {
                return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
            })
            ->rawColumns(['action', 'status', 'block', 'is_ppn', 'payment_term', 'payment_method', 'approval_badge'])
            ->make(true);
    }

    public function create()
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }
        $category       = DB::table('master_items')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $payment_term   = DB::table('payment_terms')->orderBy('name', 'ASC')->where('status', 1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $master_items   = DB::table('master_items')->orderBy('name', 'ASC')->get()->pluck('name', 'id');
        $payment_method = DB::table('payment_methods')->orderBy('name', 'ASC')->where('status', 1)->get()->pluck('name', 'id')->prepend('Tipe Pembayaran', '');
        $currency       = DB::table('currencies')->orderBy('name', 'ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $ppn            = $this->ppn;

        return view('purchase.supplier.create', compact('category', 'payment_term', 'master_items', 'payment_method', 'currency', 'ppn'));
    }

    public function store(Request $request)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $data = $request->all();
        if ($request->get('status')) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        $data['created_by'] = Auth::user()->id;

        $firstApproval = DB::table('approval_suppliers')->where('step', 1)->first();

        if (! $firstApproval) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['Belum ada konfigurasi approval supplier. Silakan setting approval terlebih dahulu di menu Master Approval.']);
        }

        $data['approval_status'] = 1;
        $data['step']            = 1;
        $data['position']        = $firstApproval->user_id;

        $supplier = Supplier::create($data);

        if ($request->get('picName')) {
            $picName = $request->get('picName');
            $pic     = [];
            for ($i = 0; $i < count($picName); $i++) {
                $noTelp     = $request->get('picTelp')[$i];
                $noMobPhone = $request->get('picMobilePhone')[$i];
                $phone      = $noMobPhone.' || '.$noTelp;
                $pic[]      = [
                    'supplier_id' => $supplier->id,
                    'title'       => $request->get('pic_Title')[$i],
                    'name'        => $request->get('picName')[$i],
                    'telp'        => $phone,
                    'email'       => $request->get('picEmail')[$i],
                ];
            }
            SupplierContact::insert($pic);
        }

        $catIds = $request->get('master_items', []);
        $catt   = [];
        foreach ($catIds as $catId) {
            $catt[] = [
                'category_id' => $catId,
                'supplier_id' => $supplier->id,
            ];
        }
        SupplierCategory::insert($catt);

        Notification::create([
            'title'   => 'Approval Supplier',
            'link'    => '/approval/supplier_set/'.Hashids::encode($supplier->id),
            'data_id' => $supplier->id,
            'content' => 'Terdapat pengajuan supplier baru: '.$supplier->name,
            'user_id' => $firstApproval->user_id,
            'status'  => 0,
        ]);

        return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Supplier berhasil ditambahkan dan menunggu approval!']);
    }

    public function edit($id)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $id             = Hashids::decode($id);
        $supplier       = Supplier::findOrFail($id['0']);
        $pic            = SupplierContact::where('supplier_id', $id['0'])->get();
        $separatedTelp  = [];

        foreach ($pic as $item) {
            $telp = $item->telp;
            if (strpos($telp, '||') !== false) {
                $parts           = explode('||', $telp);
                $separatedTelp[] = ['telp1' => $parts[0], 'telp2' => $parts[1]];
            } else {
                $separatedTelp[] = ['telp1' => $telp, 'telp2' => ''];
            }
        }

        $category          = DB::table('master_items')->get()->pluck('name', 'id');
        $payment_term      = DB::table('payment_terms')->where('status', 1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $payment_method_id = DB::table('payment_methods')->orderBy('name', 'ASC')->where('status', 1)->get()->pluck('name', 'id')->prepend('Tipe Pembayaran', '');
        $currency          = DB::table('currencies')->orderBy('name', 'ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');

        $title    = ['Bapak', 'Ibu', 'Bapak/Ibu', 'Mr', 'Mrs', 'Mr/Mrs'];
        $picTitle = [];
        foreach ($title as $val) {
            $picTitle[$val] = $val;
        }

        $master_items = DB::table('master_items')->orderBy('name', 'ASC')->get()->pluck('name', 'id');
        $defaultItems = DB::table('master_items')
            ->select('master_items.id as id')
            ->join('supplier_categories', 'master_items.id', '=', 'supplier_categories.category_id')
            ->where('supplier_categories.supplier_id', $supplier->id)
            ->get();

        $default_ids = [];
        if ($defaultItems->count() > 0) {
            foreach ($defaultItems as $item) {
                $default_ids[] = $item->id;
            }
        }

        $ppn = $this->ppn;

        return view('purchase.supplier.edit', compact(
            'category', 'supplier', 'payment_term', 'pic', 'picTitle',
            'master_items', 'default_ids', 'separatedTelp', 'payment_method_id', 'currency', 'ppn'
        ));
    }

    public function update(Request $request, $id)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $data = $request->all();
        if ($request->get('status')) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }

        $data['updated_by'] = Auth::user()->id;
        $supplier           = Supplier::findOrFail($id);

        // Jika sedang revisi, kembalikan ke pending approval step 1
        if ($supplier->approval_status == 3) {
            $firstApproval = DB::table('approval_suppliers')->where('step', 1)->first();

            if ($firstApproval) {
                $data['approval_status'] = 1;
                $data['step']            = 1;
                $data['position']        = $firstApproval->user_id;

                Notification::create([
                    'title'   => 'Approval Supplier',
                    'link'    => '/approval/supplier_set/'.Hashids::encode($supplier->id),
                    'data_id' => $supplier->id,
                    'content' => 'Supplier '.$supplier->name.' telah diperbaiki dan menunggu approval ulang.',
                    'user_id' => $firstApproval->user_id,
                    'status'  => 0,
                ]);
            }
        }

        $supplier->update($data);

        if ($request->get('picID')) {
            $picID = $request->get('picID');
            $ids   = [];
            $name  = [];
            $telp  = [];
            $email = [];
            $title = [];
            for ($i = 0; $i < count($picID); $i++) {
                $picTelp     = $request->get('picTelp')[$i];
                $picMobPhone = $request->get('picMobPhone')[$i];
                $phoneee     = $picMobPhone.' || '.$picTelp;
                $ids[]       = $request->get('picID')[$i];
                $name[]      = "WHEN id = {$request->get('picID')[$i]} THEN '".$request->get('picName')[$i]."'";
                $telp[]      = "WHEN id = {$request->get('picID')[$i]} THEN '".$phoneee."'";
                $email[]     = "WHEN id = {$request->get('picID')[$i]} THEN '".$request->get('picEmail')[$i]."'";
                $title[]     = "WHEN id = {$request->get('picID')[$i]} THEN '".$request->get('picTitle')[$i]."'";
            }
            $ids   = implode(',', $ids);
            $name  = implode(' ', $name);
            $telp  = implode(' ', $telp);
            $email = implode(' ', $email);
            $title = implode(' ', $title);
            \DB::update("UPDATE supplier_contacts SET name = CASE {$name} END, title = CASE {$title} END, telp = CASE {$telp} END, email = CASE {$email} END WHERE id in ({$ids})");
        }

        DB::table('supplier_categories')->where('supplier_id', '=', $supplier->id)->delete();

        if ($request->get('pic_Name')) {
            $picName = $request->get('pic_Name');
            $pic     = [];
            for ($i = 0; $i < count($picName); $i++) {
                $picTelp     = $request->get('pic_Telp')[$i];
                $picMobPhone = $request->get('pic_MobPhone')[$i];
                $phoneee     = $picMobPhone.' || '.$picTelp;
                $pic[]       = [
                    'supplier_id' => $supplier->id,
                    'title'       => $request->get('pic_Title')[$i],
                    'name'        => $request->get('pic_Name')[$i],
                    'telp'        => $phoneee,
                    'email'       => $request->get('pic_Email')[$i],
                ];
            }
            SupplierContact::insert($pic);
        }

        $catId = $request->get('master_items');
        $catt  = [];
        for ($i = 0; $i < count($catId); $i++) {
            $catt[] = [
                'category_id' => $request->get('master_items')[$i],
                'supplier_id' => $supplier->id,
            ];
        }
        SupplierCategory::insert($catt);

        return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Edit was successful!']);
    }

    public function show($id)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $id           = Hashids::decode($id);
        $dataSupplier = DB::table('suppliers as s')
            ->select('s.*', 'p.name AS p_payment_term', 'payment_methods.name AS payment_method')
            ->leftjoin('payment_terms as p', 'p.id', '=', 's.payment_term')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 's.payment_method_id')
            ->where('s.id', $id)
            ->first();

        $dataPIC = DB::table('supplier_contacts as scc')
            ->select('scc.*')
            ->where('scc.supplier_id', $id)
            ->get();

        $separatedTelp = [];
        foreach ($dataPIC as $item) {
            $telp = $item->telp;
            if (strpos($telp, '||') !== false) {
                $parts           = explode('||', $telp);
                $separatedTelp[] = ['telp1' => $parts[0], 'telp2' => $parts[1]];
            } else {
                $separatedTelp[] = ['telp1' => $telp, 'telp2' => '-'];
            }
        }

        $dataCategory = DB::table('supplier_categories as sc')
            ->select('sc.*', 'm.name as nameCategory')
            ->where('sc.supplier_id', $id)
            ->leftJoin('master_items as m', 'm.id', '=', 'sc.category_id')
            ->get();

        $approvalHistory = DB::table('supplier_approval_histories as h')
            ->select('h.*', 'u.name as user_name')
            ->leftJoin('users as u', 'u.id', '=', 'h.user_id')
            ->where('h.supplier_id', $id[0])
            ->orderBy('h.created_at', 'DESC')
            ->get();

        return view('purchase.supplier.show', compact('dataSupplier', 'dataPIC', 'dataCategory', 'separatedTelp', 'approvalHistory'));
    }

    public function blacklist(Request $request)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $data               = $request->all();
        $data['is_block']   = 1;
        $data['status']     = 0;
        $data['updated_by'] = Auth::user()->id;
        $items              = Supplier::findOrFail($request->id);
        $items->update($data);

        return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Blacklist was successful!']);
    }

    public function delete(Request $request)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $items          = Supplier::findOrFail($request->id);
        $data['status'] = 3;
        $items->update($data);

        return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Delete was successful!']);
    }

    public function cancel(Request $request, $id)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $supplier = Supplier::findOrFail($id);
        $isOwner  = ($supplier->created_by == Auth::user()->id);
        $isAdmin  = (Auth::user()->id == 1);

        if (! $isOwner && ! $isAdmin) {
            return abort(403);
        }

        if (! in_array($supplier->approval_status, [1, 3])) {
            return redirect()->back()->withErrors(['Pengajuan tidak dapat dibatalkan karena statusnya bukan Pending atau Revisi.']);
        }

        $supplier->update([
            'approval_status' => 4,
            'status'          => 0,
            'position'        => null,
            'updated_by'      => Auth::user()->id,
        ]);

        SupplierApprovalHistory::create([
            'supplier_id'   => $supplier->id,
            'user_id'       => Auth::user()->id,
            'jenis'         => 'cancel',
            'message'       => $request->get('message', 'Pengajuan dibatalkan.'),
            'date_approved' => now(),
        ]);

        return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Pengajuan supplier berhasil dibatalkan.']);
    }

    public function remove_pic(Request $request)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $po = DB::table('po')->where('supplier_contact_id', $request->get('id'))->get()->count();

        if ($po > 0) {
            return redirect()->back()->withErrors(['PIC telah digunakan dalam pembuatan PO sebelumnya (PIC GAGAL DIHAPUS)']);
        } else {
            $supplier = SupplierContact::where('id', $request->get('id'));
            $supplier->delete();
            return redirect()->back()->with(['success' => 'Delete Data Berhasil!']);
        }
    }

    public function pic($id)
    {
        if (! Gate::allows('supplier')) {
            return abort(401);
        }

        $id       = Hashids::decode($id);
        $pic      = DB::table('supplier_contacts')->where('supplier_contacts.supplier_id', $id['0'])->get();
        $supplier = Supplier::findOrFail($id['0']);

        return view('purchase.supplier.pic', compact('pic', 'supplier'));
    }

    public function getCategory($id)
    {
        return DB::table('supplier_categories')
            ->select('master_items.name as category')
            ->leftJoin('master_items', 'master_items.id', '=', 'supplier_categories.category_id')
            ->where('supplier_categories.supplier_id', $id)
            ->get();
    }

    public function getSupplier($pid, $category)
    {
        return Supplier::where('city_id', $pid)
            ->where('category_id', $category)
            ->where('is_block', 0)
            ->pluck('name', 'id');
    }

    public function loadData(Request $request)
    {
        if ($request->has('q')) {
            $data = DB::table('suppliers')
                ->select('suppliers.*')
                ->where('name', 'ilike', '%'.$request->q.'%')
                ->where('status', 1)
                ->where('approval_status', 2)
                ->get();

            $result = array();
            foreach ($data as $val) {
                $result[] = array(
                    'id'                => $val->id,
                    'name'              => $val->name,
                    'is_ppn'            => $val->is_ppn,
                    'is_payment'        => $val->payment_term,
                    'is_currency'       => $val->currency,
                    'is_payment_method' => $val->payment_method_id,
                );
            }
            return response()->json($result);
        }
    }

    public function getPaymentTerm()
    {
        return PaymentTerm::where('status', 1)->pluck('name', 'id');
    }

    public function getPaymentMethod()
    {
        return DB::table('payment_methods')->where('status', 1)->pluck('name', 'id');
    }

    public function getSupplierContactDetail($id)
    {
        $result = SupplierContact::where('id', $id)->first();
        return response()->json($result);
    }

    public function getSupplierContact($supplier_id)
    {
        return SupplierContact::where('supplier_id', $supplier_id)->pluck('name', 'id');
    }

    public function export(Request $request)
    {
        $date = date('Y-m-d');
        return Excel::download(new SupplierExport(), 'Report-SupplierData-'.$date.'.xlsx');
    }

    public function import(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('purchase.supplier.import');
        } else {
            $this->validate($request, array('file' => 'required'));

            if ($request->hasFile('file')) {
                $extension = File::extension($request->file->getClientOriginalName());
                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                    $file   = $request->file('file');
                    $userID = Auth::user()->id;
                    try {
                        Excel::import(new SupplierImport(), $file);
                    } catch (\Exception $e) {
                        return redirect()->back()->withInput($request->input())->withErrors($e->getMessage());
                    }
                    return redirect()->route('purchasing.suppliers.index')->with(['success' => 'Success inserting the data..']);
                } else {
                    return redirect()->route('purchasing.suppliers.index')->with(['error' => ' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
                }
            }
        }
    }

    public function counts()
    {
        return response()->json([
            'all'       => DB::table('suppliers')->whereIn('status', [0,1])->count(),
            'approved'  => DB::table('suppliers')->whereIn('status', [0,1])->where('approval_status', 2)->count(),
            'pending'   => DB::table('suppliers')->whereIn('status', [0,1])->where('approval_status', 1)->count(),
            'revision'  => DB::table('suppliers')->whereIn('status', [0,1])->where('approval_status', 3)->count(),
            'draft'     => DB::table('suppliers')->whereIn('status', [0,1])->where('approval_status', 0)->count(),
            'cancelled' => DB::table('suppliers')->whereIn('status', [0,1])->where('approval_status', 4)->count(),
            'blacklist' => DB::table('suppliers')->where('is_block', 1)->count(),
        ]);
    }
}
