<?php

namespace App\Http\Controllers\Purchasing;

use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Auth;

class ItemlatestpriceController extends Controller

{
    /**
     * Display a listing of Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('purchase.itemslatestprice.index');
    }

    public function datatables()
    {
	
	$result = DB::select("SELECT distinct on (pi.product_id) ss.name as suppliername, mip.name as productname, 
			mip.part_number as partnumber, mib.name as merek, pi.price, po.doc_no as po_no ,po.created_at as tanggal, 
			po.updated_at
            from po left join po_items as pi on pi.po_id = po.id 
			left join suppliers as ss on po.supplier_id = ss.id 
			left join master_item_products as mip on pi.product_id = mip.id 
			left join master_item_brands as mib on mip.brand_id = mib.id where po.status in (2,4,5)
			order by pi.product_id desc, tanggal desc");
        return  DataTables::of($result)
	->make(true);
	}
}


