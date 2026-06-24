<?php

namespace App\Http\Controllers\Accounting;

use App\Models\Accounting\Asset;
use App\Models\Currency;
use App\Models\Accounting\AssetCategory;
use App\Models\Accounting\AssetDepreciationBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class AssetController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:asset-list|asset-create|asset-edit|asset-delete', ['only' => ['index']]);
        $this->middleware('permission:asset-create', ['only' => ['create','store']]);
        $this->middleware('permission:asset-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:asset-delete', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('accounting.asset.index');
    }

    public function datatables()
    {
    
        $result = Asset::getData();
        
        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $action = "<a href='".route('accounting.asset.show', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-eye icon-lg'></span> </a>";  
            $action .= "<a href='".route('accounting.asset.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $action .= "<form class='delete' action='".route('accounting.asset.destroy',  $result->id)."' method='POST'>
                    <input name='_method' type='hidden' value='DELETE'>
                    ".csrf_field()."
                    <button class='btn btn-outline text-danger' title='".trans('global.btn_delete')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                </form>";
                
            return
                '<div class="btn-group">'
                .$action.
                '</div>';
        })
        ->editColumn('product', function ($result){
            return $result->product."<br><small>".$result->productCode."</small>";
        })
        ->addColumn('gross_value', function ($result){
            return format_number($result->gross_value);
        })
        ->addColumn('salvage_value', function ($result){
            return format_number($result->salvage_value);
        })
        ->addColumn('residual_value', function ($result){
            $residual_value = $result->gross_value-$result->salvage_value;
            return format_number($residual_value);
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['action', 'status','product'])
        ->make(true);

    }

    /**
     * Show the form for creating new Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $item = '';
        $currency = Currency::orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $category = AssetCategory::whereNull('deleted_at')->get()->pluck('name','id')->prepend('Silahkan pilih...', '');
        return view('accounting.asset.create', compact('item','category','currency'));
    }

    /**
     * Store a newly created Items in storage.
     *
     * @param  \App\Http\Requests\StoreItemssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        DB::beginTransaction();

        try {

            $data = $request->all();
            $data['gross_value'] = str_replace(",", "", $request->get('gross_value'));
            $data['salvage_value'] = ($request->get('salvage_value')) ? str_replace(",", "", $request->get('salvage_value')) : '0.00';
            $data['created_by'] = Auth::user()->id;

            $asset = Asset::create($data);

            $depreciation = [];
            $increment = $cumulative = 0;
            if($request->get('time_method')=='number') $loop = $request->get('number_entry');
            else $loop = get_interval_in_month($request->get('ending_date'),$request->get('date_input'),'month') / $request->get('number_sequence');

            $grossValue = ( $request->get('gross_value')) ? str_replace(",", "", $request->get('gross_value')) : 0;
            $salvageValue = ( $request->get('salvage_value')) ? str_replace(",", "", $request->get('salvage_value')) : 0;

            $gross_value = $grossValue  - $salvageValue;
            if($request->get('compute_method')=='linier') $depreciation_val =  $gross_value/$loop;
            else $depreciation_val =  $gross_value;

            for($i=0;$i < $loop ;$i++) {
                if($request->get('compute_method')=='degressive'){
                    if($loop - $i == 1) $depreciation_value = $depreciation_val - $cumulative;
                    else{
                        $residual = $depreciation_val - $cumulative;
                        $depreciation_value = $residual*$request->get('degressive_factor');
                    } 
                    $depreciation [] = array(
                        'asset_id' => $asset->id,
                        'date' => date('Y-m-d', strtotime( $request->get('date_input').' + '.$increment.' months')),
                        'depreciation' => $depreciation_value,
                    );
                    $cumulative += $residual*$request->get('degressive_factor');
                }else{
                    $depreciation [] = array(
                        'asset_id' => $asset->id,
                        'date' => date('Y-m-d', strtotime( $request->get('date_input').' + '.$increment.' months')),
                        'depreciation' => $depreciation_val,
                    );
                }
                $increment += $request->get('number_sequence');
            }

            AssetDepreciationBoard::insert($depreciation);

            DB::commit();
            return redirect()->route('accounting.asset.index')->with(['success' => 'Add was successful!']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
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
        $id = Hashids::decode($id);
        $item = Asset::findOrFail($id['0']);
        $currency = Currency::orderBy('name','ASC')->get()->pluck('name', 'name')->prepend('Silahkan pilih...', '');
        $category = AssetCategory::whereNull('deleted_at')->get()->pluck('name','id')->prepend('Silahkan pilih...', '');
        return view('accounting.asset.edit', compact('item','category','currency'));
    }

    public function show($id)
    {
        $id = Hashids::decode($id);
        $item = Asset::findOrFail($id['0']);
        return view('accounting.asset.show', compact('item'));
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
       
        $asset = Asset::findOrFail($id);

        DB::beginTransaction();

        try {

            $data = $request->all();
            $data['gross_value'] = str_replace(",", "", $request->get('gross_value'));
            $data['salvage_value'] = str_replace(",", "", $request->get('salvage_value'));
            $data['updated_by'] = Auth::user()->id;
            $asset->update($data);

            if(count($asset->depreciation)) AssetDepreciationBoard::where('asset_id',$id)->delete();

            $depreciation = [];
            $increment = $cumulative = 0;
            if($request->get('time_method')=='number') $loop = $request->get('number_entry');
            else $loop = get_interval_in_month($request->get('ending_date'),$request->get('date_input'),'month') / $request->get('number_sequence');

            $grossValue = ( $request->get('gross_value')) ? str_replace(",", "", $request->get('gross_value')) : 0;
            $salvageValue = ( $request->get('salvage_value')) ? str_replace(",", "", $request->get('salvage_value')) : 0;
            $gross_value = $grossValue  - $salvageValue;

            if($request->get('compute_method')=='linier') $depreciation_val =  $gross_value/$loop;
            else $depreciation_val =  $gross_value;

            for($i=0;$i < $loop ;$i++) {
                if($request->get('compute_method')=='degressive'){
                    if($loop - $i == 1) $depreciation_value = $depreciation_val - $cumulative;
                    else{
                        $residual = $depreciation_val - $cumulative;
                        $depreciation_value = $residual*$request->get('degressive_factor');
                    } 
                    $depreciation [] = array(
                        'asset_id' => $asset->id,
                        'date' => date('Y-m-d', strtotime( $request->get('date_input').' + '.$increment.' months')),
                        'depreciation' => $depreciation_value,
                    );
                    $cumulative += $residual*$request->get('degressive_factor');
                }else{
                    $depreciation [] = array(
                        'asset_id' => $asset->id,
                        'date' => date('Y-m-d', strtotime( $request->get('date_input').' + '.$increment.' months')),
                        'depreciation' => $depreciation_val,
                    );
                }
                $increment += $request->get('number_sequence');
            }

            AssetDepreciationBoard::insert($depreciation);

            DB::commit();
            return redirect()->route('accounting.asset.index')->with(['success' => 'Edit was successful!']);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function destroy($id)
    {
        $items  = Asset::findOrFail($id);
        $data['updated_by'] = Auth::user()->id;
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $items->update($data);
        return redirect()->route('accounting.asset.index')->with(['success' => 'Delete was successful!']);
    }


}


