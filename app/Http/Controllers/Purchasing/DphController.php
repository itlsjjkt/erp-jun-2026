<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\DphSupplier;
use App\Models\Supplier;
use App\Models\PurchaseRequisition;
use App\Models\Workarea;
use App\Models\Dph;
use App\Models\DphItem;
use App\Models\DphHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DphPrint;
use Auth;
use PDF;

class DphController extends Controller
{
    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:dph', ['only' => ['index','create','destroy','edit','show','datatables','close','revision','add_supplier','add_store','delete_supplier']]);
        $this->ppn = array(
            '0' => 'Tidak PPN',
            '11' => 'PPN 11%',
            '12' => 'PPN 12%',
        );
        $this->send_expense_ppn = array(
            '0' => 'Tidak PPN',
            '1' => 'PPN',
        );
        $this->discount_item = array(
            '0' => 'Tidak',
            '1' => 'Ya',
        );
        $this->is_item_ppn = array(
            '0' => 'Tidak Include PPN',
            '1' => 'Include PPN',
        );
    }
    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('purchase.dph.index');
    }

    public function datatables(Request $request)
    {
        $data = $request->all();

        if(isAdministrator()  || Auth::user()->data_access == 1) $result = Dph::getData($data);
        else $result  = Dph::getData($data, Auth::user()->id);

        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            // Encode the ID using Hashids
            $encodedId = Hashids::encode($result->id);

            // Generate the view button URL
            $url_view = "<a href='" . route('purchasing.dph.show', $encodedId) . "' title='" . trans('app.show_title') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span></a>";
            $url_view_edit = "<a href='" . route('purchasing.dph.show_edit', $encodedId) . "' title='" . ($result->status == 1 ? 'Edit DPH' : 'Revisi DPH') . "' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil-alt icon-lg'></span></a>";
            $url_approved     = "<form class='update' action='".route('purchasing.dph.toApproval', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline' title='Lanjutkan Ke Approval DPH' data-toggle='tooltip'><i class='ti-new-window text-primary icon-lg'></i></button>
                            </form>";

            $url_cancel     = "<form class='update' action='".route('purchasing.dph.cancel', ['id' => $result->id])."' method='POST'>
                            ".csrf_field()."
                            <button class='btn btn-outline' title='Cancel DPH' data-toggle='tooltip'><i class='ti-power-off text-danger icon-lg'></i></button>
                        </form>";

            // Generate the print button form
            $url_print = "<form action='" . route('purchasing.dph.print') . "' method='GET' style='display:inline;'>";
            $url_print .= "<input type='hidden' name='dph_id' value='" . $result->id . "'>";
            $url_print .= "<button type='submit' class='btn btn-outline float-right' title='Print Data'>";
            $url_print .= "<i class='ti-printer icon-lg'></i>";
            $url_print .= "</button>";
            $url_print .= "</form>";

            // Combine both buttons into a div with btn-group class
            if ($result->status == 2) {
                // Jika status 2
                $buttons = $url_view . ' ' . $url_print . ' ' . $url_cancel;
            } elseif ($result->status == 1 || $result->status == 3) {
                // Jika status 1 atau 3
                $buttons = $url_view . ' ' .$url_view_edit . ' ' . $url_print . ' ' . $url_cancel . ' ' . $url_approved;
            } else {
                // Untuk status lainnya
                $buttons = $url_view . ' ' . $url_print;
            }
            // Cek apakah pengguna memiliki hak akses 'purchase_admin'
            if (auth()->user()->can('purchase_admin') || $result->created_by == Auth::user()->id) {
                $out = '<div class="btn-group">' . $buttons . '</div>';
            } else {
                $out = '<div class="btn-group">' . $url_view . ' ' . $url_print . '</div>';
            }

            return $out;
        })
        ->editColumn('doc_no', function ($result) {
            $doc_no = "<a target='_blank' href='".route('purchasing.dph.show',Hashids::encode($result->id))."' title='Detail DPH' data-toggle='tooltip' ";
            $doc_no .= ">".$result->doc_no."</a>";
            return $doc_no;
        })
        ->editColumn('no_pr', function ($result) {
            $no_pr = "<a target='_blank' href='".route('purchasing.pr.show',Hashids::encode($result->id_pr))."' title='Detail PR' data-toggle='tooltip' ";
            $no_pr .= ">".$result->no_pr."</a>";
            return $no_pr;
        })
        ->editColumn('status', function ($result) {
            return getStatusDPH($result->status);
        })
        ->editColumn('created_at', function ($result) {
            return $result->created_at ? with(new Carbon($result->created_at))->format('d-M-Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status','doc_no','doc_no','no_pr'])
        ->make(true);
    }
    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_list_item(Request $request)
    {
        $id       = Hashids::decode($request->get('id'));
        $pr       = PurchaseRequisition::getByID($id['0']);
        $workarea = Workarea::findOrFail($pr->location_id);
        $approval = getApprovalDph($workarea->company_id,1);
        if ($approval) {
            if(Auth::user()->data_access==1) $pr_items = PurchaseRequisition::getProductItem($pr->id);
            else $pr_items = PurchaseRequisition::getProductItem($pr->id,Auth::user()->id);
            return view('purchase.dph.create_list_item', compact('pr','pr_items'));
        }else{
            return redirect()->back()->withInput($request->input())->withErrors(['Approval Rule tidak ditemukan']);
        }
    }

    public function create(Request $request)
    {
        $id = $request->get('pr_id');
        $id_item = [];
        $item = $request->get('iscreateDPH');
        for ($i = 0; $i < count($item); $i++) {
            $id_item[] = $item[$i];
        }
        $count_form = $request->get('count_form');
        $pr       = PurchaseRequisition::getByID($id);
        $workarea = Workarea::findOrFail($pr->location_id);
        $approval = getApprovalDph($workarea->company_id,1);
        if ($approval) {
            if(Auth::user()->data_access==1) $pr_items = Dph::getItemCreate($pr->id,$id_item);
            else $pr_items = Dph::getItemCreate($pr->id,$id_item,Auth::user()->id);
            $discount_type = array(
                '1' => 'Percent',
                '0' => 'Currency'
            );
            $currency               = DB::table('currencies')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
            $payment_term           = DB::table('payment_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $po_term                = DB::table('po_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $po_note                = DB::table('po_notes')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
            $payment_method         = DB::table('payment_methods')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Tipe Pembayaran', '');
            $price_term             = DB::table('price_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
            $price_term_location    = DB::table('cities')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
            $po                     = Dph::getByID($id['0']);
            $ppn                    = $this->ppn;
            $send_expense_ppn       = $this->send_expense_ppn;
            $discount_item          = $this->discount_item;
            $is_item_ppn            = $this->is_item_ppn;

            return view('purchase.dph.create', compact('ppn','send_expense_ppn','discount_item','is_item_ppn','payment_method','payment_term','price_term','po_term','pr','pr_items','currency','price_term_location','discount_type','po_note','po','count_form'));
        }else{
            return redirect()->back()->withInput($request->input())->withErrors(['Approval Rule tidak ditemukan']);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Menentukan nomor DPH
            $approval = getApprovalDph($request->get('company_id'), 1);

            if($approval){
                $no_dph = $this->generateDphNumber($request);
                // Data DPH
                $data = [
                    'company_id' => $request->get('company_id'),
                    'purchase_id' => $request->get('purchase_id'),
                    'doc_no' => $no_dph,
                    'created_by' => Auth::user()->id,
                    'status' => $request->get('status') == 1 ? 1 : 0,
                    'publish' => $request->get('status') == 1 ? date('Y-m-d H:i:s') : null,
                    'step' => 1,
                    'position'=> $approval->user_id,
                    'notes' => $request->get('notes_dph'),
                ];

                // Buat DPH
                $dph = Dph::create($data);

                $dataHistory = [
                    'dph_id' => $dph->id,
                    'jenis' => 'create',
                    'message' => 'Melakukan pembuatan dokumen DPH',
                    'user_id' => Auth::user()->id ,
                ];
                // Buat DPH History
                $dph_history = DphHistory::create($dataHistory);

                $dataSupplier = $this->prepareSupplierData($request, $dph->id);
                DphSupplier::insert($dataSupplier);

                // Ambil semua ID supplier yang baru saja dimasukkan
                $supplier_ids = DphSupplier::where('dph_id', $dph->id)->pluck('id')->toArray();
                $product_ids = $request->get('product_id');

                // Pastikan ada supplier dan produk
                $supplier_count = count($supplier_ids);
                $product_count = count($product_ids);

                // Hitung jumlah item per supplier
                $items_per_supplier = ($supplier_count > 0) ? ($product_count / $supplier_count) : 0;

                // Mengaitkan produk dengan supplier berdasarkan urutan
                $dataItem = $this->prepareItemData($request, $product_ids, $supplier_ids, $items_per_supplier);

                DphItem::insert($dataItem);

                DB::commit();
                return redirect()->route('purchasing.dph.show', Hashids::encode($dph->id))->with(['success' => 'Pembuatan Data DPH Berhasil!']);
            }else{
                return redirect()->back()
                ->withInput($request->input())
                ->withErrors(['Approval Rule tidak ditemukan']);
            }

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Fungsi untuk menghasilkan nomor DPH
    private function generateDphNumber($request)
    {
        if ($request->get('status') == 1) {
            $increment = DB::table('dph')
                ->whereYear("publish", date('Y'))
                ->where('status', '!=', 0)
                ->where('company_id', $request->get('company_id'))
                ->count();
            $num = sprintf("%'.05d", $increment + 1);
            return "DPH-" . $request->get('company_code') . "-JKT-" . date('my') . "-" . $num;
        } else {
            return "DPH-" . $request->get('company_code') . "-JKT-" . date('my') . "-DRAFT";
        }
    }
    // Fungsi untuk mempersiapkan data supplier
    private function prepareSupplierData($request, $dphId)
    {

        $dataSupplier = [];
        $count_loop = $request->get('count_loop');
        for ($i = 0; $i < $count_loop; $i++) {
            $type = ($request->get('price_term')[$i] == 'FRANCO' && $request->get('price_term_location')[$i] != 'JAKARTA') ? 'non_lpb' : 'lpb';
            $dp = DB::table('payment_terms')
                ->where("id", $request->get('payment_term_id')[$i])
                ->first();
            $discount_amount = $request->get('discount_amount')[$i] ?? 0;
            $mr_file = null;
            if ($request->hasFile('mr_file') && isset($request->file('mr_file')[$i])) {
                $file = $request->file('mr_file')[$i];
                $name = 'DPH-'.$request->get('supplier_id')[$i].time();
                $folder = '/uploads/dph_supplier/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $mr_file = $filePath;
            }

            $payment_method = DB::table('payment_methods')->select('payment_methods.*')->where('payment_methods.id','=',$request->get('payment_method')[$i])->first();
            $paymentMethod = $payment_method ? $payment_method->name : 'BANK TRANSFER';

            $dataSupplier[] = [
                // step position
                'dph_id' => $dphId ?? null,
                'supplier_id' => $request->get('supplier_id')[$i] ?? null,
                'discount_type' => $request->get('discount_type')[$i] ?? null,
                'discount_amount' => floatval(str_replace(",", "", $discount_amount))?? null,
                'payment_term_id' => $request->get('payment_term_id')[$i] ?? null,
                'po_term_id' => $request->get('po_term_id')[$i] ?? null,
                'price_term' => $request->get('price_term')[$i] ?? null,
                'price_term_location' => $request->get('price_term_location')[$i] ?? null,
                'status' => 0,
                'send_expense' => floatval(str_replace(",", "", $request->get('send_expense')[$i])) ?? 0,
                'supplier_contact_id' => $request->get('supplier_contact_id')[$i] ?? null,
                'type' => $type ?? null,
                'estimated_delivery_day' => $request->get('estimated_delivery_day')[$i] ?? 0,
                'dp_percentage' => $dp->dp_percentage ?? 0,
                'send_expense_ppn' => ($request->get('send_expense_ppn')[$i] ?? 0) == 1 ? 11 : 0,
                'discount_item' => $request->get('discount_item')[$i],
                'ppn' => $request->get('ppn')[$i]?? 0,
                'currency' => ($request->get('currency')[$i]) ?? null,
                'created_by' => Auth::user()->id,
                'file'=> $mr_file,
                'payment_method' => $paymentMethod,
            ];
        }

        return $dataSupplier;
    }
    //Fungsi Untuk mempersialkan data item
    private function prepareItemData($request, $product_ids, $supplier_ids, $items_per_supplier)
    {
        $dataItem = [];
        $supplier_subtotals = array_fill_keys($supplier_ids, 0);

        foreach ($product_ids as $index => $product_id) {
            $supplier_index = intval($index / ($items_per_supplier > 0 ? $items_per_supplier : 1));
            if ($supplier_index < count($supplier_ids)) {
                $qty = $request->get('qty')[$index];
                $price = floatval(str_replace(",", "", $request->get('price')[$index]));
                $supplier_id = $supplier_ids[$supplier_index];
                $supplier_subtotals[$supplier_id] += $qty * $price;
            }
        }

        foreach ($product_ids as $index => $product_id) {

            $supplier_index = intval($index / ($items_per_supplier > 0 ? $items_per_supplier : 1));

            if ($supplier_index < count($supplier_ids)) {
                $qty = $request->get('qty')[$index];
                $price = floatval(str_replace(",", "", $request->get('price')[$index]));

                $dbsup = DphSupplier::findOrFail($supplier_ids[$supplier_index]);
                $discAmount = 0;
                if ($dbsup->discount_type == 0) {
                    $discAmount = $dbsup->discount_amount;
                }
                $price_discount = round(
                    $price
                    - ($price * floatval(str_replace(",", "", $request->get('diskon_item')[$index])) / 100)
                    - ($discAmount / (($supplier_subtotals[$supplier_ids[$supplier_index]] != 0) ? $supplier_subtotals[$supplier_ids[$supplier_index]] : 1) * (($price != 0) ? $price : 1))
                );

                $i_ = $supplier_index + 1;
                $recomendation_key = "{$i_}{$request->get('pr_item_id')[$index]}";

                $dataItem[] = [
                    'dph_supplier_id' => $supplier_ids[$supplier_index] ?? null,
                    'product_id' => $product_id ?? null,
                    'qty' => $qty ?? 0,
                    'price' => $price ?? 0,
                    'status' => 1,
                    'pr_item_id' => $request->get('pr_item_id')[$index] ?? null,
                    'measure' => $request->get('measure_id')[$index] ?? null,
                    'qty_parsial' => 0,
                    'specification' => $request->get('specification')[$index] ?? null,
                    'discount' => $request->get('diskon_item')[$index] ?? 0,
                    'price_discount' => $price_discount ?? 0,
                    'is_recomendation' => $request->get('is_recomendation')[$recomendation_key] ? 1 : 0,
                ];
            }
        }
        return $dataItem;
    }

    public function edit($id)
    {
        $id = Hashids::decode($id);
        $suppliers = DphSupplier::findOrFail($id['0']);

        $currency               = DB::table('currencies')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $payment_term           = DB::table('payment_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $po_term                = DB::table('po_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $payment_method         = DB::table('payment_methods')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $price_term             = DB::table('price_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $price_term_location    = DB::table('cities')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $measure                = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'name');
        $po_note                = DB::table('po_notes')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $suppliers2             = DphSupplier::getByID($suppliers->id);
        $ppn                    = $this->ppn;
        $send_expense_ppn       = $this->send_expense_ppn;
        $discount_item          = $this->discount_item;
        $is_item_ppn            = $this->is_item_ppn;
        $discount_type = array(
            '1' => 'Percent',
            '0' => 'Currency'
        );
        return view('purchase.dph.edit', compact('suppliers2','suppliers','ppn','send_expense_ppn','discount_item','is_item_ppn','measure','payment_method','payment_term','price_term','po_term','currency','price_term_location','discount_type','po_note'));
    }

    public function update(Request $request)
    {
        $dph_supplier = DphSupplier::findOrFail($request->get('dph_supplier_id'));
        DB::beginTransaction();
        try {
            $data = $request->all();
            $approval = getApprovalDph($dph_supplier->company_id,1);
            $dp = DB::table('payment_terms')
                ->where("id", $request->get('payment_term_id'))
                ->first();
            $mr_file = null;
            if ($request->hasFile('mr_file')) {
                $file = $request->file('mr_file');
                $name = 'DPH-'.$request->get('supplier_id').time();
                $folder = '/uploads/dph_supplier/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $mr_file = $filePath;
            }

            $data['supplier_id'] = $request->get('supplier_id') ?? null;
            $data['discount_type'] = $request->get('discount_type') ?? null;
            $data['discount_amount'] = floatval(str_replace(",", "", $request->get('discount_amount') ?? 0))?? null;
            $data['payment_term_id'] = $request->get('payment_term_id') ?? null;
            $data['po_term_id'] = $request->get('po_term_id') ?? null;
            $data['price_term'] = $request->get('price_term') ?? null;
            $data['price_term_location'] = $request->get('price_term_location') ?? null;
            $data['status'] = 0;
            $data['send_expense'] = floatval(str_replace(",", "", $request->get('send_expense'))) ?? 0;
            $data['supplier_contact_id'] = $request->get('supplier_contact_id') ?? null;
            $data['type'] = ($request->get('price_term') == 'FRANCO' && $request->get('price_term_location') != 'JAKARTA') ? 'non_lpb' : 'lpb';
            $data['dp_percentage'] = $dp->dp_percentage ?? 0;
            $data['send_expense_ppn'] = ($request->get('send_expense_ppn') ?? 0) == 1 ? 11 : 0;
            $data['discount_item'] = $request->get('discount_item');
            $data['ppn'] = $request->get('ppn') ?? 0;
            $data['updated_by'] = Auth::user()->id;
            $data['currency'] = $request->get('currency') ?? null;
            $data['estimated_delivery_day'] = $request->get('estimated_delivery_day') ?? 0;
            $data['file'] = $mr_file ?? $request->get('mr_file_hidden') ?? null;

            $payment_method = DB::table('payment_methods')->select('payment_methods.*')->where('payment_methods.id','=',$request->get('payment_method'))->first();
            $paymentMethod = $payment_method ? $payment_method->name : 'BANK TRANSFER';

            $data['payment_method'] = $paymentMethod ?? 'BANK TRANSFER';

            $dph_supplier->update($data);

            $dataDPH = DB::table('dph_suppliers')->select('dph.*')->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')->where('dph_suppliers.id','=',$dph_supplier->id)->first();
            $datamasterSupplier = DB::table('dph_suppliers')->select('suppliers.*')->leftJoin('suppliers','suppliers.id','=','dph_suppliers.supplier_id')->where('suppliers.id','=',$dph_supplier->supplier_id)->first();
            $dataHistory = [
                'dph_id' => $dph_supplier->dph_id,
                'jenis' => 'update',
                'message' => 'Melakukan pembaruan data supplier '.$datamasterSupplier->name,
                'user_id' => Auth::user()->id,
            ];
            // Edit DPH History
            $dph_history = DphHistory::create($dataHistory);

            $po_ids = $po_qty = $po_specification = $po_price = $po_diskon = $po_status = [];
            $product = $request->get('product_id');

            $discount_item = 0;
            if ($request->get('discount_item')=='1') {
                $discountTotal = floatval(str_replace(",", "", $request->get('discount_amount')));
                $discount_item = $discountTotal/count($product);
            }
            for($i=0;$i < count($product);$i++) {
                if ($request->get('is_item_ppn')==1) {
                    $price      = str_replace(",", "", $request->get('price')[$i]);
                    $item_price  = ($price * 100/111);
                } else {
                    $item_price  = str_replace(",", "", $request->get('price')[$i]);
                }

                $subTotal = 0;
                for($j=0;$j < count($request->get('product_id'));$j++){
                    $subTotal += (str_replace(",", "", $request->get('price')[$j]) * ($request->get('qty_po')[$j]));
                }

                $discAmount = ($request->get('discount_type') == '0')
                    ? str_replace(",", "", $request->get('discount_amount'))
                    : 0;
                $price_after_discount = round( $item_price
                        - ($item_price * str_replace(",", "", $request->get('diskon_item')[$i])/100)
                        - ($discAmount /
                        ($subTotal!=0?$subTotal:1) * ($item_price!=0?$item_price:1)));

                $diskon_item  = ($request->get('diskon_item')[$i]) ? $request->get('diskon_item')[$i] : 0;
                $po_ids[]               = $request->get('po_item_id')[$i];
                $po_qty[]               = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$request->get('qty_po')[$i];
                $po_specification[]     = "WHEN id = {$request->get('po_item_id')[$i]} THEN '".$request->get('specification')[$i]."'";
                $po_price[]             = "WHEN id = {$request->get('po_item_id')[$i]} THEN ". str_replace(",", "", $item_price);
                $po_price_discount[]    = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$price_after_discount;
                $po_diskon[]            = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$diskon_item;
                $po_is_recomendation[]  = "WHEN id = {$request->get('po_item_id')[$i]} THEN ".$request->get('is_recomendation')[$request->get('po_item_id')[$i]];
            }

            $po_ids             = implode(',', $po_ids);
            $po_qty             = implode(' ', $po_qty);
            $po_specification   = implode(' ', $po_specification);
            $po_price           = implode(' ', $po_price);
            $po_price_discount  = implode(' ', $po_price_discount);
            $po_diskon          = implode(' ', $po_diskon);
            $po_is_recomendation= implode(' ', $po_is_recomendation);

            \DB::update("UPDATE dph_items SET qty  = CASE {$po_qty} END, discount = CASE {$po_diskon} END, specification = CASE {$po_specification} END,  price = CASE {$po_price} END, price_discount = CASE {$po_price_discount} END, is_recomendation = CASE {$po_is_recomendation} END WHERE id in ({$po_ids})");

            DB::commit();

            return redirect()->route('purchasing.dph.show_edit', Hashids::encode($dph_supplier->dph_id))->with(['success' => 'Update Data Supplier DPH Berhasil!']);
        } catch (\Exception $e) {

            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $id = Hashids::decode($id);
        $dph   = Dph::getByID($id['0']);

        return view('purchase.dph.show', compact('dph'));
    }

    public function show_edit($id)
    {
        $id = Hashids::decode($id);
        $dph   = Dph::getByID($id['0']);

        return view('purchase.dph.show_edit', compact('dph'));
    }

    public function print(Request $request)
    {
        $dph_id = $request->get('dph_id');
        $data = Dph::findOrFail($dph_id);
        return Excel::download(new DphPrint($dph_id), 'Data-'.$data->doc_no.'.xlsx');
    }

    public function delete_supplier(Request $request)
    {
        $supplierId = $request->get('dph_supplier_id');
        $dphSupplier = DB::table('dph_suppliers')->where('id', $supplierId)->first();
        if (!$dphSupplier) {
            return redirect()->back()->with('error', 'Supplier tidak ditemukan.');
        }
        $id_dph = $dphSupplier->dph_id;
        $dph = Dph::findOrFail($id_dph);
        $supplier = Supplier::findOrFail($dphSupplier->supplier_id);

        $dataDPH = DB::table('dph_suppliers')->select('dph.*')->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')->where('dph_suppliers.id','=',$dphSupplier->id)->first();
        $dataHistory = [
            'dph_id' => $dphSupplier->dph_id,
            'jenis' => 'delete',
            'message' => 'Melakukan hapus data supplier '.$supplier->name,
            'user_id' => Auth::user()->id,
        ];
        // Delete Supplier DPH History
        $dph_history = DphHistory::create($dataHistory);

        DB::table('dph_suppliers')->where('id', $supplierId)->delete();
        return redirect()->route('purchasing.dph.show', Hashids::encode($id_dph))->with('success', 'Sukses Hapus Data Supplier '.$supplier->name.' Dari '.$dph->doc_no);
    }
    public function add_supplier(Request $request)
    {
        $id_dph = $request->get('dph_id');
        $dph = Dph::findOrFail($id_dph);
        $pr       = PurchaseRequisition::getByID($dph->purchase_id);
        $id_item = [];
        $item = $request->get('pr_item_id');
        for ($i = 0; $i < count($item); $i++) {
            $id_item[] = $item[$i];
        }
        if(Auth::user()->data_access==1) $pr_items = Dph::getItemCreate($pr->id,$id_item);
        else Dph::getItemCreate($pr->id,$id_item,Auth::user()->id);

        $currency               = DB::table('currencies')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $payment_term           = DB::table('payment_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $po_term                = DB::table('po_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $payment_method         = DB::table('payment_methods')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $price_term             = DB::table('price_terms')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $price_term_location    = DB::table('cities')->orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $measure                = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'name');
        $po_note                = DB::table('po_notes')->orderBy('name','ASC')->where('status',1)->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $ppn                    = $this->ppn;
        $send_expense_ppn       = $this->send_expense_ppn;
        $discount_item          = $this->discount_item;
        $is_item_ppn            = $this->is_item_ppn;
        $discount_type = array(
            '1' => 'Percent',
            '0' => 'Currency'
        );
        return view('purchase.dph.add_supplier', compact('pr_items','pr','dph','ppn','send_expense_ppn','discount_item','is_item_ppn','measure','payment_method','payment_term','price_term','po_term','currency','price_term_location','discount_type','po_note'));
    }

    public function add_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $type = ($request->get('price_term') == 'FRANCO' && $request->get('price_term_location') != 'JAKARTA') ? 'non_lpb' : 'lpb';

            $dp = DB::table('payment_terms')
                ->where("id", $request->get('payment_term_id'))
                ->first();
            $mr_file = null;
            if ($request->hasFile('mr_file')) {
                $file = $request->file('mr_file');
                $name = 'DPH-'.$request->get('supplier_id').time();
                $folder = '/uploads/dph_supplier/'.date('Y').'/'.date('M').'/';
                $filePath = $folder . $name. '.' . $file->getClientOriginalExtension();
                $this->uploadOne($file, $folder, 'public', $name);
                $mr_file = $filePath;
            }
            $discount_amount = $request->get('discount_amount') ?? 0;

            $payment_method = DB::table('payment_methods')->select('payment_methods.*')->where('payment_methods.id','=',$request->get('payment_method'))->first();
            $paymentMethod = $payment_method ? $payment_method->name : 'BANK TRANSFER';

            $data = [
                'dph_id' => $request->get('dph_id'),
                'supplier_id' => $request->get('supplier_id'),
                'discount_type' => $request->get('discount_type'),
                'discount_amount' => floatval(str_replace(",", "", $discount_amount)),
                'payment_term_id' => $request->get('payment_term_id'),
                'po_term_id' => $request->get('po_term_id'),
                'price_term' => $request->get('price_term'),
                'price_term_location' => $request->get('price_term_location'),
                'status' => 0,
                'send_expense' => floatval(str_replace(",", "", $request->get('send_expense'))),
                'supplier_contact_id' => $request->get('supplier_contact_id'),
                'type' => $type,
                'dp_percentage' => $dp->dp_percentage,
                'send_expense_ppn' => $request->get('send_expense_ppn') == 1 ? 11 : 0,
                'discount_item' => $request->get('discount_item'),
                'ppn' => $request->get('ppn') ?? 0,
                'currency' => $request->get('currency'),
                'estimated_delivery_day' => $request->get('estimated_delivery_day'),
                'created_by' => Auth::user()->id,
                'file' => $mr_file,
                'payment_method' => $paymentMethod
            ];

            $dph_ = DphSupplier::create($data);

            $dataDPH = DB::table('dph_suppliers')->select('dph.*')->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')->where('dph_suppliers.id','=',$dph_->id)->first();
            $datamasterSupplier = DB::table('dph_suppliers')->select('suppliers.*')->leftJoin('suppliers','suppliers.id','=','dph_suppliers.supplier_id')->where('suppliers.id','=',$dph_->supplier_id)->first();
            $dataHistory = [
                'dph_id' => $dph_->dph_id,
                'jenis' => 'delete',
                'message' => 'Melakukan tambah data supplier '.$datamasterSupplier->name,
                'user_id' => Auth::user()->id,
            ];
            // Tambah Supplier DPH History
            $dph_history = DphHistory::create($dataHistory);

            $product = $request->get('pr_item_id');
            $dataItem = [];
            $subTotal = 0;
            for ($j = 0; $j < count($product); $j++) {
                $subTotal += floatval(str_replace(",", "", $request->get('price')[$j])) * floatval(str_replace(",", "", $request->get('qty')[$j]));
            }
            for ($i = 0; $i < count($product); $i++) {
                $discAmount = ($request->get('discount_type') == '0')
                    ? floatval(str_replace(",", "", $request->get('discount_amount')))
                    : 0;

                $price = floatval(str_replace(",", "", $request->get('price')[$i]));
                $discountItem = floatval(str_replace(",", "", $request->get('diskon_item')[$i]));
                $dataItem[] = [
                    'dph_supplier_id' => $dph_->id,
                    'product_id'      => $request->get('product_id')[$i] ?? null,
                    'qty'             => $request->get('qty')[$i] ?? null,
                    'price'           => $price ?? null,
                    'status'          => 1,
                    'pr_item_id'      => $request->get('pr_item_id')[$i] ?? null,
                    'measure'         => $request->get('measure_id')[$i] ?? null,
                    'qty_parsial'     => 0,
                    'specification'   => $request->get('specification')[$i] ?? null,
                    'discount'        => $discountItem ?? null,
                    'price_discount'  => round( $price -
                                ($price * $discountItem / 100) -
                                ($discAmount / ($subTotal != 0 ? $subTotal : 1) * ($price != 0 ? $price : 1))
                                ),
                    'is_recomendation' => $request->get('is_recomendation')[$request->get('pr_item_id')[$i]]
                ];
            }

            DphItem::insert($dataItem);
            DB::commit();

            return redirect()->route('purchasing.dph.show_edit', Hashids::encode($dph_->dph_id))->with(['success' => 'Tambah Data Supplier Berhasil!']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function toApproval($id)
    {
        $dph  = DPH::findOrFail($id);
        $dataDPH['status'] = '2';
        $dph->update($dataDPH);

        $dataHistory = [
            'dph_id' => $dph->id,
            'jenis' => 'approval',
            'message' => 'Melakukan pengajuan approval dokumen '. $dph->doc_no,
            'user_id' => Auth::user()->id,
        ];
        // To Approval DPH History
        $dph_history = DphHistory::create($dataHistory);

        return redirect()->route('purchasing.dph.index')->with(['success' => 'Pengajuan Data DPH Berhasil!']);
    }
    public function cancel($id)
    {
        $dph  = DPH::findOrFail($id);
        $dataDPH['status'] = '5';
        $dph->update($dataDPH);

        $dataHistory = [
            'dph_id' => $dph->id,
            'jenis' => 'cancel',
            'message' => 'Melakukan cancel dokumen '. $dph->doc_no,
            'user_id' => Auth::user()->id,
        ];
        // Cancel DPH History
        $dph_history = DphHistory::create($dataHistory);

        return redirect()->route('purchasing.dph.index')->with(['success' => 'Cancel Data '.$dph->doc_no.' Berhasil!']);
    }

    public function updateNotes(Request $request, $id)
    {

        $dph  = DPH::findOrFail($id);
        $dataDPH['notes'] = $request->get('notes_dph');
        $dph->update($dataDPH);

        return redirect()->route('purchasing.dph.show_edit', Hashids::encode($dph->id))->with(['success' => 'Update Notes DPH Berhasil!']);
    }
}
