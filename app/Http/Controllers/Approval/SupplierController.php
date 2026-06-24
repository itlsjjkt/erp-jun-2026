<?php

namespace App\Http\Controllers\Approval;

use App\Models\Supplier;
use App\Models\SupplierApprovalHistory;
use App\Models\Notification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Vinkla\Hashids\Facades\Hashids;
use Auth;

class SupplierController extends Controller
{
    public function index()
    {
        if (! Gate::allows('approval_supplier')) {
            return abort(401);
        }
        return view('approval.supplier.index');
    }

    public function datatables()
    {
        if (! Gate::allows('approval_supplier')) {
            return abort(401);
        }

        $result = DB::table('suppliers')
            ->select('suppliers.*', 'users.name AS created_by_name')
            ->leftJoin('users', 'users.id', '=', 'suppliers.created_by')
            ->where('suppliers.position', Auth::user()->id)
            ->where('suppliers.approval_status', 1)
            ->orderBy('suppliers.created_at', 'DESC');

        return DataTables::of($result)
            ->addColumn('action', function ($result) {
                $url = "<a href='".route('approval.supplier.set', Hashids::encode($result->id))."' title='Proses Approval' data-toggle='tooltip' class='btn btn-outline'><span class='ti-thumb-up icon-lg'></span></a>";
                return '<div class="btn-group">'.$url.'</div>';
            })
            ->editColumn('created_at', function ($result) {
                return $result->created_at ? with(new Carbon($result->created_at))->format('d/m/Y') : '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function set($id)
    {
        if (! Gate::allows('approval_supplier')) {
            return abort(401);
        }

        $id = Hashids::decode($id)[0];

        $supplier = DB::table('suppliers as s')
            ->select('s.*', 'pt.name AS payment_term_name', 'pm.name AS payment_method_name', 'u.name AS created_by_name')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 's.payment_term')
            ->leftJoin('payment_methods as pm', 'pm.id', '=', 's.payment_method_id')
            ->leftJoin('users as u', 'u.id', '=', 's.created_by')
            ->where('s.id', $id)
            ->first();

        $contacts = DB::table('supplier_contacts')->where('supplier_id', $id)->get();

        $separatedTelp = [];
        foreach ($contacts as $item) {
            $telp = $item->telp;
            if (strpos($telp, '||') !== false) {
                $parts         = explode('||', $telp);
                $separatedTelp[] = ['telp1' => $parts[0], 'telp2' => $parts[1]];
            } else {
                $separatedTelp[] = ['telp1' => $telp, 'telp2' => '-'];
            }
        }

        $categories = DB::table('supplier_categories as sc')
            ->select('m.name')
            ->leftJoin('master_items as m', 'm.id', '=', 'sc.category_id')
            ->where('sc.supplier_id', $id)
            ->get();

        $history = DB::table('supplier_approval_histories as h')
            ->select('h.*', 'u.name as user_name')
            ->leftJoin('users as u', 'u.id', '=', 'h.user_id')
            ->where('h.supplier_id', $id)
            ->orderBy('h.created_at', 'DESC')
            ->get();

        $notification = Notification::where([
            'user_id' => Auth::user()->id,
            'data_id' => $id,
            'status'  => 0,
        ])->first();

        if ($notification) {
            $notification->update(['status' => 1]);
        }

        return view('approval.supplier.set', compact('supplier', 'contacts', 'separatedTelp', 'categories', 'history'));
    }

    public function update(Request $request, $id)
    {
        if (! Gate::allows('approval_supplier')) {
            return abort(401);
        }

        $supplier   = Supplier::findOrFail($id);
        $status     = (int) $request->get('status');

        $totalSteps = DB::table('approval_suppliers')->count();

        if ($status === 1) {
            if ($supplier->step >= $totalSteps) {
                $supplier->update([
                    'approval_status' => 2,
                    'position'        => null,
                    'updated_by'      => Auth::user()->id,
                ]);

                Notification::create([
                    'title'   => 'Supplier Disetujui',
                    'link'    => '/purchasing/suppliers/'.Hashids::encode($supplier->id),
                    'data_id' => $supplier->id,
                    'content' => 'Supplier '.$supplier->name.' telah disetujui.',
                    'user_id' => $supplier->created_by,
                    'status'  => 0,
                ]);
            } else {
                $nextApproval = DB::table('approval_suppliers')->where('step', $supplier->step + 1)->first();
                $supplier->update([
                    'step'            => $supplier->step + 1,
                    'position'        => $nextApproval->user_id,
                    'approval_status' => 1,
                    'updated_by'      => Auth::user()->id,
                ]);

                Notification::create([
                    'title'   => 'Approval Supplier',
                    'link'    => '/approval/supplier_set/'.Hashids::encode($supplier->id),
                    'data_id' => $supplier->id,
                    'content' => 'Terdapat pengajuan supplier: '.$supplier->name,
                    'user_id' => $nextApproval->user_id,
                    'status'  => 0,
                ]);
            }
            $jenis = 'approval';
        } else {
            $supplier->update([
                'approval_status' => 3,
                'position'        => null,
                'updated_by'      => Auth::user()->id,
            ]);

            Notification::create([
                'title'   => 'Revisi Supplier',
                'link'    => '/purchasing/suppliers/'.Hashids::encode($supplier->id).'/edit',
                'data_id' => $supplier->id,
                'content' => 'Supplier '.$supplier->name.' perlu diperbaiki. Catatan: '.$request->get('message'),
                'user_id' => $supplier->created_by,
                'status'  => 0,
            ]);

            $jenis = 'revisi';
        }

        SupplierApprovalHistory::create([
            'supplier_id'   => $id,
            'user_id'       => Auth::user()->id,
            'jenis'         => $jenis,
            'message'       => $request->get('message'),
            'date_approved' => now(),
        ]);

        $msg = $status === 1 ? 'Approval Supplier Berhasil!' : 'Revisi Supplier Berhasil!';
        return redirect()->route('approval.supplier.index')->with(['success' => $msg]);
    }
}
