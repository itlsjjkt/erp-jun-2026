<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryReturnIn extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_return_in';
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['doc_no','status','created_at','created_by','operator','inventory_return_out_id','publish','location_id'];

    public function searchableAs()
    {
        return 'inventory_return_in';
    }

    public static function getById($id){
        $query = DB::table('inventory_return_in')
        ->select(
        'inventory_return_in.*', 'inventory_return_out.doc_no AS doc_rot',
        'locations.id AS locationID','locations.alias AS locationCode','companies.alias AS companyCode',
        'locations.name AS location','locations.address AS locationAddress','locations.telp AS locationTelp','companies.name AS company','companies.logo AS companyLogo'
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_return_in.location_id')
        ->leftJoin('inventory_return_out', 'inventory_return_out.id', '=', 'inventory_return_in.inventory_return_out_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('inventory_return_in.id', $id)
        ->first();
        return $query;
    }


    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_return_in')
        ->select(
            'inventory_return_in.*', 
            'users.name AS created',
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_return_in.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_return_in.created_by')
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('locations.id',$location);
        })
        ->when(!empty($request['location_id']), function ($query) use ($request){
            return $query->where('inventory_return_in.location_id',$request['location_id']);
        }) 
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_return_in.created_at', [$start , $end]);
        });
        return $query;
    }


}
