<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class InventoryTransferIn extends Model
{



    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_transfer_in';
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
    'status',
    'created_by',
    'publish',
    'received_date',
    'received',
    'transfer_out_id',
    'type',
    'type_status'];

    public function searchableAs()
    {
        return 'inventory_transfer_in';
    }

    public static function getById($id){
        $query = DB::table('inventory_transfer_in')
        ->select(
        'inventory_transfer_out.doc_no as out_doc_no',
        'inventory_transfer_out.created_at AS created_at_wto',
        'inventory_transfer_out.operator AS operator_wto',
        'inventory_transfer_out.type AS type_wto',
        'inventory_transfer_in.*',
        'locations.company_id AS companyID',
        'locations.name AS location',
        'locations.address AS locationAddress',
        'locations.telp AS locationTelp',
        'companies.name AS company',
        'companies.alias AS companyCode',
        'companies.logo AS companyLogo',
        'comAsal.name AS comAsal',
        'comAsal.alias AS comAsalCode',
        'comAsal.logo AS comAsalLogo',
        'asalll.name AS lokasiasal'
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_in.location_id')
        ->leftJoin('inventory_transfer_out', 'inventory_transfer_out.id', '=', 'inventory_transfer_in.transfer_out_id')
        ->leftJoin('locations as asalll', 'asalll.id', '=', 'inventory_transfer_out.location_id')
        ->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
        ->leftJoin('companies as comAsal', 'comAsal.id', '=', 'asalll.company_id')
        ->where('inventory_transfer_in.id', $id)
        ->first();

        return $query;

    }


    public static function getData($request, $company = null, $location = null){

        $query = DB::table('inventory_transfer_in')
        ->select(
            'inventory_transfer_in.*',
            'users.name AS created',
            'inventory_transfer_out.doc_no AS doc_no_wto',
            'inventory_transfer_out.created_at AS created_at_wto'
        )
        ->leftJoin('locations', 'locations.id', '=', 'inventory_transfer_in.location_id')
        ->leftJoin('users', 'users.id', '=', 'inventory_transfer_in.created_by')
        ->leftJoin('inventory_transfer_out','inventory_transfer_out.id','=','inventory_transfer_in.transfer_out_id')
        ->orderBy('inventory_transfer_in.created_at','DESC')
        ->when(!empty($company), function ($query) use ($company) {
            return $query->where('locations.company_id', $company);
        })
        ->when(!empty($location), function ($query) use ($location) {
            return $query->where('locations.id',$location);
        })
        ->when(!empty($request['location_id']), function ($query) use ($request){
            return $query->where('inventory_transfer_in.location_id',$request['location_id']);
        })
        ->when(isset($request['type']), function ($query) use ($request){
            return $query->where('inventory_transfer_in.type', $request['type']);
        })
        ->when(!empty($request['amp;start_date']), function ($query) use ($request) {
            $start = date("Y-m-d",strtotime($request['amp;start_date']));
            $end   = date("Y-m-d",strtotime($request['amp;end_date']."+1 day"));
            return $query->whereBetween('inventory_transfer_in.created_at', [$start , $end]);
        });
        return $query;
    }


}
