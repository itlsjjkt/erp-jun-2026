<?php

namespace App\Http\Controllers\Logistic;

use App\Models\InventoryAsset;
use App\Models\InventoryAssetHistory;
use App\Models\UserAsset;
use App\Models\ParentInventoryAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Auth;
use File;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Http\UploadedFile;
use App\Traits\UploadTrait;


class InventoryAssetController extends Controller
{
    use UploadTrait;
    public function index()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $company = DB::table('companies')->get()->pluck('name', 'id')->prepend('Silahkan pilih company', '');
        return view('logistic.inventory_asset.index',compact('company'));
    }

    public function datatables()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $result = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->whereNull('inventory_assets.deleted_at')
            ->orderBy('inventory_assets.created_at','DESC');
        return  DataTables::of($result)

        ->addColumn('action', function ($result) {
            $encodedId = Hashids::encode($result->id);

            // Tombol Edit
            $url_edit = "<a href='#'
                data-url='" . route('logistic.inventory_asset.edit', ['inventory_asset' => $encodedId]) . "'
                class='btn btn-outline modalEdit'
                data-toggle='tooltip'
                title='Update Data'>
                <span class='ti-pencil-alt'></span>
            </a>";

            // Tombol Show Data
            $url_view = "<a value='" . route('logistic.inventory_asset.show', ['inventory_asset' => $encodedId]) . "' class='icon-lg modalShow'
                style='padding-top: 5px;padding-left: 5px;'
                title='Show Data'
                data-toggle='modal'
                data-target='#modalShow'>
                <span class='ti-eye icon-lg'></span>
            </a>";

            // Tombol Print QR
            $idEncoded = Hashids::encode($result->id);
            $baseUrl = route('logistic.inventory_asset.print', $idEncoded);
            $print = <<<HTML
                <div class="dropdown" style="margin-left:8px;">
                    <a class="btn btn-outline dropdown-toggle" href="#" role="button" id="dropdownMenuPrint{$result->id}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Cetak QR">
                        <i class="fa fa-qrcode icon-lg"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuPrint{$result->id}">
                        <a class="dropdown-item" href="{$baseUrl}?ukuran=24" target="_blank">Label 24 mm</a>
                        <a class="dropdown-item" href="{$baseUrl}?ukuran=36" target="_blank">Label 36 mm</a>
                    </div>
                </div>
            HTML;

            $url_delete = "<form class='delete' action='".route('logistic.inventory_asset.destroy', Hashids::encode($result->id))."' method='POST'>
                ".csrf_field()."
                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
            </form>";

            // Logic Button Sesuai Status
            switch (strtolower($result->status)) {
                case 1: // Publish
                    return "<div class='btn-group'>{$url_view}{$print}</div>";
                default:
                    return "<div class='btn-group'>{$url_edit}{$url_view}{$print}{$url_delete}</div>";
            }
        })
        ->editColumn('produk', function ($result) {
            return $result->produk."<br><small>Pn/Spec :".($result->produkpn??'-')."<br>Brand :".($result->brand??'-')."<br>Satuan :".($result->measure??'-')."</small>";
        })
        ->editColumn('status', function ($result){
            return getStatusInventoryAsset($result->status);
        })
        ->editColumn('created_by', function ($result){
            return getUserByID($result->created_by);
        })
        ->editColumn('created_at', function ($result){
            return getDateId($result->created_at);
        })
        ->rawColumns(['action', 'status','produk'])
        ->make(true);
    }

    public function create()
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $produk = array(
            '0' => 'Silahkan pilih produk'
        );
        $type_relation = array(
            '0'     => 'Tidak Ada',
            'po'    => 'PO',
            'bpb'   => 'BPB'
        );
        $relation_item_id = array(
            '0'     => 'Silahkan pilih dokumen'
        );
        $user_assets = DB::table('user_assets')
            ->orderBy('name', 'ASC')
            ->pluck('name', 'id')
            ->prepend('Silahkan pilih user...', '');
        $status = array(
            '1' => 'Aktif',
            '0' => 'Non Aktif'
        );
        $lokasi = array(
            '0'     => 'Silahkan pilih lokasi'
        );
        $department = array(
            '0'     => 'Silahkan pilih department'
        );

        $company = DB::table('companies')->get()->pluck('name', 'id')->prepend('Silahkan pilih company', '');

        return view('logistic.inventory_asset.create',compact('produk','type_relation','relation_item_id','user_assets','status','lokasi','company','department'));
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        DB::beginTransaction();
        try{
            $data = $request->all();
            $increment = DB::table('parent_inventory_assets')
                ->whereYear("created_at", date('Y'))
                ->whereMonth("created_at", date('m'))
                ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no_dia = "DIA-".date('my')."-".$num;
            $dia['doc_no'] = $no_dia;
            $dia['created_by'] = Auth::user()->id;
            $data_dia = ParentInventoryAsset::create($dia);
            $product = $request->get('product_id');
            for($i=0;$i < count($product);$i++) {

                $get_cat = DB::table('master_item_products')
                    ->select('master_item_products.*','master_items.code AS cat_code')
                    ->leftJoin('master_items','master_items.id','=','master_item_products.item_id')
                    ->where('master_item_products.id','=',$request->get('product_id')[$i])
                    ->first();
                $increment = DB::table('inventory_assets')
                    ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
                    ->whereYear("inventory_assets.created_at", date('Y'))
                    ->whereMonth("inventory_assets.created_at", date('m'))
                    ->where('master_item_products.item_id','=',$get_cat->item_id)
                    ->count();
                $num = sprintf("%'.05d", $increment + 1);
                $company = DB::table('companies')->where('id','=', $request->get('company_id')[$i])->first();
                $no_dia_item = "AST-".$company->code."-".$get_cat->cat_code."-".date('my')."-".$num;
                // ATTACHMENT
                $attachmentPath = null;
                if ($request->hasFile('attachment') && isset($request->file('attachment')[$i])) {
                    $attachmentFile = $request->file('attachment')[$i];
                    $attachmentName = $data_dia->doc_no . '-' . time();
                    $attachmentFolder = '/uploads/inventory_asset/attachment/' . date('Y') . '/' . date('M') . '/';
                    $attachmentFullPath = $attachmentFolder . $attachmentName . '.' . $attachmentFile->getClientOriginalExtension();
                    $this->uploadOne($attachmentFile, $attachmentFolder, 'public', $attachmentName);
                    $attachmentPath = $attachmentFullPath;
                }

                // IMAGE
                $imagePath = null;
                if ($request->hasFile('image') && isset($request->file('image')[$i])) {
                    $imageFile = $request->file('image')[$i];
                    $imageName = $data_dia->doc_no . '-' . time();
                    $imageFolder = '/uploads/inventory_asset/image/' . date('Y') . '/' . date('M') . '/';
                    $imageFullPath = $imageFolder . $imageName . '.' . $imageFile->getClientOriginalExtension();
                    $this->uploadOne($imageFile, $imageFolder, 'public', $imageName);
                    $imagePath = $imageFullPath;
                }
                $cleanPrice = 0;
                if($request->get('price')[$i]){
                    $cleanPrice = str_replace(',', '', $request->get('price')[$i]);
                }
                // $userNik = UserAsset::getByID($request->get('user_asset_id')[$i]);
                $dataAst =[
                    'parent_inventory_asset_id' => $data_dia->id,
                    // 'user_asset_id'     => $request->get('user_asset_id')[$i],
                    // 'user_asset_nik'    => $userNik->nik ?? 0,
                    'product_id'        => $request->get('product_id')[$i],
                    'company_id'        => $request->get('company_id')[$i],
                    'department_id'     => $request->get('department_id')[$i],
                    'measure'           => $request->get('measure')[$i],
                    'type_relation'     => $request->get('type_relation')[$i],
                    'relation_item_id'  => $request->get('relation_item_id')[$i],
                    'location_id'       => $request->get('location_id')[$i],
                    'price'             => $cleanPrice,
                    'notes'             => $request->get('notes')[$i],
                    'status'            => $request->get('status')[$i],
                    'attachment'        => $attachmentPath,
                    'image'             => $imagePath,
                    'created_by'        =>Auth::user()->id,
                    'doc_no'            => $no_dia_item
                ];
                $inv_as = InventoryAsset::create($dataAst);
                InventoryAssetHistory::create([
                    'inventory_asset_id' => $inv_as->id,
                    'type' => 'create',
                    'created_by' => Auth::user()->id
                ]);
                DB::commit();
            }
            return redirect()->route('logistic.parent_inventory_asset.show', Hashids::encode($data_dia->id))->with(['success' => 'Berhasil menambahkan data!']);
        }catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function create_instant(Request $request)
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        DB::beginTransaction();
        try{
            $increment = DB::table('parent_inventory_assets')
            ->whereYear("created_at", date('Y'))
            ->whereMonth("created_at", date('m'))
            ->count();
            $num = sprintf("%'.05d", $increment + 1) ;
            $no_dia = "DIA-".date('my')."-".$num;
            $dia['doc_no'] = $no_dia;
            $dia['created_by'] = Auth::user()->id;
            $data_dia = ParentInventoryAsset::create($dia);
            $count = (int) $request->get('count_barcode');
            for($i=0;$i < $count;$i++) {
                $get_cat = DB::table('master_item_products')
                    ->select('master_item_products.*','master_items.code AS cat_code','measures.name AS measure')
                    ->leftJoin('master_items','master_items.id','=','master_item_products.item_id')
                    ->leftJoin('measures','measures.id','=','master_item_products.measure_inventory')
                    ->where('master_item_products.id','=',$request->get('product_id'))
                    ->first();
                $increment = DB::table('inventory_assets')
                    ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
                    ->whereYear("inventory_assets.created_at", date('Y'))
                    ->whereMonth("inventory_assets.created_at", date('m'))
                    ->where('master_item_products.item_id','=',$get_cat->item_id)
                    ->count();
                $num = sprintf("%'.05d", $increment + 1);
                $company = DB::table('companies')->where('id','=', $request->get('company_id'))->first();
                $no_dia_item = "AST-".$company->code."-".$get_cat->cat_code."-".date('my')."-".$num;
                $dataAst =[
                    'parent_inventory_asset_id' => $data_dia->id,
                    'product_id'        => $request->get('product_id'),
                    'company_id'        => $request->get('company_id'),
                    'measure'           => $get_cat->measure,
                    'created_by'        => Auth::user()->id,
                    'doc_no'            => $no_dia_item,
                    'status'            => 0
                ];
                $inv_as = InventoryAsset::create($dataAst);
                InventoryAssetHistory::create([
                    'inventory_asset_id' => $inv_as->id,
                    'type' => 'draft',
                    'created_by' => Auth::user()->id
                ]);
            }
            DB::commit();
          return redirect()->route('logistic.parent_inventory_asset.show', Hashids::encode($data_dia->id))->with(['success' => 'Berhasil menambahkan data!']);
        }catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function show($idx){
        $id  = Hashids::decode($idx);
        $data = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'companies.name AS companyy',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand',
                'departments.name AS depttt'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('departments','departments.id','=','inventory_assets.department_id')
            ->leftJoin('companies','companies.id','=','inventory_assets.company_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->whereIn('inventory_assets.id', $id)
            ->orderBy('master_item_products.name','ASC')
            ->first();

        return view('logistic.inventory_asset.show',compact('data'))->renderSections()['content'];
    }

    public function edit($idx)
    {
        if (! Gate::allows('inventory_asset')) {
            return abort(401);
        }
        $id = Hashids::decode($idx);

        $data = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'companies.name AS companyy',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand',
                'departments.name AS depttt'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('departments','departments.id','=','inventory_assets.department_id')
            ->leftJoin('companies','companies.id','=','inventory_assets.company_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->where('inventory_assets.id','=', $id[0])
            ->first();
        $department = DB::table('departments')
            ->where('company_id','=',$data->company_id)
            ->pluck('name', 'id')
            ->prepend('Silahkan pilih department...', '');
        $location = DB::table('locations')
            ->where('company_id','=',$data->company_id)
            ->pluck('name', 'id')
            ->prepend('Silahkan pilih lokasi...', '');
        $user_asset = DB::table('user_assets')
            ->pluck('name', 'id')
            ->prepend('Silahkan pilih user...', '');
        $type_relation = array(
                '0'     => 'Tidak Ada',
                'po'    => 'PO',
                'bpb'   => 'BPB'
            );
        $status = array(
            '1' => 'Aktif',
            '0' => 'Non Aktif'
        );
        $data_relation = [];
        if ($data->type_relation == 'po') {
            $data_relation = DB::table('po_items')
                ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
                ->where('po_items.product_id', $data->product_id)
                ->select('po.doc_no', 'po_items.id', 'po.created_at')
                ->groupBy('po.doc_no', 'po_items.id', 'po.created_at')
                ->orderBy('po.created_at', 'DESC')
                ->pluck('po.doc_no', 'po_items.id')
                ->prepend('Silahkan pilih dokumen...', '');
        } elseif ($data->type_relation == 'bpb') {
            $data_relation = DB::table('bpb_items')
                ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
                ->leftJoin('purchase_items', 'purchase_items.id', '=', 'bpb_items.pr_item_id')
                ->where('purchase_items.product_id', $data->product_id)
                ->select('bpb.doc_no', 'bpb_items.id', 'bpb.created_at')
                ->groupBy('bpb.doc_no', 'bpb_items.id', 'bpb.created_at')
                ->orderBy('bpb.created_at', 'DESC')
                ->pluck('bpb.doc_no', 'bpb_items.id')
                ->prepend('Silahkan pilih dokumen...', '');
        }
        return view('logistic.inventory_asset.edit', compact('data','department','location','user_asset','type_relation','status','data_relation'))->renderSections()['content'];
    }

    public function update(Request $request, $idx)
    {
        if (!Gate::allows('inventory_asset')) {
            abort(401);
        }

        $decoded = Hashids::decode($idx);
        $id = $decoded[0] ?? null;
        if (!$id) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        try {
            $asset = InventoryAsset::findOrFail($id);
            $data = $request->except(['attachment', 'image']);

            $price = 0;
            if($request->get('price')){
                $price = str_replace(',', '', $request->get('price'));
            }
            $data['price']      = $price;
            $data['updated_by'] = Auth::user()->id;

            $docNo = $asset->doc_no ?? 'asset';

            // Handle Attachment
            if ($request->hasFile('attachment')) {
                // Hapus attachment lama jika ada
                if (!empty($asset->attachment) && \Storage::disk('public')->exists($asset->attachment)) {
                    \Storage::disk('public')->delete($asset->attachment);
                }

                // Upload attachment baru
                $attachmentFile = $request->file('attachment');
                $attachmentName = $docNo . '-' . time();
                $attachmentFolder = '/uploads/inventory_asset/attachment/' . date('Y') . '/' . date('M') . '/';
                $attachmentFullPath = $attachmentFolder . $attachmentName . '.' . $attachmentFile->getClientOriginalExtension();
                $this->uploadOne($attachmentFile, $attachmentFolder, 'public', $attachmentName);
                $data['attachment'] = $attachmentFullPath;
            }

            // Handle Image
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if (!empty($asset->image) && \Storage::disk('public')->exists($asset->image)) {
                    \Storage::disk('public')->delete($asset->image);
                }

                // Upload gambar baru
                $imageFile = $request->file('image');
                $imageName = $docNo . '-' . time();
                $imageFolder = '/uploads/inventory_asset/image/' . date('Y') . '/' . date('M') . '/';
                $imageFullPath = $imageFolder . $imageName . '.' . $imageFile->getClientOriginalExtension();
                $this->uploadOne($imageFile, $imageFolder, 'public', $imageName);
                $data['image'] = $imageFullPath;
            }


            $asset->update($data);
            InventoryAssetHistory::create([
                    'inventory_asset_id' => $asset->id,
                    'type' => 'update',
                    'created_by' => Auth::user()->id
                ]);

            return redirect()->route('logistic.parent_inventory_asset.show', Hashids::encode($asset->parent_inventory_asset_id))
                ->with('success', 'Berhasil melakukan edit!');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal update data: ' . $e->getMessage());
        }
    }

    public function getProductByReq(Request $request)
    {
        if ($request->has('q')) {
            $query = DB::table('master_item_products')
            ->select('master_item_products.*', 'measures.name AS measure','master_item_brands.name AS brand')
            ->leftJoin('measures', 'master_item_products.measure_inventory', '=', 'measures.id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            // ->where('master_item_products.status', 1)
            // ->whereNull('master_item_products.deleted_at')
            ->where(function ($query) use ($request) {
                $keywords = explode(' ', $request->q);
                foreach ($keywords as $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('master_item_products.name', 'ilike', '%' . $keyword . '%')
                        ->orWhere('master_item_products.code', 'ilike', '%' . $keyword . '%')
                        ->orWhere('master_item_products.part_number', 'ilike', '%' . $keyword . '%')
                        ->orWhere('master_item_brands.name', 'ilike', '%' . $keyword . '%');
                    });
                }
            })
            ->get();
            $result = array();
            foreach ($query as $val) {
                if($val->status == 1){
                    $result[] = array(
                        'id' => $val->id,
                        'measure' => $val->measure,
                        'brand' => ($val->brand) ? $val->brand : '-',
                        'name' => $val->name,
                        'code' => $val->code,
                        'item_id' => $val->item_id,
                        'description' => $val->description,
                        'part_number' => ($val->part_number) ? $val->part_number : '-',
                    );
                }
            }
            return response()->json($result);
        }
    }

    public function getDataRelation($type, $product_id)
    {
            $result = [];
        if($type == 'po'){
            $result = DB::table('po_items')
                ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
                ->whereIn('po.status',[2,4,5])
                ->where('po_items.product_id', $product_id)
                ->select('po.doc_no', 'po_items.id', 'po.created_at')
                ->groupBy('po.doc_no', 'po_items.id', 'po.created_at')
                ->orderBy('po.created_at', 'DESC')
                ->pluck('po.doc_no', 'po_items.id');

        }elseif($type == 'bpb'){
            $result = DB::table('bpb_items')
                ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
                ->leftJoin('purchase_items','purchase_items.id','bpb_items.pr_item_id')
                ->where('purchase_items.product_id', $product_id)
                ->select('bpb.doc_no', 'bpb_items.id', 'bpb.created_at')
                ->groupBy('bpb.doc_no', 'bpb_items.id', 'bpb.created_at')
                ->orderBy('bpb.created_at', 'DESC')
                ->pluck('bpb.doc_no', 'bpb_items.id');
        }
        return response()->json($result);
    }

    public function getDataDeptByCompany($company_id)
    {
        $result = DB::table('departments')
            ->leftJoin('companies', 'companies.id', '=', 'departments.company_id')
            ->where('departments.company_id', '=', $company_id)
            ->where('departments.status', '=', 1)
            ->selectRaw("CONCAT(companies.code, ' - ', departments.name) AS label, departments.id")
            ->orderBy('departments.name', 'ASC')
            ->pluck('label', 'departments.id');
        return response()->json($result);
    }

    public function getDataLocByCompany($company_id)
    {
        $result = DB::table('locations')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('locations.company_id','=',$company_id)
        ->selectRaw("CONCAT(companies.code, ' - ', locations.name) AS label, locations.id")
        ->orderBy('locations.name', 'ASC')
        ->pluck('label', 'locations.id');

        return response()->json($result);
    }

    public function print(Request $request, $id)
    {
        $id = Hashids::decode($id);
        $data = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->where('inventory_assets.id','=',$id[0])
            ->first();
        $tinggiMM = $request->get('ukuran');
        $ukuranQr = ($tinggiMM-4) * 3.7;
        $qrCodeData = QrCode::size($ukuranQr)->generate('https://erp.haritashipping.com/inventory_asset/' . Hashids::encode($data->id).'/'.$data->uuid);
        $qrCodeBase64 = base64_encode($qrCodeData);
        $qrCodeImage = 'data:image/png;base64,' . $qrCodeBase64;
        $lebarMM = $tinggiMM * 3;

        $tinggiPt = $tinggiMM * 2.83465;
        $lebarPt = $lebarMM * 2.83465;
        $pdf = Pdf::loadView('logistic.inventory_asset.print', compact('data', 'qrCodeImage', 'tinggiMM'))->setPaper([0, 0, $lebarPt, $tinggiPt], 'portrait');
        return $pdf->download('['.$tinggiMM.'mm]QRCODE-' . $data->doc_no . '.pdf');
    }

    public function print_merge(Request $request)
    {
        $id = explode(',', $request->get('ast_id'));
        $countRow = $request->get('count_row');
        $result = DB::table('inventory_assets')
            ->select(
                'inventory_assets.*',
                'master_item_products.name AS produk',
                'master_item_products.part_number AS produkpn',
                'master_item_products.code AS produkcode',
                'locations.name AS lokasi',
                'user_assets.name AS user',
                'parent_inventory_assets.doc_no AS parent_doc_no',
                'master_item_brands.name AS brand',
                'inventory_assets.measure AS measure'
            )
            ->leftJoin('parent_inventory_assets','parent_inventory_assets.id','=','inventory_assets.parent_inventory_asset_id')
            ->leftJoin('master_item_products','master_item_products.id','=','inventory_assets.product_id')
            ->leftJoin('locations','locations.id','=','inventory_assets.location_id')
            ->leftJoin('user_assets','user_assets.id','=','inventory_assets.user_asset_id')
            ->leftJoin('master_item_brands','master_item_brands.id','=','master_item_products.brand_id')
            ->whereIn('inventory_assets.id', $id)
            ->orderBy('master_item_products.name','ASC')
            ->get();
        $tinggiMM = $request->get('ukuran');
        $ukuranQr = ($tinggiMM-4) * 3.7;
        $lebarMM = $tinggiMM * 3;
        $tinggiPt = $tinggiMM * 2.83465;
        $lebarPt = $lebarMM * 2.83465;
        foreach($result as $data){
            $qrCodeData = QrCode::size($ukuranQr)->generate('https://erp.haritashipping.com/inventory_asset/' . Hashids::encode($data->id) . '/' . $data->uuid);
            $qrCodeBase64 = base64_encode($qrCodeData);
            $qrCodeImage = 'data:image/png;base64,' . $qrCodeBase64;
            $data->id = $qrCodeImage;
        }
        $pdf = Pdf::loadView('logistic.inventory_asset.print_multiple', compact('result','tinggiMM'))->setPaper([0, 0, $lebarPt, $tinggiPt], 'portrait');
        return $pdf->download('['.$tinggiMM.'mm]-QRCODE-ALL.pdf');
    }

    public function destroy($id)
    {
        // Decode ID dari Hashids
        $decodedId = Hashids::decode($id);
        if (!isset($decodedId[0])) {
            return redirect()->back()->with('error', 'ID tidak valid');
        }
        
        $inventoryAssetId = $decodedId[0];
        // dd($inventoryAssetId);

        DB::table('inventory_assets')
            ->where('id', $inventoryAssetId)
            ->update([
                'deleted_at' => now(),
                'deleted_by' => Auth::user()->id,
                'status'    => 2
            ]);

        return redirect()->route('logistic.inventory_asset.index')->with('success', 'Data berhasil dihapus');
    }

    public function printItem($id)
    {
        $decodedId = Hashids::decode($id)[0];

        $asset = DB::table('inventory_assets')
            ->where('id', $decodedId)
            ->first();

        if (!$asset) {
            abort(404);
        }

        return view('logistic.inventory_assets.print_item', compact('asset'));
    }
}
