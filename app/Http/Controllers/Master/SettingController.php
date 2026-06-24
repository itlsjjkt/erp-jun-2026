<?php

namespace App\Http\Controllers\Master;

use App\Models\SpbOperator;
use App\Models\Workarea;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    
    use UploadTrait;

    function __construct()
    {
        $this->middleware('permission:master_setting');
    }

    // SPB
    public function index()
    {
        $operator = SpbOperator::get();
        return view('master.setting.index',compact('operator'));
    }

    public function spbStore(Request $request)
    {
       
        SpbOperator::truncate();
        
        $operator = $request->get('name');
        $dataOperator = [];
        for($i=0;$i < count($operator);$i++) {
                if($request->get('sign_exist')[$i] == 'new' ){
                    $image = $request->file('sign')[$i];
                    $name = Str::slug($request->input('name')[$i]).'_'.time();
                    $folder = '/uploads/images/';
                    $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
                    $this->uploadOne($image, $folder, 'public', $name);
                }else{
                    if (isset($request->file('sign')[$i])) {
                        $image = $request->file('sign')[$i];
                        $name = Str::slug($request->input('name')[$i]).'_'.time();
                        $folder = '/uploads/images/';
                        $filePath = $folder . $name. '.' . $image->getClientOriginalExtension();
                        $this->uploadOne($image, $folder, 'public', $name);
                    }else{
                        $filePath = $request->get('sign_exist')[$i];
                    }
                }
       
            $dataOperator[] = [
                'name'   => $request->get('name')[$i],
                'sign'   =>  $filePath
            ];
        }
        
        SpbOperator::insert($dataOperator);
        return redirect()->route('master.setting.index')->with(['success' => 'Config was successful!']);
    }



}
