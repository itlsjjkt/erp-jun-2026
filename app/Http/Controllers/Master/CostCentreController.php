<?php

namespace App\Http\Controllers\Master;

use App\Models\Department;
use App\Models\CostCentre;
use App\Models\Company;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;
use File;

use Rap2hpoutre\FastExcel\FastExcel;

class CostCentreController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:setting_company');
    }
    
    /**
     * Display a listing of Department.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
     
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);
        
        return view('master.cost_centre.index', compact('company'));
    }


    public function datatables($company_id)
    {
        $result = DB::table('cost_centre')
        ->select('cost_centre.*')
        ->where('company_id',$company_id);

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
            $url_edit = "<a href='".route('cost_centre.edit', [ $result->id, 'company_id' => Hashids::encode($result->company_id)])."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('cost_centre.destroy', [ $result->id, 'company_id' => Hashids::encode($result->company_id)])."' method='POST'>
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
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);
        return view('master.cost_centre.create', compact('company'));
    }

    /**
     * Store a newly created Department in storage.
     *
     * @param  \App\Http\Requests\StoreDepartmentsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $data = $request->all();
        $data['created_by'] = Auth::user()->id;
        $data['name'] = strtoupper($request->get('name'));
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;

        CostCentre::create($data);

        return redirect()->route('cost_centre.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Add was successful!']);
    }


    /**
     * Show the form for editing Department.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
     
        $company_id = Hashids::decode($request->company_id);
        $company = Company::findOrFail($company_id['0']);
        $cost_centre = CostCentre::findOrFail($id);

        return view('master.cost_centre.edit', compact('cost_centre','company'));
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
       
        $cost_centre = CostCentre::findOrFail($id);
        $data = $request->all();
        
        $data['name'] = strtoupper($request->get('name'));
        $data['updated_by'] = Auth::user()->id;
        if($request->get('status')) $data['status'] = 1;
        else $data['status'] = 0;

        $cost_centre->update($data);

        return redirect()->route('cost_centre.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Edit was successful!']);
        
    }

    /**
     * Remove Department from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
      
        $cost_centre  = CostCentre::findOrFail($id);
        $cost_centre->delete();
        return redirect()->route('cost_centre.index',['id'=> $request->company_id])->with(['success' => 'Delete was successful!']);
    }


    public function getItem()
    {
        return CostCentre::get()->pluck('name', 'id');
    }

    public function export(Request $request)
    {

        $id = Hashids::decode($request->get('id'));
        $company_id = $id['0'];
        
        $query = DB::table('cost_centre')
        ->select('cost_centre.*')
        ->where('company_id',$company_id )
        ->orderBy('name','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('cost_centre.index',['id'=> $request->id])->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('Master-Cost-Center'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'ID'            => $inv->id,
                    'Kode'          => $inv->code,
                    'Nama'          => $inv->name,
                    'Status'        => ($inv->status == '1' ? 'Aktif' : 'Non Aktif')
                ];
            });
        }

    }


    public function upload(Request $request)
    {
       
        $id = Hashids::decode($request->id);
        $company = Company::findOrFail($id['0']);
        return view('master.cost_centre.upload', compact('company'));
    }

    public function import(Request $request){
        //validate the xls file
        $this->validate($request, array(
            'file'      => 'required'
        ));
    
        if($request->hasFile('file')){
            $extension = File::extension($request->file->getClientOriginalName());
            if ($extension == "xlsx" || $extension == "xls" || $extension == "csv") {
                
                $path = $request->file('file')->store('excel-files');
                try{
                    $users = (new FastExcel)->import(storage_path('app/public/' . $path), function ($line) {
                        return CostCentre::firstOrCreate([
                            'code'          => strtoupper($line['code']),
                            'name'          => strtoupper($line['name']),
                            'created_by'    =>  Auth::user()->id,
                            'status'        => 1
                        ]);
                    });
                } catch (\Exception $e) {
                    return redirect()->back()
                    ->withInput($request->input())  
                    ->withErrors(['Terdapat Template yang belum sesuai']);
                }
                return redirect()->route('cost_centre.index',['id'=> Hashids::encode($request->company_id)])->with(['success' => 'Success inserting the data..']);
    
            } else {
                return redirect()->route('cost_centre.upload',['id'=> Hashids::encode($request->company_id)])->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
            }
        }
    }

}
