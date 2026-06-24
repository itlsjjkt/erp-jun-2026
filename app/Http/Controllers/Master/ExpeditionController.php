<?php

namespace App\Http\Controllers\Master;

use App\Models\Expedition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class ExpeditionController extends Controller
{
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
        return view('master.expedition.index');
    }

    public function datatables()
    {
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
        $result = DB::table('expeditions')
        ->select('expeditions.*')
        ->orderBy('updated_at','DESC');
        

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('master.expeditions.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('master.expeditions.delete', ['id' => $result->id])."' method='POST'>
                                 ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";

            return
                '<div class="btn-group">'
                    .$url_edit .$url_delete.
                '</div>';
        }) 
        ->addColumn('status', function ($result){
            if($result->status==1){
                return "<span class='badge badge-success'>Aktif</span>";
            }else{
                return "<span class='badge badge-danger'>Non Aktif</span>";
            }
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['action', 'status','block'])
        ->make(true);

    }

    /**
     * Show the form for creating new Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
        return view('master.expedition.create');
    }

    /**
     * Store a newly created Items in storage.
     *
     * @param  \App\Http\Requests\StoreItemssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }

        $data = $request->all();
        if ($request->get('status'))  $data['status'] = 1;
        else $data['status'] = 0;

        if ($request->get('is_handcarry'))  $data['is_handcarry'] = true;
        else $data['is_handcarry'] = false;

        $data['created_by'] = Auth::user()->id;
        $expedition =  Expedition::create($data);

        return redirect()->route('master.expeditions.index')->with(['success' => 'Add was successful!']);
    }


    /**
     * Show the form for editing Items.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
       
        $id = Hashids::decode($id);
        $expedition = Expedition::findOrFail($id['0']);

        return view('master.expedition.edit', compact('expedition'));
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
        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
        $data = $request->all();
        if ($request->get('status'))  $data['status'] = 1;
        else $data['status'] = 0;

        if ($request->get('is_handcarry'))  $data['is_handcarry'] = true;
        else $data['is_handcarry'] = false;

        $data['updated_by'] = Auth::user()->id;
        $items =  Expedition::findOrFail($id);
        $items->update($data);

        return redirect()->route('master.expeditions.index')->with(['success' => 'Edit was successful!']);
        
    }


    public function delete(Request $request)
    {

        if (! Gate::allows('master_expedition')) {
            return abort(401);
        }
        $items  = Expedition::findOrFail($request->id);
        $items->delete();
        return redirect()->route('master.expeditions.index')->with(['success' => 'Delete was successful!']);

    }


    public function getExpedition()
    {
        return Expedition::where()->pluck('name', 'id');
    }


}


