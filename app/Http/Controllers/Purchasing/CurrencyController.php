<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class CurrencyController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:purchase_setting');
    }
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('purchase.master.currency.index');
    }

    public function datatables()
    {
        $result = DB::table('currencies')->orderBy('updated_at','ASC');
        
       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $action = "<a href='".route('purchasing.currency.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $action .= "<form class='delete' action='".route('purchasing.currency.destroy', $result->id)."' method='POST'>
                <input name='_method' type='hidden' value='DELETE'>
                    ".csrf_field()."
                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
            </form>";
            return
                '<div class="btn-group">'
                .$action.
                '</div>';
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
        return view('purchase.master.currency.create');
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
        if($request->get('status'))$data['status'] = 1;
        else $data['status'] = 0;
        $data['created_by'] = Auth::user()->id;
        $supplier =  Currency::create($data);
        return redirect()->route('purchasing.currency.index')->with(['success' => 'Add was successful!']);
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
        $data = Currency::findOrFail($id['0']);
        return view('purchase.master.currency.edit', compact('data'));
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
        if($request->get('status'))$data['status'] = 1;
        else $data['status'] = 0;

        $data['updated_by'] = Auth::user()->id;
        $items =  Currency::findOrFail($id);
        $items->update($data);
        return redirect()->route('purchasing.currency.index')->with(['success' => 'Edit was successful!']);
    }


    public function destroy($id)
    {
        $items  = Currency::findOrFail($id);
        $items->delete();
        return redirect()->route('purchasing.currency.index')->with(['success' => 'Delete was successful!']);
    }


    public function getCurrency()
    {
        return Currency::get()->pluck('name', 'id');
    }

}


