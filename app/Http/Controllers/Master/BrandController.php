<?php

namespace App\Http\Controllers\Master;

use App\Models\MasterBrand;
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

class BrandController extends Controller
{
    /**
     * Display a listing of Brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        return view('master.brand.index');
    }

    public function datatables()
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        $result = DB::table('master_item_brands');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('master.brands.edit', $result->id)."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
            $url_delete = "<form class='delete' action='".route('master.brands.delete', ['id' => $result->id])."' method='POST'>
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
     * Show the form for creating new Brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        return view('master.brand.create');
    }

    /**
     * Store a newly created Brands in storage.
     *
     * @param  \App\Http\Requests\StoreBrandssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        $cekItem = MasterBrand::where('name',strtoupper($request->get('name')))->count();
        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{
            $data = $request->all();
            if($request->get('status')) $data['status'] = 1;
            else $data['status'] = 0;
            
            $data['name'] = strtoupper($request->get('name'));
            $data['created_by'] = Auth::user()->id;

            $items = MasterBrand::create($data);

            return redirect()->route('master.brands.index')->with(['success' => 'Add was successful!']);
        }
    }


    /**
     * Show the form for editing Brands.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }

        $items = MasterBrand::findOrFail($id);

        return view('master.brand.edit', compact('items'));
    }
    

    /**
     * Update Brands in storage.
     *
     * @param  \App\Http\Requests\  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        $cekItem = MasterBrand::where('name',strtoupper($request->get('name')))->where('id','!=',$id)->count();
        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data silahkan cek melalui Fitur Pencarian']);
        }else{
            $data = $request->all();
            if($request->get('status')) $data['status'] = 1;
            else $data['status'] = 0;
            
            $data['name'] = strtoupper($request->get('name'));
            $data['updated_by'] = Auth::user()->id;
            $items = MasterBrand::findOrFail($id);
            $items->update($data);

            return redirect()->route('master.brands.index')->with(['success' => 'Edit was successful!']);
        
        }
    }

    /**
     * Remove Brands from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }

        $items  = MasterBrand::findOrFail($id);
        $items->delete();
      
        return redirect()->route('master.brands.index')->with(['success' => 'Delete was successful!']);

    }

    public function delete(Request $request)
    {

        if (! Gate::allows('master_brand')) {
            return abort(401);
        }

        $items  = MasterBrand::findOrFail($request->id);
        $items->delete();
      
        return redirect()->route('master.brands.index')->with(['success' => 'Delete was successful!']);

    }


    public function export()
    {

        $query = DB::table('master_item_brands')
        ->select('*')
        ->orderBy('id','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('master.item_brands.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
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
        if (! Gate::allows('master_brand')) {
            return abort(401);
        }
        return view('master.brand.upload');
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
                        return MasterBrand::firstOrCreate([
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
                return redirect()->route('master.brands.index')->with(['success' => 'Success inserting the data..']);
    
            } else {
                return redirect()->route('master.brands.upload')->with(['error' =>' File is a '.$extension.' file.!! Please upload a valid xls/csv file..!!']);
            }
        }
    }

}
