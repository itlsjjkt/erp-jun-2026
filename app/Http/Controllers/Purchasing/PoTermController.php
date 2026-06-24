<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PoTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class PoTermController extends Controller
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
        
        return view('purchase.master.po_term.index');
    }

    public function datatables()
    {
       
        $result = DB::table('po_terms')
        ->select('po_terms.*')
        ->orderBy('updated_at','ASC');
        

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('purchasing.po_terms.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('purchasing.po_terms.delete', ['id' => $result->id])."' method='POST'>
                                 ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                .$url_edit .$url_delete.
                '</div>';
        })->addColumn('status', function ($result){
            if($result->status==1){
                return "<span class='badge badge-success'>Aktif</span>";
            }else{
                return "<span class='badge badge-danger'>Non Aktif</span>";
            }
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
        return view('purchase.master.po_term.create');
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
        if($request->get('status')){
            $data['status'] = 1;
        }else{
            $data['status'] = 0;
        }
        $data['created_by'] = Auth::user()->id;
        $supplier =  PoTerm::create($data);

        return redirect()->route('purchasing.po_terms.index')->with(['success' => 'Add was successful!']);
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
        $supplier = PoTerm::findOrFail($id['0']);
        return view('purchase.master.po_term.edit', compact('supplier'));
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
        if($request->get('status')){
            $data['status'] = 1;
        }else{
            $data['status'] = 0;
        }
        $data['updated_by'] = Auth::user()->id;
        $items =  PoTerm::findOrFail($id);
        $items->update($data);
        return redirect()->route('purchasing.po_terms.index')->with(['success' => 'Edit was successful!']);
        
    }


    public function delete(Request $request)
    {
        $items  = PoTerm::findOrFail($request->id);
        $items->delete();
        return redirect()->route('purchasing.po_terms.index')->with(['success' => 'Delete was successful!']);

    }


    public function getPoTerm()
    {
        return PoTerm::get()->pluck('name', 'id');
    }

}


