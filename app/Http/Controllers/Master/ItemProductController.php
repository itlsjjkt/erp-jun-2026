<?php

namespace App\Http\Controllers\Master;

use App\Models\MasterItemProduct;
use App\Models\MasterItem;
use App\Models\MasterProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Imports\ProductImport;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
use File;
use Rap2hpoutre\FastExcel\FastExcel;

class ItemProductController extends Controller
{
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        if (! Gate::allows('master.product-view')) {
            return abort(401);
        }

        $item       = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")
        ->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');

        $brand   = DB::table('master_item_brands')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $type_data = array('ASSET CONSUMABLE','ASSET NON CONSUMABLE','JASA');

        $type = array();
        $no   = 1;
        foreach($type_data as $val){
            $type[$val]= $val;
            $no++;
        }

        return view('master.product.index',compact('item','brand','type'));
    }

    public function datatables(Request $request)
    {

        $result = DB::table('master_item_products')
        ->select(
            'master_item_products.*',
            'master_item_brands.name AS brand',
            'measures.name AS unit',
            'users.name AS created_by'
        )
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('users', 'users.id', '=', 'master_item_products.created_by')
        ->when(!empty($request->get('item_id')), function ($result) use ($request) {
            return $result->where('master_item_products.item_id',$request->get('item_id'));
        })
        ->when(!empty($request->get('amp;brand_id')), function ($result) use ($request) {
            return $result->where('master_item_products.brand_id',$request->get('amp;brand_id'));
        })
        ->whereNull('deleted_at');


       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $action = "<a href='".route('master.item_products.show', Hashids::encode($result->id))."' title='Tampilkan' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";
            
            if( auth()->user()->can('master.product-action') ){
                $action .= "<a href='".route('master.item_products.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
                
                if(Auth::user()->id == 1){
                    $action .= "<form class='delete' action='".route('master.item_products.delete', ['id' => $result->id])."' method='POST'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
                }
            }
            return
                '<div class="btn-group">'
                 .$action.
                '</div>';
        })
      
        ->addColumn('unit', function ($result){
            if($result->unit != NULL){
                return $result->unit;
            }else{
                return $result->satuan;
            }
        })
        ->addColumn('status', function ($result){
            if($result->status==1){
                return "<span class='badge badge-success'>Aktif</span>";
            }else{
                return "<span class='badge badge-danger'>Non Aktif</span>";
            }
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('d/m/Y H:i:s') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    /**
     * Show the form for creating new Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }
        $item       = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")
        ->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');

        $measure    = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        $brand      = DB::table('master_item_brands')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');

        return view('master.product.create', compact('item','measure','brand'));
    }

    /**
     * Store a newly created Items in storage.
     *
     * @param  \App\Http\Requests\StoreItemssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }

        $cekProduct = MasterItemProduct::cekProductName(strtoupper($request->get('name')),$request->get('part_number'),$request->get('measure_id'),$request->get('item_id'), $request->get('category_id'), $request->get('brand_id'))->count();

        if( $cekProduct > 0){
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{

            $lastProduct = DB::table('master_item_products')
            ->where('item_id',$request->get('item_id'))
            ->orderBy('code','DESC')
            ->first();

            if($lastProduct){
                $increment = explode('-',$lastProduct->code);
                $num = sprintf("%'.05d", $increment['1'] + 1) ;
                $code = $increment['0']."-".$num;
            }else{
                $item   = DB::table('master_items')
                ->where('id',$request->get('item_id'))
                ->first();

                $product = DB::table('master_item_products')
                ->where('item_id',$request->get('item_id'))
                ->count();
                $num = sprintf("%'.05d", $product + 1) ;
                $code = $item->code."-".$num;
            }

            $data = $request->all();
            $data['created_by'] = Auth::user()->id;
            $data['code']       = $code;
            $data['name']       = strtoupper($request->get('name'));
            if ($request->get('status')=='1') $data['status'] = 1;
            else  $data['status'] = 0;

            MasterItemProduct::create($data);

            return redirect()->route('master.item_products.index')->with(['success' => 'Add was successful!']);

        }

    }


    /**
     * Show the form for editing Items.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }
        $id = Hashids::decode($id);

        $item       = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")
        ->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');

        $brand      = DB::table('master_item_brands')->orderBy('name','ASC')->get()->pluck('name', 'id');
        $measure    = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'id');

        $product = DB::table('master_item_products')
        ->select('master_item_products.*', 'master_items.name AS item')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->where('master_item_products.id',$id['0'])
        ->first();

        return view('master.product.edit', compact('item','product','measure','brand'));
    }


    public function show($id)
    {
       
        $id = Hashids::decode($id);
        $product = MasterItemProduct::findOrFail($id['0']);

        return view('master.product.show', compact('product'));
    }
    /**
     * Update Items in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }
        $data = $request->all();

        $cekProduct = MasterItemProduct::cekProductName(strtoupper($request->get('name')),$request->get('part_number'),$request->get('measure_id'),$request->get('item_id'),$request->get('brand_id'),$id)->count();


        if( $cekProduct > 0){
            return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{

            $product = DB::table('master_item_products')
                ->where('id',$id)->first();

            if($request->get('item_id') != $product->item_id ){

                $lastProduct = DB::table('master_item_products')
                ->where('item_id',$request->get('item_id'))
                ->orderBy('code','DESC')
                ->first();

                if($lastProduct){
                    $increment = explode('-',$lastProduct->code);
                    $num = sprintf("%'.05d", $increment['1'] + 1) ;
                    $code = $increment['0']."-".$num;
                }else{
                    $item   = DB::table('master_items')
                    ->where('id',$request->get('item_id'))
                    ->first();

                    $product = DB::table('master_item_products')
                    ->where('item_id',$request->get('item_id'))
                    ->count();

                    $num = sprintf("%'.05d", $product + 1) ;
                    $code = $item->code."-".$num;

                }
                $data['code']       = $code;
            }

            if ($request->get('status')) $data['status'] = 1;
            else  $data['status'] = 0;

            $data['updated_by'] = Auth::user()->id;
            $data['name']       = strtoupper($request->get('name'));
            $items = MasterItemProduct::findOrFail($id);
            $items->update($data);

            return redirect()->route('master.item_products.index')->with(['success' => 'Edit was successful!']);
        }
    }

    public function delete(Request $request)
    {

        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }

        $items  = MasterItemProduct::findOrFail($request->id);
        $data['updated_by'] = Auth::user()->id;
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $items->update($data);

        return redirect()->route('master.item_products.index')->with(['success' => 'Delete was successful!']);

    }


    public function search(Request $request)
    {
        $query = 'item_id='.$request->get('item_i').'&brand_id='.$request->get('brand_id');
     
        $data = $request->all();
        $search = "Cari Berdasarkan: ";

        if($request->input('item_id')) $search .= "<strong> Kategori Produk: </strong>".getDataByID('master_items',$request->input('item_id'))->name;
        if($request->input('brand_id')) $search .= "<strong> Merk: </strong>".getDataByID('master_item_brands',$request->input('brand_id'))->name;

        $item = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');
        $brand = DB::table('master_item_brands')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan pilih...', '');
        return view('master.product.search', compact('data', 'search','item','brand','query'));
    }

    public function export(Request $request)
    {
        $data = $request->all();

        $query = DB::table('master_item_products')
        ->select('master_items.code AS itemCode', 'master_items.name AS item',
            'master_item_products.*',
            'master_item_brands.name AS productBrand',
            'measures.name AS unit',
            'users.name AS created_by'
            )
        ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
        ->leftJoin('master_items', 'master_items.id', '=', 'master_item_products.item_id')
        ->leftJoin('measures', 'measures.id', '=', 'master_item_products.measure_id')
        ->leftJoin('users', 'users.id', '=', 'master_item_products.created_by')
        ->whereNull('master_item_products.deleted_at')
        ->when(!empty($data['item_id']), function ($query) use ($data) {
            return $query->where('master_items.id',$data['item_id']);
        })
        ->when(!empty($data['brand_id']), function ($query) use ($data) {
            return $query->where('master_item_brands.id',$data['brand_id']);
        })
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('master.item_products.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('product-'.date('d-m-Y').'.xlsx', function ($inv) {

                return [
                    'Kode Produk'   => $inv->code,
                    'Nama Produk'   => $inv->name,
                    'PN/SPEC'   => $inv->part_number,
                    'Merk'          => $inv->productBrand,
                    'ID Merk'       => $inv->brand_id,
                    'Satuan'        => $inv->unit,
                    'ID Satuan'     => $inv->measure_id,
                    'Kategori Kode' => $inv->itemCode,
                    'Kategori'      => $inv->item,
                    'Dibuat'        => $inv->created_by,
                    'Dibuat Tanggal' => date('d M Y',strtotime($inv->created_at)), 
                    'Terakhir Diubah' => date('d M Y',strtotime($inv->updated_at)), 
                    'Status'        => ($inv->status == '1' ? 'Aktif' : 'Non Aktif')
                ];
            });
        }

    }


    public function upload()
    {
        if (! Gate::allows('master.product-action')) {
            return abort(401);
        }

        $item       = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")
        ->orderBy('level2','ASC')->get()->pluck('level2', 'id')->prepend('Silahkan pilih...', '');

        return view('master.product.upload',compact('item'));
    }

    public function import(Request $request){
        //validate the xls file
        $this->validate($request, array(
            'file'      => 'required'
        ));

        if($request->hasFile('file')){
            $extension = File::extension($request->file->getClientOriginalName());
            if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {

                $item       = MasterItem::where("id", $request->get('item_id'))->first();
                $lastCode   = MasterItemProduct::where('item_id',$item->id)->orderBy('id','desc')->first();
                $userID     = Auth::user()->id;

                $extCode = 0;
                if (!empty($lastCode)) {
                    $extCode = $lastCode->code;
                }

                $file = $request->file('file');
                try {
                    Excel::import(new ProductImport( $item->code, $extCode, $request->get('item_id'), $userID), $file);
                } catch (\Exception $e) {
                    return redirect()->back()
                    ->withInput($request->input())
                    ->withErrors($e->getMessage());
                }
                return redirect()->route('master.item_products.index')->with(['success' => 'Success inserting the data..']);

            } else {
                return redirect()->route('master.item_products.index')->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
            }
        }
    }

    public function getProduct($cat_id)
    {
        return MasterItemProduct::where('category_id', $cat_id)->pluck('name', 'id');
    }


    public function loadData(Request $request,$cat_id = null)
    {

        if ($request->has('q')) {

            $query = DB::table('master_item_products')
            ->select('master_item_products.*', 'measures.name AS measure','master_item_brands.name AS brand')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->where('master_item_products.status', 1)
            ->whereNull('master_item_products.deleted_at')
            ->where(function ($query) use ($request) {
                $query
                ->where('master_item_products.name', 'ilike','%'.$request->q.'%')
                ->orWhere('master_item_products.code', 'ilike','%'.$request->q.'%')
                ->orWhere('master_item_products.part_number', 'ilike','%'.$request->q.'%');
            })
            ->when(!empty($cat_id), function ($query) use ($cat_id) {
                return $query->where('master_item_products.item_id', $cat_id);
            })
            ->get();

            $result = array();
            foreach ($query as $val) {
                if($val->status == 1){
                    $result[] = array(
                        'id' => $val->id, 
                        'measure' => $val->measure, 
                        'measure_purchasing' => $val->measure_id, 
                        'measure_inventory' => $val->measure_inventory, 
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


    public function getData(Request $request,$cat_id = null)
    {
        $category = explode(',', $cat_id);
        if ($request->has('q')) {

            $query = DB::table('master_item_products')
            ->select('master_item_products.*', 'measures.name AS measure','master_item_brands.name AS brand')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
            ->where('master_item_products.status', 1)
            ->whereNull('master_item_products.deleted_at')
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
            ->when(!empty($category), function ($query) use ($category) {
                return $query->whereIn('master_item_products.item_id', $category);
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


    public function getProductbyName(Request $request)
    {

        $q = strlen($request->q);

        $result='';
        if ($request->has('q') && $q > 3) {

            $query = DB::table('master_item_products')
            ->select('master_item_products.*', 'measures.name AS measure','master_items.name AS item')
            ->leftJoin('master_items', 'master_item_products.item_id', '=', 'master_items.id')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->where('master_item_products.name', 'ilike','%'.$request->q.'%')
            ->where('master_item_products.status', 1)
            ->whereNull('master_item_products.deleted_at')
            ->get();

            if(count($query) > 0){
            $result = '<div class="alert alert-danger"><p>Nama Produk Sejenis yang telah terdapat di Master Produk</p>';
            $result .= '<table class="table"  style="font-size:11px">';
            $result .= "<tr>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Kategori</th>
                        </tr>
                        ";
            foreach ($query as $val) {
                $part_number = '';
                if ($val->part_number != null){
                    $part_number = '<br>PN:'.$val->part_number;
                }

                $result .= "<tr>
                        <td style='padding:2px;'>[".$val->code."] ".$val->name.$part_number."</td>
                        <td style='padding:2px;'>".$val->measure."</td>
                        <td style='padding:2px;'>".$val->item."</td>
                    </tr>
                    ";
            }
            $result .= '</table></div>';
            echo $result;
            }else{
                echo $result;
            }
        }else{
            echo $result;

        }
    }

    // Create Multiple Paga
    public function create_multiple()
    {
        $item = MasterItem::selectRaw("CONCAT(name, ' (', code, ')') as level2, id")
            ->orderBy('level2', 'ASC')
            ->get()
            ->pluck('level2', 'id')
            ->prepend('Silahkan Pilih...', '');
        
        $measure    = DB::table('measures')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan Pilih...', '');
        $brand      = DB::table('master_item_brands')->orderBy('name','ASC')->get()->pluck('name', 'id')->prepend('Silahkan Pilih...', '');

        return view('master.product.create_multiple', compact('item', 'measure', 'brand'));
    }

    // Store Multiple
    public function store_multiple(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'name.*' => 'required|string',
            'part_number.*' => 'nullable|string',
            'brand_id.*' => 'nullable|string',
            'measure_id.*' => 'required|integer',
            'measure_inventory.*' => 'required|integer',
            'conversion.*' => 'required|numeric',
        ]);

        $item = DB::table('master_items')->where('id', $request->item_id)->first();
        if (!$item) {
            return redirect()->back()->withErrors(['Item tidak ditemukan.']);
        }

        // Kode
        $lastProduct = DB::table('master_item_products')
            ->where('item_id', $request->item_id)
            ->orderBy('code', 'desc')
            ->first();

        $startIncrement = 1;
        if ($lastProduct) {
            $increment = explode('-', $lastProduct->code);
            $startIncrement = isset($increment[1]) ? intval($increment[1]) + 1 : 1;
        } else {
            $productCount = DB::table('master_item_products')->where('item_id', $request->item_id)->count();
            $startIncrement = $productCount + 1;
        }

        $errors = [];
        foreach ($request->name as $i => $name) {
            // Cek Duplikasi Kode
            $isDuplicate = MasterItemProduct::cekProductName(
                strtoupper($name),
                $request->part_number[$i],
                $request->measure_id[$i],
                $request->item_id,
                $request->category_id[$i] ?? null,
                $request->brand_id[$i] ?? null
            )->count();

            if ($isDuplicate > 0) {
                $errors[] = "Produk ke-" . ($i + 1) . " duplikat. Silakan periksa kembali.";
                continue;
            }

            // Generate Kode
            $num = sprintf("%'.05d", $startIncrement++);
            $code = $item->code . '-' . $num;

            MasterItemProduct::create([
                'item_id' => $request->item_id,
                'code' => $code,
                'name' => strtoupper($name),
                'part_number' => $request->part_number[$i],
                'brand_id' => $request->brand_id[$i] ?? null,
                'measure_id' => $request->measure_id[$i],
                'measure_inventory' => $request->measure_inventory[$i],
                'conversion' => $request->conversion[$i],
                'description' => $request->description[$i] ?? null,
                'status' => 1,
                'created_by' => Auth::user()->id,
            ]);
        }

        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        return redirect()->route('master.item_products.index')->with('success', 'Multiple items successfully added!');
    }

    public function getPartNumber($cat_id)
    {
        return MasterItemProduct::where('category_id', $cat_id)->pluck('part_number', 'id');
    }

    // Suggestion Product Name
    // public function getProductbyName(Request $request)
    // {

    //     $q = strlen($request->q);
    //     $result='';
    //     if ($request->has('q') && $q > 3) {
    //         $query = DB::table('master_item_products')
    //         ->select('master_item_products.*', 'measures.name AS measure','master_items.name AS item')
    //         ->leftJoin('master_items', 'master_item_products.item_id', '=', 'master_items.id')
    //         ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
    //         ->where('master_item_products.name', 'ilike','%'.$request->q.'%')
    //         ->where('master_item_products.status', 1)
    //         ->whereNull('master_item_products.deleted_at')
    //         ->get();
    //         if(count($query) > 0){
    //         $result = '<div class="alert alert-danger">';
    //         $result .= '<table class="table table-fixed-header" style="font-size:11px">';
    //         $result .= "
    //                     <thead>
    //                         <tr style='border:none'>
    //                             <th colspan='3' style='border:none; font-weight:bold; text-align:center color:black; font-size:18px;'>
    //                                 Daftar Produk Sejenis yang terdapat di Master Produk
    //                             </th>
    //                         </tr>
    //                         <tr>
    //                             <th>Nama Produk</th>
    //                             <th>Satuan</th>
    //                             <th>Kategori</th>
    //                         </tr>
    //                     </thead>
    //                     <tbody>
    //                     ";
    //         foreach ($query as $val) {
    //             $part_number = '';
    //             if ($val->part_number != null){
    //                 $part_number = '<br>PN:'.$val->part_number;
    //             }

    //             $result .= "<tr>
    //                     <td style='padding:2px;'>[".$val->code."] ".$val->name.$part_number."</td>
    //                     <td style='padding:2px;'>".$val->measure."</td>
    //                     <td style='padding:2px;'>".$val->item."</td>
    //                 </tr>
    //                 ";
    //         }
    //         $result .= '</tbody></table></div></div>';
    //         echo $result;
    //         }else{
    //             echo $result;
    //         }
    //     }else{
    //         echo $result;
    //     }
    // }

    // Suggestion Product Part Number
    public function getProductbyPartNumber(Request $request)
    {

        $q = strlen($request->q);

        $result='';
        if ($request->has('q') && $q > 3) {

            $query = DB::table('master_item_products')
            ->select('master_item_products.*', 'measures.name AS measure','master_items.name AS item')
            ->leftJoin('master_items', 'master_item_products.item_id', '=', 'master_items.id')
            ->leftJoin('measures', 'master_item_products.measure_id', '=', 'measures.id')
            ->where('master_item_products.part_number', 'ilike','%'.$request->q.'%')
            ->where('master_item_products.status', 1)
            ->whereNull('master_item_products.deleted_at')
            ->get();

            if(count($query) > 0){
            $result = '<div class="alert alert-danger">';
            $result .= '<table class="table table-fixed-header" style="font-size:11px">';
            $result .= "
                        <thead>
                            <tr style='border:none'>
                                <th colspan='3' style='border:none; font-weight:bold; text-align:center color:black; font-size:18px;'>
                                    Daftar PN/SPEC Sejenis yang terdapat di Master Produk
                                </th>
                            </tr>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Kategori</th>
                            </tr>
                        </thead>
                        <tbody>
                        ";
            foreach ($query as $val) {
                $part_number = '';
                if ($val->part_number != null){
                    $part_number = '<br>PN:'.$val->part_number;
                }

                $result .= "<tr>
                        <td style='padding:2px;'>[".$val->code."] ".$val->name.$part_number."</td>
                        <td style='padding:2px;'>".$val->measure."</td>
                        <td style='padding:2px;'>".$val->item."</td>
                    </tr>
                    ";
            }
            $result .= '</tbody></table></div></div>';
            echo $result;
            }else{
                echo $result;
            }
        }else{
            echo $result;
        }
    }
    

}
