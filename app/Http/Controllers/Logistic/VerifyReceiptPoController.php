<?php

namespace App\Http\Controllers\Logistic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;
use App\Models\Lpb;
use App\Models\Bpb;
use Illuminate\Support\Facades\Log;

class VerifyReceiptPoController extends Controller
{
    public function index()
    {
        $lpbAll   = DB::table('lpb')->whereNotIn('status', [3, 4])->count();
        $lpbBelum = DB::table('lpb')->whereNotIn('status', [3, 4])->whereNull('verified_at')->count();
        $lpbSudah = DB::table('lpb')->whereNotIn('status', [3, 4])->whereNotNull('verified_at')->count();
        $lpbPending = DB::table('lpb')->whereNotIn('status', [3, 4])
                        ->whereNull('verified_at')
                        ->whereNotNull('verify_request_at')
                        ->count();

        $bpbAll   = DB::table('bpb')->whereNull('spb_id')->count();
        $bpbBelum = DB::table('bpb')->whereNull('spb_id')->whereNull('verified_at')->count();
        $bpbSudah = DB::table('bpb')->whereNull('spb_id')->whereNotNull('verified_at')->count();
        $bpbPending = DB::table('bpb')->whereNull('spb_id')
                        ->whereNull('verified_at')
                        ->whereNotNull('verify_request_at')
                        ->count();

        return view('logistic.verify-receipt-po.index', compact(
            'lpbAll', 'lpbBelum', 'lpbSudah', 'lpbPending',
            'bpbAll', 'bpbBelum', 'bpbSudah', 'bpbPending'
        ));
    }

