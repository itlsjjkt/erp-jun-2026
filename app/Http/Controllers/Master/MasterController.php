<?php

namespace App\Http\Controllers\Master;

use App\Models\Province;
use App\Models\Regency;
use App\Models\District;
use App\Models\Village;
use App\Models\MasterMeasure;
use App\Models\Inventory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Auth;

class MasterController extends Controller
{


    public function getMeasure()
    {
        return MasterMeasure::orderBy('name', 'ASC')->pluck('name', 'name');
    }

    public function getProvince()
    {
        return Province::pluck('name', 'id');
    }

    public function getRegency($pid)
    {
        return Regency::where('province_id', $pid)->pluck('name', 'id');
    }

    public function getDistricts($pid)
    {
        return District::where('regency_id', $pid)->pluck('name', 'id');
    }

    public function getVillages($pid)
    {
        return Village::where('district_id', $pid)->pluck('name', 'id');
    }



    public function getDistrict($pid,Request $request)
    {
        if ($request->has('q')) {
            $data = District::search($request->q)
                ->where('regency_id', $pid)
                ->get();
            $result = array();
            foreach ($data as $val) {
                $result[] = array('id' => $val->id, 'name' => $val->name);
            }
            return response()->json($result);
        }
    }

    public function getVillage($pid,Request $request)
    {
        if ($request->has('q')) {
            $data = Village::search($request->q)
                ->where('district_id', $pid)
                ->get();
            $result = array();
            foreach ($data as $val) {
                $result[] = array('id' => $val->id, 'name' => $val->name);
            }
            return response()->json($result);
        }
    }



    public function loadDataUsers(Request $request, $id = null)
    {
        if ($request->has('q')) {
            $query = DB::table('users')
            ->select('users.name','users.id')
            ->where('users.name', 'ilike','%'.$request->q.'%')
            ->when(!empty($id), function ($query) use ($id) {
                $query->where('company_id', $id);
            })
            ->get();

            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }


    public function searchUsers(Request $request,$id = null)
    {
        if ($request->has('q')) {
            $query = DB::table('users')
            ->select('name','users.id')
            ->where('name', 'ilike','%'.$request->q.'%')
            ->when(!empty($id), function ($query) use ($id) {
                $query->where('company_id', $id);
            })
            ->get();
            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }

    public function searchEmployees(Request $request,$id=null)
    {
        if ($request->has('q')) {
            $query = DB::table('employees')
            ->select('employees.name','employees.user_id')
            ->where('employees.name', 'ilike','%'.$request->q.'%')
            ->when(!empty($id), function ($query) use ($id) {
                $query->where('company_id', $id);
            })
            ->get();
            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->user_id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }

    public function searchDepartments(Request $request, $id =null)
    {
        if ($request->has('q')) {
            $query = DB::table('departments')
            ->select('departments.name','departments.id')
            ->where('departments.name', 'ilike','%'.$request->q.'%')
            ->when(!empty($id), function ($query) use ($id) {
                $query->where('company_id', $id);
            })
            ->get();
            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }

    public function loadDataOperator($id)
    {
            $query = DB::table('spb_operators')
            ->select('*')
            ->where('id', $id)
            ->first();
            return response()->json($query);
    }

    public function loadDataExpedition($id)
    {
            $query = DB::table('expeditions')
            ->select('*')
            ->where('is_handcarry',false)
            ->where('id', $id)
            ->first();
            return response()->json($query);
    }
    
    public function loadDataHandCarry($id)
    {
            $query = DB::table('expeditions')
            ->select('*')
            ->where('is_handcarry',true)
            ->where('id', $id)
            ->first();
            return response()->json($query);
    }



    public function loadUsersPurchasing(Request $request)
    {
        if ($request->has('q')) {
            $query = DB::table('users')
            ->select('users.name','users.id')
            ->where('users.name', 'ilike','%'.$request->q.'%')
            ->where('type', 4)
            ->get();

            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }

    public function loadCheckerPc(Request $request)
    {
        if ($request->has('q')) {
            $query = DB::table('users')
            ->select('users.name','users.id')
            ->where('users.name', 'ilike','%'.$request->q.'%')
            ->get();

            $result = array();
            foreach ($query as $val) {
                $result[] = array('id' => $val->id, 'name' =>$val->name);
            }
            return response()->json($result);
        }
    }


    public function generate($table){

        $data = Inventory::whereNull('uuid')->get();

        try{
            DB::beginTransaction();
            foreach($data as $item){
                $datas['uuid'] = Str::uuid();
                $item->update($datas);
            }
            DB::commit();
        }
        catch(Exception $e){
            DB::rollback();
        }
    }


}
