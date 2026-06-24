<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\PoPostMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class PoPostMailsController extends Controller
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

        return view('purchase.master.po_post_mail.index');
    }

    public function datatables()
    {
        $result = DB::table('po_post_mails')->orderBy('email','ASC');
        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('purchasing.po_post_mails.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";
            return
                '<div class="btn-group">'
                .$url_edit.
                '</div>';
        })
        ->editColumn('status', function ($result) {
            return getStatusEmailPO($result->status);
        })
        ->rawColumns(['action','status'])
        ->make(true);
    }

    public function create()
    {
        $status = array(
            '0' => 'Tidak Aktif',
            '1' => 'Aktif'
        );
        return view('purchase.master.po_post_mail.create',compact('status'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $po_post_mails =  PoPostMail::create($data);
        return redirect()->route('purchasing.po_post_mails')->with(['success' => 'Add was successful!']);
    }

    public function edit($id)
    {
        $id = Hashids::decode($id);
        $mail = PoPostMail::findOrFail($id['0']);
        $status = array(
            '0' => 'Tidak Aktif',
            '1' => 'Aktif'
        );
        return view('purchase.master.po_post_mail.edit', compact('mail','status'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $mail =  PoPostMail::findOrFail($id);
        $mail->update($data);
        return redirect()->route('purchasing.po_post_mails')->with(['success' => 'Edit was successful!']);
    }

}
