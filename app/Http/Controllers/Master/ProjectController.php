<?php

namespace App\Http\Controllers\Master;

use App\Models\Company;
use App\Models\MasterItem;
use App\Models\Project;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use File;
use Auth;
use Rap2hpoutre\FastExcel\FastExcel;

class ProjectController extends Controller
{

    function __construct()
    {
    }

    /**
     * Display a listing of Project.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->middleware('permission:setting_company');
        return view('master.project.index');
    }


    public function datatables()
    {
      
        $result = DB::table('projects');

       return  DataTables::of($result)
        ->editColumn('status', function ($result) {
            if($result->status == 1){
                $status = "<span class='badge badge-success'>Active</span>";
            }else{
                $status = "<span class='badge badge-danger'>Deactive</span>";
            }
            return $status;
        }) 
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('master.project.edit', $result->id)."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('master.project.destroy', $result->id)."' method='POST'>
                            <input name='_method' type='hidden' value='DELETE'>
                            ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                 .$url_edit .$url_delete.
                '</div>';
        }) 
        ->editColumn('category', function ($result) {
            if($result->category != null){
                $category = json_decode($result->category);
                $item = MasterItem::whereIn('id',$category)->get();
                $category_name = [];

                foreach ($item as $val){
                    $category_name []= $val->name;
                }
                return implode(',',$category_name );

            }else return false;
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->escapeColumns([])
        ->make(true);

    }



    /**
     * Show the form for creating new Project.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->middleware('permission:setting_company');
        $category = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")->orderBy('level2','ASC')->get()->pluck('level2', 'id');
        return view('master.project.create', compact('category'));
    }

    /**
     * Store a newly created Project in storage.
     *
     * @param  \App\Http\Requests\StoreProjectsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      
        $this->middleware('permission:setting_company');
        $data = $request->all();
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;
        
        $project = Project::create($data);

        return redirect()->route('master.project.index')->with(['success' => 'Add was successful!']);
    }


    /**
     * Show the form for editing Project.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->middleware('permission:setting_company');
        $category = MasterItem::selectRaw("CONCAT (name,'  (', code,')') as level2,id")->orderBy('level2','ASC')->get()->pluck('level2', 'id');
        $project = Project::findOrFail($id);

        return view('master.project.edit', compact('project','category'));
    }

    /**
     * Update Project in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id,Request $request)
    {
        $this->middleware('permission:setting_company');
        $data = $request->all();
        $project = Project::findOrFail($id);
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;
        $project->update($data);

        return redirect()->route('master.project.index')->with(['success' => 'Edit was successful!']);
        
    }

    /**
     * Remove Project from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->middleware('permission:setting_company');
        $project  = Project::findOrFail($id);
        $project->delete();
        return redirect()->route('project.index')->with(['success' => 'Delete was successful!']);

    }


    public function getItem($id = null)
    {
        if($id != null){
            return Project::where('company_id',$id)->get()->pluck('name', 'id');
        }else{
            return Project::get()->pluck('name', 'id');
        }
    }


    public function export(Request $request)
    {

        $query = Project::orderBy('projects.name','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('master.project.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
           

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('Master-Cost-Centre'.date('d-m-Y').'.xlsx', function ($query ) {
                return [
                    'Kode'          => $query->code,
                    'Nama'          => $query ->name,
                    'Status'          => ($query->status == '1' ? 'Aktif' : 'Non Aktif')
                ];
            });
        }

    }



}
