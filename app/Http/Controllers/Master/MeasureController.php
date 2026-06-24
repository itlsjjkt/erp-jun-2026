<?php

namespace App\Http\Controllers\Master;

use App\Models\MasterMeasure;
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

class MeasureController extends Controller
{
    /**
     * Display a listing of Measures.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }
        return view('master.measure.index');
    }

    public function datatables()
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }
        $result = DB::table('measures');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('master.measures.edit',  $result->id)."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('master.measures.delete', ['id' => $result->id])."' method='POST'>
                                 ".csrf_field()."
                                <button type='submit' class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
            return
                '<div class="btn-group">'
                 .$url_edit .$url_delete.
                '</div>';
        }) 
        ->addColumn('status', function ($result){
            if($result->status=='1'){
                return "<span class='badge badge-success'>Aktif</span>";
            }else{
                return "<span class='badge badge-danger'>Non Aktif</span>";
            }
        })
        ->editColumn('updated_at', function ($result) {
            return $result->updated_at ? with(new Carbon($result->updated_at))->format('m/d/Y') : '';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);

    }

    /**
     * Show the form for creating new Measures.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }
        return view('master.measure.create');
    }

    /**
     * Store a newly created Measures in storage.
     *
     * @param  \App\Http\Requests\StoreMeasuressRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }
        $cekItem = MasterMeasure::where('name',strtoupper($request->get('name')))->count();
        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{
            $data = $request->all();
            $data['created_by'] = Auth::user()->id;
            $data['name']       = strtoupper($request->get('name'));

            $items = MasterMeasure::create($data);

            return redirect()->route('master.measures.index')->with(['success' => 'Add was successful!']);
        }
    }


    /**
     * Show the form for editing Measures.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }

        $items = MasterMeasure::findOrFail($id);

        return view('master.measure.edit', compact('items'));
    }
    

    /**
     * Update Measures in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }
        $cekItem = MasterMeasure::where('name',strtoupper($request->get('name')))->where('id','!=',$id)->count();
        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{
            $data = $request->all();
            if ($request->get('status'))  $data['status'] = 1;
            else $data['status'] = 0;
            
            $data['updated_by'] = Auth::user()->id;
            $data['name']       = strtoupper($request->get('name'));

            $items = MasterMeasure::findOrFail($id);
            $items->update($data);

            return redirect()->route('master.measures.index')->with(['success' => 'Edit was successful!']);
        }   
    }

    /**
     * Remove Measures from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }

        $items  = MasterMeasure::findOrFail($id);
        $items->delete();
      
        return redirect()->route('master.measures.index')->with(['success' => 'Delete was successful!']);

    }

    public function delete(Request $request)
    {

        if (! Gate::allows('master_measure')) {
            return abort(401);
        }

        $items  = MasterMeasure::findOrFail($request->id);
        $items->delete();
      
        return redirect()->route('master.measures.index')->with(['success' => 'Delete was successful!']);

    }


    public function export()
    {

        $query = DB::table('measures')
        ->select('*')
        ->orderBy('id','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('master.item_measures.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
          
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('Master-Brand'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'ID'            => $inv->id,
                    'Nama'          => $inv->name,
                    'Status'        => ($inv->status == '1' ? 'Aktif' : 'Non Aktif')
                ];
            });
        }

    }


    public function upload()
    {
        if (! Gate::allows('master_measure')) {
            return abort(401);
        }

        return view('master.measure.upload');
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
                        return MasterMeasure::create([
                            'name'   => strtoupper($line['name']),
                            'created_by'    =>  Auth::user()->id,
                            'status' => 1
                        ]);
                    });
                } catch (\Exception $e) {
                    return redirect()->back()
                    ->withInput($request->input())  
                    ->withErrors(['Terdapat Template yang belum sesuai']);
                }
                return redirect()->route('master.measures.index')->with(['success' => 'Success inserting the data..']);
    
            } else {
                return redirect()->route('master.measures.upload')->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
            }
        }
    }


}
