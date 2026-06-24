<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {
        $this->middleware('permission:purchase_setting');
    }

    public function index()
    {
        return view('purchase.master.payment_method.index');
    }

    public function datatables()
    {
        $result = DB::table('payment_methods')
        ->select('payment_methods.*')
        ->orderBy('updated_at','ASC');
        

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('purchasing.payment_methods.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('purchasing.payment_methods.delete', ['id' => $result->id])."' method='POST'>
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
        return view('purchase.master.payment_method.create');
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
        $supplier =  PaymentMethod::create($data);

        return redirect()->route('purchasing.payment_methods.index')->with(['success' => 'Add was successful!']);
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
        $supplier = PaymentMethod::findOrFail($id['0']);
        return view('purchase.master.payment_method.edit', compact('supplier'));
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
        $items =  PaymentMethod::findOrFail($id);
        $items->update($data);
        return redirect()->route('purchasing.payment_methods.index')->with(['success' => 'Edit was successful!']);
        
    }


    public function delete(Request $request)
    {
        $items  = PaymentMethod::findOrFail($request->id);
        $items->delete();
        return redirect()->route('purchasing.payment_methods.index')->with(['success' => 'Delete was successful!']);
    }


    public function getPaymentMethod()
    {
        return PaymentMethod::get()->pluck('name', 'id');
    }

}


