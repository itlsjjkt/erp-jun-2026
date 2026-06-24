<?php

namespace App\Http\Controllers\Accounting;

use App\Models\Accounting\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class AssetCategoryController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:asset_category');
    }
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('accounting.asset_category.index');
    }

    public function datatables()
    {
        $result = AssetCategory::whereNull('deleted_at');
        
       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('accounting.asset_category.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('accounting.asset_category.destroy',  $result->id)."' method='POST'>
                    <input name='_method' type='hidden' value='DELETE'>
                    ".csrf_field()."
                    <button class='btn btn-outline text-danger' title='".trans('global.btn_delete')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                </form>";
                
            return
                '<div class="btn-group">'
                .$url_edit .$url_delete.
                '</div>';
        })->addColumn('compute_method', function ($result){
            return ucwords($result->compute_method);
        })
        ->addColumn('time_method', function ($result){
            return ($result->time_method=='date') ? 'Ending Date' : 'Number of Entries';
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
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
        $item = '';
        return view('accounting.asset_category.create', compact('item'));
    }

    /**
     * Store a newly created Items in storage.
     *
     * @param  \App\Http\Requests\StoreItemssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = $request->all();
        $data['created_by'] = Auth::user()->id;
        AssetCategory::create($data);

        return redirect()->route('accounting.asset_category.index')->with(['success' => 'Add was successful!']);
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
        $item = AssetCategory::findOrFail($id['0']);
        return view('accounting.asset_category.edit', compact('item'));
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
       
        $data = $request->all();
        $data['updated_by'] = Auth::user()->id;
        $items =  AssetCategory::findOrFail($id);
        $items->update($data);
        return redirect()->route('accounting.asset_category.index')->with(['success' => 'Edit was successful!']);
        
    }


    public function destroy($id)
    {
        $items  = AssetCategory::findOrFail($id);
        $data['updated_by'] = Auth::user()->id;
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $items->update($data);
        return redirect()->route('accounting.asset_category.index')->with(['success' => 'Delete was successful!']);
    }

    public function get($id)
    {
        $result = AssetCategory::findOrFail($id);
        return response()->json($result);
    }

}


