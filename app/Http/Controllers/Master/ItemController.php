<?php

namespace App\Http\Controllers\Master;

use App\Models\MasterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Rap2hpoutre\FastExcel\FastExcel;

class ItemController extends Controller
{
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */

    function __construct()
    {
        $this->middleware('permission:master_item', ['only' => ['index','create','store','edit','update','delete']]);

    }

    public function index()
    {
        return view('master.item.index');
    }

    public function datatables()
    {
        $result = DB::table('master_items');

       return  DataTables::of($result)
        ->addColumn('action', function ($result) {
            $url_edit = "<a href='".route('master.items.edit', Hashids::encode($result->id))."' title='".trans('Edit')."' data-toggle='tooltip' class='btn btn-outline'><span class='ti-pencil icon-lg'></span> </a>";  
          
            
            $url_delete = "<form class='delete' action='".route('master.items.delete', ['id' => $result->id])."' method='POST'>
                                 ".csrf_field()."
                                <button type='submit' class='btn btn-outline text-danger' title='".trans('app.delete_title')."' data-toggle='tooltip'><i class='ti-trash icon-lg'></i></button>
                            </form>";
         
            if(isAdministrator() || isAdministratorCompany()){
                return
                '<div class="btn-group">'
                 .$url_edit .$url_delete.
                '</div>';
            }else{
                return '';
            }
        })->addColumn('status', function ($result){
            if($result->status==1){
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
     * Show the form for creating new Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $type_data = array('ASSET CONSUMABLE','ASSET NON CONSUMABLE','JASA');

        $type = array();
        $no   = 1;
        foreach($type_data as $val){
            $type[$val]= $val;
            $no++;
        }

        return view('master.item.create',compact('type'));
    }

    /**
     * Store a newly created Items in storage.
     *
     * @param  \App\Http\Requests\StoreItemssRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      
        $cekItem = MasterItem::where('name',strtoupper($request->get('name')))->count();
        $cekKode = MasterItem::where('code',strtoupper($request->get('code')))->count();

        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data pada Field Nama silahkan cek melalui Fitur Pencarian']);
        }elseif($cekKode){
            return redirect()->back()
            ->withInput($request->input())  
            ->withErrors(['Terdapat Duplikasi Data pada Field Kode silahkan cek melalui Fitur Pencarian']);
        }else{

            $data = $request->all();
            $data['created_by'] = Auth::user()->id;
            $data['name']       = strtoupper($request->get('name'));
            $data['code']       = strtoupper($request->get('code'));
            $items = MasterItem::create($data);

            return redirect()->route('master.items.index')->with(['success' => 'Add was successful!']);
        }
    }


    /**
     * Show the form for editing Items.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       
        $id = Hashids::decode($id);

        $type_data = array('ASSET CONSUMABLE','ASSET NON CONSUMABLE','JASA');

        $type = array();
        $no   = 1;
        foreach($type_data as $val){
            $type[$val]= $val;
            $no++;
        }
        $items = MasterItem::findOrFail($id['0']);

        return view('master.item.edit', compact('items','type'));
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
       
        $cekItem = MasterItem::where('name',strtoupper($request->get('name')))->where('id','!=',$id)->count();
        $cekKode = MasterItem::where('code',strtoupper($request->get('code')))->where('id','!=',$id)->count();

        if($cekItem){
            return redirect()->back()
                ->withInput($request->input())  
                ->withErrors(['Terdapat Duplikasi Data Field Name silahkan cek melalui Fitur Pencarian']);
        }
        elseif($cekKode){
            return redirect()->back()
            ->withInput($request->input())  
            ->withErrors(['Terdapat Duplikasi Data pada Field Kode silahkan cek melalui Fitur Pencarian']);
        }else{
            $data = $request->all();
            if ($request->get('status')) {
                $data['status'] = 1;
            } else {
                $data['status'] = 0;
            }
            $data['updated_by'] = Auth::user()->id;
            $data['name']       = strtoupper($request->get('name'));
            $data['code']       = strtoupper($request->get('code'));
            $items = MasterItem::findOrFail($id);
            $items->update($data);
            return redirect()->route('master.items.index')->with(['success' => 'Edit was successful!']);
        }
    }

    public function delete(Request $request)
    {

        $items  = MasterItem::findOrFail($request->id);
        $items->delete();
      
        return redirect()->route('master.items.index')->with(['success' => 'Delete was successful!']);

    }

    /**
     * Delete all selected Items at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
       
        if ($request->input('ids')) {
            $entries = MasterItem::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }

    public function getItem()
    {
        return MasterItem::get()->pluck('name', 'id');
    }

    public function getItemDetail($id)
    {
        return MasterItem::where('id',$id)->first();
    }


    public function export()
    {
        $query = DB::table('master_items')
        ->select('*')
        ->orderBy('name','ASC')
        ->get();

        if( $query->isEmpty() ){
            return redirect()->route('master.items.index')->with(['error' => 'Tidak terdapat data untuk di Export']);
        }else{
         

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="your_name.xls"');
            header('Cache-Control: max-age=0');
            return (new FastExcel($query))->download('Master-Item-'.date('d-m-Y').'.xlsx', function ($inv) {
                return [
                    'ID'            => $inv->id,
                    'Kode'          => $inv->code,
                    'Nama'          => $inv->name,
                    'Level 1'       => $inv->type,
                    'Status'        => ($inv->status == '1' ? 'Aktif' : 'Non Aktif')
                ];
            });
        }

    }

}
