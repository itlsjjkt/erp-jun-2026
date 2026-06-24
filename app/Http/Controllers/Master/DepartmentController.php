<?php

namespace App\Http\Controllers\Master;

use App\Models\Company;
use App\Models\Department;
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

class DepartmentController extends Controller
{

    function __construct()
    {
    }

    /**
     * Display a listing of Department.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      
        $this->middleware('permission:setting_company');
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);
        
        return view('master.department.index', compact('company'));
    }


    public function datatables($company_id)
    {
      
        $result = DB::table('departments')
        ->where('company_id',$company_id)
        ->orderBy('status','DESC')
        ->orderBy('isdpm','DESC');

       return  DataTables::of($result)
        ->editColumn('status', function ($result) {
            if($result->status == 1){
                $status = "<span class='badge badge-success'>Active</span>";
            }else{
                $status = "<span class='badge badge-danger'>Deactive</span>";
            }
            return $status;
        })
        ->editColumn('isdpm', function ($result) {
            if($result->isdpm == 1){
                $isdpm = "<span class='badge badge-success'>True</span>";
            }else{
                $isdpm = "<span class='badge badge-danger'>False</span>";
            }
            return $isdpm;
        }) 
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('department.edit', [ $result->id, 'company_id' => Hashids::encode($result->company_id)])."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('department.destroy', [$result->id, 'company_id' => Hashids::encode($result->company_id)])."' method='POST'>
                                <input name='_method' type='hidden' value='DELETE'>
                                ".csrf_field()."
                                <button class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                 .$url_edit .$url_delete.
                '</div>';
        }) 
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->escapeColumns([])
        ->make(true);

    }



    /**
     * Show the form for creating new Department.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->middleware('permission:setting_company');
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);

        return view('master.department.create', compact('company'));
    }

    /**
     * Store a newly created Department in storage.
     *
     * @param  \App\Http\Requests\StoreDepartmentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('setting_company')) {
            return abort(401);
        }
        $data = $request->all();
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;
        $department = Department::create( $data );

        return redirect()->route('department.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Add was successful!']);
    }


    /**
     * Show the form for editing Department.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        $this->middleware('permission:setting_company');
        $company_id = Hashids::decode($request->company_id);
        $company = Company::findOrFail($company_id['0']);

        $department = Department::findOrFail($id);

        return view('master.department.edit', compact('department','company'));
    }

    /**
     * Update Department in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
        $this->middleware('permission:setting_company');
        $department = Department::findOrFail($id);
        $data = $request->all();
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;
        $department->update($data);

        return redirect()->route('department.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Edit was successful!']);
        
    }

    /**
     * Remove Department from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $this->middleware('permission:setting_company');
        $department  = Department::findOrFail($id);
        $department->delete();
        return redirect()->route('department.index',['id'=> $request->company_id])->with(['success' => 'Delete was successful!']);

    }

    public function getItem($id = null)
    {
        if($id != null){
            return Department::where('company_id',$id)->get()->pluck('name', 'id');
        }else{
            return Department::get()->pluck('name', 'id');
        }
    }


    public function export(Request $request)
    {

        $this->middleware('permission:setting_company');
        $id = Hashids::decode($request->id);
        $company_id = $id['0'];
        
        $query = Department::where('departments.company_id',$company_id)
        ->orderBy('departments.name','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('department.index',['id'=> $request->id])->with(['error' => 'Tidak terdapat data untuk di Export']);
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


    public function import(Request $request){
        $this->middleware('permission:setting_company');
        if($request->isMethod('get')){
            $id     = Hashids::decode($request->id);
            $company= Company::findOrFail($id['0']);
            return view('master.department.upload', compact('company'));
        }else{

            $this->validate($request, array(
                'file'      => 'required'
            ));
        
            if($request->hasFile('file')){
                $extension = File::extension($request->file->getClientOriginalName());
                if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                    
                    $path = $request->file('file')->store('excel-files');
                    try{
                        $users = (new FastExcel)->import(storage_path('app/public/' . $path), function ($line) use ($request) {
                            return Department::firstOrCreate([
                                'code'          => strtoupper($line['code']),
                                'name'          => strtoupper($line['name']),
                                'company_id'    => $request->company_id,
                                'created_by'    => Auth::user()->id,
                                'status'        => 1
                            ]);
                        });
                    } catch (\Exception $e) {
                        return redirect()->back()
                        ->withInput($request->input())  
                        ->withErrors(['error' => $e->getMessage()]);
                    }
                    return redirect()->route('department.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Success inserting the data..']);
        
                } else {
                    return redirect()->route('department.upload',['id'=> Hashids::encode($request->company_id)])->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
                }
            }
        }

    }


}
