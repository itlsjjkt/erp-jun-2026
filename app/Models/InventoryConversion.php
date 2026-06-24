<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryConversion extends Model
{

    

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_conversions';
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
    protected $fillable = ['id','doc_no','location_id','status','created_by','operator','publish'];

    public function searchableAs()
    {
        return 'inventory_conversions';
    }

    public static function getById($id){
        $query = DB::table('inventory_conversions')
        ->select(
        'inventory_conversions.*',
        'locations.name AS location','locations.address AS locationAddress','locations.telp AS locationTelp','companies.name AS company','companies.logo AS companyLogo'
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_conversions.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->where('inventory_conversions.id', $id)
        ->first();
        
        return $query;
        
    }

    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_conversions')
        ->select(
            'inventory_conversions.*', 
            'users.name AS created',
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_conversions.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_conversions.created_by')
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('locations.id',$location);
        })
        ->when(!empty($request['location_id']), function ($query) use ($request){
            return $query->where('inventory_conversions.location_id',$request['location_id']);
        }) 
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_conversions.created_at', [$start , $end]);
        });
        return $query;
    }

}
