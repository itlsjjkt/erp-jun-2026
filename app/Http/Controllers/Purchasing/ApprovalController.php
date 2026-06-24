<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Company;
use App\Models\ApprovalPurchasing;
use App\Models\ApprovalDph;
use App\Models\CheckerPc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class ApprovalController extends Controller
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

        return view('purchase.master.approval.index');
    }

    public function datatables()
    {
        $result = DB::table('companies');

       return  DataTables::of($result)
       ->addColumn('action', function ($result) {
            $url_edit_po = "<a style='margin-left: 35px;' href='".route('purchasing.approval.config', ['id' => $result->id])."' title='Edit Approval PO' data-toggle='tooltip' class='btn btn-outline'><span class='ti-file icon-lg '></span><br><small class='ti-settings '>Approval PO</small> </a>";
            // $url_edit_dph = "<a href='".route('purchasing.approval.config_dph', ['id' => $result->id])."' title='Edit Approval DPH' data-toggle='tooltip' class='btn btn-outline'><span class='ti-files icon-lg '></span><br><small class='ti-settings '>Approval DPH</small> </a>";
            $url_edit_pc = "<a href='".route('purchasing.approval.config_pc', ['id' => $result->id])."' title='Edit Checker PC' data-toggle='tooltip' class='btn btn-outline'><span class='ti-files icon-lg '></span><br><small class='ti-settings '>Checker PC</small> </a>";
            return
                '<div class="btn-group">'
                .$url_edit_po.$url_edit_pc.
                '</div>';
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }


    public function config($id)
    {
        $company  = Company::findOrFail($id);
        $approval = ApprovalPurchasing::select('approval_purchasings.*','users.name as name')
        ->leftJoin('users', 'users.id', '=', 'approval_purchasings.user_id')
        ->where('approval_purchasings.company_id', $id)
        ->orderBy('approval_purchasings.step','ASC')
        ->get();
        return view('purchase.master.approval.config', compact('company','approval'));
    }

    public function store(Request $request)
    {
        $delete = ApprovalPurchasing::where('company_id', $request->company_id)->delete();
        $dataApproval = [];
        $user = $request->get('user_id');
        for($i=0;$i < count($user);$i++) {
            $dataApproval[] = [
                'user_id'       => $request->get('user_id')[$i],
                'step'          => $request->get('step')[$i],
                'company_id'   => $request->company_id,
            ];
        }
        ApprovalPurchasing::insert($dataApproval);
        return redirect()->route('purchasing.approval')->with(['success' => 'Config was successful!']);
    }
    public function config_dph($id)
    {
        $company  = Company::findOrFail($id);
        $approval = ApprovalDph::select('approval_dph.*','users.name as name')
        ->leftJoin('users', 'users.id', '=', 'approval_dph.user_id')
        ->where('approval_dph.company_id', $id)
        ->orderBy('approval_dph.step','ASC')
        ->get();
        return view('purchase.master.approval.config_dph', compact('company','approval'));
    }

    public function store_dph(Request $request)
    {
        $delete = ApprovalDph::where('company_id', $request->company_id)->delete();
        $dataApproval = [];
        $user = $request->get('user_id');
        for($i=0;$i < count($user);$i++) {
            $dataApproval[] = [
                'user_id'       => $request->get('user_id')[$i],
                'step'          => $request->get('step')[$i],
                'company_id'   => $request->company_id,
            ];
        }
        ApprovalDph::insert($dataApproval);
        return redirect()->route('purchasing.approval')->with(['success' => 'Config Dph was successful!']);
    }

    public function config_pc($id)
    {
        $company  = Company::findOrFail($id);
        $approval = CheckerPc::select('checker_pc.*','users.name as name')
        ->leftJoin('users', 'users.id', '=', 'checker_pc.user_id')
        ->where('checker_pc.company_id', $id)
        ->orderBy('checker_pc.step','ASC')
        ->get();
        return view('purchase.master.approval.config_pc', compact('company','approval'));
    }

    public function store_pc(Request $request)
    {
        $delete = CheckerPc::where('company_id', $request->company_id)->delete();
        $dataApproval = [];
        $user = $request->get('user_id');
        for($i=0;$i < count($user);$i++) {
            $dataApproval[] = [
                'user_id'       => $request->get('user_id')[$i],
                'company_id'   => $request->company_id,
            ];
        }
        CheckerPc::insert($dataApproval);
        return redirect()->route('purchasing.approval')->with(['success' => 'Config Pc was successful!']);
    }

}