    public function datatablesLpb(Request $request)
    {
        if (!Gate::allows('verify_receipt_po')) abort(401);

        $query = DB::table('lpb')
            ->select(
                'lpb.*',
                'po.doc_no AS po_no',
                'purchase_requisitions.doc_no AS pr_no',
                'purchase_requisitions.dpm_no AS dpm_no',
                'users.name AS created',
                'verifier.name AS verified_name',
            )
            ->leftJoin('po', 'po.id', '=', 'lpb.po_id')
            ->leftJoin('purchase_requisitions', 'po.purchase_id', '=', 'purchase_requisitions.id')
            ->leftJoin('users', 'users.id', '=', 'lpb.created_by')
            ->leftJoin('users AS verifier', 'verifier.id', '=', 'lpb.verified_by')
            ->whereNotIn('lpb.status', [3, 4]);

        if ($request->verified === '1') {
            $query->whereNotNull('lpb.verified_at');
        } elseif ($request->verified === '0') {
            $query->whereNull('lpb.verified_at');
        } elseif ($request->verified === 'pending') {
            $query->whereNull('lpb.verified_at')->whereNotNull('lpb.verify_request_at');
        }

        if (isAdministratorCompany()) {
            $query->where('po.company_id', Auth::user()->company_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('po_no', function ($row) {
                if ($row->po_id) {
                    return '<a href="' . route('purchasing.po.show', Hashids::encode($row->po_id)) . '" target="_blank">' . $row->po_no . '</a>';
                }
                return $row->po_no ?? '-';
            })
            ->editColumn('doc_no', function ($row) {
                return '<a href="' . route('logistic.verify-receipt-po.show-lpb', Hashids::encode($row->id)) . '" target="_blank">' . $row->doc_no . '</a>';
            })
            ->editColumn('status', function ($row) {
                return getStatusLPB($row->status, $row->spb_status);
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status_verifikasi', function ($row) {
                if ($row->verified_at) {
                    return '<span class="badge badge-success"><i class="ti-check mr-1"></i>Terverifikasi</span>';
                }
                if ($row->verify_request_at) {
                    return '<span class="badge badge-warning"><i class="ti-comment-alt mr-1"></i>Perlu Perbaikan</span>';
                }
                return '<span class="badge badge-secondary">Belum Diverifikasi</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('logistic.verify-receipt-po.show-lpb', Hashids::encode($row->id)) . '"
                            class="btn btn-sm btn-outline">
                            <i class="ti-settings"></i>
                        </a>';
            })
            ->rawColumns(['doc_no', 'po_no', 'status', 'status_verifikasi', 'aksi'])
            ->make(true);
    }

    public function datatablesBpb(Request $request)
    {
        if (!Gate::allows('verify_receipt_po')) abort(401);

        $query = DB::table('bpb')
            ->select(
                'bpb.*',
                'po.doc_no AS po_no',
                'users.name AS created',
                'verifier.name AS verified_name',
            )
            ->leftJoin('po', 'po.id', '=', 'bpb.po_id')
            ->leftJoin('users', 'users.id', '=', 'bpb.created_by')
            ->leftJoin('users AS verifier', 'verifier.id', '=', 'bpb.verified_by')
            ->whereNull('bpb.spb_id');

        if ($request->verified === '1') {
            $query->whereNotNull('bpb.verified_at');
        } elseif ($request->verified === '0') {
            $query->whereNull('bpb.verified_at');
        } elseif ($request->verified === 'pending') {
            $query->whereNull('bpb.verified_at')->whereNotNull('bpb.verify_request_at');
        }

        if (isAdministratorCompany()) {
            $query->where('po.company_id', Auth::user()->company_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('po_no', function ($row) {
                if ($row->po_id) {
                    return '<a href="' . route('purchasing.po.show', Hashids::encode($row->po_id)) . '" target="_blank">' . $row->po_no . '</a>';
                }
                return $row->po_no ?? '-';
            })
            ->editColumn('doc_no', function ($row) {
                return '<a href="' . route('logistic.verify-receipt-po.show-bpb', Hashids::encode($row->id)) . '" target="_blank">' . $row->doc_no . '</a>';
            })
            ->editColumn('status', function ($row) {
                return getStatusData($row->status);
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status_verifikasi', function ($row) {
                if ($row->verified_at) {
                    return '<span class="badge badge-success"><i class="ti-check mr-1"></i>Terverifikasi</span>';
                }
                if ($row->verify_request_at) {
                    return '<span class="badge badge-warning"><i class="ti-comment-alt mr-1"></i>Perlu Perbaikan</span>';
                }
                return '<span class="badge badge-secondary">Belum Diverifikasi</span>';
            })
            ->addColumn('aksi', function ($row) {
                return '<a href="' . route('logistic.verify-receipt-po.show-bpb', Hashids::encode($row->id)) . '"
                            class="btn btn-sm btn-outline">
                            <i class="ti-settings"></i>
                        </a>';
            })
            ->rawColumns(['doc_no', 'po_no', 'status', 'status_verifikasi', 'aksi'])
            ->make(true);
    }

    public function verify(Request $request)
    {
        if (!Gate::allows('verify_receipt_po')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id    = Hashids::decode($request->id)[0];
        $type  = $request->type;
        $table = $type === 'lpb' ? 'lpb' : 'bpb';

        DB::table($table)->where('id', $id)->update([
            'verified_by'          => Auth::user()->id,
            'verified_at'          => now(),
            'verified_notes'       => $request->verified_notes,
            'verify_request_notes' => null,  // ← reset
            'verify_request_by'    => null,  // ← reset
            'verify_request_at'    => null,  // ← reset
        ]);

        return response()->json([
            'message'        => 'Berhasil diverifikasi',
            'verified_by'    => Auth::user()->name,
            'verified_at'    => now()->format('d/m/Y H:i'),
            'verified_notes' => $request->verified_notes,
        ]);
    }

    public function showLpb($id)
    {
        if (!Gate::allows('verify_receipt_po')) abort(401);

        $id          = Hashids::decode($id);
        $lpb         = Lpb::getByID($id[0]);
        $lpb_items   = Lpb::getProductItem($id[0]);
        $lpb_history = Lpb::getHistory($id[0]);

        return view('logistic.verify-receipt-po.show-lpb', compact('lpb', 'lpb_items', 'lpb_history'));
    }

    public function showBpb($id)
    {
        if (!Gate::allows('verify_receipt_po')) abort(401);

        $id        = Hashids::decode($id);
        $bpb       = Bpb::findOrFail($id[0]);
        $bpb_items = Bpb::getProductFrancoItem($id[0]);

        return view('logistic.verify-receipt-po.show-bpb', compact('bpb', 'bpb_items'));
    }

    public function requestPerbaikan(Request $request)
    {
        if (!Gate::allows('verify_receipt_po')) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $decoded = Hashids::decode($request->id);

        if (empty($decoded)) {
            return redirect()->back()->with('error', 'ID tidak valid');
        }

        $id = $decoded[0];
        $table = $request->type === 'lpb' ? 'lpb' : 'bpb';

        DB::table($table)->where('id', $id)->update([
            'verify_request_notes' => $request->verify_request_notes,
            'verify_request_by'    => Auth::id(),
            'verify_request_at'    => now(),
        ]);

        $docData = DB::table($table)->where('id', $id)->first();
        $userReq = DB::table('users')->where('id', Auth::id())->first();

        try {
            if ($docData && $userReq) {

                $userTujuan = DB::table('users')
                    ->where('id', $docData->created_by)
                    ->first();

                if ($userTujuan && $userTujuan->is_whatsapp && $userTujuan->telp) {

                    $body  = "*From User:* {$userReq->name}\n";
                    $body .= "*To User:* {$userTujuan->name}\n\n";
                    $body .= "Mohon lengkapi pesan berikut:\n";
                    $body .= ($docData->doc_no ?? '-') . "\n";
                    $body .= "Pesan: " . ($request->verify_request_notes ?? '-') . "\n\n";
                    $body .= "Silahkan lengkapi pesan tersebut di ERP Shipping";

                    $this->sendWhatsapp($userTujuan->telp, $body);
                }
            }

        } catch (\Exception $e) {
            \Log::error('WA Error: ' . $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', 'Berhasil mengirim request perbaikan dan notifikasi WA');
    }
    private function sendWhatsapp($telp,$body){
        $curl = curl_init();
        $token = "QCAJBftXQTi2ZbJMkhp4";
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $telp,
            'message' => $body,
            ),
        CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }
}
