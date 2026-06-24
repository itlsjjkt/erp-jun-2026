<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\ApprovalSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class ApprovalSupplierSettingController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:purchase_setting');
    }

    public function index()
    {
        $approval = ApprovalSupplier::select('approval_suppliers.*', 'users.name as name')
            ->leftJoin('users', 'users.id', '=', 'approval_suppliers.user_id')
            ->orderBy('approval_suppliers.step', 'ASC')
            ->get();

        return view('purchase.master.approval.config_supplier', compact('approval'));
    }

    public function store(Request $request)
    {
        ApprovalSupplier::truncate();

        $dataApproval = [];
        $user         = $request->get('user_id');

        for ($i = 0; $i < count($user); $i++) {
            $dataApproval[] = [
                'user_id'    => $request->get('user_id')[$i],
                'step'       => $request->get('step')[$i],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ApprovalSupplier::insert($dataApproval);

        return redirect()->route('purchasing.approval.supplier.config')->with(['success' => 'Config Approval Supplier berhasil disimpan!']);
    }
}
