<?php

namespace App\Http\Controllers\Master;

use App\Models\Company;
use App\Models\Workarea;
use App\Models\ApprovalLogistic;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class WorkareaController extends Controller
{

    function __construct()
    {
    }
    /**
     * Display a listing of Workarea.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->middleware('permission:setting_company');
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);

        return view('master.workarea.index', compact('company'));
    }


    public function datatables($company_id)
    {
       
        $result = DB::table('locations')
            ->select('locations.*','areas.name AS area')
            ->leftJoin('areas','areas.id','=','locations.area_id')
            ->where('company_id',$company_id)
            ->orderBy('locations.isDPM','DESC')
            ->orderBy('locations.name', 'ASC');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_approval = "<a href='".route('workarea.approval', ['id' => Hashids::encode($result->id)])."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-settings icon-lg'></span> </a>";  
            $url_edit = "<a href='".route('workarea.edit', [$result->id, 'company_id' => Hashids::encode($result->company_id)] )."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('workarea.destroy', [$result->id, 'company_id' => Hashids::encode($result->company_id)])."' method='POST'>
                                <input name='_method' type='hidden' value='DELETE'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                 .$url_edit .$url_delete.  $url_approval.
                '</div>';
        }) 
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->editColumn('isDPM', function ($result) {
            return $result->isDPM ? '<span class="fw-bold text-success">TRUE<span>' : '<span class="fw-bold text-danger">FALSE<span>' ; 
        })
        ->editColumn('area', function ($result) {
            return $result->area ? $result->area:'-';
        })
        ->editColumn('status', function ($result) {
            return $result->status == 1 ? '<span class="badge badge-success">Aktif<span>' : '<span class="badge badge-danger">Non-Aktif<span>' ;
        })
        ->rawColumns(['isDPM','action','status'])
        ->make(true);

    }

    /**
     * Show the form for creating new Workarea.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->middleware('permission:setting_company');
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);
        $area  = DB::table('areas')->pluck('name','id')->prepend('Pilih Area', '');

        return view('master.workarea.create', compact('company','area'));
    }

    /**
     * Store a newly created Workarea in storage.
     *
     * @param  \App\Http\Requests\StoreWorkareasRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->middleware('permission:setting_company');
        $workarea = Workarea::create($request->all());
        return redirect()->route('workarea.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Add was successful!']);
    }


    /**
     * Show the form for editing Workarea.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $this->middleware('permission:setting_company');
        $company_id = Hashids::decode($request->company_id);
        $company  = Company::findOrFail($company_id['0']);
        $workarea = Workarea::findOrFail($id);
        $area  = DB::table('areas')->pluck('name','id')->prepend('Pilih Area', '');
        return view('master.workarea.edit', compact('workarea','company','area'));
    }

    /**
     * Update Workarea in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->middleware('permission:setting_company');
        $workarea = Workarea::findOrFail($id);
        $workarea->update($request->all());
        return redirect()->route('workarea.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Edit was successful!']);
        
    }

    /**
     * Remove Workarea from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $this->middleware('permission:setting_company');
        $workarea  = Workarea::findOrFail($id);
        $workarea->delete();
        return redirect()->route('workarea.index',['id'=> $request->company_id])->with(['success' => 'Delete was successful!']);

    }
    

    public function getItem($id = null)
    {
        if($id){
            return Workarea::where('company_id',$id)->get()->pluck('name', 'id');
        }else{
            return Workarea::get()->pluck('name', 'id');
        }
    }


    public function approval($id)
    {
       
        $this->middleware('permission:setting_company');
        $id = Hashids::decode($id);

        $workarea = Workarea::findOrFail($id['0']);
        $company  = Company::findOrFail($workarea->company_id);
        $approval = DB::table('approval_logistics')
        ->select('approval_logistics.*','users.name as name')
        ->leftJoin('users', 'users.id', '=', 'approval_logistics.user_id')
        ->where('approval_logistics.location_id', $id['0'])
        ->orderBy('approval_logistics.step','ASC')
        ->get();

        return view('master.workarea.approval', compact('workarea','company','approval'));
    }

    public function approval_store(Request $request)
    {
        $this->middleware('permission:setting_company');
        $delete = ApprovalLogistic::where('location_id', $request->location_id)->delete();
        $dataApproval = [];
        $user = $request->get('user_id');

        for($i=0;$i < count($user);$i++) {
            $dataApproval[] = [
                'user_id'       => $request->get('user_id')[$i],
                'step'          => $request->get('step')[$i],
                'location_id'   => $request->location_id,
            ];
        }
        ApprovalLogistic::insert($dataApproval);
        return redirect()->route('workarea.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Config was successful!']);
    }


}
