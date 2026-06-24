<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryTtb extends Model
{



    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_ttbs';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    protected $guarded  = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function searchableAs()
    {
        return 'inventory_ttbs';
    }

    public function location(){
        return $this->belongsTo(Workarea::class,'location_id');
    }

    public function department(){
        return $this->belongsTo(Department::class,'department_id');
    }


    public function project(){
        return $this->belongsTo('App\Models\Project','project_id');
    }

    public function cost_center(){
        return $this->belongsTo(CostCentre::class,'coa','code');
    }

    public static function getById($id){
        $query = DB::table('inventory_ttbs')
        ->select(
        'inventory_ttbs.*',
        'departments.name AS department',
        'projects.name AS project',
        'locations.name AS location',
        'locations.address AS locationAddress',
        'locations.telp AS locationTelp',
        'companies.name AS company',
        'companies.logo AS companyLogo',
        'companies.fax AS companyFax',
        'companies.address AS companyAddress',
        'companies.telp AS companyTelp'
        )
        ->leftJoin('departments', 'departments.id', '=', 'inventory_ttbs.department_id')
        ->leftJoin('projects', 'projects.id', '=', 'inventory_ttbs.project_id')
        ->leftJoin('locations', 'locations.id', '=', 'inventory_ttbs.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('inventory_ttbs.id', $id)
        ->first();

        return $query;

    }

    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_ttbs')
        ->select(
            'inventory_ttbs.*',
            'users.name AS created',
            'projects.name AS project',
            'departments.name AS department'
        )
        ->leftJoin('projects', 'projects.id', '=', 'inventory_ttbs.project_id')
        ->leftJoin('departments', 'departments.id', '=', 'inventory_ttbs.department_id')
        ->leftJoin('locations','locations.id','=','inventory_ttbs.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_ttbs.created_by')
        ->where('inventory_ttbs.is_local', false)
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('inventory_ttbs.location_id',$location);
        })
        ->when(!empty($request['department_id']), function ($query) use ($request) {
            return $query->where('inventory_ttbs.department_id',$request['department_id']);
        })
        ->when(!empty($request['amp;project_id']), function ($query) use ($request) {
            return $query->where('inventory_ttbs.project_id',$request['amp;project_id']);
        })
        ->when(!empty($request['amp;location_id']), function ($query) use ($request){
            return $query->where('inventory_ttbs.location_id',$request['amp;location_id']);
        })
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_ttbs.created_at', [$start , $end]);
        });

        return $query;

    }

}
