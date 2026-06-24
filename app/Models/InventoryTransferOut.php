<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryTransferOut extends Model
{



    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_transfer_out';
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
    protected $fillable = ['id',
    'doc_no',
    'location_id',
    'location_destination',
    'status',
    'created_by',
    'operator',
    'publish',
    'approved_at',
    'approved_by',
    'file',
    'type'];

    public function searchableAs()
    {
        return 'inventory_transfer_out';
    }

    public static function getById($id){
        $query = DB::table('inventory_transfer_out')
        ->select(
        'inventory_transfer_out.*',
        'locations.company_id AS companyID',
        'destination.name AS location_destination_name',
        'destination.alias AS location_destination_code',
        'locations.name AS location',
        'locations.address AS locationAddress',
        'locations.telp AS locationTelp',
        'companies.name AS company',
        'companies.alias AS companyCode',
        'companies.logo AS companyLogo',
        'comdestinasi.name AS comdestinasi',
        'comdestinasi.alias AS comdestinasiCode',
        'comdestinasi.logo AS comdestinasiLogo'
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_out.location_id')
        ->leftJoin('locations as destination', 'destination.id', '=', 'inventory_transfer_out.location_destination')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('companies as comdestinasi', 'comdestinasi.id', '=', 'destination.company_id')
        ->where('inventory_transfer_out.id', $id)
        ->first();
        return $query;
    }


    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_transfer_out')
        ->select(
            'inventory_transfer_out.*',
            'users.name AS created',
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_out.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_transfer_out.created_by')
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('locations.id',$location);
        })
        ->when(!empty($request['location_id']), function ($query) use ($request){
            return $query->where('inventory_transfer_out.location_id',$request['location_id']);
        })
        ->when(isset($request['type']), function ($query) use ($request){
            return $query->where('inventory_transfer_out.type', $request['type']);
        })
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_transfer_out.created_at', [$start , $end]);
        })
        ->orderByRaw("CASE WHEN inventory_transfer_out.status = 1 THEN 0 ELSE 1 END")
        ->orderBy('inventory_transfer_out.id', 'desc');
        return $query;
    }

}
