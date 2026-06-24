<?php

namespace App\Http\Controllers\Logistic;

use App\Models\UserAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use Auth;
use File;

use Rap2hpoutre\FastExcel\FastExcel;

class MasterUserAssetController extends Controller
{

    public function index()
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        return view('logistic.master_user_asset.index');
    }

    public function datatables()
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        $result = DB::table('user_assets')->orderBy('name','ASC');
        return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $encodedId = Hashids::encode($result->id);
            $action = "<a href='" . route('logistic.master_user_asset.edit', ['master_user_asset' => $encodedId]) . "'
                title='" . trans('Edit') . "' data-toggle='tooltip' class='btn btn-outline'>
                <span class='ti-pencil icon-lg'></span>
            </a>";
            if(Auth::user()->id == 1){
                $action .= "
                    <form action='" . route('logistic.master_user_asset.destroy', ['master_user_asset' => $encodedId]) . "'
                        method='POST' style='display:inline-block;'>
                        " . csrf_field() . method_field('DELETE') . "
                        <button type='submit' class='btn btn-outline btn_deleteee' title='" . trans('Delete') . "' data-toggle='tooltip'>
                            <span class='ti-trash text-danger icon-lg'></span>
                        </button>
                    </form>";
            }
            return '<div class="btn-group">' . $action . '</div>';
        })
        ->addColumn('status', function ($result){
            return getStatusUserAsset($result->status);
        })
        ->rawColumns(['action', 'status'])
        ->make(true);
    }

    public function create()
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        return view('logistic.master_user_asset.create');
    }

    public function store(Request $request)
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        $ceknik = UserAsset::where('nik',strtoupper($request->get('nik')))->count();
        if($ceknik){
            return redirect()->back()
                ->withInput($request->input())
                ->withErrors(['Terdapat Duplikasi Data NIK silahkan cek melalui Fitur Pencarian']);
        }else{
            $data['name']       = strtoupper($request->get('name'));
            $data['nik']        = strtoupper($request->get('nik'));;
            $data['status']     = $request->get('status');
            $data['created_by'] = Auth::user()->id;
            $items = UserAsset::create($data);
            return redirect()->route('logistic.master_user_asset.index')->with(['success' => 'Berhasil menambahkan data!']);
        }
    }

    public function edit($idx)
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        $id = Hashids::decode($idx);
        $data = DB::table('user_assets')->where('id','=',$id)->first();
        return view('logistic.master_user_asset.edit', compact('data'));
    }

    public function update(Request $request, $idx)
    {
        if (!Gate::allows('master_user_asset')) {
            abort(401);
        }
        $decoded = Hashids::decode($idx);
        $id = $decoded[0] ?? null;
        if (!$id) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $data['name']       = strtoupper($request->get('name'));
        $data['nik']        = strtoupper($request->get('nik'));
        $data['status']     = $request->get('status');
        $data['updated_by'] = Auth::user()->id;

        $items = UserAsset::findOrFail($id);
        $items->update($data);

        return redirect()->route('logistic.master_user_asset.index')->with('success', 'Berhasil melakukan edit!');
    }

    public function destroy($idx)
    {
        if (! Gate::allows('master_user_asset')) {
            return abort(401);
        }
        $decoded = Hashids::decode($idx);
        $id = $decoded[0] ?? null;
        $items  = UserAsset::findOrFail($id);
        $items->delete();
        return redirect()->route('logistic.master_user_asset.index')->with(['success' => 'Delete was successful!']);
    }

}
